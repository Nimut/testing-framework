<?php
namespace Nimut\TestingFramework\TestCase;

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

use Doctrine\DBAL\DBALException;
use Nimut\TestingFramework\Database\Database;
use Nimut\TestingFramework\Database\DatabaseInterface;
use Nimut\TestingFramework\Exception\Exception;
use Nimut\TestingFramework\Http\Response;
use Nimut\TestingFramework\TestSystem\AbstractTestSystem;
use Nimut\TestingFramework\TestSystem\TestSystem;
use PHPUnit\Util\PHP\DefaultPhpProcess;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base test case class for functional tests
 */
abstract class FunctionalTestCase extends AbstractTestCase
{
    /**
     * Core extensions to load.
     *
     * If the test case needs additional core extensions as requirement,
     * they can be noted here and will be added to LocalConfiguration
     * extension list and ext_tables.sql of those extensions will be applied.
     *
     * This property will stay empty in this abstract, so it is possible
     * to just overwrite it in extending classes. Extensions noted here will
     * be loaded for every test of a test case and it is not possible to change
     * the list of loaded extensions between single tests of a test case.
     *
     * A default list of core extensions is always loaded.
     *
     * @see FunctionalTestCaseUtility $defaultActivatedCoreExtensions
     * @var array
     */
    protected $coreExtensionsToLoad = [];

    /**
     * Array of test/fixture extensions paths that should be loaded for a test.
     *
     * This property will stay empty in this abstract, so it is possible
     * to just overwrite it in extending classes. Extensions noted here will
     * be loaded for every test of a test case and it is not possible to change
     * the list of loaded extensions between single tests of a test case.
     *
     * Given path is expected to be relative to your document root, example:
     *
     * array(
     *   'typo3conf/ext/some_extension/Tests/Functional/Fixtures/Extensions/test_extension',
     *   'typo3conf/ext/base_extension',
     * );
     *
     * Extensions in this array are linked to the test instance, loaded
     * and their ext_tables.sql will be applied.
     *
     * @var array
     */
    protected $testExtensionsToLoad = [];

    /**
     * Array of test/fixture folder or file paths that should be linked for a test.
     *
     * This property will stay empty in this abstract, so it is possible
     * to just overwrite it in extending classes. Path noted here will
     * be linked for every test of a test case and it is not possible to change
     * the list of folders between single tests of a test case.
     *
     * array(
     *   'link-source' => 'link-destination'
     * );
     *
     * Given paths are expected to be relative to the test instance root.
     * The array keys are the source paths and the array values are the destination
     * paths, example:
     *
     * array(
     *   'typo3/sysext/impext/Tests/Functional/Fixtures/Folders/fileadmin/user_upload' =>
     *   'fileadmin/user_upload',
     *   'typo3conf/ext/my_own_ext/Tests/Functional/Fixtures/Folders/uploads/tx_myownext' =>
     *   'uploads/tx_myownext'
     * );
     *
     * To be able to link from my_own_ext the extension path needs also to be registered in
     * property $testExtensionsToLoad
     *
     * @var array
     */
    protected $pathsToLinkInTestInstance = [];

    /**
     * This configuration array is merged with TYPO3_CONF_VARS
     * that are set in default configuration and factory configuration
     *
     * @var array
     */
    protected $configurationToUseInTestInstance = [];

    /**
     * Array of folders that should be created inside the test instance document root.
     *
     * This property will stay empty in this abstract, so it is possible
     * to just overwrite it in extending classes. Path noted here will
     * be linked for every test of a test case and it is not possible to change
     * the list of folders between single tests of a test case.
     *
     * Per default the following folder are created
     * /fileadmin
     * /typo3temp
     * /typo3conf/ext
     * /typo3temp/var/tests
     * /typo3temp/var/transient
     * /uploads
     *
     * To create additional folders add the paths to this array. Given paths are expected to be
     * relative to the test instance root and have to begin with a slash. Example:
     *
     * array(
     *   'fileadmin/user_upload'
     * );
     *
     * @var array
     */
    protected $additionalFoldersToCreate = [];

    /**
     * The fixture which is used when initializing a backend user
     *
     * @var string
     */
    protected $backendUserFixture = 'ntf://Database/be_users.xml';

    /**
     * Private utility class used for database abstraction. Do NOT use it in test cases!
     *
     * @var DatabaseInterface
     */
    private $database = null;

    /**
     * Private utility class used in setUp() and tearDown(). Do NOT use in test cases!
     *
     * @var AbstractTestSystem
     */
    private $testSystem = null;

    /**
     * Run tests in a separate PHP process each
     *
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * Avoid serialization of some properties containing objects
     *
     * @return array
     */
    public function __sleep()
    {
        $objectVars = get_object_vars($this);
        unset($objectVars['database'], $objectVars['testSystem']);

        return $objectVars;
    }

    /**
     * Setup creates a test instance and database
     *
     * This method has to be called with parent::setUp() in your test cases
     *
     * @return void
     */
    protected function setUp()
    {
        if (!defined('ORIGINAL_ROOT')) {
            $this->markTestSkipped('Functional tests must be called through phpunit on CLI');
        }
        $this->testSystem = new TestSystem(get_class($this));
        $this->testSystem->setUp(
            $this->coreExtensionsToLoad,
            $this->testExtensionsToLoad,
            $this->pathsToLinkInTestInstance,
            $this->configurationToUseInTestInstance,
            $this->additionalFoldersToCreate
        );
    }

    /**
     * Remove symlinks that were created during setUp
     */
    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->pathsToLinkInTestInstance as $destination) {
            @unlink($destination);
        }
    }

    /**
     * Returns the system identifier
     *
     * @return string
     */
    protected function getInstanceIdentifier()
    {
        return $this->testSystem->getSystemIdentifier();
    }

    /**
     * Return the path for the test system
     *
     * @return string
     */
    protected function getInstancePath()
    {
        return $this->testSystem->getSystemPath();
    }

    /**
     * Get database connection instance
     *
     * This method should be used instead of direct access to
     * any database instance.
     *
     * @return DatabaseInterface
     */
    protected function getDatabaseConnection()
    {
        if (null === $this->database) {
            $this->database = new Database();
        }

        return $this->database;
    }

    /**
     * @return ConnectionPool
     * @deprecated will be removed once TYPO3 9 LTS is released
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\ConnectionPool');
    }

    /**
     * Initialize backend user
     *
     * @param int $userUid uid of the user we want to initialize. This user must exist in the fixture file
     * @throws Exception
     * @return BackendUserAuthentication
     */
    protected function setUpBackendUserFromFixture($userUid)
    {
        $this->importDataSet($this->backendUserFixture);
        $database = $this->getDatabaseConnection();
        $userRow = $database->selectSingleRow('*', 'be_users', 'uid = ' . (int)$userUid);

        /** @var $backendUser BackendUserAuthentication */
        $backendUser = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
        $sessionId = $backendUser->createSessionId();
        $_COOKIE['be_typo_user'] = $sessionId;
        $backendUser->id = $sessionId;
        $backendUser->sendNoCacheHeaders = false;
        $backendUser->dontSetCookie = true;
        $backendUser->createUserSession($userRow);

        $GLOBALS['BE_USER'] = $backendUser;
        $GLOBALS['BE_USER']->start();
        if (!is_array($GLOBALS['BE_USER']->user) || !$GLOBALS['BE_USER']->user['uid']) {
            throw new Exception(
                'Can not initialize backend user',
                1377095807
            );
        }
        $GLOBALS['BE_USER']->backendCheckLogin();

        return $backendUser;
    }

    /**
     * Imports a data set represented as XML into the test database,
     *
     * @param string $path Absolute path to the XML file containing the data set to load
     * @throws Exception
     * @return void
     */
    protected function importDataSet($path)
    {
        if (!is_file($path)) {
            throw new Exception(
                'Fixture file ' . $path . ' not found',
                1376746261
            );
        }

        $database = $this->getDatabaseConnection();

        $fileContent = file_get_contents($path);
        // Disables the functionality to allow external entities to be loaded when parsing the XML, must be kept
        $previousValueOfEntityLoader = libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($fileContent);
        libxml_disable_entity_loader($previousValueOfEntityLoader);
        $foreignKeys = [];

        /** @var $table \SimpleXMLElement */
        foreach ($xml->children() as $table) {
            $insertArray = [];

            /** @var $column \SimpleXMLElement */
            foreach ($table->children() as $column) {
                $columnName = $column->getName();
                $columnValue = null;

                if (isset($column['ref'])) {
                    list($tableName, $elementId) = explode('#', $column['ref']);
                    $columnValue = $foreignKeys[$tableName][$elementId];
                } elseif (isset($column['is-NULL']) && ($column['is-NULL'] === 'yes')) {
                    $columnValue = null;
                } else {
                    $columnValue = (string)$table->$columnName;
                }

                $insertArray[$columnName] = $columnValue;
            }

            $tableName = $table->getName();
            if ($database->getDatabaseInstance() instanceof QueryBuilder) {
                try {
                    $database->insertArray($tableName, $insertArray);
                } catch (DBALException $e) {
                    throw new Exception(
                        'Error when processing fixture file: ' . $path . ' Can not insert data to table ' . $tableName . ': ' . $e->getMessage(),
                        1494782046
                    );
                }
            } else {
                $result = $database->insertArray($tableName, $insertArray);
                if ($result === false) {
                    throw new Exception(
                        'Error when processing fixture file: ' . $path . ' Can not insert data to table ' . $tableName . ': ' . $database->getDatabaseInstance()->sql_error(),
                        1376746262
                    );
                }
            }
            if (isset($table['id'])) {
                $elementId = (string)$table['id'];
                $foreignKeys[$tableName][$elementId] = $database->lastInsertId();
            }
        }
    }

    /**
     * @param int $pageId
     * @param array $typoScriptFiles
     */
    protected function setUpFrontendRootPage($pageId, array $typoScriptFiles = [])
    {
        $pageId = (int)$pageId;
        $page = $this->getDatabaseConnection()->selectSingleRow('*', 'pages', 'uid=' . $pageId);

        if (empty($page)) {
            $this->fail('Cannot set up frontend root page "' . $pageId . '"');
        }

        $pagesFields = [
            'is_siteroot' => 1,
        ];

        $this->getDatabaseConnection()->updateArray('pages', ['uid' => $pageId], $pagesFields);

        $templateFields = [
            'pid' => $pageId,
            'title' => '',
            'config' => '',
            'clear' => 3,
            'root' => 1,
        ];

        foreach ($typoScriptFiles as $typoScriptFile) {
            if (!file_exists($typoScriptFile)) {
                $templateFields['config'] .= '<INCLUDE_TYPOSCRIPT: source="FILE:' . $typoScriptFile . '">' . LF . LF;
            } else {
                $templateFields['config'] .= '// <INCLUDE_TYPOSCRIPT: source="FILE:' . $typoScriptFile . '">' . LF;
                $templateFields['config'] .= file_get_contents($typoScriptFile) . LF . LF;
            }
        }

        $this->getDatabaseConnection()->delete('sys_template', ['pid' => $pageId]);
        $this->getDatabaseConnection()->insertArray('sys_template', $templateFields);
    }

    /**
     * @param int $pageId
     * @param int $languageId
     * @param int $backendUserId
     * @param int $workspaceId
     * @param bool $failOnFailure
     * @param int $frontendUserId
     * @return Response
     */
    protected function getFrontendResponse($pageId, $languageId = 0, $backendUserId = 0, $workspaceId = 0, $failOnFailure = true, $frontendUserId = 0)
    {
        $pageId = (int)$pageId;
        $languageId = (int)$languageId;

        $additionalParameter = '';

        if (!empty($frontendUserId)) {
            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
        }
        if (!empty($backendUserId)) {
            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
        }
        if (!empty($workspaceId)) {
            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
        }

        $arguments = [
            'documentRoot' => $this->getInstancePath(),
            'requestUrl' => 'http://localhost/?id=' . $pageId . '&L=' . $languageId . $additionalParameter,
        ];

        $template = new \Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments' => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot' => __DIR__ . '/../../../',
            ]
        );

        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === Response::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        $response = new Response($result['status'], $result['content'], $result['error']);

        return $response;
    }
}
