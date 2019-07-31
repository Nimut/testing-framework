<?php
namespace Nimut\Testbase\Tests\Unit\ViewHelpers;

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

use Nimut\Testbase\ViewHelpers\RenderChildrenTYPO3CMSFluidViewHelper;
use Nimut\Testbase\ViewHelpers\RenderChildrenViewHelper;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use PHPUnit\Framework\MockObject\MockObject;

class RenderChildrenViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var RenderChildrenTYPO3CMSFluidViewHelper|MockObject
     */
    protected $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        if (class_exists('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper')) {
            $renderChildrenViewHelperClass = RenderChildrenViewHelper::class;
        } else {
            $renderChildrenViewHelperClass = RenderChildrenTYPO3CMSFluidViewHelper::class;
        }

        $this->viewHelper = $this->getMockBuilder($renderChildrenViewHelperClass)
            ->setMethods(['renderChildren'])
            ->getMock();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderReturnsExpectedResult()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')->willReturn('foo');

        $this->assertSame('foo', $this->viewHelper->render());
    }
}
