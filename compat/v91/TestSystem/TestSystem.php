<?php
namespace Nimut\TestingFramework\v91\TestSystem;

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

use Nimut\TestingFramework\TestSystem\AbstractTestSystem;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TestSystem extends AbstractTestSystem
{
    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = $this->getClassLoaderFilepath();

        $classLoader = require $classLoaderFilepath;

        $this->bootstrap->initializeClassLoader($classLoader)
            ->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI)
            ->baseSetup()
            ->loadConfigurationAndInitialize(true);

        $this->bootstrap->loadTypo3LoadedExtAndExtLocalconf(true)
            ->initializeBackendRouter()
            ->setFinalCachingFrameworkCacheConfiguration()
            ->unsetReservedGlobalVariables();
    }

    /**
     * Loads TCA and ext_tables.php files from extensions
     *
     * @return void
     */
    protected function loadExtensionConfiguration()
    {
        $this->prepareDatabaseConnection();
        $this->bootstrap->loadBaseTca(true)->loadExtTables(true);
    }

    /**
     * Initializes default database connection
     *
     * @see https://forge.typo3.org/issues/83770
     * @return void
     */
    protected function prepareDatabaseConnection()
    {
        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_log');
    }
}
