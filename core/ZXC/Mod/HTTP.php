<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 16/11/2017
 * Time: 22:41
 */

namespace ZXC\Mod;

use ZXC\Factory;

class HTTP extends Factory
{
    /**
     * https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * @var array
     */
    public $headers = [
        // 1xx Informational responses
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        // 4xx Client errors
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // 5xx Server errors
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    private $get;
    private $host;
    private $path;
    private $post;
    private $port;
    private $server;
    private $method;
    private $scheme;
    private $baseRoute;

    public function __construct()
    {
        $this->server = &$_SERVER;
        $this->method = &$this->server['REQUEST_METHOD'];
        $this->path = &$this->server['REQUEST_URI'];
        $this->post = &$_POST;
        $this->get = &$_GET;
        $this->baseRoute = dirname($_SERVER['SCRIPT_NAME']);
        $this->host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        $this->port = &$this->server['SERVER_PORT'];
        $this->scheme = &$this->server['REQUEST_SCHEME'];
    }

    function reinitialize()
    {
        // TODO: Implement reinitialize() method.
    }

    public function sendHeader($status = 404)
    {
        header("HTTP/1.0 $status {$this->headers[$status]}");
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getScheme()
    {

    }

    public function getProtocolVersion()
    {

    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return mixed
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * @return string
     */
    public function getBaseRoute(): string
    {
        return $this->baseRoute;
    }
}