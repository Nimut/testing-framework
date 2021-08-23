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
use PHPUnit\Framework\MockObject\MockObject;

class RenderChildrenViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var RenderChildrenViewHelper|MockObject
     */
    protected $viewHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMockBuilder(RenderChildrenViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function renderReturnsExpectedResult()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')->willReturn('foo');

        $this->assertSame('foo', $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function setArgumentsUnderTestCanBeCalled()
    {
        $this->setArgumentsUnderTest(
            $this->viewHelper,
            [
                'value' => 'foo',
            ]
        );

        $this->assertSame('foo', $this->viewHelper->render());
    }
}
