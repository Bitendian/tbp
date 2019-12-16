<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitendian\TBP\Domain\Connection\Database;

use \Bitendian\TBP\Domain\Connection\Interfaces\DatabaseConnectionInterface;
use Bitendian\TBP\TBPException;

/**
 * Class with implementation of DatabaseConnectionInterface for MicrosoftSQL.
 *
 * Uses sqlsrv and prepared statements.
 *
 * Select returns rows in an associative array with autoincrement field as index if exists.
*/
class MssqlDatabaseConnection implements DatabaseConnectionInterface
{
    /**
     * @var resource
     */
    private $connection;
    /**
     * @var \stdClass
     */
    private $config;

    /**
     * MssqlDatabaseConnection constructor.
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return false|resource
     */
    private function createConnectionFromConfig()
    {
        $connectionInfo  = array(
            'Database' => $this->config->database,
            'UID' => $this->config->uid,
            'PWD' => $this->config->pwd,
            'CharacterSet' => 'UTF-8',
            'TransactionIsolation' => SQLSRV_TXN_READ_UNCOMMITTED
        );

        return sqlsrv_connect($this->config->serverName, $connectionInfo);
    }

    /**
     * opens the connection
     * @throws TBPException
     */
    public function open()
    {
        if (!($this->connection = $this->createConnectionFromConfig())) {
            throw new TBPException($this->connection->connect_error, $this->connection->connect_errno);
        }
    }

    /**
     * closes the connection
     * @throws TBPException
     */
    public function close()
    {
        if (!sqlsrv_close($this->connection)) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }
    }

    /**
     * returns select result
     * @param string $sql
     * @param array $params
     * @return array
     * @throws TBPException
     */
    public function select($sql, $params = array())
    {
        if (!($statement = sqlsrv_prepare($this->connection, $sql, $params))) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        if (sqlsrv_execute($statement) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        $result = array();
        while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        if (sqlsrv_free_stmt($statement) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        return $result;
    }

    /**
     * returns command result
     * @param $sql
     * @param array $params
     * @return boolean
     * @throws TBPException
     */
    public function command($sql, $params = array())
    {
        if (!($statement = sqlsrv_prepare($this->connection, $sql, $params))) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        if (sqlsrv_execute($statement) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        if (sqlsrv_free_stmt($statement) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        return true;
    }

    /**
     * @param string|null $table
     * @param string|null $field
     * @return integer
     */
    public function lastInsertId($table = null, $field = null)
    {
        // TODO: Implement lastInsertId() method.
        return 0;
    }

    /**
     * @param string $table
     * @param string $field
     * @return array
     * @throws TBPException
     */
    public function getEnumValues($table, $field)
    {
        // TODO: Implement getEnumValues() method.
        return array();
    }

    /**
     * begin a transaction
     * @throws TBPException
     */
    public function begin()
    {
        if (sqlsrv_begin_transaction($this->connection) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }
    }

    /**
     * commits a transaction
     * @throws TBPException
     */
    public function commit()
    {
        if (sqlsrv_commit($this->connection) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }
    }

    /**
     * rollbacks a transaction
     * @throws TBPException
     */
    public function rollback()
    {
        if (sqlsrv_rollback($this->connection) === false) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }
    }
}
