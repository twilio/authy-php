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
 * User implementation. Extends from Authy_response
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
namespace Authy;

use Psr\Http\Message\responseInterface;

class AuthyUser extends AuthyResponse
{
    /**
     * Constructor.
     *
     * @param ResponseInterface $response server response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
    }
}
