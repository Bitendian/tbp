<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/config.php');
require_once(TBP_BASE_PATH . '/domain/connection/database/mysql_database_connection.php');

abstract class abstract_mysql_domain {

	var $connection;

	function __construct($config) {
		$this->connection = new mysql_database_connection($config);
	}

	// helper function: returns a single result (as object) or false if there are 0 or more than 1 results
	static function get_single($results) {
		if (is_array($results) && count($results) == 1) return (object)array_shift($results);

		return false;
	}

	// helper function: returns a collection of results (as objects array)
	static function get_all($results) {
		$to_object = function($array) {
			return (object)$array;
		};

		return array_map($to_object, $results);
	}

}

