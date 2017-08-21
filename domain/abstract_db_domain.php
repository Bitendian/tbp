<?php

require_once(TBP_BASE_PATH . "/domain/connection/mysql_connection.php");

abstract class abstract_db_domain {

	static $connection;

	public static function get_single($results) {
		if (is_array($results) && count($results) == 1) return (object)array_shift($results);
		return false;
	}

	public static function get_all($results) {
		$toObject = function($array) {
			return (object)$array;
		};

		return array_map($toObject, $results);
	}

	public static function begin_transaction() {
		self::$connection->begin_transaction();
	}

	public static function commit() {
		self::$connection->commit();
	}

	public static function rollback() {
		self::$connection->rollback();
	}

}

