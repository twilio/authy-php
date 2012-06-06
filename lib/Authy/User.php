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
 * User implementation. Extends from AuthyResponse
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
class AuthyUser extends \AuthyResponse
{

    /**
     * Constructor.
     *
     * @param array $raw_response Raw server response
     */
    public function __construct($raw_response)
    {
        if (isset($raw_response['body']->user)) {
            // response is {user: {id: id}}
            $raw_response['body'] = $raw_response['body']->user;
        }

        parent::__construct($raw_response);
    }
}
