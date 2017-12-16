<?php
namespace Nimut\TestingFramework\v76\Database;

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

use Nimut\TestingFramework\Database\DatabaseInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class Database implements DatabaseInterface
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection = null)
    {
        $this->databaseConnection = $databaseConnection ?? $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return bool|\mysqli_result
     */
    public function select($fields, $table, $where)
    {
        return $this->databaseConnection->exec_SELECTquery($fields, $table, $where);
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return array
     */
    public function selectSingleRow($fields, $table, $where)
    {
        return $this->databaseConnection->exec_SELECTgetSingleRow($fields, $table, $where);
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return int
     */
    public function selectCount($fields, $table, $where = '1=1')
    {
        return $this->databaseConnection->exec_SELECTcountRows($fields, $table, $where);
    }

    /**
     * @param string $table
     * @param array $insertArray
     * @return \mysqli_result
     */
    public function insertArray($table, array $insertArray)
    {
        return $this->databaseConnection->exec_INSERTquery($table, $insertArray);
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->databaseConnection->sql_insert_id();
    }

    /**
     * @param string $table
     * @param array $whereArray
     * @param array $updateArray
     * @return bool|\mysqli_result
     */
    public function updateArray($table, array $whereArray, array $updateArray)
    {
        $whereClause = [];
        foreach ($whereArray as $key => $value) {
            if ((int)$value !== $value) {
                $value = $this->databaseConnection->fullQuoteStr($value, $table);
            }
            $whereClause[] = $key . '=' . $value;
        }

        return $this->databaseConnection->exec_UPDATEquery($table, implode(' AND ', $whereClause), $updateArray);
    }

    /**
     * @param string $table
     * @param array $whereArray
     * @return bool|\mysqli_result
     */
    public function delete($table, array $whereArray)
    {
        $whereClause = [];
        foreach ($whereArray as $key => $value) {
            if ((int)$value !== $value) {
                $value = $this->databaseConnection->fullQuoteStr($value, $table);
            }
            $whereClause[] = $key . '=' . $value;
        }

        return $this->databaseConnection->exec_DELETEquery($table, implode(' AND ', $whereClause));
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseInstance()
    {
        return $this->databaseConnection;
    }
}
