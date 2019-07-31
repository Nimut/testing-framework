<?php
namespace Nimut\Testbase\Tests\Functional\ViewHelpers;

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

class RenderChildrenViewHelperTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'fluid',
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/testbase',
    ];

    /**
     * @test
     */
    public function ownTypoScripFileIsUsedInFrontendRequest()
    {
        $this->importDataSet('ntf://Database/pages.xml');

        if (class_exists('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper')) {
            $this->setUpFrontendRootPage(1, ['EXT:testbase/Tests/Functional/Fixtures/TypoScript/Page.ts']);
        } else {
            // load template with view helper for pre v9 fluid
            $this->setUpFrontendRootPage(1, ['EXT:testbase/Tests/Functional/Fixtures/TypoScript/PageTYPO3CMSFluid.ts']);
        }

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());
        $this->assertSame('foo', trim($response->getContent()));
    }
}
