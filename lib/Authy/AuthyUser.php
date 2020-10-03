<?php

namespace Authy;

use Psr\Http\Message\ResponseInterface;

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
 * User implementation. Extends from Authy_Response
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
class AuthyUser extends AuthyResponse
{
    /**
     * Constructor.
     *
     * @param ResponseInterface $guzzleResponse the response from Guzzle
     */
    public function __construct(ResponseInterface $guzzleResponse)
    {
        $body = json_decode($guzzleResponse->getBody());

        if (isset($body->user)) {
            $guzzleResponse->body = $body->user;
        }

        parent::__construct($guzzleResponse);
    }
}
