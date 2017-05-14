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

interface DatabaseInterface
{
    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return mixed
     */
    public function select($fields, $table, $where);

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return array
     */
    public function selectSingleRow($fields, $table, $where);

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return int
     */
    public function selectCount($fields, $table, $where = '1=1');

    /**
     * @param string $table
     * @param array $insertArray
     * @return mixed
     */
    public function insertArray($table, array $insertArray);

    /**
     * @return int
     */
    public function lastInsertId();

    /**
     * @param string $table
     * @param array $whereArray
     * @param array $updateArray
     * @return mixed
     */
    public function updateArray($table, array $whereArray, array $updateArray);

    /**
     * @param string $table
     * @param array $whereArray
     * @return mixed
     */
    public function delete($table, array $whereArray);

    /**
     * @return mixed
     */
    public function getDatabaseInstance();
}
