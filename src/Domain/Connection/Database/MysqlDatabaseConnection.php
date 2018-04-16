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

/*
 * Class with implementation of DatabaseConnectionInterface for MySQL.
 *
 * Uses mysqli and prepared statements.
 *
 * Select returns by default rows in an associative array with autoincrement field as index if exists.
*/

class MysqlDatabaseConnection implements DatabaseConnectionInterface
{
    /**
     * @var \mysqli
     */
    private $connection;
    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var bool
     */
    private $useAssociativeArrayWhenPossible = true;

    /**
     * MysqlDatabaseConnection constructor.
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->useAssociativeArrayWhenPossible = (
            !isset($this->config) ||
            !isset($this->config->use_associative_array) ||
            strtolower($this->config->use_associative_array) != 'no'
        );
    }


    private function createConnectionFromConfig()
    {
        return new \mysqli(
            $this->config->server,
            $this->config->username,
            $this->config->password,
            $this->config->database
        );
    }

    /**
     * open database connection
     * @throws TBPException
     */
    public function open()
    {
        if (!($this->connection = $this->createConnectionFromConfig())) {
            throw new TBPException($this->connection->connect_error, $this->connection->connect_errno);
        }

        $this->connection->set_charset('utf8');
        $this->connection->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
    }

    /**
     * close database connection
     * @throws TBPException
     */
    public function close()
    {
        if (!$this->connection->close()) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }
    }

    /**
     * @param string $table
     * @param string $field
     * @return array
     * @throws TBPException
     */
    public function getEnumValues($table, $field)
    {
        $sql = '
            SELECT SUBSTRING(SUBSTRING(COLUMN_TYPE, 6), 1, CHAR_LENGTH(COLUMN_TYPE) - 6)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?';

        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        $statement->bind_param('sss', $this->config->database, $table, $field);

        if (!$statement->execute()) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        $statement->store_result();

        $statement->bind_result($result);

        if (!$statement->fetch()) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        return str_getcsv($result, ',', "'");
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     * @throws TBPException
     */
    public function select($sql, $params = array())
    {
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        if (count($params) > 0) {
            $statement_params = array();
            for ($i = 0; $i < count($params); $i++) {
                $statement_params[$i] = &$params[$i];
            }

            array_unshift($statement_params, str_pad('', count($params), 's'));
            call_user_func_array(array($statement, 'bind_param'), $statement_params);
        }

        if (!$statement->execute()) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        $key_field = null;
        $fields = array();
        $meta = $statement->result_metadata();
        while ($field = $meta->fetch_field()) {
            if (($field->flags & 512) > 0) {
                $key_field = $field->name;
            }

            $fields[] = &$row[$field->name];
        }

        call_user_func_array(array($statement, 'bind_result'), $fields);

        $result = array();
        while ($statement->fetch()) {
            foreach ($row as $key => $value) {
                $row_assoc[$key] = $value;
            }

            if ($key_field != null && $this->useAssociativeArrayWhenPossible) {
                if (isset($result[$row_assoc[$key_field]])) {
                    // is impossible to index by key_field, first we convert associtive to indexed array
                    $result = array_values($result);
                    // no more key_field, is not really a key
                    $key_field = null;
                    // pushing to new indexed array
                    $result[] = $row_assoc;
                } else {
                    $result[$row_assoc[$key_field]] = $row_assoc;
                }
            } else {
                $result[] = $row_assoc;
            }
        }

        $statement->close();

        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     * @throws TBPException
     */
    public function command($sql, $params = array())
    {
        if (!($statement = $this->connection->prepare($sql))) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        if (count($params)) {
            $statement_params = array();
            for ($i = 0; $i < count($params); $i++) {
                $statement_params[$i] = &$params[$i];
            }

            array_unshift($statement_params, str_pad('', count($params), 's'));
            call_user_func_array(array($statement, 'bind_param'), $statement_params);
        }

        if (!$statement->execute()) {
            throw new TBPException($this->connection->error, $this->connection->errno);
        }

        $statement->close();

        return true;
    }

    /**
     * @param string|null $table
     * @param string|null $field
     * @return integer
     */
    public function lastInsertId($table = null, $field = null)
    {
        return $this->connection->insert_id;
    }

    /**
     * begin transaction
     */
    public function begin()
    {
        $this->connection->autocommit(false);
        $this->connection->begin_transaction();
    }

    /**
     * commit transaction
     */
    public function commit()
    {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }

    /**
     * rollback transaction
     */
    public function rollback()
    {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
}
