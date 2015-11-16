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
 * User implementation. Extends from Authy_Response
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
namespace Authy;

class AuthyUser extends AuthyResponse
{
    /**
     * Constructor.
     *
     * @param array $raw_response Raw server response
     */
    public function __construct($raw_response)
    {
        $body = json_decode($raw_response->getBody());

        if (isset($body->user)) {
            // response is {user: {id: id}}
            $raw_response->body = $body->user;
        }

        parent::__construct($raw_response);
    }
}
