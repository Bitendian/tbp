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

use Bitendian\TBP\Domain\Connection\Interfaces\DatabaseConnectionInterface as DatabaseConnectionInterface;
use Bitendian\TBP\TBPException;
use SQLite3;
use stdClass;

class SqliteDatabaseConnection implements DatabaseConnectionInterface
{
    /**
     * @var stdClass
     */
    private $config;

    /**
     * @var SQLite3
     */
    private $connection = null;


    /**
     * MysqlDatabaseConnection constructor.
     * @param stdClass $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    private function createConnectionFromConfig()
    {
        $filename = isset($this->config->filename) ? $this->config->filename : 'default.db';
        $flags = SQLITE3_OPEN_CREATE + SQLITE3_OPEN_READWRITE;
        if (isset($this->config->readOnly) && $this->config->readOnly == 'yes') {
            $flags = SQLITE3_OPEN_READONLY;
        }
        $encryptionKey = isset($this->config->encryptionKey) ? $this->config->encryptionKey : '';

        return new SQLite3($filename, $flags, $encryptionKey);
    }

    /**
     * open database connection
     * @throws TBPException
     */
    public function open()
    {
        if (!($this->connection = $this->createConnectionFromConfig())) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }
    }

    /**
     * close database connection
     * @throws TBPException
     */
    public function close()
    {
        if ($this->connection == null) {
            return;
        }
        if (!$this->connection->close()) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }
    }

    public function select($sql, $params = array())
    {
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }

        $counter = 1;
        foreach ($params as &$param) {
            $statement->bindParam($counter, $param, SQLITE3_TEXT);
            $counter++;
        }

        $r = $statement->execute();
        if (!$r) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }

        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results []= $row;
        }

        $statement->close();

        return $results;
    }

    public function command($sql, $params = array())
    {
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }

        $counter = 1;
        foreach ($params as &$param) {
            $statement->bindParam($counter, $param, SQLITE3_TEXT);
            $counter++;
        }

        if (!$statement->execute()) {
            throw new TBPException($this->connection->lastErrorMsg(), $this->connection->lastErrorCode());
        }

        $statement->close();

        return true;
    }

    private function convertQuestionMarksIntoIds($originalSql)
    {
        $sql = $originalSql;
        $counter = 0;
        while (($pos = strpos($sql, '?')) !== false) {
            $sql = substr_replace($sql, ":$counter", $pos, 1);
            $counter++;
        }

        return $sql;
    }

    /**
     * Table and field selector are not implemented in SQLite.
     * @param null $table
     * @param null $field
     * @return int
     */
    public function lastInsertId($table = null, $field = null)
    {
        return $this->connection->lastInsertRowID();
    }

    /**
     * @param string $table
     * @param string $field
     * @return array
     * @throws TBPException
     */
    public function getEnumValues($table, $field)
    {
        throw new TBPException('not implemented', -1);
    }

    /**
     * @throws TBPException
     */
    public function begin()
    {
        return $this->command("BEGIN TRANSACTION");
    }

    /**
     * @throws TBPException
     */
    public function commit()
    {
        return $this->command("COMMIT");
    }

    /**
     * @throws TBPException
     */
    public function rollback()
    {
        return $this->command("ROLLBACK");
    }
}
