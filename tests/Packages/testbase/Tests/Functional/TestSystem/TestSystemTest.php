<?php

namespace Nimut\Testbase\Tests\Functional\TestSystem;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TestSystemTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/testbase',
    ];

    /**
     * @var array
     */
    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/testbase/Tests/Functional/Fixtures/Database/tx_testbase_foo.sql' => 'typo3conf/ext/testbase/ext_tables.sql',
    ];

    /**
     * Prevent initial setUp
     */
    protected function setUp(): void
    {
        if (!defined('ORIGINAL_ROOT')) {
            $this->markTestSkipped('Functional tests must be called through phpunit on CLI');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->pathsToLinkInTestInstance as $destination) {
            GeneralUtility::rmdir($this->getInstancePath() . ltrim($destination, '/'));
        }
    }

    /**
     * @test
     * @group destructive
     */
    public function databaseExceptionContainsErrorMessage()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1505058450);
        $this->expectExceptionMessageMatches('/Invalid default value for \'testdate\'/');

        parent::setUp();
    }
}
