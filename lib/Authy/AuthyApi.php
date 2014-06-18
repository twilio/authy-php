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
 * Authy API interface.
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
namespace Authy;

use Resty\Resty;

class AuthyApi
{
    const VERSION = '1.3.0';
    protected $rest;
    protected $api_key;
    protected $api_url;

    /**
     * Constructor.
     *
     * @param string $api_key Api Key
     * @param string $api_url Optional api url
     */
    public function __construct($api_key, $api_url = "https://api.authy.com")
    {
        $this->rest = new Resty();
        $this->rest->setBaseURL($api_url);
        $this->rest->setUserAgent("authy-php v".AuthyApi::VERSION);

        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    /**
     * Register a user.
     *
     * @param  string    $email        New user's email
     * @param  string    $cellphone    New user's cellphone
     * @param  int       $country_code New user's country code. defaults to USA(1)
     * @return AuthyUser the new registered user
     */
    public function registerUser($email, $cellphone, $country_code = 1)
    {
        $params = $this->defaultParams();
        $params['user'] = array(
            "email" => $email,
            "country_code" => $country_code,
            "cellphone" => $cellphone
        );

        $resp = $this->rest->post('/protected/json/users/new', $params);

        return new AuthyUser($resp);
    }

    /**
     * Verify a given token.
     *
     * @param string $authy_id User's id stored in your database
     * @param string $token    The token entered by the user
     * @param array  $opts     Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function verifyToken($authy_id, $token, $opts = array())
    {
        $params = array_merge($this->defaultParams(), $opts);
        if (!array_key_exists("force", $params)) {
            $params["force"] = "true";
        }
        $url = '/protected/json/verify/'. urlencode($token)
                                        .'/'. urlencode($authy_id);
        $resp = $this->rest->get($url, $params);

        return new AuthyResponse($resp);
    }

    /**
     * Request a valid token via SMS.
     *
     * @param string $authy_id User's id stored in your database
     * @param array  $opts     Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function requestSms($authy_id, $opts = array())
    {
        $params = array_merge($this->defaultParams(), $opts);
        $url = '/protected/json/sms/'.urlencode($authy_id);

        $resp = $this->rest->get($url, $params);

        return new AuthyResponse($resp);
    }

    /**
     * Cellphone call, usually used with SMS Token issues or if no smartphone is available.
     * This function needs the app to be on Starter Plan (free) or higher.
     *
     * @param string $authy_id User's id stored in your database
     * @param array  $opts     Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function phoneCall($authy_id, $opts = array())
    {
        $params = array_merge($this->defaultParams(), $opts);
        $url = '/protected/json/call/'.urlencode($authy_id);

        $resp = $this->rest->get($url, $params);

        return new AuthyResponse($resp);
    }

    /**
     * Deletes an user.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function deleteUser($authy_id)
    {
        $params = array_merge($this->defaultParams());
        $url = '/protected/json/users/delete/'.urlencode($authy_id);

        $resp = $this->rest->post($url, $params);

        return new AuthyResponse($resp);
    }

    /**
     * Return the default parameters.
     *
     * @return array array with the default parameters
     */
    protected function defaultParams()
    {
        return array("api_key" => $this->api_key);
    }
}
