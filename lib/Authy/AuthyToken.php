<?php

namespace Authy;

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
 * Token implementation. Extends from Authy_Response
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
class AuthyToken extends AuthyResponse
{
    /**
     * Check if the response was ok
     *
     * @return bool return true if the response code is 200
     */
    public function ok(): bool
    {
        if (parent::ok()) {
            return $this->getBodyValue('token') == 'is valid';
        }

        return false;
    }
}
