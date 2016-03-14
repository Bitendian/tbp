<?php

abstract class abstract_api_rest {

	private static $request_status = array(
		200 => 'OK',
		201 => 'Created',
		204 => 'No Content',
		303 => 'See Other',
		400 => 'Bad Request',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		500 => 'Internal Server Error',
	);

	protected $api;
	protected $method;
	protected $body = null;
	protected $path = array();
	protected $params = array();

	static function response($data, $status = 200, $location = null) {
		header('HTTP/1.1 ' . $status . ' ' . self::$request_status[$status]);

		if ($location !== null)
			header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $location);

		if ($data !== null)
			echo(json_encode($data));

		die();
	}

	static function redirect($location) {
		self::response(null, 303, $location);
	}

	function __construct() {
		$this->path = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
		$this->api = array_shift($this->path);

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				throw new Exception("Unexpected Header");
			}
		}

		switch($this->method) {
			case 'DELETE':
			case 'POST':
			case 'PUT':
				$this->body = file_get_contents('php://input');
				break;
			case 'GET':
				$this->params = $this->clean_inputs($_GET);
				break;
			default:
				self::response('invalid method: ' . $this->method, 405);
				break;
		}
	}

	function process_api() {
		if (method_exists($this, strtolower($this->method)))
			return self::response($this->{$this->method}());

		self::response(array('error' => 'invalid method ' . $this->method), 405);
	}

	private function clean_inputs($data) {
		$clean_input = array();
		if (is_array($data))
			foreach ($data as $key => $value)
				$clean_input[$key] = $this->clean_inputs($value);
		else
			$clean_input = trim(strip_tags($data));

		return $clean_input;
	}

	protected abstract function get();

	protected abstract function put();

	protected abstract function delete();

	protected abstract function post();

}

