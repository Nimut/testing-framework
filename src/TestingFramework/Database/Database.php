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

use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Database implements DatabaseInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection ?? GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\ConnectionPool')
                ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return int|Statement
     */
    public function select($fields, $table, $where)
    {
        return $this->getDatabaseInstance()
            ->select($fields)
            ->from($table)
            ->where($where)
            ->execute();
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return array
     */
    public function selectSingleRow($fields, $table, $where)
    {
        return $this->getDatabaseInstance()
            ->select($fields)
            ->from($table)
            ->where($where)
            ->execute()
            ->fetch();
    }

    /**
     * @param string $fields
     * @param string $table
     * @param string $where
     * @return int
     */
    public function selectCount($fields, $table, $where = '1=1')
    {
        return $this->getDatabaseInstance()
            ->count($fields)
            ->from($table)
            ->where($where)
            ->execute()
            ->fetchColumn(0);
    }

    /**
     * @param string $table
     * @param array $insertArray
     * @return int
     */
    public function insertArray($table, array $insertArray)
    {
        return $this->connection->insert($table, $insertArray);
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $whereArray
     * @param array $updateArray
     * @return mixed
     */
    public function updateArray($table, array $whereArray, array $updateArray)
    {
        return $this->connection->update($table, $updateArray, $whereArray);
    }

    /**
     * @param string $table
     * @param array $whereArray
     * @return mixed
     */
    public function delete($table, array $whereArray)
    {
        return $this->connection->delete($table, $whereArray);
    }

    /**
     * @return QueryBuilder
     */
    public function getDatabaseInstance()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder;
    }
}
