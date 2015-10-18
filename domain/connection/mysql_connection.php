<?php

require_once(BASEPATH . '/tbp/config.php');
require_once(BASEPATH . '/tbp/domain/connection/db_connection.php');
require_once(BASEPATH . '/tbp/domain/application_exception.php');

class mysql_connection implements db_connection {

	var $connection;
	var $config;

	public function __construct($config) {
			$this->config = $config;
	}

	public function open() {
		if (!($this->connection = new mysqli($this->config->server, $this->config->username, $this->config->passwd, $this->config->database)))
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR);

		$this->connection->set_charset('utf8');
		$this->connection->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
	}

	public function close() {
		if (!mysql_close($this->connection))
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, mysqli_error($this->connection));
	}

	public function get_enum_values($table, $field) {
		$sql = '
			SELECT SUBSTRING(SUBSTRING(COLUMN_TYPE, 6), 1, CHAR_LENGTH(COLUMN_TYPE) - 6)
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = ?
				AND TABLE_NAME = ?
				AND COLUMN_NAME = ?';

		if (!($statement = $this->connection->prepare($sql)))
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, $this->connection->error);

		$statement->bind_param('sss', $this->config->database, $table, $field);

		if (!$statement->execute())
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, mysqli_error($this->connection));

		$statement->store_result();

		$statement->bind_result($result);

		if (!$statement->fetch())
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, mysqli_error($this->connection));

		return str_getcsv($result, ',', "'");
	}

	public function select($sql, $params = array()) {
		if (!($statement = $this->connection->prepare($sql)))
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, $this->connection->error);

		if (count($params) > 0) {
			$statement_params = array();
			for ($i = 0; $i < count($params); $i++)
				$statement_params[$i] = &$params[$i];

			array_unshift($statement_params, str_pad('', count($params), 's'));
			call_user_func_array(array($statement, 'bind_param'), $statement_params);
		}

		if (!$statement->execute())
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, mysqli_error($this->connection));

		$fields = array();
		$meta = $statement->result_metadata();
		while ($field = $meta->fetch_field())
			$fields[] = &$row[$field->name];

		call_user_func_array(array($statement, 'bind_result'), $fields);

		$result = array();
		while ($statement->fetch()) {
			foreach($row as $key => $value)
				$row_assoc[$key] = $value;
			$result[] = $row_assoc;
		}

		$statement->close();

		return $result;
	}

	public function command($sql, $params = array()) {
		if (!($statement = $this->connection->prepare($sql)))
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, $this->connection->error);

		if (count($params)) {
			$statement_params = array();
			for ($i = 0; $i < count($params); $i++)
				$statement_params[$i] = &$params[$i];

			array_unshift($statement_params, str_pad('', count($params), 's'));
			call_user_func_array(array($statement, 'bind_param'), $statement_params);
		}

		if (!$statement->execute())
			throw application_exception::add_exception(null, application_exception::DATABASE_COMMUNICATION_ERROR, $this->connection->error);

		$statement->close();

		return true;
	}

	public function last_insert_id($table = null, $field = null) {
		return mysqli_insert_id($this->connection);
	}

	public function begin_transaction() {
		mysqli_autocommit($this->connection, false);
		$this->connection->query('START TRANSACTION');
	}

	public function commit() {
		$this->connection->commit();
		$this->connection->autocommit = true;
	}

	public function rollback() {
		$this->connection->rollback();
		$this->connection->autocommit = true;
	}

}

