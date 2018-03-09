<?php
namespace Nimut\TestingFramework\v76\TestSystem;

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
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Service\SqlExpectedSchemaService;
use TYPO3\CMS\Install\Service\SqlSchemaMigrationService;

class TestSystem extends AbstractTestSystem
{
    /**
     * Create tables and import static rows.
     * For functional and acceptance tests.
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

        $result = [];
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
        $originalConfigurationArray = [];

        $databaseName = trim(getenv('typo3DatabaseName'));
        $databaseHost = trim(getenv('typo3DatabaseHost'));
        $databaseUsername = trim(getenv('typo3DatabaseUsername'));
        $databasePassword = trim(getenv('typo3DatabasePassword'));
        $databasePort = trim(getenv('typo3DatabasePort'));
        $databaseSocket = trim(getenv('typo3DatabaseSocket'));
        if ($databaseName || $databaseHost || $databaseUsername || $databasePassword || $databasePort || $databaseSocket) {
            // Try to get database credentials from environment variables first
            $originalConfigurationArray = [
                'DB' => [],
            ];
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
     * Returns the version number of the PackageStates.php file
     *
     * @return int
     */
    protected function getPackageStatesVersion()
    {
        return 4;
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

        $this->bootstrap->initializeClassLoader($classLoader)
            ->baseSetup()
            ->loadConfigurationAndInitialize(true)
            ->loadTypo3LoadedExtAndExtLocalconf(true)
            ->initializeBackendRouter()
            ->setFinalCachingFrameworkCacheConfiguration()
            ->defineLoggingAndExceptionConstants()
            ->unsetReservedGlobalVariables();
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] reusing an existing database with all tables truncated
     *
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

        $databaseConfiguration['database'] = strtolower($databaseName);

        return $databaseConfiguration;
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
}
