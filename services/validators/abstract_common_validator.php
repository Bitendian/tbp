<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/services/validators/i_validator.php');

abstract class abstract_common_validator {
	
	static function is_int($value) {
		return (!is_null($value) && !is_bool($value) && !is_array($value) && !is_object($value) && ((string) $value) === ((string)(int) $value));
	}

	static function is_int_positive($value) {
		return (static::is_int($value) && $value > 0);
	}

	static function validate($item, $errors = null, $throw_at_the_end = true) {

		static::validation($item, $errors);

		if ($throw_at_the_end) {
			application_exception::throw_if_required($errors);
		}
		
		return $errors;
	}
}
