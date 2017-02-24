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

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

if (interface_exists('TYPO3Fluid\\Fluid\\View\\ViewInterface')) {
    $bootstrap = new \Nimut\TestingFramework\Bootstrap\UnitTestsBootstrap();
} else {
    $bootstrap = new \Nimut\TestingFramework\Bootstrap\Legacy\LegacyUnitTestsBootstrap();
}
$bootstrap->bootstrapSystem();
