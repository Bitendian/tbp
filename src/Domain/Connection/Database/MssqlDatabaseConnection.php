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

/*
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

    private function createConnectionFromConfig()
    {
        $connectionInfo  = array(
            'Database' => $this->config->database,
            'UID' => $this->config->uid,
            'PWD' => $this->config->pwd,
            'CharacterSet' => 'UTF-8',
            'TransactionIsolation' => SQLSRV_TXN_READ_UNCOMMITTED
        );

        return sqlsrv_connect($this->config->serverName, $connectionInfo );
    }

    /**
     * opens the connection
     */
    public function open()
    {
        if (!($this->connection = $this->createConnectionFromConfig())) {
            throw new TBPException($this->connection->connect_error, $this->connection->connect_errno);
        }
    }

    /**
     * closes the connection
     */
    public function close()
    {
        if (!sqlsrv_close($this->connection)) {
            print_r(sqlsrv_errors());
            die();
        }
    }

    /**
     * returns select result
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = array())
    {
        if (!($statement = sqlsrv_prepare($this->connection, $sql, $params))) {
            print_r(sqlsrv_errors());
            die();
        }

        if(sqlsrv_execute($statement) === false) {
            print_r(sqlsrv_errors());
            die();
        }

        $result = array();
        while ($row = sqlsrv_fetch_array( $statement, SQLSRV_FETCH_ASSOC )) {
            $result[] = $row;
        }

        if(sqlsrv_free_stmt($statement) === false) {
            print_r(sqlsrv_errors());
            die();
        }

        return $result;
    }

    /**
     * returns command result
     * @param $sql
     * @param array $params
     * @return boolean
     */
    public function command($sql, $params = array())
    {
        if (!($statement = sqlsrv_prepare($this->connection, $sql, $params))) {
            print_r(sqlsrv_errors());
            die();
        }

        if(sqlsrv_execute($statement) === false) {
            print_r(sqlsrv_errors());
            die();
        }

        if(sqlsrv_free_stmt($statement) === false) {
            print_r(sqlsrv_errors());
            die();
        }

        return true;
    }

    /**
     * returns last inserted id with the connection
     * @param string|null $table
     * @param string|null $field
     */
    public function lastInsertId($table = null, $field = null)
    {
        // TODO: Implement lastInsertId() method.
    }

    /**
     * returns all values of a enumeration
     * @param string $table
     * @param string $field
     */
    public function getEnumValues($table, $field)
    {
        // TODO: Implement getEnumValues() method.
    }

    /**
     * begin a transaction
     */
    public function begin()
    {
        if (sqlsrv_begin_transaction($this->connection) === false) {
            print_r(sqlsrv_errors());
            die();
        }
    }

    /**
     * commits a transaction
     */
    public function commit()
    {
        if (sqlsrv_commit($this->connection) === false) {
            print_r(sqlsrv_errors());
            die();
        }
    }

    /**
     * rollbacks a transaction
     */
    public function rollback()
    {
        if (sqlsrv_rollback($this->connection) === false) {
            print_r(sqlsrv_errors());
            die();
        }
    }
}
