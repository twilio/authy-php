<?php

namespace Authy;

use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * ApiClient
 *
 * PHP version 7.4
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
class AuthyResponse
{
    /**
     * @var ResponseInterface $guzzleResponse the guzzle response received
     */
    protected ResponseInterface $guzzleResponse;

    /**
     * @var stdClass|null the body of the response
     */
    protected ?stdClass $body;

    /**
     * @var stdClass the errors for the response
     */
    protected stdClass $errors;

    /**
     * Constructor.
     *
     * @param ResponseInterface $guzzleResponse the Guzzle Response
     */
    public function __construct(ResponseInterface $guzzleResponse)
    {
        $this->guzzleResponse = $guzzleResponse;
        $this->body = !isset($this->guzzleResponse->body)
            ? json_decode($this->guzzleResponse->getBody())
            : $this->guzzleResponse->body;
        $this->errors = new stdClass();

        $this->allocateAccurateErrorsAndBody();
    }

    /**
     * Check if the response was ok
     *
     * @return bool return true if the response code is 200
     */
    public function ok(): bool
    {
        return $this->guzzleResponse->getStatusCode() == 200;
    }

    /**
     * Returns the id of the response if present
     *
     * @return int|null id of the response
     */
    public function id(): ?int
    {
        return isset($this->body->id) ? $this->body->id : null;
    }

    /**
     * Get the request errors
     *
     * @return stdClass object containing the request errors
     */
    public function errors(): stdClass
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->body->message;
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function getBodyValue(string $value)
    {
        return isset($this->body->$value) ? $this->body->$value : null;
    }

    private function allocateAccurateErrorsAndBody(): void
    {
        if (isset($this->body->errors)) {
            $this->errors = $this->body->errors;
            unset($this->body->errors);
        } elseif ($this->guzzleResponse->getStatusCode() === 400) {
            $this->errors = $this->body;
            $this->body = new stdClass();
        } elseif (!$this->ok() && gettype($this->body) === 'string') {
            $this->errors = (object) ["error" => $this->body];
            $this->body = new stdClass();
        }
    }
}