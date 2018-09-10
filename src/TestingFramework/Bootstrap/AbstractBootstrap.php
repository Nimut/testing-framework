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

use Nimut\TestingFramework\File\NtfStreamWrapper;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap as CoreBootstrap;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Package\UnitTestPackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractBootstrap
{
    /**
     * @var CoreBootstrap
     */
    protected $bootstrap;

    /**
     * AbstractBootstrap constructor.
     *
     * @param CoreBootstrap $bootstrap
     */
    public function __construct(CoreBootstrap $bootstrap = null)
    {
        putenv('TYPO3_CONTEXT=Testing');
        $this->bootstrap = (null !== $bootstrap) ? $bootstrap : CoreBootstrap::getInstance();
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

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE | SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        CoreBootstrap::initializeClassLoader($classLoader);
        CoreBootstrap::baseSetup();
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
     * Bootstraps the system for functional tests
     *
     * @return void
     */
    public function bootstrapFunctionalTestSystem()
    {
        $this->enableDisplayErrors();
        $this->createNecessaryDirectoriesInDocumentRoot();
        $this->defineOriginalRootPath();
    }

    /**
     * Bootstraps the system for unit tests
     *
     * @return void
     */
    public function bootstrapUnitTestSystem()
    {
        $this->enableDisplayErrors();
        $this->createNecessaryDirectoriesInDocumentRoot();
        $this->defineSitePath();
        $this->setTypo3Context();
        $this->includeAndStartCoreBootstrap();
        $this->initializeConfiguration();
        $this->initializePackageManager();
        $this->dumpAutoloadFiles();
        $this->registerNtfStreamWrapper();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return void
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);
    }

    /**
     * Creates the following directories in the TYPO3 document root:
     * - typo3conf/ext
     * - typo3temp/assets
     * - typo3temp/var/tests
     * - typo3temp/var/transient
     * - uploads
     *
     * @return void
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $webRoot = $this->getWebRoot();
        $this->createDirectory($webRoot . 'typo3conf/ext');
        $this->createDirectory($webRoot . 'typo3temp/assets');
        $this->createDirectory($webRoot . 'typo3temp/var/tests');
        $this->createDirectory($webRoot . 'typo3temp/var/transient');
        $this->createDirectory($webRoot . 'uploads');
    }

    /**
     * Defines the constant ORIGINAL_ROOT for the path to the original TYPO3 document root
     *
     * @return void
     */
    protected function defineOriginalRootPath()
    {
        if (!defined('ORIGINAL_ROOT')) {
            /** @var string */
            define('ORIGINAL_ROOT', $this->getWebRoot());
        }

        if (!file_exists(ORIGINAL_ROOT . 'typo3/index.php')) {
            $this->exitWithMessage(
                'Unable to determine path to entry script.'
                . ' Please check your path or set an environment variable \'TYPO3_PATH_ROOT\' to your root path.'
            );
        }
    }

    /**
     * Returns the absolute path to the TYPO3 document root
     *
     * @return string the TYPO3 document root using Unix path separators
     */
    protected function getWebRoot()
    {
        if (getenv('TYPO3_PATH_ROOT')) {
            $webRoot = getenv('TYPO3_PATH_ROOT');
        } elseif (getenv('TYPO3_PATH_WEB')) {
            $webRoot = getenv('TYPO3_PATH_WEB');
        } else {
            $webRoot = getcwd();
        }

        return rtrim(str_replace('\\', '/', $webRoot), '/') . '/';
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
        clearstatcache();
        if (is_dir($directory)) {
            return;
        }

        if (!@mkdir($directory, 0777, true)) {
            // Wait a couple of microseconds to prevent multiple derectory access due to parallel testing
            usleep(mt_rand(1000, 2000));
            if (!@mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException('Directory "' . $directory . '" could not be created', 1423043755);
            }
        }
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

    /**
     * Defines the PATH_site, PATH_thisScript constant and sets $_SERVER['SCRIPT_NAME'].
     *
     * @return void
     */
    protected function defineSitePath()
    {
        /** @var string */
        define('PATH_site', $this->getWebRoot());
        /** @var string */
        define('PATH_thisScript', PATH_site . 'typo3/index.php');
        $_SERVER['SCRIPT_NAME'] = PATH_thisScript;

        if (!file_exists(PATH_thisScript)) {
            $this->exitWithMessage('Unable to determine path to entry script. Please check your path or set an environment variable \'TYPO3_PATH_ROOT\' to your root path.');
        }
    }

    /**
     * Defines some constants and sets the environment variable TYPO3_CONTEXT
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
    }

    /**
     * Provides the default configuration in $GLOBALS['TYPO3_CONF_VARS']
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
     * Initializes a package manager for tests that activates all packages by default
     *
     * @return void
     */
    protected function initializePackageManager()
    {
        $cache = new PhpFrontend('cache_core', new NullBackend('production', []));
        $packageManager = CoreBootstrap::createPackageManager(UnitTestPackageManager::class, $cache);

        GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
        ExtensionManagementUtility::setPackageManager($packageManager);
    }

    /**
     * Dump autoload info if in non composer mode
     *
     * @return void
     */
    protected function dumpAutoloadFiles()
    {
        if (!Environment::isComposerMode()) {
            ClassLoadingInformation::dumpClassLoadingInformation();
            ClassLoadingInformation::registerClassLoadingInformation();
        }
    }

    /**
     * Registers the NtfStreamWrapper for ntf:// protocol
     *
     * @return void
     */
    protected function registerNtfStreamWrapper()
    {
        NtfStreamWrapper::register();
    }

    /**
     * Defines a list of basic constants that are used by GeneralUtility and other
     * helpers during tests setup. Those are sanitized in SystemEnvironmentBuilder
     * to be not defined again.
     *
     * @return void
     * @see SystemEnvironmentBuilder::defineBaseConstants()
     */
    protected function defineBaseConstants()
    {
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

    /**
     * Checks and returns the file path of the autoload.php
     *
     * @return string
     */
    protected function getClassLoaderFilepath()
    {
        $classLoaderFilepath = __DIR__ . '/../../../../../autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            if (file_exists(__DIR__ . '/../../../.Build/vendor/autoload.php')) {
                $classLoaderFilepath = __DIR__ . '/../../../.Build/vendor/autoload.php';
            } elseif (file_exists($this->getWebRoot() . '../vendor/autoload.php')) {
                $classLoaderFilepath = $this->getWebRoot() . '../vendor/autoload.php';
            } else {
                $this->exitWithMessage(
                    'ClassLoader can\'t be loaded.'
                    . ' Tried to find "' . $classLoaderFilepath . '".'
                    . ' Please check your path or set an environment variable \'TYPO3_PATH_ROOT\' to your root path.'
                );
            }
        }

        return $classLoaderFilepath;
    }
}
