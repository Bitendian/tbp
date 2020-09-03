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

/**
 * Interface to be implemented by classes that provides access to a cache server.
 *
 * This interface can be implemented outside TBP. However TBP will provide implementations for well known cache servers
 * (memcached, redis).
*/
interface CacheConnectionInterface
{
    /**
     * opens a connection
     * @return mixed
     */
    public function open();

    /**
     * closes a connection
     * @return mixed
     */
    public function close();

    /**
     * retrieve existing keys (works with wildcards)
     * @param $pattern
     * @return mixed
     */
    public function keys($pattern);

    /**
     * checks if key(s) exists (works with arrays)
     * @param $keys
     * @return mixed
     */
    public function exists($keys);

    /**
     * removes some key(s) (works with arrays)
     * @param $keys
     * @return mixed
     */
    public function remove($keys);

    /**
     * removes all keys
     * @return mixed
     */
    public function clear();

    /**
     * stores single value into key
     * @param $key
     * @param $value
     * @return mixed
     */
    public function store($key, $value, $ttl = null);

    /**
     * gets a key value
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * gets list cardinality
     * @param $key
     * @return mixed
     */
    public function listCardinality($key);

    /**
     * stores some value(s) into a list beginning (works with arrays)
     * @param $key
     * @param $values
     * @return mixed
     */
    public function listPrepend($key, $values);

    /**
     * stores some value(s) into a list end (works with arrays)
     * @param $key
     * @param $values
     * @return mixed
     */
    public function listAppend($key, $values);

    /**
     * stores some value into a list at index
     * @param $key
     * @param $index
     * @param $value
     * @return mixed
     */
    public function listStore($key, $index, $value);

    /**
     * gets all values from a list
     * @param $key
     * @return mixed
     */
    public function listGetAll($key);

    /**
     * stores some value(s) into a set (works with arrays)
     * @param $key
     * @param $values
     * @return mixed
     */
    public function setAdd($key, $values);

    /**
     * gets set cardinality
     * @param $key
     * @return mixed
     */
    public function setCardinality($key);

    /**
     * removes some value(s) from a set
     * @param $key
     * @param $values
     * @return mixed
     */
    public function setRemove($key, $values);

    /**
     * gets all values from a set
     * @param $key
     * @return mixed
     */
    public function setGetAll($key);

    /**
     * checks if a set contains some value(s) (work with arrays)
     * @param $key
     * @param $values
     * @return mixed
     */
    public function setContains($key, $values);

    /**
     * gets difference between sets
     * @param $keys
     * @return mixed
     */
    public function setGetDiff($keys);

    /**
     * stores difference between sets into a key
     * @param $key
     * @param $keys
     * @return mixed
     */
    public function setStoreDiff($key, $keys);

    /**
     * stores some value(s) into a sorted set with their scores (works with arrays)
     * @param $key
     * @param $scores
     * @param $values
     * @return mixed
     */
    public function sortedSetAdd($key, $scores, $values);

    /**
     * gets all values from a sorted set
     * @param $key
     * @return mixed
     */
    public function sortedSetGetAll($key);

    /**
     * stores some pairs key => values into a hash
     * @param $key
     * @param $data
     * @return mixed
     */
    public function hashAdd($key, $data);

    /**
     * get a value from a given key in a hash
     * @param $key
     * @param $field
     * @return mixed
     */
    public function hashGet($key, $field);

    /**
     * get all key value pairs from hash
     * @param $key
     * @return mixed
     */
    public function hashGetAll($key);

    /**
     * get database information
     * @return mixed
     */
    public function info();

    /**
     * save database to disk
     * @return mixed
     */
    public function save();
}
