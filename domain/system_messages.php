<?php

class system_messages {

	static $errors = array();
	static $warnings = array();
	static $infos = array();

	public static function has_messages() {
		return count(self::$errors) + count(self::$warnings) + count(self::$infos);
	}

	public static function has_error_messages() {
		return count(self::$errors);
	}

	public static function has_warning_messages() {
		return count(self::$warnings);
	}

	public static function has_info_messages() {
		return count(self::$infos);
	}

	public static function add_error($message, $id = '') {
		self::$errors[microtime()] = array($message, $id);
	}

	public static function add_warning($message, $id = '') {
		self::$warnings[microtime()] = array($message, $id);
	}

	public static function add_info($message, $id = '') {
		self::$infos[microtime()] = array($message, $id);
	}

	public static function save(&$array) {
		$array['messages_infos'] = serialize(self::$infos);
		$array['messages_warnings'] = serialize(self::$warnings);
		$array['messages_errors'] = serialize(self::$errors);
	}

	public static function restore(&$array) {
		if (isset($array['messages_infos'])) {
			self::$infos = unserialize($_SESSION['messages_infos']);
			unset($array['messages_infos']);
		}

		if (isset($array['messages_warnings'])) {
			self::$warnings = unserialize($_SESSION['messages_warnings']);
			unset($array['messages_warnings']);
		}

		if (isset($array['messages_errors'])) {
			self::$errors = unserialize($_SESSION['messages_errors']);
			unset($array['messages_errors']);
		}
	}

}
