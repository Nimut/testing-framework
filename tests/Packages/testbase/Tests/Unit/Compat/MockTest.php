<?php
namespace Nimut\Testbase\Tests\Unit\Compat;

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

use Nimut\Testbase\Mock;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class MockTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getMockReturnsMock()
    {
        /** @var Mock|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMock(Mock::class, array('dummy'), array('foo'));

        $this->assertSame('foo', $subjectMock->getAProtectedProperty());
    }

    /**
     * @test
     */
    public function getAccessibleMockReturnsAccessibleMock()
    {
        /** @var Mock|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $subjectAccessibleMock */
        $subjectAccessibleMock = $this->getAccessibleMock(Mock::class, array('dummy'));
        $subjectAccessibleMock->_set('aProtectedProperty', 'foo');

        $this->assertSame('foo', $subjectAccessibleMock->getAProtectedProperty());
    }
}
