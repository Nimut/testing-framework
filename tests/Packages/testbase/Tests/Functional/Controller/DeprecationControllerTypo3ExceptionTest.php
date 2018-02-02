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
use TYPO3\CMS\Core\Error\Exception;

class DeprecationControllerTypo3ExceptionTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $configurationToUseInTestInstance = [
        'SYS' => [
            'exceptionalErrors' => E_ALL & ~(E_STRICT | E_NOTICE | E_COMPILE_WARNING | E_COMPILE_ERROR | E_CORE_WARNING | E_CORE_ERROR | E_PARSE | E_ERROR | E_WARNING | E_USER_ERROR | E_USER_NOTICE | E_USER_WARNING),
        ],
    ];

    /**
     * @var DeprecationController
     */
    protected $deprecationController;

    protected function setUp()
    {
        if (!defined('ORIGINAL_ROOT')) {
            $this->markTestSkipped('Functional tests must be called through phpunit on CLI');
        }

        $this->deprecationController = new DeprecationController();
    }

    /**
     * @test
     */
    public function someDeprecatedMethodThrowsDeprecationMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/^TYPO3 Deprecation Notice/');

        parent::setUp();

        $this->deprecationController->someDeprecatedMethod();
    }
}
