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
     * @test
     */
    public function adminUserIsLoggedIn()
    {
        $backendUser = $this->setUpBackendUserFromFixture(1);

        $this->assertTrue($backendUser->isAdmin());
    }
}
