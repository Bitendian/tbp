<?php

if (!defined('TBP_BASE_PATH')) die('FATAL: TBP_BASE_PATH undefined');

require_once(TBP_BASE_PATH . '/ui/interfaces/i_action.php');
require_once(TBP_BASE_PATH . '/ui/widget.php');
require_once(TBP_BASE_PATH . '/util.php');

abstract class component extends widget implements i_action {

	var $action = '';

	function __construct() {

		parent::__construct();
		$this->action = self::action_encode(get_class($this));
	}

	function run_action(&$params) {

		$this->action($params);
	}

	public static function action_encode($action) {

		return urlencode(base64_encode($action));
	}

	public static function action_decode($action) {
		
		return base64_decode(urldecode($action));
	}
}
