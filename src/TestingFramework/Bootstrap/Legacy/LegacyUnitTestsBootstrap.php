<?php
namespace Nimut\TestingFramework\Bootstrap\Legacy;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It was taken from the TYPO3 CMS project (www.typo3.org).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */

use Nimut\TestingFramework\Bootstrap\UnitTestsBootstrap;
use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * This file is defined as bootstrap configuration in UnitTests.xml and called by PHPUnit
 * before instantiating the test suites. It must also be called on CLI
 * with PHPUnit parameter --bootstrap if executing single test case classes.
 *
 * Example: call whole unit test suite
 * - cd /var/www/t3master/foo  # Document root of TYPO3 CMS sources (location of index.php)
 * - vendor/bin/phpunit -c vendor/nimut/testing-framework/res/Configuration/UnitTests.xml \
 *     typo3conf/ext/example_extension/Tests/Unit
 */
class LegacyUnitTestsBootstrap extends UnitTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors();
        $this->defineSitePath();
        $this->setTypo3Context();
        $this->createNecessaryDirectoriesInDocumentRoot();
        $this->includeAndStartCoreBootstrap();
        $this->initializeConfiguration();
        $this->initializeCachingHandling();
        $this->initializePackageManager();
        $this->registerNtfStreamWrapper();
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions.
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = __DIR__ . '/../../../../../../autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            $classLoaderFilepath = __DIR__ . '/../../../../.Build/vendor/autoload.php';
            if (!file_exists($classLoaderFilepath)) {
                $this->exitWithMessage('ClassLoader can\'t be loaded.'
                    . ' Tried to find "' . $classLoaderFilepath . '".'
                    . ' Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
            }
        }

        $bootstrap = Bootstrap::getInstance();
        if (!method_exists($bootstrap, 'disableCoreAndClassesCache')) {
            $classLoader = require $classLoaderFilepath;
            $bootstrap->initializeClassLoader($classLoader)
                ->baseSetup();
        } else {
            $bootstrap->baseSetup()
                ->initializeClassLoader();
        }
    }

    /**
     * Initializes core cache handling
     *
     * @return void
     */
    protected function initializeCachingHandling()
    {
        $bootstrap = Bootstrap::getInstance();
        if (!method_exists($bootstrap, 'disableCoreAndClassesCache')) {
            $bootstrap->disableCoreCache()
                ->initializeCachingFramework()
                ->ensureClassLoadingInformationExists();
        } else {
            $bootstrap->disableCoreAndClassesCache()
                ->initializeCachingFramework()
                ->initializeClassLoaderCaches();
        }
    }
}
