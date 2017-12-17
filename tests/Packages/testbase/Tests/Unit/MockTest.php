<?php
namespace Nimut\Testbase\Tests\Unit;

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
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MockTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getAccessibleMockReturnsAccessibleMock()
    {
        /** @var Mock|MockObject|AccessibleMockObjectInterface $subjectAccessibleMock */
        $subjectAccessibleMock = $this->getAccessibleMock('Nimut\\Testbase\\Mock', ['dummy']);
        $subjectAccessibleMock->_set('aProtectedProperty', 'foo');

        $this->assertSame('foo', $subjectAccessibleMock->getAProtectedProperty());
    }
}
