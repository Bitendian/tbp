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
    // opens the connection
    public function open();

    // closes the connection
    public function close();

    // returns select result
    public function select($sql, $params);

    // retuns command result
    public function command($sql, $params);

    // returns last inserted id with the connection
    public function lastInsertId($table = null, $field = null);

    // returns all values of a enumeration
    public function getEnumValues($table, $field);

    // begin a transaction
    public function begin();

    // commits a transaction
    public function commit();

    // rollbacks a transaction
    public function rollback();
}
