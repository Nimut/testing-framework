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
use TYPO3\CMS\Core\Package\UnitTestPackageManager;

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
class UnitTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors()
            ->checkForCliDispatch()
            ->defineSitePath()
            ->setTypo3Context()
            ->createNecessaryDirectoriesInDocumentRoot()
            ->includeAndStartCoreBootstrap()
            ->initializeConfiguration()
            ->finishCoreBootstrap();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);

        return $this;
    }

    /**
     * Checks whether the tests are run using the CLI dispatcher. If so, echos a helpful message and exits with
     * an error code 1.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function checkForCliDispatch()
    {
        if (!defined('TYPO3_MODE')) {
            return $this;
        }

        array_shift($_SERVER['argv']);
        $flatArguments = implode(' ', $_SERVER['argv']);
        echo 'Please run the unit tests using the following command:' . chr(10) .
            sprintf('typo3/../bin/phpunit %s', $flatArguments) . chr(10) .
            chr(10);

        exit(1);
    }

    /**
     * Defines the PATH_site and PATH_thisScript constant and sets $_SERVER['SCRIPT_NAME'].
     *
     * @return UnitTestsBootstrap fluent interface
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

        return $this;
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
     * @return UnitTestsBootstrap fluent interface
     */
    protected function setTypo3Context()
    {
        /** @var string */
        define('TYPO3_MODE', 'BE');
        /** @var string */
        define('TYPO3_cliMode', true);
        putenv('TYPO3_CONTEXT=Testing');

        return $this;
    }

    /**
     * Creates the following directories in the TYPO3 document root:
     * - typo3conf
     * - typo3conf/ext
     * - typo3temp
     * - uploads
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(PATH_site . 'uploads');
        $this->createDirectory(PATH_site . 'typo3temp');
        $this->createDirectory(PATH_site . 'typo3temp/assets');
        $this->createDirectory(PATH_site . 'typo3temp/var/tests');
        $this->createDirectory(PATH_site . 'typo3temp/var/transient');
        $this->createDirectory(PATH_site . 'typo3conf/ext');

        return $this;
    }

    /**
     * Creates the directory $directory (recursively if required).
     *
     * If $directory already exists, this method is a no-op.
     *
     * @param string $directory absolute path of the directory to be created
     * @throws \RuntimeException
     * @return void
     */
    protected function createDirectory($directory)
    {
        if (is_dir($directory)) {
            return;
        }

        @mkdir($directory, 0777, true);
        clearstatcache();
        if (!is_dir($directory)) {
            throw new \RuntimeException('Directory "' . $directory . '" could not be created', 1423043755);
        }
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = __DIR__ . '/../../../../../autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            $this->exitWithMessage('ClassLoader can\'t be loaded. Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
        }
        $classLoader = require $classLoaderFilepath;

        $bootstrap = Bootstrap::getInstance();
        $reflection = new \ReflectionMethod($bootstrap, 'initializeClassLoader');
        if (empty($reflection->getNumberOfParameters())) {
            $bootstrap->baseSetup()->initializeClassLoader();
        } else {
            if (is_callable([$bootstrap, 'setRequestType'])) {
                $bootstrap->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI);
            }
            $bootstrap->initializeClassLoader($classLoader)->baseSetup();
        }

        return $this;
    }

    /**
     * Provides the default configuration in $GLOBALS['TYPO3_CONF_VARS'].
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function initializeConfiguration()
    {
        $configurationManager = new ConfigurationManager();
        $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

        // Avoid failing tests that rely on HTTP_HOST retrieval
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';

        return $this;
    }

    /**
     * Finishes the last steps of the Core Bootstrap.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function finishCoreBootstrap()
    {
        $bootstrap = Bootstrap::getInstance();
        if (is_callable([$bootstrap, 'disableCoreCache'])) {
            $bootstrap->disableCoreCache()
                ->initializeCachingFramework()
                ->initializePackageManagement(UnitTestPackageManager::class)
                ->ensureClassLoadingInformationExists();
        } else {
            $bootstrap->disableCoreAndClassesCache()
                ->initializeCachingFramework()
                ->initializeClassLoaderCaches()
                ->initializePackageManagement(UnitTestPackageManager::class);
        }

        return $this;
    }

    /**
     * Echo out a text message and exit with error code
     *
     * @param string $message
     */
    protected function exitWithMessage($message)
    {
        echo $message . PHP_EOL;
        exit(1);
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new UnitTestsBootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
