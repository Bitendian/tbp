<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/config.php');
require_once(TBP_BASE_PATH . '/domain/connection/cache/redis_cache_connection.php');

abstract class abstract_redis_domain {

	var $connection;

	function __construct($config) {
		$this->connection = new redis_cache_connection($config);
	}

}

