<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitendian\TBP;

use Predis\Client as Client;

use Bitendian\TBP\Utils\Config as Config;
use Bitendian\TBP\Domain\Connection\Interfaces\CacheConnectionInterface as CacheConnectionInterface;

/*
 * Class with implementation of CacheConnectionInterface for redis.
 *
 * Uses predis third-party library.
*/

class RedisCacheConnection implements CacheConnectionInterface
{
    private $config;
    private $connection;

    public function __construct($config)
    {
        $this->config = $config;
    }

    private function createConnectionFromConfig()
    {
        $connection_parameters = array();
        $connection_parameters['scheme'] = $this->config->scheme;
        $connection_parameters['host'] = $this->config->host;
        $connection_parameters['port'] = $this->config->port;
        $connection_parameters['database'] = $this->config->database;

        if (isset($this->config->read_write_timeout)) {
            $connection_parameters['read_write_timeout'] = $this->config->read_write_timeout;
        }

        return new Client($connection_parameters);
    }

    public function open()
    {
        if (!$this->connection) {
            $this->connection = $this->createConnectionFromConfig());
        }
    }

    public function close()
    {
        $this->connection = null;
    }

    public function keys($pattern)
    {
        return $this->connection->keys($pattern);
    }

    public function exists($keys)
    {
        return $this->connection->exists($keys);
    }

    public function remove($keys)
    {
        return $this->connection->del($keys);
    }

    public function clear()
    {
        return $this->connection->flushdb();
    }

    public function store($key, $value)
    {
        return $this->connection->set($key, $value);
    }

    public function get($key)
    {
        return $this->connection->get($key);
    }

    public function listCardinality($key)
    {
        return $this->connection->llen($key);
    }

    public function listPrepend($key, $values)
    {
        $this->connection->lpush($key, $values);
    }

    public function listAppend($key, $values)
    {
        $this->connection->rpush($key, $values);
    }

    public function listStore($key, $index, $value)
    {
        if ((!$this->exists($key) && $index == 0) || ($index == $this->list_cardinality($key))) {
            $this->list_append($key, $value);
        } else {
            $this->connection->lset($key, $index, $value);
        }
    }

    public function listGetAll($key)
    {
        return $this->connection->lrange($key, 0, -1);
    }

    public function setAdd($key, $values)
    {
        $this->connection->sadd($key, $values);
    }

    public function setCardinality($key)
    {
        $this->connection->scard($key);
    }

    public function setRemove($key, $values)
    {
        $this->connection->srem($key, $values);
    }

    public function setGetAll($key)
    {
        return $this->connection->smembers($key);
    }

    public function setContains($key, $value)
    {
        return $this->connection->sismember($key, $value);
    }

    public function setGetDiff($keys)
    {
        return $this->connection->sdiff($keys);
    }

    public function setStoreDiff($key, $keys)
    {
        $this->connection->sdiffstore($key, $keys);
    }

    public function sortedSetAdd($key, $scores, $values)
    {
        $this->connection->zadd($key, $scores, $values);
    }

    public function sortedSetGetAll($key, $with_scores = false)
    {
        return $this->connection->zrange($key, 0, -1, array('WITHSCORES' => $with_scores));
    }

    public function hashAdd($key, $data)
    {
        $this->connection->hmset($key, $data);
    }

    public function hashGet($key, $field)
    {
        return $this->connection->hget($key, $field);
    }

    public function hashGetAll($key)
    {
        return $this->connection->hgetall($key);
    }

    public function info()
    {
        return $this->connection->info();
    }
}
