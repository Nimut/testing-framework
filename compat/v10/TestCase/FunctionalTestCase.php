<?php

namespace Nimut\TestingFramework\v10\TestCase;

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

use Nimut\TestingFramework\Exception\Exception;
use Nimut\TestingFramework\TestCase\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base test case class for functional tests
 */
abstract class FunctionalTestCase extends AbstractFunctionalTestCase
{
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

        $backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $sessionId = $backendUser->createSessionId();
        $_COOKIE[$backendUser->name] = $sessionId;
        $backendUser->id = $sessionId;
        $backendUser->sendNoCacheHeaders = false;
        $backendUser->dontSetCookie = true;
        $backendUser->lockIP = 0;
        $backendUser->createUserSession($userRow);

        $backendUser->start();
        if (!is_array($backendUser->user) || !$backendUser->user['uid']) {
            throw new Exception(
                'Can not initialize backend user',
                1377095807
            );
        }
        $backendUser->backendCheckLogin();
        $GLOBALS['BE_USER'] = $backendUser;

        return $backendUser;
    }
}
