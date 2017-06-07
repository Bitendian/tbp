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

use Bitendian\TBP\Domain\Connection\Interfaces\DatabaseConnectionInterface;

abstract class AbstractDatabaseDomain
{
    /**
     * @var DatabaseConnectionInterface
     */
    protected $connection;

    /**
     * helper function: returns a single result (as object) or false if there are 0 or more than 1 results
     * @param $results
     * @return bool|object
     */
    protected static function getSingle($results)
    {
        if (is_array($results) && count($results) == 1) {
            return (object)array_shift($results);
        }

        return false;
    }

    /**
     * helper function: returns a collection of results (as objects array)
     * @param $results
     * @return array
     */
    protected static function getAll($results)
    {
        $to_object = function ($array) {
            return (object)$array;
        };

        return array_map($to_object, $results);
    }

    //
    /**
     * helper function: if a row has been inserted returns last inserted autoincrement id (or false)
     * @param $result
     * @return integer|bool
     */
    protected function insertWithAutoincrement($result)
    {
        if ($result != false) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    // it is a database domain, so transactions are expected at domain level too
    public function open()
    {
        $this->connection->open();
    }

    public function close()
    {
        $this->connection->close();
    }

    public function begin()
    {
        $this->connection->begin();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }
}
