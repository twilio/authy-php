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
 * Token implementation. Extends from Authy_Response
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
namespace Authy;

class AuthyToken extends AuthyResponse
{

    /**
     * Check if the response was ok
     *
     * @return boolean return true if the response code is 200
     */
    public function ok()
    {
        if( parent::ok() ){
            return $this->bodyvar('token') == 'is valid';
        }
        return false;
    }
}
