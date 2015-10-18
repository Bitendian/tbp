<?php

require_once(BASEPATH . '/tbp/config.php');
require_once(BASEPATH . '/tbp/domain/connection/connection.php');
require_once(BASEPATH . '/tbp/domain/application_exception.php');

class postgres_connection implements connection {

	var $connection;
	var $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function open() {
		if (!($this->connection = pg_connect(
			'host=' . $this->config->server .
			' dbname=' . $this->config->database .
			' user=' . $this->config->username .
			' password=' . $this->config->passwd))) {
				$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR);
				throw $e;
		}
	}

	public function close() {
		if (!pg_close($this->connection)) {
			$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, pg_last_error($this->connection));
			throw $e;
		}
	}

	public function get_enum_values($table, $field) {
		throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, 'method get_enum_values() not implemented');
	}

	public function select($sql, $params) {
		$result = array();
		if (($rs = pg_query_params($this->connection, $sql, $params)) === false) {
			$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, pg_last_error($this->connection));
			throw $e;
		}
		while ($row = pg_fetch_assoc($rs)) $result[] = $row;
		return $result;
	}

	public function command($sql, $params) {
		if (pg_query_params($this->connection, $sql, $params) === false) {
			$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, pg_last_error($this->connection));
			throw $e;
		}
		return true;
	}

	public function last_insert_id($table = null, $field = null) {
		if ($table == null || $field == null) {
			if (($row = pg_fetch_row(pg_query($this->connection, "select lastval()"))) === false) {
				$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, pg_last_error($this->connection));
				throw $e;
			}
		} else {
			if (($row = pg_fetch_row(pg_query($this->connection, "select currval('{$table}_{$field}_seq')"))) === false) {
				$e = application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, pg_last_error($this->connection));
				throw $e;
			}
		}
		return $row[0];
	}

	public function begin_transaction() {
		$this->command("BEGIN", array());
	}

	public function commit() {
		$this->command("COMMIT", array());
	}

	public function rollback() {
		$this->command("ROLLBACK", array());
	}

}

