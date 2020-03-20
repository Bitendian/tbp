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
use PDO;

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
     * @var PDO
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
     * @return PDO
     */
    private function createConnectionFromConfig()
    {
        $connectionInfo = "sqlsrv:Server=" . $this->config->server . ";Database=" . $this->config->database;
        $connection = new PDO($connectionInfo, $this->config->username, $this->config->password);
        $connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $connection->setAttribute( PDO::SQLSRV_ATTR_QUERY_TIMEOUT, 30 );

        return $connection;
    }

    /**
     * opens the connection
     * @throws TBPException
     */
    public function open()
    {
        if (!($this->connection = $this->createConnectionFromConfig())) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }
    }

    /**
     * closes the connection
     * @throws TBPException
     */
    public function close()
    {
        if (!$this->connection->close()) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
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
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }

        if ($statement->execute($params) === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }

        $result = array();
        while ($row = $statement->fetchAll( SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        if ($statement->closeCursor() === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
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
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }

        if ($statement->execute($params) === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }

        if ($statement->closeCursor() === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
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
        return [];
    }

    /**
     * begin a transaction
     * @throws TBPException
     */
    public function begin()
    {
        if ($this->connection->beginTransaction() === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }
    }

    /**
     * commits a transaction
     * @throws TBPException
     */
    public function commit()
    {
        if ($this->connection->commit() === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }
    }

    /**
     * rollbacks a transaction
     * @throws TBPException
     */
    public function rollback()
    {
        if ($this->connection->rollBack() === false) {
            throw new TBPException($this->connection->errorInfo(), $this->connection->errorCode());
        }
    }
}
