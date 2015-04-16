<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * This file is derived from original zend framework package and is used as part of G4 framework
 * @author Dejan Samardzija samardzija.dejan@gmail.com
 * @copyright  Copyright (c) 2013-2014 G4 Code doo (http://g4code.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace G4\Http;

use G4\Constants\Parameters;

class Request
{
    /**
     * Scheme for http
     *
     */
    const SCHEME_HTTP = 'http';

    /**
     * Scheme for https
     *
     */
    const SCHEME_HTTPS = 'https';

    /**
     * Allowed parameter sources
     * @var array
     */
    protected $_paramSources = array('_GET', '_POST');

    /**
     * Raw request body
     * @var string|false
     */
    protected $_rawBody;

    /**
     * Request parameters
     * @var array
     */
    protected $_params = array();

    public function __construct()
    {
        $this->parseRequestRawBody();
        $this->appendParamsFromHeader();
    }

    private function parseRequestRawBody()
    {
        if ($this->isPut() || $this->isDelete()) {
            $raw = array();
            parse_str($this->getRawBody(), $raw);
            $this->setParams($raw);
        }
        return $this;
    }

    private function appendParamsFromHeader()
    {
        foreach ($this->getListOfHeaderKeys() as $key) {
            $this->setParam($key, $this->getHeader($key));
        }
        return $this;
    }

    private function getListOfHeaderKeys()
    {
        return [
            Parameters::X_ND_AUTH,
            Parameters::X_ND_APP_KEY,
            Parameters::X_ND_APP_TOKEN,
        ];
    }

    /**
     * Access values contained in the superglobals as public members
     * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
     *
     * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return $this->_params[$key];
            case isset($_GET[$key]):
                return $_GET[$key];
            case isset($_POST[$key]):
                return $_POST[$key];
            case isset($_COOKIE[$key]):
                return $_COOKIE[$key];
            case ($key == 'REQUEST_URI'):
                return $this->getRequestUri();
            case ($key == 'PATH_INFO'):
                return $this->getPathInfo();
            case isset($_SERVER[$key]):
                return $_SERVER[$key];
            case isset($_ENV[$key]):
                return $_ENV[$key];
            default:
                return null;
        }
    }

    /**
     * Alias to __get
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Set values
     *
     * In order to follow {@link __get()}, which operates on a number of
     * superglobals, setting values through overloading is not allowed and will
     * raise an exception. Use setParam() instead.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws Zend_Controller_Request_Exception
     */
    public function __set($key, $value)
    {
        throw new \Exception('Setting values in superglobals not allowed; please use setParam()');
    }

    /**
     * Alias to __set()
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        return $this->__set($key, $value);
    }

    /**
     * Check to see if a property is set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return true;
            case isset($_GET[$key]):
                return true;
            case isset($_POST[$key]):
                return true;
            case isset($_COOKIE[$key]):
                return true;
            case isset($_SERVER[$key]):
                return true;
            case isset($_ENV[$key]):
                return true;
            default:
                return false;
        }
    }

    /**
     * Alias to __isset()
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->__isset($key);
    }

    /**
     * Set GET values
     *
     * @param  string|array $spec
     * @param  null|mixed $value
     * @return Zend_Controller_Request_Http
     */
    public function setQuery($spec, $value = null)
    {
        if ((null === $value) && !is_array($spec)) {
            throw new \Exception('Invalid value passed to setQuery(); must be either array of values or key/value pair');
        }

        if ((null === $value) && is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setQuery($key, $value);
            }
            return $this;
        }

        $_GET[(string) $spec] = $value;
        return $this;
    }

    /**
     * Retrieve a member of the $_GET superglobal
     *
     * If no $key is passed, returns the entire $_GET array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getQuery($key = null, $default = null)
    {
        if (null === $key) {
            return $_GET;
        }

        return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }

    /**
     * Set POST values
     *
     * @param  string|array $spec
     * @param  null|mixed $value
     * @return Zend_Controller_Request_Http
     */
    public function setPost($spec, $value = null)
    {
        if ((null === $value) && !is_array($spec)) {
            throw new \Exception('Invalid value passed to setPost(); must be either array of values or key/value pair');
        }

        if ((null === $value) && is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setPost($key, $value);
            }
            return $this;
        }

        $_POST[(string) $spec] = $value;
        return $this;
    }

    /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }

    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getCookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * Retrieve a member of the $_ENV superglobal
     *
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getEnv($key = null, $default = null)
    {
        if (null === $key) {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }

    /**
     * Set allowed parameter sources
     *
     * Can be empty array, or contain one or more of '_GET' or '_POST'.
     *
     * @param  array $paramSoures
     * @return Zend_Controller_Request_Http
     */
    public function setParamSources(array $paramSources = array())
    {
        $this->_paramSources = $paramSources;
        return $this;
    }

    /**
     * Get list of allowed parameter sources
     *
     * @return array
     */
    public function getParamSources()
    {
        return $this->_paramSources;
    }

    /**
     * Set a userland parameter
     *
     * Uses $key to set a userland parameter.
     *
     * A $value of null will unset the $key if it exists
     *
     * @param mixed $key
     * @param mixed $value
     * @return Zend_Controller_Request_Http
     */
    public function setParam($key, $value)
    {
        $key = (string) $key;

        if ((null === $value) && isset($this->_params[$key])) {
            unset($this->_params[$key]);
        } elseif (null !== $value) {
            $this->_params[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieve a parameter
     *
     * Retrieves a parameter from the instance. Priority is in the order of
     * userland parameters (see {@link setParam()}), $_GET, $_POST. If a
     * parameter matching the $key is not found, null is returned.
     *
     * @param mixed $key
     * @param mixed $default Default value to use if key not found
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $paramSources = $this->getParamSources();

        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        } elseif (in_array('_GET', $paramSources) && (isset($_GET[$key]))) {
            return $_GET[$key];
        } elseif (in_array('_POST', $paramSources) && (isset($_POST[$key]))) {
            return $_POST[$key];
        }

        return $default;
    }

    /**
     * Retrieve an array of parameters
     *
     * Retrieves a merged array of parameters, with precedence of userland
     * params (see {@link setParam()}), $_GET, $POST (i.e., values in the
     * userland params will take precedence over all others).
     *
     * @return array
     */
    public function getParams()
    {
        $return    = $this->_params;
        $paramSources = $this->getParamSources();

        if (in_array('_GET', $paramSources)
            && isset($_GET)
            && is_array($_GET)
        ) {
            $return += $_GET;
        }

        if (in_array('_POST', $paramSources)
            && isset($_POST)
            && is_array($_POST)
        ) {
            $return += $_POST;
        }

        return $return;
    }

    /**
     * Set parameters
     *
     * Set one or more parameters. Parameters are set as userland parameters,
     * using the keys specified in the array.
     *
     * @param array $params
     * @return Zend_Controller_Request_Http
     */
    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     *
     * @param unknown_type $method
     * @return boolean
     */
    protected function isMethod($method)
    {
        if(!is_scalar($method)) {
            // throw new InvalidArgumentException
            return false;
        }

        $allowed_methods = array('POST', 'GET', 'PUT', 'DELETE', 'HEAD', 'OPTIONS');

        // normalize argument name
        $method = strtoupper($method);

        return in_array($method, $allowed_methods)
            ? strtoupper($this->getMethod()) == $method
            : false;
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost() {
        return $this->isMethod('POST');
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public function isGet() {
        return $this->isMethod('GET');
    }

    /**
     * Was the request made by PUT?
     *
     * @return boolean
     */
    public function isPut() {
        return $this->isMethod('PUT');
    }

    /**
     * Was the request made by DELETE?
     *
     * @return boolean
     */
    public function isDelete() {
        return $this->isMethod('DELETE');
    }

    /**
     * Was the request made by HEAD?
     *
     * @return boolean
     */
    public function isHead() {
        return $this->isMethod('HEAD');
    }

    /**
     * Was the request made by OPTIONS?
     *
     * @return boolean
     */
    public function isOptions() {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Is the request a CLI (Command Line Interface) request
     *
     * @return boolean
     */
    public function isCli()
    {
        static $isCli = null;
        if(null !== $isCli) { return $isCli; }

        return $isCli = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }

    /**
     * Is the request a Ajax (Javascript XMLHttpRequest)?
     *
     * @return boolean
     */
    public function isAjax()
    {
        static $isAjax = null;
        if(null !== $isAjax) { return $isAjax; }

        return $isAjax = ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Is https secure request
     *
     * @return boolean
     */
    public function isSecure()
    {
        return ($this->getScheme() === self::SCHEME_HTTPS);
    }

    /**
     * Return the raw body of the request, if present
     *
     * @return string|false Raw body, or false if not present
     */
    public function getRawBody()
    {
        if (null === $this->_rawBody) {
            $body = file_get_contents('php://input');

            if (strlen(trim($body)) > 0) {
                $this->_rawBody = $body;
            } else {
                $this->_rawBody = false;
            }
        }
        return $this->_rawBody;
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Zend_Controller_Request_Exception
     */
    public function getHeader($header)
    {
        if (empty($header)) {
            throw new \Exception('An HTTP header name is required');
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }

        return false;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->getServer('SERVER_NAME');
        $port   = $this->getServer('SERVER_PORT');

        if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }

    /**
     * Unset all user parameters
     *
     * @return Request
     */
    public function clearParams()
    {
        $this->_params = array();
        return $this;
    }

} // end of class
