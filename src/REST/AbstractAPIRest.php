<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\REST;

use Bitendian\TBP\TBPException as TBPException;

/*
 * Class to extend and create REST APIs.
 *
 * Subclasses must implement classical REST methods (HTTP verbs) as needed. Subclasses will receive params and other
 * call info as method parameters or class properties.
 *
 * This abstract class provides convenience method to send response to API caller.
 */

abstract class AbstractAPIRest
{
    private static $request_status = array(
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        303 => 'See Other',
        400 => 'Bad Request',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error'
    );

    protected $api;
    protected $method;
    protected $body = null;
    protected $path = array();
    protected $params = array();

    public function __construct()
    {
        $this->path = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
        $this->api = array_shift($this->path);

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } elseif ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new TBPException("Unexpected Header", -1);
            }
        }

        switch ($this->method) {
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

    protected static function response($data, $status = 200, $location = null)
    {
        header('HTTP/1.1 ' . $status . ' ' . self::$request_status[$status]);

        if ($location !== null) {
            header(
                'Location: ' .
                $_SERVER['REQUEST_SCHEME'] .
                '://' . $_SERVER['HTTP_HOST'] .
                ':' . $_SERVER['SERVER_PORT'] .
                $location
            );
        }

        if ($data !== null) {
            echo(json_encode($data));
        }

        die();
    }

    protected static function redirect($location)
    {
        self::response(null, 303, $location);
    }

    public function processAPI()
    {
        if (method_exists($this, strtolower($this->method))) {
            return self::response($this->{$this->method}());
        }

        self::response(array('error' => 'invalid method ' . $this->method), 405);
    }

    private function cleanInputs($data)
    {
        $clean_input = array();

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $clean_input[$key] = $this->cleanInputs($value);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }

        return $clean_input;
    }

    abstract protected function get();

    abstract protected function put();
                      
    abstract protected function delete();
                      
    abstract protected function post();
}
