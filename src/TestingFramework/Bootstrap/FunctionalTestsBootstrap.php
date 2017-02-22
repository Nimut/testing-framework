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

/**
 * This file is defined in FunctionalTests.xml and called by phpunit
 * before instantiating the test suites, it must also be included
 * with phpunit parameter --bootstrap if executing single test case classes.
 */
class FunctionalTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors()
            ->loadClassFiles()
            ->defineOriginalRootPath()
            ->createNecessaryDirectoriesInDocumentRoot()
            ->addCompatibilityPrerequests();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return FunctionalTestsBootstrap fluent interface
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);

        return $this;
    }

    /**
     * Requires classes the functional test classes extend from or use for further bootstrap.
     * Only files required for "new TestCaseClass" are required here and a general exception
     * that is thrown by setUp() code.
     *
     * @return FunctionalTestsBootstrap fluent interface
     */
    protected function loadClassFiles()
    {
        if (!class_exists('PHPUnit_Framework_TestCase')) {
            $this->exitWithMessage('PHPUnit wasn\'t found. Please check your settings and command.');
        }
        if (!class_exists('Nimut\\TestingFramework\\TestCase\\BaseTestCase')) {
            // PHPUnit is invoked globally, so we need to include the project autoload file
            require_once __DIR__ . '/../../../../../autoload.php';
        }

        return $this;
    }

    /**
     * Defines the constant ORIGINAL_ROOT for the path to the original TYPO3 document root.
     *
     * If ORIGINAL_ROOT already is defined, this method is a no-op.
     *
     * @return FunctionalTestsBootstrap fluent interface
     */
    protected function defineOriginalRootPath()
    {
        if (!defined('ORIGINAL_ROOT')) {
            /** @var string */
            define('ORIGINAL_ROOT', $this->getWebRoot());
        }

        if (!file_exists(ORIGINAL_ROOT . 'typo3/cli_dispatch.phpsh')) {
            $this->exitWithMessage('Unable to determine path to entry script. Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
        }

        return $this;
    }

    /**
     * Creates the following directories in the TYPO3 core:
     * - typo3temp
     *
     * @return FunctionalTestsBootstrap fluent interface
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/tests');
        $this->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/transient');

        return $this;
    }

    protected function addCompatibilityPrerequests()
    {
        if (class_exists('TYPO3\\CMS\\Core\\Tests\\Testbase')) {
            // A null, a tabulator, a linefeed, a carriage return, a substitution, a CR-LF combination
            defined('NUL') ?: define('NUL', chr(0));
            defined('TAB') ?: define('TAB', chr(9));
            defined('LF') ?: define('LF', chr(10));
            defined('CR') ?: define('CR', chr(13));
            defined('SUB') ?: define('SUB', chr(26));
            defined('CRLF') ?: define('CRLF', CR . LF);

            if (!defined('TYPO3_OS')) {
                // Operating system identifier
                // Either "WIN" or empty string
                $typoOs = '';
                if (!stristr(PHP_OS, 'darwin') && !stristr(PHP_OS, 'cygwin') && stristr(PHP_OS, 'win')) {
                    $typoOs = 'WIN';
                }
                define('TYPO3_OS', $typoOs);
            }
        }

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
        mkdir($directory, 0777, true);
        clearstatcache();
        if (!is_dir($directory)) {
            throw new \RuntimeException('Directory "' . $directory . '" could not be created', 1404038665);
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
$bootstrap = new FunctionalTestsBootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
