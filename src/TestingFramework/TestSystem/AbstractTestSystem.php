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
use Nimut\TestingFramework\File\NtfStreamWrapper;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Service\SqlExpectedSchemaService;
use TYPO3\CMS\Install\Service\SqlSchemaMigrationService;

abstract class AbstractTestSystem
{
    /**
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * Extensions that are always loaded
     *
     * @var array
     */
    protected $defaultActivatedCoreExtensions = array(
        'core',
        'backend',
        'frontend',
        'lang',
        'extbase',
        'install',
    );

    /**
     * Configuration to set as default
     *
     * @var array
     */
    protected $defaultConfiguration = array(
        'SYS' => array(
            'caching' => array(
                'cacheConfigurations' => array(
                    'extbase_object' => array(
                        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\NullBackend',
                    ),
                ),
            ),
            'displayErrors' => '1',
            'debugExceptionHandler' => '',
            'encryptionKey' => 'i-am-not-a-secure-encryption-key',
            'isInitialDatabaseImportDone' => true,
            'isInitialInstallationInProgress' => false,
            'setDBinit' => 'SET SESSION sql_mode = \'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY\';',
            'trustedHostsPattern' => '.*',
        ),
    );

    /**
     * Folders that are always created
     *
     * @var array
     */
    protected $defaultFoldersToCreate = array(
        '',
        '/fileadmin',
        '/typo3conf/ext',
        '/typo3temp/var/tests',
        '/typo3temp/var/transient',
        '/uploads',
    );

    /**
     * @var string Identifier calculated from test case class
     */
    protected $identifier;

    /**
     * @var string Absolute path to test system document root
     */
    protected $systemPath;

    /**
     * @param string $identifier Name of test case class
     * @param Bootstrap $bootstrap
     */
    public function __construct($identifier, Bootstrap $bootstrap = null)
    {
        putenv('TYPO3_CONTEXT=Testing');
        $this->bootstrap = $bootstrap === null ? Bootstrap::getInstance() : $bootstrap;
        $this->identifier = substr(sha1($identifier), 0, 7);
        $this->systemPath = ORIGINAL_ROOT . 'typo3temp/var/tests/functional-' . $this->identifier . '/';
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    abstract protected function includeAndStartCoreBootstrap();

    /**
     * Setup creates a test system and database
     *
     * @param array $coreExtensionsToLoad Array of core extensions to load
     * @param array $testExtensionsToLoad Array of test extensions to load
     * @param array $pathsToLinkInTestSystem Array of source => destination path pairs to be linked
     * @param array $configurationToUse Array of TYPO3_CONF_VARS that need to be overridden
     * @param array $additionalFoldersToCreate Array of folder paths to be created
     * @return void
     */
    public function setUp(
        array $coreExtensionsToLoad,
        array $testExtensionsToLoad,
        array $pathsToLinkInTestSystem,
        array $configurationToUse,
        array $additionalFoldersToCreate
    ) {
        $this->registerNtfStreamWrapper();
        $this->setTypo3Context();
        if ($this->recentTestSystemExists()) {
            $this->includeAndStartCoreBootstrap();
            $this->initializeTestDatabase();
            $this->loadExtensionConfiguration();
        } else {
            $this->removeOldSystemIfExists();
            $this->setUpSystemDirectories($additionalFoldersToCreate);
            $this->setUpSystemCoreLinks();
            $this->linkTestExtensionsToSystem($testExtensionsToLoad);
            $this->linkPathsInTestSystem($pathsToLinkInTestSystem);
            $this->setUpLocalConfiguration($configurationToUse);
            $this->setUpPackageStates($coreExtensionsToLoad, $testExtensionsToLoad);
            $this->includeAndStartCoreBootstrap();
            $this->setUpTestDatabase();
            $this->loadExtensionConfiguration();
            $this->createDatabaseStructure();
        }
    }

    /**
     * Returns the calculated identifier
     *
     * @return string
     */
    public function getSystemIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Calculates path to TYPO3 CMS test installation for this test system
     *
     * @return string
     */
    public function getSystemPath()
    {
        return $this->systemPath;
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

        // Ensure TYPO3_PATH_ROOT is pointing to the document root of the test environment
        // It will be evaluated in the TYPO3 bootstrap and a previously set value may interfere here
        putenv('TYPO3_PATH_ROOT=' . rtrim($this->systemPath, '/'));
        $_SERVER['PWD'] = $this->systemPath;
        $_SERVER['argv'][0] = 'index.php';
    }

    /**
     * Checks whether the current test system exists and is younger than 5 minutes
     *
     * @return bool
     */
    protected function recentTestSystemExists()
    {
        if (@file_get_contents($this->systemPath . 'last_run.txt') <= (time() - 300)) {
            return false;
        }

        return true;
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] reusing an existing database with all tables truncated
     *
     * @throws Exception
     * @return void
     */
    protected function initializeTestDatabase()
    {
        $this->bootstrap->initializeTypo3DbGlobal();
        /** @var DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];
        if (!$database->sql_pconnect()) {
            throw new Exception(
                'TYPO3 Fatal Error: The current username, password or host was not accepted when the'
                . ' connection to the database was attempted to be established!',
                1377620117
            );
        }

        $database->setDatabaseName($GLOBALS['TYPO3_CONF_VARS']['DB']['database']);
        $database->sql_select_db();
        foreach ($database->admin_get_tables() as $table) {
            $database->admin_query('TRUNCATE ' . $table['Name'] . ';');
        }
    }

    /**
     * Loads extension configuration from ext_localconf.php and ext_tables.php files
     *
     * @return void
     */
    protected function loadExtensionConfiguration()
    {
        $this->bootstrap->loadExtensionTables(true);
    }

    /**
     * Remove test system folder structure in setUp() if it exists
     *
     * @throws Exception
     * @return void
     */
    protected function removeOldSystemIfExists()
    {
        if (is_dir($this->systemPath)) {
            if (!$this->rmdir($this->systemPath, true)) {
                throw new Exception(
                    'Can not remove folder: ' . $this->systemPath,
                    1376657210
                );
            }
        }
    }

    /**
     * Create folder structure of test system
     *
     * @param array $additionalFoldersToCreate Array of additional folders to be created
     * @throws Exception
     * @return void
     */
    protected function setUpSystemDirectories(array $additionalFoldersToCreate = array())
    {
        $foldersToCreate = array_merge($this->defaultFoldersToCreate, $additionalFoldersToCreate);
        foreach ($foldersToCreate as $folder) {
            $folder = ltrim($folder, '/');

            clearstatcache();
            if (is_dir($this->systemPath . $folder)) {
                continue;
            }

            if (!@mkdir($this->systemPath . $folder, 0777, true) && !is_dir($this->systemPath . $folder)) {
                throw new Exception(
                    'Creating directory failed: ' . $this->systemPath . $folder,
                    1376657189
                );
            }
        }

        // Store the time we created this directory
        file_put_contents($this->systemPath . 'last_run.txt', time());
    }

    /**
     * Link TYPO3 CMS core from original system
     *
     * @throws Exception
     * @return void
     */
    protected function setUpSystemCoreLinks()
    {
        $linksToSet = array(
            ORIGINAL_ROOT . 'typo3' => $this->systemPath . 'typo3',
            ORIGINAL_ROOT . 'index.php' => $this->systemPath . 'index.php',
        );
        foreach ($linksToSet as $from => $to) {
            if (!symlink($from, $to)) {
                throw new Exception(
                    'Creating link failed: from ' . $from . ' to: ' . $to,
                    1376657199
                );
            }
        }
    }

    /**
     * Link test extensions to the typo3conf/ext folder of the system
     *
     * @param array $extensionPaths Contains paths to extensions relative to document root
     * @throws Exception
     * @return void
     */
    protected function linkTestExtensionsToSystem(array $extensionPaths)
    {
        foreach ($extensionPaths as $extensionPath) {
            $absoluteExtensionPath = ORIGINAL_ROOT . $extensionPath;
            if (!is_dir($absoluteExtensionPath)) {
                throw new Exception(
                    'Test extension path ' . $absoluteExtensionPath . ' not found',
                    1376745645
                );
            }
            $destinationPath = $this->systemPath . 'typo3conf/ext/' . basename($absoluteExtensionPath);
            if (!symlink($absoluteExtensionPath, $destinationPath)) {
                throw new Exception(
                    'Can not link extension folder: ' . $absoluteExtensionPath . ' to ' . $destinationPath,
                    1376657142
                );
            }
        }
    }

    /**
     * Link paths inside the test system
     *
     * @param array $pathsToLinkInTestSystem Contains paths as array of source => destination
     * @throws Exception if a source path could not be found
     * @throws Exception on failing creating the symlink
     * @return void
     */
    protected function linkPathsInTestSystem(array $pathsToLinkInTestSystem)
    {
        foreach ($pathsToLinkInTestSystem as $sourcePath => $destinationPath) {
            $sourcePath = $this->systemPath . ltrim($sourcePath, '/');
            if (!file_exists($sourcePath)) {
                throw new Exception(
                    'Path ' . $sourcePath . ' not found',
                    1376745645
                );
            }
            $destinationPath = $this->systemPath . ltrim($destinationPath, '/');
            if (!symlink($sourcePath, $destinationPath)) {
                throw new Exception(
                    'Can not link the path ' . $sourcePath . ' to ' . $destinationPath,
                    1389969623
                );
            }
        }
    }

    /**
     * Create LocalConfiguration.php file in the test system
     *
     * @param array $configurationToMerge
     * @throws Exception
     * @return void
     */
    protected function setUpLocalConfiguration(array $configurationToMerge)
    {
        $originalConfigurationArray = $this->getDatabaseConfiguration();
        if (empty($originalConfigurationArray)) {
            if (file_exists(ORIGINAL_ROOT . 'typo3conf/LocalConfiguration.php')) {
                // Load configuration from original system
                $originalConfigurationArray = require ORIGINAL_ROOT . 'typo3conf/LocalConfiguration.php';
            } else {
                throw new Exception(
                    'Database credentials for functional tests are neither set through environment'
                    . ' variables, and can not be found in an existing LocalConfiguration file',
                    1397406356
                );
            }
        }

        $finalConfigurationArray = require ORIGINAL_ROOT . 'typo3/sysext/core/Configuration/FactoryConfiguration.php';
        $configurationToMerge = array_replace_recursive(
            $this->defaultConfiguration,
            $configurationToMerge
        );
        $finalConfigurationArray['DB'] = $this->setDatabaseName($originalConfigurationArray['DB']);
        $this->mergeRecursiveWithOverrule($finalConfigurationArray, $configurationToMerge);

        $content = '<?php' . chr(10) . 'return '
            . var_export($finalConfigurationArray, true)
            . ';' . chr(10) . '?>';

        if (!$this->writeFile($this->systemPath . 'typo3conf/LocalConfiguration.php', $content)) {
            throw new Exception('Can not write local configuration', 1376657277);
        }
    }

    /**
     * Compile typo3conf/PackageStates.php containing defaultActivatedCoreExtensions, additional core extensions
     * and test extensions to load
     *
     * @param array $coreExtensionsToLoad Additional core extensions to load
     * @param array $testExtensionPaths Paths to extensions relative to document root
     * @throws Exception
     */
    protected function setUpPackageStates(array $coreExtensionsToLoad, array $testExtensionPaths)
    {
        $packageStates = array(
            'packages' => array(),
            'version' => $this->getPackageStatesVersion(),
        );

        // Register default list of extensions and set active
        foreach ($this->defaultActivatedCoreExtensions as $extensionName) {
            $packageStates['packages'][$extensionName] = array(
                'state' => 'active',
                'packagePath' => 'typo3/sysext/' . $extensionName . '/',
                'classesPath' => 'Classes/',
            );
        }

        // Register additional core extensions and set active
        foreach ($coreExtensionsToLoad as $extensionName) {
            if (isset($packageSates['packages'][$extensionName])) {
                throw new Exception(
                    $extensionName . ' is already registered as default core extension to load, no need to load it explicitly',
                    1390913893
                );
            }
            $packageStates['packages'][$extensionName] = array(
                'state' => 'active',
                'packagePath' => 'typo3/sysext/' . $extensionName . '/',
                'classesPath' => 'Classes/',
            );
        }

        // Activate test extensions that have been symlinked before
        foreach ($testExtensionPaths as $extensionPath) {
            $extensionName = basename($extensionPath);
            if (isset($packageSates['packages'][$extensionName])) {
                throw new Exception(
                    $extensionName . ' is already registered as extension to load, no need to load it explicitly',
                    1390913894
                );
            }
            $packageStates['packages'][$extensionName] = array(
                'state' => 'active',
                'packagePath' => 'typo3conf/ext/' . $extensionName . '/',
                'classesPath' => 'Classes/',
            );
        }

        $content = '<?php' . chr(10) . 'return '
            . var_export($packageStates, true)
            . ';' . chr(10) . '?>';

        if (!$this->writeFile($this->systemPath . 'typo3conf/PackageStates.php', $content)) {
            throw new Exception('Can not write PackageStates', 1381612729);
        }
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

        /** @var DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];
        if (!$database->sql_pconnect()) {
            throw new Exception(
                'TYPO3 Fatal Error: The current username, password or host was not accepted when the'
                . ' connection to the database was attempted to be established!',
                1377620117
            );
        }

        $databaseName = $GLOBALS['TYPO3_CONF_VARS']['DB']['database'];
        // Drop database in case a previous test had a fatal and did not clean up properly
        $database->admin_query('DROP DATABASE IF EXISTS `' . $databaseName . '`');
        $createDatabaseResult = $database->admin_query('CREATE DATABASE `' . $databaseName . '`');
        if (!$createDatabaseResult) {
            $user = $GLOBALS['TYPO3_CONF_VARS']['DB']['username'];
            $host = $GLOBALS['TYPO3_CONF_VARS']['DB']['host'];
            throw new Exception(
                'Unable to create database with name ' . $databaseName . '. This is probably a permission problem.'
                . ' For this instance this could be fixed executing'
                . ' "GRANT ALL ON `' . substr($databaseName, 0, -10) . '_ft%`.* TO `' . $user . '`@`' . $host . '`;"',
                1376579070
            );
        }
        $database->setDatabaseName($databaseName);
        // On windows, this still works, but throws a warning, which we need to discard.
        @$database->sql_select_db();
    }

    /**
     * Create tables and import static rows
     *
     * @return void
     */
    protected function createDatabaseStructure()
    {
        /** @var SqlSchemaMigrationService $schemaMigrationService */
        $schemaMigrationService = GeneralUtility::makeInstance('TYPO3\\CMS\\Install\\Service\\SqlSchemaMigrationService');
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var SqlExpectedSchemaService $expectedSchemaService */
        $expectedSchemaService = $objectManager->get('TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService');

        // Raw concatenated ext_tables.sql and friends string
        $expectedSchemaString = $expectedSchemaService->getTablesDefinitionString(true);
        $statements = $schemaMigrationService->getStatementArray($expectedSchemaString, true);
        list($_, $insertCount) = $schemaMigrationService->getCreateTables($statements, true);

        $fieldDefinitionsFile = $schemaMigrationService->getFieldDefinitions_fileContent($expectedSchemaString);
        $fieldDefinitionsDatabase = $schemaMigrationService->getFieldDefinitions_database();
        $difference = $schemaMigrationService->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
        $updateStatements = $schemaMigrationService->getUpdateSuggestions($difference);

        $result = array();
        $updateResult = $schemaMigrationService->performUpdateQueries($updateStatements['add'], $updateStatements['add']);
        if (is_array($updateResult)) {
            $failedStatements = array_intersect_key($updateStatements['add'], $updateResult);
            foreach ($failedStatements as $key => $query) {
                $result[$key] = 'Query "' . $query . '" returned "' . $updateResult[$key] . '"';
            }
        }
        $updateResult = $schemaMigrationService->performUpdateQueries($updateStatements['change'], $updateStatements['change']);
        if (is_array($updateResult)) {
            $failedStatements = array_intersect_key($updateStatements['change'], $updateResult);
            foreach ($failedStatements as $key => $query) {
                $result[$key] = 'Query "' . $query . '" returned "' . $updateResult[$key] . '"';
            }
        }
        $updateResult = $schemaMigrationService->performUpdateQueries($updateStatements['create_table'], $updateStatements['create_table']);
        if (is_array($updateResult)) {
            $failedStatements = array_intersect_key($updateStatements['create_table'], $updateResult);
            foreach ($failedStatements as $key => $query) {
                $result[$key] = 'Query "' . $query . '" returned "' . $updateResult[$key] . '"';
            }
        }

        if (!empty($result)) {
            throw new \RuntimeException(implode("\n", $result), 1505058450);
        }

        foreach ($insertCount as $table => $count) {
            $insertStatements = $schemaMigrationService->getTableInsertStatements($statements, $table);
            foreach ($insertStatements as $insertQuery) {
                $insertQuery = rtrim($insertQuery, ';');
                /** @var DatabaseConnection $database */
                $database = $GLOBALS['TYPO3_DB'];
                $database->admin_query($insertQuery);
            }
        }
    }

    /**
     * Returns the current database connection set in environment
     *
     * @return array
     */
    protected function getDatabaseConfiguration()
    {
        $originalConfigurationArray = array();

        $databaseName = trim(getenv('typo3DatabaseName'));
        $databaseHost = trim(getenv('typo3DatabaseHost'));
        $databaseUsername = trim(getenv('typo3DatabaseUsername'));
        $databasePassword = trim(getenv('typo3DatabasePassword'));
        $databasePort = trim(getenv('typo3DatabasePort'));
        $databaseSocket = trim(getenv('typo3DatabaseSocket'));
        if ($databaseName || $databaseHost || $databaseUsername || $databasePassword || $databasePort || $databaseSocket) {
            // Try to get database credentials from environment variables first
            $originalConfigurationArray = array(
                'DB' => array(),
            );
            if ($databaseName) {
                $originalConfigurationArray['DB']['database'] = $databaseName;
            }
            if ($databaseHost) {
                $originalConfigurationArray['DB']['host'] = $databaseHost;
            }
            if ($databaseUsername) {
                $originalConfigurationArray['DB']['username'] = $databaseUsername;
            }
            if ($databasePassword) {
                $originalConfigurationArray['DB']['password'] = $databasePassword;
            }
            if ($databasePort) {
                $originalConfigurationArray['DB']['port'] = $databasePort;
            }
            if ($databaseSocket) {
                $originalConfigurationArray['DB']['socket'] = $databaseSocket;
            }
        }

        return $originalConfigurationArray;
    }

    /**
     * Merges two arrays recursively and "binary safe" (integer keys are overridden as well), overruling similar values
     * in the original array with the values of the overrule array.
     * In case of identical keys, ie. keeping the values of the overrule array.
     *
     * This method takes the original array by reference for speed optimization with large arrays
     *
     * The differences to the existing PHP function array_merge_recursive() are:
     *  * Keys of the original array can be unset via the overrule array. ($enableUnsetFeature)
     *  * Much more control over what is actually merged. ($addKeys, $includeEmptyValues)
     *  * Elements or the original array get overwritten if the same key is present in the overrule array.
     *
     * @param array $original Original array. It will be *modified* by this method and contains the result afterwards!
     * @param array $overrule Overrule array, overruling the original array
     * @param bool $addKeys If set to FALSE, keys that are NOT found in $original will not be set. Thus only existing value can/will be overruled from overrule array.
     * @param bool $includeEmptyValues if set, values from $overrule will overrule if they are empty or zero
     * @param bool $enableUnsetFeature if set, special values "__UNSET" can be used in the overrule array in order to unset array keys in the original array
     * @return void
     */
    protected function mergeRecursiveWithOverrule(array &$original, array $overrule, $addKeys = true, $includeEmptyValues = true, $enableUnsetFeature = true)
    {
        foreach ($overrule as $key => $_) {
            if ($enableUnsetFeature && $overrule[$key] === '__UNSET') {
                unset($original[$key]);
                continue;
            }
            if (isset($original[$key]) && is_array($original[$key])) {
                if (is_array($overrule[$key])) {
                    $this->mergeRecursiveWithOverrule($original[$key], $overrule[$key], $addKeys, $includeEmptyValues, $enableUnsetFeature);
                }
            } elseif (
                ($addKeys || isset($original[$key])) &&
                ($includeEmptyValues || $overrule[$key])
            ) {
                $original[$key] = $overrule[$key];
            }
        }
        // This line is kept for backward compatibility reasons.
        reset($original);
    }

    /**
     * Sets the new database name for the test system in configuration
     *
     * @param array $databaseConfiguration
     * @throws Exception
     * @return array
     */
    protected function setDatabaseName(array $databaseConfiguration)
    {
        $originalDatabaseName = $databaseConfiguration['database'];
        $databaseName = $originalDatabaseName . '_ft' . $this->identifier;

        // Maximum database name length for mysql is 64 characters
        if (strlen($databaseName) > 64) {
            throw new Exception(
                'The name of the database that is used for the functional test (' . $databaseName . ')' .
                ' exceeds the maximum length of 64 character allowed by MySQL. You have to shorten your' .
                ' original database name to 54 characters',
                1377600104
            );
        }

        $databaseConfiguration['database'] = $databaseName;

        return $databaseConfiguration;
    }

    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param bool $removeNonEmpty Allow deletion of non-empty directories
     * @return bool true if @rmdir went well!
     */
    protected function rmdir($path, $removeNonEmpty = false)
    {
        $OK = false;
        // Remove trailing slash
        $path = preg_replace('|/$|', '', $path);
        if (file_exists($path)) {
            $OK = true;
            if (!is_link($path) && is_dir($path)) {
                if ($removeNonEmpty == true && ($handle = opendir($path))) {
                    while ($OK && false !== ($file = readdir($handle))) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        $OK = $this->rmdir($path . '/' . $file, $removeNonEmpty);
                    }
                    closedir($handle);
                }
                if ($OK) {
                    $OK = @rmdir($path);
                }
            } else {
                // If $path is a symlink to a folder we need rmdir() on Windows systems
                if (DIRECTORY_SEPARATOR === '\\'
                    && is_link($path)
                    && is_dir($path . '/')
                ) {
                    $OK = rmdir($path);
                } else {
                    $OK = unlink($path);
                }
            }
            clearstatcache();
        } elseif (is_link($path)) {
            $OK = unlink($path);
            clearstatcache();
        }

        return $OK;
    }

    /**
     * Writes $content to the file $file
     *
     * @param string $file Filepath to write to
     * @param string $content Content to write
     * @return bool TRUE if the file was successfully opened and written
     */
    protected function writeFile($file, $content)
    {
        if ($fd = fopen($file, 'wb')) {
            $res = fwrite($fd, $content);
            fclose($fd);
            if ($res === false) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the version number of the PackageStates.php file
     *
     * @return int
     */
    protected function getPackageStatesVersion()
    {
        return 4;
    }

    /**
     * Checks and returns the file path of the autoload.php
     *
     * @throws Exception
     * @return string
     */
    protected function getClassLoaderFilepath()
    {
        $classLoaderFilepath = __DIR__ . '/../../../../../autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            if (file_exists(__DIR__ . '/../../../.Build/vendor/autoload.php')) {
                $classLoaderFilepath = __DIR__ . '/../../../.Build/vendor/autoload.php';
            } elseif (file_exists(ORIGINAL_ROOT . '../vendor/autoload.php')) {
                $classLoaderFilepath = ORIGINAL_ROOT . '../vendor/autoload.php';
            } else {
                throw new Exception(
                    'ClassLoader can\'t be loaded.'
                    . ' Tried to find "' . $classLoaderFilepath . '".'
                    . ' Please check your path or set an environment variable \'TYPO3_PATH_ROOT\' to your root path.'
                );
            }
        }

        return $classLoaderFilepath;
    }
}
