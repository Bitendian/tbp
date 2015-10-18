<?php

class application_exception extends Exception {

	const HTML_500_ERROR = -1;
	const HTML_404_ERROR = -2;
	const HTML_403_ERROR = -3;

	const DATABASE_COMMUNICATION_ERROR = 1;

	const FORBIDDEN_ACCESS_TO_METHOD = 2;

	private $_code_set = array();

	public static $messages = array();

	public static function add_exception($exception = null, $code = 0, $message = null) {
		if ($message == null) $message = self::$messages[$code];
		if ($exception === null) {
			$exception = new application_exception($message, $code);
		}
		$exception->_code_set[$code] = $message;

		return $exception;
	}

	public static function throw_if_required($exception = null) {

		if ($exception !== null && self::has($exception)) {
			throw $exception;
		}
	}

	public function get_all_codes() {

		return $this->_code_set;
	}

	public static function has($exception = null) {

		return ($exception != null ? count($exception->_code_set) > 0 : false);
	}

}

$r = new ReflectionClass("application_exception");
foreach($r->getConstants() as $c) application_exception::$messages[$c] = "application_exception::$c";
