<?php
namespace Nimut\TestingFramework\Bootstrap;

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

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;

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
class UnitTestsBootstrap extends AbstractTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors();
        $this->defineBaseConstants();
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
     * Defines the PATH_site and PATH_thisScript constant and sets $_SERVER['SCRIPT_NAME'].
     *
     * @return void
     */
    protected function defineSitePath()
    {
        /** @var string */
        define('PATH_site', $this->getWebRoot());
        /** @var string */
        define('PATH_thisScript', PATH_site . 'typo3/cli_dispatch.phpsh');
        $_SERVER['SCRIPT_NAME'] = PATH_thisScript;

        if (!file_exists(PATH_thisScript)) {
            $this->exitWithMessage('Unable to determine path to entry script. Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
        }
    }

    /**
     * Returns the absolute path the TYPO3 document root.
     *
     * @return string the TYPO3 document root using Unix path separators
     */
    protected function getWebRoot()
    {
        if (getenv('TYPO3_PATH_WEB')) {
            $webRoot = getenv('TYPO3_PATH_WEB');
        } else {
            $webRoot = getcwd();
        }

        return rtrim(strtr($webRoot, '\\', '/'), '/') . '/';
    }

    /**
     * Defines TYPO3_MODE, TYPO3_cliMode and sets the environment variable TYPO3_CONTEXT.
     *
     * @return void
     */
    protected function setTypo3Context()
    {
        /** @var string */
        define('TYPO3_MODE', 'BE');
        /** @var string */
        define('TYPO3_cliMode', true);
        // Disable TYPO3_DLOG
        define('TYPO3_DLOG', false);
        putenv('TYPO3_CONTEXT=Testing');
    }

    /**
     * Creates the following directories in the TYPO3 document root:
     * - typo3conf
     * - typo3conf/ext
     * - typo3temp
     * - uploads
     *
     * @return void
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(PATH_site . 'uploads');
        $this->createDirectory(PATH_site . 'typo3conf/ext');
        $this->createDirectory(PATH_site . 'typo3temp/assets');
        $this->createDirectory(PATH_site . 'typo3temp/var/tests');
        $this->createDirectory(PATH_site . 'typo3temp/var/transient');
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions.
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = __DIR__ . '/../../../../../autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            $classLoaderFilepath = __DIR__ . '/../../../.Build/vendor/autoload.php';
            if (!file_exists($classLoaderFilepath)) {
                $this->exitWithMessage('ClassLoader can\'t be loaded.'
                    . ' Tried to find "' . $classLoaderFilepath . '".'
                    . ' Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
            }
        }

        $classLoader = require $classLoaderFilepath;

        $bootstrap = Bootstrap::getInstance();
        $bootstrap->initializeClassLoader($classLoader)
            ->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI)
            ->baseSetup();
    }

    /**
     * Provides the default configuration in $GLOBALS['TYPO3_CONF_VARS'].
     *
     * @return void
     */
    protected function initializeConfiguration()
    {
        $configurationManager = new ConfigurationManager();
        $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

        // Avoid failing tests that rely on HTTP_HOST retrieval
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
    }

    /**
     * Initializes core cache handling
     *
     * @return void
     */
    protected function initializeCachingHandling()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->disableCoreCache()
            ->initializeCachingFramework();

        if (!Bootstrap::usesComposerClassLoading()) {
            // Dump autoload info if in non composer mode
            ClassLoadingInformation::dumpClassLoadingInformation();
            ClassLoadingInformation::registerClassLoadingInformation();
        }
    }

    /**
     * Initializes a package manager for tests that activates all packages by default
     *
     * @return void
     */
    protected function initializePackageManager()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->initializePackageManagement('TYPO3\\CMS\\Core\\Package\\UnitTestPackageManager');
    }
}
