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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Nimut\TestingFramework\Exception\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        if (!class_exists('TYPO3\\CMS\\Core\\Database\\Schema\\SchemaMigrator')) {
            parent::createDatabaseStructure();
        } else {
            $schemaMigrationService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\Schema\\SchemaMigrator');
            $sqlReader = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\Schema\\SqlReader');
            $sqlCode = $sqlReader->getTablesDefinitionString(true);

            $createTableStatements = $sqlReader->getCreateTableStatementArray($sqlCode);

            $updateResult = $schemaMigrationService->install($createTableStatements);
            $failedStatements = array_filter($updateResult);
            $result = array();
            foreach ($failedStatements as $query => $error) {
                $result[] = 'Query "' . $query . '" returned "' . $error . '"';
            }

            if (!empty($result)) {
                throw new \RuntimeException(implode("\n", $result), 1505058450);
            }

            $insertStatements = $sqlReader->getInsertStatementArray($sqlCode);
            $schemaMigrationService->importStaticData($insertStatements);
        }
    }

    /**
     * Returns the current database connection set in environment
     *
     * @return array
     */
    protected function getDatabaseConfiguration()
    {
        if (!class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            return parent::getDatabaseConfiguration();
        }

        $originalConfigurationArray = array();

        $databaseName = trim(getenv('typo3DatabaseName'));
        $databaseHost = trim(getenv('typo3DatabaseHost'));
        $databaseUsername = trim(getenv('typo3DatabaseUsername'));
        $databasePassword = getenv('typo3DatabasePassword');
        $databasePasswordTrimmed = trim($databasePassword);
        $databasePort = trim(getenv('typo3DatabasePort'));
        $databaseSocket = trim(getenv('typo3DatabaseSocket'));
        $databaseDriver = trim(getenv('typo3DatabaseDriver'));
        if ($databaseName || $databaseHost || $databaseUsername || $databasePassword || $databasePort || $databaseSocket) {
            // Try to get database credentials from environment variables first
            $originalConfigurationArray = array(
                'DB' => array(
                    'Connections' => array(
                        'Default' => array(
                            'driver' => 'mysqli',
                            'initCommands' => $this->defaultConfiguration['SYS']['setDBinit'],
                        ),
                    ),
                ),
            );
            if ($databaseName) {
                $originalConfigurationArray['DB']['Connections']['Default']['dbname'] = $databaseName;
            }
            if ($databaseHost) {
                $originalConfigurationArray['DB']['Connections']['Default']['host'] = $databaseHost;
            }
            if ($databaseUsername) {
                $originalConfigurationArray['DB']['Connections']['Default']['user'] = $databaseUsername;
            }
            if ($databasePassword !== false) {
                $originalConfigurationArray['DB']['Connections']['Default']['password'] = $databasePasswordTrimmed;
            }
            if ($databasePort) {
                $originalConfigurationArray['DB']['Connections']['Default']['port'] = $databasePort;
            }
            if ($databaseSocket) {
                $originalConfigurationArray['DB']['Connections']['Default']['unix_socket'] = $databaseSocket;
            }
            if ($databaseDriver) {
                $originalConfigurationArray['DB']['Connections']['Default']['driver'] = $databaseDriver;
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
        return 5;
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
            ->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI)
            ->baseSetup()
            ->loadConfigurationAndInitialize(true)
            ->loadTypo3LoadedExtAndExtLocalconf(true)
            ->setFinalCachingFrameworkCacheConfiguration()
            ->unsetReservedGlobalVariables();

        if (method_exists($this->bootstrap, 'defineLoggingAndExceptionConstants')) {
            $this->bootstrap->defineLoggingAndExceptionConstants();
        }
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] reusing an existing database with all tables truncated
     *
     * @return void
     */
    protected function initializeTestDatabase()
    {
        if (!class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            parent::initializeTestDatabase();
        } else {
            if (method_exists($this->bootstrap, 'initializeTypo3DbGlobal')) {
                $this->bootstrap->initializeTypo3DbGlobal();
            }

            $connection = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\ConnectionPool')
                ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
            $schemaManager = $connection->getSchemaManager();
            foreach ($schemaManager->listTables() as $table) {
                $connection->truncate($table->getName());
            }
        }
    }

    /**
     * Loads extension configuration from ext_localconf.php and ext_tables.php files
     *
     * @return void
     */
    protected function loadExtensionConfiguration()
    {
        $this->bootstrap->loadBaseTca(true)->loadExtTables(true);
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
        if (!class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            return parent::setDatabaseName($databaseConfiguration);
        }

        $originalDatabaseName = $databaseConfiguration['Connections']['Default']['dbname'];
        $databaseName = $originalDatabaseName . '_ft' . $this->identifier;

        // Maximum database name length for mysql is 64 characters
        if (strlen($databaseName) > 64) {
            throw new Exception(
                'The name of the database that is used for the functional test (' . $databaseName . ')' .
                ' exceeds the maximum length of 64 character allowed by MySQL. You have to shorten your' .
                ' original database name to 54 characters',
                1488117937
            );
        }

        $databaseConfiguration['Connections']['Default']['dbname'] = $databaseName;

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
        if (!class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            parent::setUpTestDatabase();
        } else {
            if (method_exists($this->bootstrap, 'initializeTypo3DbGlobal')) {
                $this->bootstrap->initializeTypo3DbGlobal();
            }

            $connectionParameters = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'];
            $databaseName = $connectionParameters['dbname'];
            unset($connectionParameters['dbname']);
            $schemaManager = DriverManager::getConnection($connectionParameters)->getSchemaManager();

            if (in_array($databaseName, $schemaManager->listDatabases(), true)) {
                $schemaManager->dropDatabase($databaseName);
            }

            try {
                $schemaManager->createDatabase($databaseName);
            } catch (DBALException $e) {
                $user = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
                $host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
                throw new Exception(
                    'Unable to create database with name ' . $databaseName . '. This is probably a permission problem.'
                    . ' For this instance this could be fixed executing:'
                    . ' GRANT ALL ON `' . substr($databaseName, 0, -10) . '_%`.* TO `' . $user . '`@`' . $host . '`;'
                    . ' Original message thrown by database layer: ' . $e->getMessage(),
                    1376579070
                );
            }
        }
    }
}
