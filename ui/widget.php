<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/ui/interfaces/i_fetch.php');
require_once(TBP_BASE_PATH . '/ui/abstract_renderizable.php');

abstract class widget extends abstract_renderizable implements i_fetch {

	var $local_path = '';

	function __construct() {

		$reflector = new ReflectionClass(get_class($this));
		$this->local_path = dirname($reflector->getFileName());
	}

	public function run_fetch(&$params) {
		$this->fetch($params);
	}
}
