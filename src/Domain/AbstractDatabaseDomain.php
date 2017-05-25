<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitendian\TBP\Domain;

use Bitendian\TBP\Utils\Config as Config;
use Bitendian\TBP\Domain\Connection\MysqlDatabaseConnection as MysqlDatabaseConnection;

/*
 * Abstract class to be extended by app classes that needs to access a database domain.
 *
 * Provides three convenience methods to manage select results:
 *
 * - getSingle returns a single object (or false) from selects that returns only one value
 * - getAll returns array of objects with all results from selects that returns multiple values
 * - insertWithAutoincrement returns last insert id after making an insert into a table with ai
 *
 * TBP provides extensions classes for well known database servers (mysql, postgresql)
*/

abstract class AbstractDatabaseDomain
{
    protected $connection;

    // helper function: returns a single result (as object) or false if there are 0 or more than 1 results
    protected static function getSingle($results)
    {
        if (is_array($results) && count($results) == 1) {
            return (object)array_shift($results);
        }

        return false;
    }

    // helper function: returns a collection of results (as objects array)
    protected static function getAll($results)
    {
        $to_object = function ($array) {
            return (object)$array;
        };

        return array_map($to_object, $results);
    }

    // helper function: if a row has been inserted returns last inserted autoincrement id (or false)
    protected function insertWithAutoincrement($result)
    {
        if ($result != false) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    // it is a database domain, so transactions are expected at domain level too
    public function begin()
    {
        $this->connection->begin();
    }

    public abstract function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }
}
