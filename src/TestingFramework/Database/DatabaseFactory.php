<?php
namespace Nimut\TestingFramework\Database;

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

final class DatabaseFactory
{
    /**
     * Analyses the system and returns proper bootstrap instance
     *
     * @return DatabaseInterface
     */
    public static function createDatabaseInstance()
    {
        return new OldDatabase($GLOBALS['TYPO3_DB']);
    }
}
