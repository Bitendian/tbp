<?php

require_once(TBP_BASE_PATH . '/interfaces/i_action.php');
require_once(TBP_BASE_PATH . '/widget.php');
require_once(TBP_BASE_PATH . '/util.php');

abstract class component extends widget implements i_action {

	var $action = '';

	function __construct() {
		parent::__construct();
		$this->action = util::action_encode(get_class($this));
	}

	function run_action(&$params) {
		$this->action($params);
	}
}
