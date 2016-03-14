<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/config.php');
require_once(TBP_BASE_PATH . '/domain/connection/interfaces/i_database_connection.php');

class mysql_database_connection implements i_database_connection {

	var $connection;
	var $config;

	public function __construct($config) {

		$this->config = $config;
	}

	public function open() {

		if (!($this->connection = new \mysqli($this->config->server, $this->config->username, $this->config->passwd, $this->config->database)))
			throw new \Exception($this->connection->connect_error(), $this->connection->connect_errno());

		$this->connection->set_charset('utf8');
		$this->connection->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
	}

	public function close() {

		if (!$this->connection->close())
			throw new \Exception($this->connection->error, $this->connection->errno);
	}

	public function get_enum_values($table, $field) {

		$sql = '
			SELECT SUBSTRING(SUBSTRING(COLUMN_TYPE, 6), 1, CHAR_LENGTH(COLUMN_TYPE) - 6)
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = ?
				AND TABLE_NAME = ?
				AND COLUMN_NAME = ?';

		if (!($statement = $this->connection->prepare($sql)))
			throw new \Exception($this->connection->error, $this->connection->errno);

		$statement->bind_param('sss', $this->config->database, $table, $field);

		if (!$statement->execute())
			throw new \Exception($this->connection->error, $this->connection->errno);

		$statement->store_result();

		$statement->bind_result($result);

		if (!$statement->fetch())
			throw new \Exception($this->connection->error, $this->connection->errno);

		return \str_getcsv($result, ',', "'");
	}

	public function select($sql, $params = array()) {

		if (!($statement = $this->connection->prepare($sql)))
			throw new \Exception($this->connection->error, $this->connection->errno);


		if (count($params) > 0) {
			$statement_params = array();
			for ($i = 0; $i < count($params); $i++)
				$statement_params[$i] = &$params[$i];

			\array_unshift($statement_params, str_pad('', count($params), 's'));
			\call_user_func_array(array($statement, 'bind_param'), $statement_params);
		}

		if (!$statement->execute())
			throw new \Exception($this->connection->error, $this->connection->errno);

		$key_field = null;
		$fields = array();
		$meta = $statement->result_metadata();
		while ($field = $meta->fetch_field()) {
			if (($field->flags & 512) > 0)
				$key_field = $field->name;
			$fields[] = &$row[$field->name];
		}

		\call_user_func_array(array($statement, 'bind_result'), $fields);

		$result = array();
		while ($statement->fetch()) {
			foreach($row as $key => $value)
				$row_assoc[$key] = $value;
			if ($key_field != null)
				$result[$row_assoc[$key_field]] = $row_assoc;
			else
				$result[] = $row_assoc;
		}

		$statement->close();

		return $result;
	}

	public function command($sql, $params = array()) {

		if (!($statement = $this->connection->prepare($sql)))
			throw new \Exception($this->connection->error, $this->connection->errno);

		if (count($params)) {
			$statement_params = array();
			for ($i = 0; $i < count($params); $i++)
				$statement_params[$i] = &$params[$i];

			\array_unshift($statement_params, str_pad('', count($params), 's'));
			\call_user_func_array(array($statement, 'bind_param'), $statement_params);
		}

		if (!$statement->execute())
			throw new \Exception($this->connection->error, $this->connection->errno);

		$statement->close();

		return true;
	}

	public function last_insert_id($table = null, $field = null) {

		return $this->connection->insert_id;
	}

	public function begin_transaction() {

		$this->connection->autocommit = false;
		$this->connection->begin_transaction();
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
