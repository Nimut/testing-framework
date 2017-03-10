<?php
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

/**
 * This file is defined as bootstrap configuration in FunctionalTests.xml and called by PHPUnit
 * before instantiating the test suites. It must also be called on CLI
 * with PHPUnit parameter --bootstrap if executing single test case classes.
 *
 * Example: call whole functional test suite
 * - cd /var/www/t3master/foo  # Document root of TYPO3 CMS sources (location of index.php)
 * - vendor/bin/phpunit -c vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml \
 *     typo3conf/ext/example_extension/Tests/Functional
 */
if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    die('This script supports command line usage only. Please check your command.');
}

// In case PHPUnit is invoked from a global composer installation or from a phar file, we need to include
// the autoloader to make the classes available
if (!class_exists('Nimut\\TestingFramework\\Bootstrap\\BootstrapFactory')) {
    require __DIR__ . '/../../../../autoload.php';
}

call_user_func(function () {
    $bootstrap = \Nimut\TestingFramework\Bootstrap\BootstrapFactory::createBootstrapInstance();
    $bootstrap->bootstrapFunctionalTestSystem();
});
