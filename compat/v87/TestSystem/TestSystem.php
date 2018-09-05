<?php
namespace Nimut\TestingFramework\v87\TestSystem;

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
use Nimut\TestingFramework\TestSystem\AbstractTestSystem;

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
            ->loadConfigurationAndInitialize(true)
            ->loadTypo3LoadedExtAndExtLocalconf(true)
            ->initializeBackendRouter()
            ->setFinalCachingFrameworkCacheConfiguration()
            ->unsetReservedGlobalVariables()
            ->defineLoggingAndExceptionConstants();
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] reusing an existing database with all tables truncated
     *
     * @return void
     */
    protected function initializeTestDatabase()
    {
        $this->bootstrap->initializeTypo3DbGlobal();

        parent::initializeTestDatabase();
    }

    /**
     * Loads TCA and ext_tables.php files from extensions
     *
     * @return void
     */
    protected function loadExtensionConfiguration()
    {
        $this->bootstrap->loadBaseTca(true)->loadExtTables(true);
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] and create test database
     *
     * @throws Exception
     * @return void
     */
    protected function setUpTestDatabase()
    {
        $this->bootstrap->initializeTypo3DbGlobal();

        parent::setUpTestDatabase();
    }
}
