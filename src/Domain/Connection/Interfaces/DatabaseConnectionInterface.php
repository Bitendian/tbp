<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\Domain\Connection\Interfaces;

/*
 * Interface to be implemented by classes that provides access to a database server
 *
 * This interface can be implemented outside TBP. However TBP will provide implementations for well known database
 * servers (mysql, postgres)
*/

interface DatabaseConnectionInterface
{
    /**
     * opens the connection
     * @return mixed
     */
    public function open();

    /**
     * closes the connection
     * @return mixed
     */
    public function close();

    /**
     * returns select result
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = array());

    /**
     * returns command result
     * @param $sql
     * @param array $params
     * @return boolean
     */
    public function command($sql, $params = array());

    /**
     * returns last inserted id with the connection
     * @param string|null $table
     * @param string|null $field
     * @return integer
     */
    public function lastInsertId($table = null, $field = null);

    /**
     * returns all values of a enumeration
     * @param string $table
     * @param string $field
     * @return array
     */
    public function getEnumValues($table, $field);

    /**
     * begin a transaction
     * @return mixed
     */
    public function begin();

    /**
     * commits a transaction
     * @return mixed
     */
    public function commit();

    /**
     * rollbacks a transaction
     * @return mixed
     */
    public function rollback();
}
