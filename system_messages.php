<?php

class system_messages {

	static $errors = array();
	static $warnings = array();
	static $infos = array();

	static function has_messages() {
		return count(self::$errors) + count(self::$warnings) + count(self::$infos);
	}

	static function add_error($message, $id = '') {
		self::$errors[] = array($message, $id);
	}

	static function add_warning($message, $id = '') {
		self::$warnings[] = array($message, $id);
	}

	static function add_info($message, $id = '') {
		self::$infos[] = array($message, $id);
	}

	static function save(&$array) {
		$array['messages_infos'] = serialize(self::$infos);
		$array['messages_warnings'] = serialize(self::$warnings);
		$array['messages_errors'] = serialize(self::$errors);
	}

	static function restore(&$array) {
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

