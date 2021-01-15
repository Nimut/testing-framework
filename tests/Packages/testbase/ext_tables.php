<?php

defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'testbase',
    'setup',
    'lib.testbase = TEXT'
);
