<?php

namespace Bitendian\TBP\REST;

use Bitendian\TBP\TBPException;

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
    /**
     * HTTP request status
     *
     * @var array
     */
    protected static $requestStatus = array(
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

    /**
     * The request method
     *
     * @var string
     */
    protected $method;

    /**
     * The request body
     *
     * @var bool|null|string
     */
    protected $body = null;

    /**
     * The request body parsed (if possible) into a PHP array or object
     *
     * @var null|array|object
     */
    protected $bodyParsed = null;

    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var callable[]
     */
    protected $bodyParsers = [];

    /**
     * The request URI
     *
     * @var array
     */
    protected $path = [];

    /**
     * The request parameters
     *
     * @var array
     */
    private $params = [];

    /**
     * The API response
     * @var null
     */
    protected $response = null;

    /**
     * HTTP method constants
     */
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

        $this->registerMediaTypeParser('application/json', function ($input) {
            $result = json_decode($input, true);
            if (!is_array($result)) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('application/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('text/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
            parse_str($input, $data);
            return $data;
        });

        if ($this->method === self::POST_HTTP_METHOD &&
            in_array($this->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])) {
            $this->bodyParsed = $this->parseBodyWithParams($_POST);
        }
    }

    /**
     * @param $data
     * @param int $status
     * @param null $location
     */
    public function response($data, $status = 200, $location = null)
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

        $this->response = $data;
    }

    /**
     * @param string $location
     */
    protected function redirect($location)
    {
        $this->response(null, 303, $location);
    }

    /**
     * Launch API execution
     */
    public function processAPI()
    {
        if (method_exists($this, strtolower($this->method))) {
            $this->response($this->{$this->method}($this->params));
        } else {
            $this->response(array('error' => 'invalid method ' . $this->method), 405);
        }
    }


    /**
     * @param $data
     * @return array|string
     */
    private function cleanInputs(&$data)
    {
        $cleanInput = [];

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
     * Get specified header
     *
     * @param string $header
     * @return null|string
     */
    private function getHeader($header)
    {
        $index = str_replace('-', '_', strtoupper($header));
        return isset($_SERVER[$index]) ? $_SERVER[$index] : null;
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return null|string
     */
    private function getContentType()
    {
        return $this->getHeader('Content-Type');
    }

    /**
     * Get processed response from API.
     *
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->response;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return null|string
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            return strtolower($contentTypeParts[0]);
        }
        return null;
    }

    /**
     * @return null|object|array
     * @throws TBPException
     */
    protected function parseBody()
    {
        if ($this->bodyParsed) {
            return $this->bodyParsed;
        }

        if (!$this->body) {
            return null;
        }

        $mediaType = $this->getMediaType();
        $parts = explode('+', $mediaType);
        if (count($parts) >= 2) {
            $mediaType = 'application/' . $parts[count($parts)-1];
        }

        if (isset($this->bodyParsers[$mediaType]) === true) {
            $body = (string) $this->body;
            $parsed = $this->bodyParsers[$mediaType]($body);

            if (!is_null($parsed) && !is_object($parsed) && !is_array($parsed)) {
                $message = _('Request body media type parser return value must be an array, an object, or null');
                throw new TBPException($message, -1);
            }
            return $parsed;
        }

        return null;
    }

    /**
     * @param $data
     * @return mixed
     * @throws TBPException
     */
    protected function parseBodyWithParams($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new TBPException(_('Parsed body value must be an array, an object, or null'));
        }

        return $data;
    }

    /**
     * @param int $code
     * @param string $status
     */
    public function setRequestStatus($code, $status)
    {
        self::$requestStatus[$code] = $status;
    }

    /**
     * @param int $code
     */
    public function unsetRequestStatus($code)
    {
        unset(self::$requestStatus[$code]);
    }

    /**
     * @param $mediaType
     * @param callable $callable
     */
    private function registerMediaTypeParser($mediaType, callable $callable)
    {
        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this);
        }
        $this->bodyParsers[(string)$mediaType] = $callable;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function removeParam($key)
    {
        unset($this->params[$key]);
    }

    /**
     * @var array $params
     */
    abstract protected function get(&$params);

    /**
     * @var array $params
     */
    abstract protected function put(&$params);

    /**
     * @var array $params
     */
    abstract protected function delete(&$params);

    /**
     * @var array $params
     */
    abstract protected function post(&$params);
}
