<?php

interface i_database_connection {

	function open();

	function close();

	function select($sql, $params);

	function command($sql, $params);

	function last_insert_id($table = null, $field = null);

	function get_enum_values($table, $field);

	function begin_transaction();

	function commit();

	function rollback();
}
