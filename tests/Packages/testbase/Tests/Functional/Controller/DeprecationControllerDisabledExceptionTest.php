<?php
namespace Nimut\Testbase\Tests\Functional\Controller;

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

use Nimut\Testbase\Controller\DeprecationController;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

class DeprecationControllerDisabledExceptionTest extends FunctionalTestCase
{
    /**
     * @var bool
     */
    protected $disableDeprecations = true;

    /**
     * @var DeprecationController
     */
    protected $deprecationController;

    protected function setUp()
    {
        parent::setUp();

        $this->deprecationController = new DeprecationController();
    }

    /**
     * @test
     */
    public function someDeprecatedMethodThrowsNoExceptionIfDisabled()
    {
        $this->deprecationController->someDeprecatedMethod();
        $this->assertTrue(true);
    }
}
