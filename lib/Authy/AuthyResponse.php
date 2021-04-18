<?php

/**
 * ApiClient
 *
 * PHP version 5
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */

/**
 * Friendly class to parse response from the authy API
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
namespace Authy;

use \Psr\Http\Message\ResponseInterface;
use \stdClass;

class AuthyResponse
{
    /**
     * @var ResponseInterface
     */
    protected $raw_response;

    /**
     * @var stdClass
     */
    protected $body;

    /**
     * @var stdClass
     */
    protected $errors;

    /**
     * Constructor.
     *
     * @param ResponseInterface $raw_response Raw server response
     */
    public function __construct($raw_response)
    {
        $this->raw_response = $raw_response;
        $this->body = (! isset($raw_response->body)) ? json_decode($raw_response->getBody()) : $raw_response->body;;
        $this->errors = new \stdClass();

        // Handle errors
        if (isset($this->body->errors)) {
            $this->errors = $this->body->errors; // when response is {errors: {}}
            unset($this->body->errors);
        } elseif ($raw_response->getStatusCode() == 400) {
            $this->errors = $this->body; // body here is a stdClass
            $this->body = new \stdClass();
        } elseif (!$this->ok() && gettype($this->body) == 'string') {
            // the response was an error so put the body as an error
            $this->errors = (object) ["error" => $this->body];
            $this->body = new \stdClass();
        }
    }

    /**
     * Check if the response was ok
     *
     * @return bool return true if the response code is 200
     */
    public function ok()
    {
        return $this->raw_response->getStatusCode() == 200;
    }

    /**
     * Returns the id of the response if present
     *
     * @return int|null id of the response
     */
    public function id()
    {
        return isset($this->body->id) ? $this->body->id : null;
    }

    /**
     * Get the request errors
     *
     * @return stdClass object containing the request errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->body->message;
    }

    /**
     * Returns the variable specified in the response if present
     *
     * @return mixed|null
     */
    public function bodyvar($var)
    {
        return isset($this->body->$var) ? $this->body->$var: null;
    }
}
