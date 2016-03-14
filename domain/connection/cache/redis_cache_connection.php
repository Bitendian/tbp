<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');
if (!defined('VENDOR_PATH')) die('FATAL: VENDOR_PATH undefined');

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

	public function exists($keys) {

		return $this->connection->exists($keys);
	}

	public function remove($keys) {

		return $this->connection->del($keys);
	}

	public function store($key, $value) {

		return $this->connection->set($key, $value);
	}

	public function get($key) {

		return $this->connection->get($key);
	}

	public function list_prepend($key, $values) {

		$this->connection->lpush($key, $values);
	}

	public function list_append($key, $values) {

		$this->connection->rpush($key, $values);
	}

	public function list_get_all($key) {

		return $this->connection->lrange($key, 0, -1);
	}

	public function set_add($key, $values) {

		$this->connection->sadd($key, $values);
	}

	public function set_cardinality($key) {

		$this->connection->scard($key);
	}

	public function set_remove($key, $values) {

		$this->connection->srem($key, $values);
	}

	public function set_get_all($key) {

		return $this->connection->smembers($key);
	}

	public function set_contains($key, $value) {

		return $this->connection->sismember($key, $value);
	}

	public function set_get_diff($keys) {

		return $this->connection->sdiff($keys);
	}

	public function set_store_diff($key, $keys) {

		$this->connection->sdiffstore($key, $keys);
	}
}

predis_autoloader::register();
