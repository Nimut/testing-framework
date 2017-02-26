<?php
namespace Nimut\TestingFramework\Bootstrap;

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

use TYPO3\CMS\Core\Core\Bootstrap as CoreBootstrap;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;

/**
 * Unit Test Bootstrap for TYPO3 >= 8.0
 */
class Bootstrap extends AbstractBootstrap
{
    /**
     * Defines the constant ORIGINAL_ROOT for the path to the original TYPO3 document root
     *
     * @return void
     */
    protected function defineOriginalRootPath()
    {
        parent::defineOriginalRootPath();
        $this->defineBaseConstants();
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = $this->getClassLoaderFilepath();

        $classLoader = require $classLoaderFilepath;

        $bootstrap = CoreBootstrap::getInstance();
        $bootstrap->initializeClassLoader($classLoader)
            ->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI)
            ->baseSetup();
    }

    /**
     * Initializes core cache handling
     *
     * @return void
     */
    protected function initializeCachingHandling()
    {
        $bootstrap = CoreBootstrap::getInstance();
        $bootstrap->disableCoreCache()
            ->initializeCachingFramework();

        if (!CoreBootstrap::usesComposerClassLoading()) {
            // Dump autoload info if in non composer mode
            ClassLoadingInformation::dumpClassLoadingInformation();
            ClassLoadingInformation::registerClassLoadingInformation();
        }
    }

    /**
     * Defines some constants and sets the environment variable TYPO3_CONTEXT
     *
     * @return void
     */
    protected function setTypo3Context()
    {
        parent::setTypo3Context();
        $this->defineBaseConstants();
    }
}
