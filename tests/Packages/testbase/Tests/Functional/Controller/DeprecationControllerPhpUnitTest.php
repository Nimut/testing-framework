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
use PHPUnit\Framework\Error\Deprecated;

class DeprecationControllerPhpUnitTest extends FunctionalTestCase
{
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
    public function someDeprecatedMethodThrowsPhpUnitDeprecation()
    {
        $this->expectException(Deprecated::class);
        $this->expectExceptionMessage('This is some deprecated method that will never be removed.');

        $this->deprecationController->someDeprecatedMethod();
    }
}
