<?php

namespace Nimut\Testbase\Tests\Functional;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FunctionalTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/testbase',
    ];

    /**
     * @test
     */
    public function extTablesIsLoaded()
    {
        $this->assertStringContainsString(
            'lib.testbase = TEXT',
            $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup']
        );
    }

    /**
     * @test
     */
    public function adminUserIsLoggedIn()
    {
        $backendUser = $this->setUpBackendUserFromFixture(1);

        $this->assertTrue($backendUser->isAdmin());
    }

    /**
     * @test
     */
    public function loadPagesDatabaseFixtures()
    {
        $this->importDataSet('ntf://Database/pages.xml');

        $this->assertSame(7, $this->getDatabaseConnection()->selectCount('*', 'pages'));
    }

    /**
     * @test
     */
    public function routesAreInitialized()
    {
        $router = GeneralUtility::makeInstance(Router::class);
        $uriBuilder = new UriBuilder($router);
        $uri = $uriBuilder->buildUriFromRoute('login', [], UriBuilder::ABSOLUTE_PATH);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertNotEmpty((string)$uri);
    }

    /**
     * @test
     */
    public function frontendIsRendered()
    {
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet('ntf://Database/tt_content.xml');
        $this->setUpFrontendRootPage(1, ['ntf://TypoScript/JsonRenderer.ts']);

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $sections = $response->getResponseSections();
        $defaultSection = array_shift($sections);
        $structure = $defaultSection->getStructure();

        $this->assertTrue(is_array($structure['pages:1']['__contents']['tt_content:1']));
    }
}
