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

/**
 * Class to extend and create REST APIs.
 *
 * Subclasses must implement classical REST methods (HTTP verbs) as needed. Subclasses will receive params and other
 * call info as method parameters or class properties.
 *
 * This abstract class provides convenience method to send response to API caller.
 */
abstract class AbstractAPIRest
{
    private static $requestStatus = array(
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

    const DELETE_HTTP_METHOD = 'DELETE';
    const PUT_HTTP_METHOD = 'PUT';
    const POST_HTTP_METHOD = 'POST';
    const GET_HTTP_METHOD = 'GET';

    /**
     * AbstractAPIRest constructor.
     * @throws TBPException
     */
    public function __construct()
    {
        $this->path = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
        $this->api = array_shift($this->path);

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == self::POST_HTTP_METHOD && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == self::DELETE_HTTP_METHOD) {
                $this->method = self::DELETE_HTTP_METHOD;
            } elseif ($_SERVER['HTTP_X_HTTP_METHOD'] == self::PUT_HTTP_METHOD) {
                $this->method = self::PUT_HTTP_METHOD;
            } else {
                throw new TBPException("Unexpected Header", -1);
            }
        }

        switch ($this->method) {
            case self::DELETE_HTTP_METHOD:
            case self::POST_HTTP_METHOD:
            case self::PUT_HTTP_METHOD:
                $this->body = file_get_contents('php://input');
                break;
            case self::GET_HTTP_METHOD:
                $this->params = $this->cleanInputs($_GET);
                break;
            default:
                self::response('invalid method: ' . $this->method, 405);
                break;
        }
    }

    /**
     * @param $data
     * @param int $status
     * @param null $location
     */
    public static function response($data, $status = 200, $location = null)
    {
        header('HTTP/1.1 ' . $status . ' ' . self::$requestStatus[$status]);

        if (!empty($location)) {
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

    /**
     * @param string $location
     */
    protected static function redirect($location)
    {
        self::response(null, 303, $location);
    }

    /**
     *
     */
    public function processAPI()
    {
        if (method_exists($this, strtolower($this->method))) {
            self::response($this->{$this->method}());
        }

        self::response(array('error' => 'invalid method ' . $this->method), 405);
    }

    /**
     * @param $data
     * @return array|string
     */
    private function cleanInputs($data)
    {
        $cleanInput = array();

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $cleanInput[$key] = $this->cleanInputs($value);
            }
        } else {
            $cleanInput = trim(strip_tags($data));
        }

        return $cleanInput;
    }

    /**
     * @return mixed
     */
    abstract protected function get();

    /**
     * @return mixed
     */
    abstract protected function put();

    /**
     * @return mixed
     */
    abstract protected function delete();

    /**
     * @return mixed
     */
    abstract protected function post();
}
