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

use Nimut\Testbase\ViewHelpers\RenderChildrenViewHelper;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;

class RenderChildrenViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var RenderChildrenViewHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(RenderChildrenViewHelper::class, array('renderChildren'));
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
