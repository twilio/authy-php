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

use Psr\Http\Message\ResponseInterface;

class AuthyResponse
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $body;

    /**
     * Constructor.
     *
     * @param ResponseInterface $response Raw server response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->body = json_decode((string)$this->response->getBody(), true);
    }

    /**
     * Check if the response was ok
     *
     * @return boolean return true if the response code is 200
     */
    public function ok()
    {
        return $this->response->getStatusCode() === 200;
    }

    /**
     * Returns the id of the response if present
     *
     * @return integer id of the response
     */
    public function id()
    {
        return isset($this->body['user']) && isset($this->body['user']['id']) ? $this->body['user']['id'] : null;
    }

    /**
     * Get the request errors
     *
     * @return \stdClass containing the request errors
     */
    public function errors()
    {
        $errors = new \stdClass();

        if (!$this->ok()) {
            foreach ($this->body['errors'] as $key => $value) {
                $errors->$key = $value;
            }
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function message()
    {
        return (string)$this->response->getBody();
    }

    /**
     * Returns the variable specified in the response if present
     *
     * @return mixed
     */
    public function bodyvar($var)
    {
        return isset($this->body[$var]) ? $this->body[$var]: null;
    }
}
