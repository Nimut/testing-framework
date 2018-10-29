<?php
namespace Nimut\TestingFramework\TestSystem;

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

use Nimut\TestingFramework\Exception\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;

class TestSystem extends AbstractTestSystem
{
    /**
     * Extensions that are always loaded
     *
     * @var array
     */
    protected $defaultActivatedCoreExtensions = [
        'core',
        'backend',
        'frontend',
        'extbase',
        'fluid',
        'install',
        'recordlist',
    ];

    /**
     * Populate $GLOBALS['TYPO3_DB'] and create test database
     *
     * @throws Exception
     * @return void
     */
    protected function setUpTestDatabase()
    {
        // The TYPO3 core misses to reset its internal connection state
        // This means we need to reset all connections to ensure database connection can be initialized
        $closure = \Closure::bind(function () {
            foreach (ConnectionPool::$connections as $connection) {
                $connection->close();
            }
            ConnectionPool::$connections = [];
        }, null, ConnectionPool::class);
        $closure();

        parent::setUpTestDatabase();
    }
}
