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

class FunctionalTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = array(
        'typo3conf/ext/testbase',
    );

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
        $this->importDataSet('ntf://Database/pages_language_overlay.xml');

        $this->assertSame(7, $this->getDatabaseConnection()->exec_SELECTcountRows('*', 'pages'));
        $this->assertSame(2, $this->getDatabaseConnection()->exec_SELECTcountRows('*', 'pages_language_overlay'));
    }

    /**
     * @test
     */
    public function frontendIsRendered()
    {
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet('ntf://Database/tt_content.xml');
        $this->setUpFrontendRootPage(1, array('ntf://TypoScript/JsonRenderer.ts'));

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $sections = $response->getResponseSections();
        $defaultSection = array_shift($sections);
        $structure = $defaultSection->getStructure();

        $this->assertTrue(is_array($structure['pages:1']['__contents']['tt_content:1']));
    }
}
