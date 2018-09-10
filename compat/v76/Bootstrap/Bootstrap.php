<?php
namespace Nimut\TestingFramework\v76\Bootstrap;

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

use Nimut\TestingFramework\Bootstrap\AbstractBootstrap;
use TYPO3\CMS\Core\Package\UnitTestPackageManager;

/**
 * Unit Test Bootstrap for TYPO3 7.6
 */
class Bootstrap extends AbstractBootstrap
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
            ->baseSetup();
    }

    /**
     * Initializes core cache handling
     *
     * @return void
     */
    protected function initializeCachingHandling()
    {
        $this->bootstrap->disableCoreCache()
            ->initializeCachingFramework();
    }

    /**
     * Initializes a package manager for tests that activates all packages by default
     *
     * @return void
     */
    protected function initializePackageManager()
    {
        $this->initializeCachingHandling();
        $this->bootstrap->initializePackageManagement(UnitTestPackageManager::class);
    }

    /**
     * Dump autoload info if in non composer mode
     *
     * @return void
     */
    protected function dumpAutoloadFiles()
    {
        $this->bootstrap->ensureClassLoadingInformationExists();
    }
}
