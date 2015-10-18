<?php

interface i_validator {

	static function validate($item, $errors = null, $throw_at_the_end = true);
	static function validation($item, &$errors);
}
