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

use TYPO3\CMS\Core\Database\DatabaseConnection;

class OldDatabase implements DatabaseInterface
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * @param string$fields
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
     * @param string $where
     * @param array $updateArray
     * @return bool|\mysqli_result
     */
    public function updateArray($table, array $where, array $updateArray)
    {
        return $this->databaseConnection->exec_UPDATEquery($table, $where, $updateArray);
    }

    /**
     * @param string $table
     * @param string $where
     * @return bool|\mysqli_result
     */
    public function delete($table, array $where)
    {
        return $this->databaseConnection->exec_DELETEquery($table, $where);
    }

    /**
     * @return string
     */
    public function getSqlError()
    {
        return $this->databaseConnection->sql_error();
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseInstance()
    {
        return $this->databaseConnection;
    }
}
