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
 * Interface to be implemented by classes that provides access to a cache server.
 *
 * This interface can be implemented outside TBP. However TBP will provide implementations for well known cache servers
 * (memcached, redis).
*/

interface CacheConnectionInterface
{
    // opens a connection
    public function open();

    // closes a connection
    public function close();

    // retrieve existing keys (works with wildcards)
    public function keys($pattern);

    // checks if key(s) exists (works with arrays)
    public function exists($keys);

    // removes some key(s) (works with arrays)
    public function remove($keys);

    // removes all keys
    public function clear();

    // stores single value into key
    public function store($key, $value);

    // gets a key value
    public function get($key);

    // gets list cardinality
    public function listCardinality($key);

    // stores some value(s) into a list beginning (works with arrays)
    public function listPrepend($key, $values);

    // stores some value(s) into a list end (works with arrays)
    public function listAppend($key, $values);

    // stores some value into a list at index
    public function listStore($key, $index, $value);

    // gets all values from a list
    public function listGetAll($key);

    // stores some value(s) into a set (works with arrays)
    public function setAdd($key, $values);

    // gets set cardianlity
    public function setCardinality($key);

    // removes some value(s) from a set
    public function setRemove($key, $values);

    // gets all values from a set
    public function setGetAll($key);

    // checks if a set contains some value(s) (work with arrays)
    public function setContains($key, $values);

    // gets diffrecence between sets
    public function setGetDiff($keys);

    // stores difference between sets into a key
    public function setStoreDiff($key, $keys);

    // stores some value(s) into a sorted set with their scores (works with arrays)
    public function sortedSetAdd($key, $scores, $values);

    // gets all values from a sorted set
    public function sortedSetGetAll($key);

    // stores some pairs key => values into a hash
    public function hashAdd($key, $data);

    // get a value from a given key in a hash
    public function hashGet($key, $field);

    // get all key value pairs from hash
    public function hashGetAll($key);

    // get database information
    public function info();
}
