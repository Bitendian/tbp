<?php

require_once(TBP_BASE_PATH . '/config.php');
require_once(TBP_BASE_PATH . '/domain/connection/interfaces/i_cache_connection.php');

require_once(VENDOR_PATH . '/autoload.php');

use Predis\Autoloader as predis_autoloader;
use Predis\Client as predis_client;

class redis_cache_connection implements i_cache_connection {

	var $config;
	var $connection;

	public function __construct($config) {
			$this->config = $config;
	}

	public function open() {
		if (!$this->connection)
			$this->connection = new predis_client(array("scheme" => $this->config->scheme, "host" => $this->config->host, "port" => $this->config->port, "database" => $this->config->database));
	}

	public function close() {
		$this->connection = null;
	}

	public function keys($pattern) {
		return $this->connection->keys($pattern);
	}

	public function exists($keys) {
		return $this->connection->exists($keys);
	}

	public function remove($keys) {
		return $this->connection->del($keys);
	}

	public function clear() {
		return $this->connection->flushall();
	}

	public function store($key, $value) {
		return $this->connection->set($key, $value);
	}

	public function get($key) {
		return $this->connection->get($key);
	}

	function list_cardinality($key) {
		return $this->connection->llen($key);
	}

	function list_prepend($key, $values) {
		$this->connection->lpush($key, $values);
	}

	function list_append($key, $values) {
		$this->connection->rpush($key, $values);
	}

	function list_store($key, $index, $value) {
		if ((!$this->exists($key) && $index == 0) || ($index == $this->list_cardinality($key)))
			$this->list_append($key, $value);
		else
			$this->connection->lset($key, $index, $value);
	}

	function list_get_all($key) {
		return $this->connection->lrange($key, 0, -1);
	}

	public function set_add($key, $values) {
		$this->connection->sadd($key, $values);
	}

	function set_cardinality($key) {
		$this->connection->scard($key);
	}

	function set_remove($key, $values) {
		$this->connection->srem($key, $values);
	}

	function set_get_all($key) {
		return $this->connection->smembers($key);
	}

	function set_contains($key, $value) {
		return $this->connection->sismember($key, $value);
	}

	function set_get_diff($keys) {
		return $this->connection->sdiff($keys);
	}

	function set_store_diff($key, $keys) {
		$this->connection->sdiffstore($key, $keys);
	}

	function sorted_set_add($key, $scores, $values) {
		$this->connection->zadd($key, $scores, $values);
	}

	function sorted_set_get_all($key, $with_scores = false) {
		return $this->connection->zrange($key, 0, -1, array('WITHSCORES' => $with_scores));
	}

}

predis_autoloader::register();

