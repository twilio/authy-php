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

class AuthyApi
{
    const VERSION = '2.5.0';

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
        $this->rest = new \GuzzleHttp\Client(array(
            'base_url' => "{$api_url}/protected/json/",
            'defaults' => array(
                'headers'       => array('User-Agent' => $this->__getUserAgent(), 'X-Authy-API-Key' => $api_key ),
                'exceptions'    => false
            )
        ));

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
        $resp = $this->rest->post('users/new', array(
            'query' => array(
                'user' => array(
                    "email"        => $email,
                    "cellphone"    => $cellphone,
                    "country_code" => $country_code
                )
            )
        ));

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
        $params = [];

        if (! array_key_exists("force", $opts)) {
            $params["force"] = "true";
        }

        $token = urlencode($token);
        $authy_id = urlencode($authy_id);
        $this->__validateVerify($token, $authy_id);

        $resp = $this->rest->get("verify/{$token}/{$authy_id}", array(
            'query' => $params
        ));

        return new AuthyToken($resp);
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
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->get("sms/{$authy_id}", array(
            'query' => $opts
        ));

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
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->get("call/{$authy_id}", array(
            'query' => $opts
        ));

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
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->post("users/delete/{$authy_id}");
        return new AuthyResponse($resp);
    }

    /**
     * Gets user status.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function userStatus($authy_id)
    {
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->get("users/{$authy_id}/status");
        return new AuthyResponse($resp);
    }

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $via The method the token will be sent to user (sms or call)
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart($phone_number, $country_code, $via='sms', $locale=null)
    {
        $query = array(
            "phone_number" => $phone_number,
            "country_code" => $country_code,
            "via"          => $via
        );

        if ($locale != null)
          $query["locale"] = $locale;

        $resp = $this->rest->post("phones/verification/start", array('query' => $query));

        return new AuthyResponse($resp);
    }

    /**
     * Phone verification check. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $verification_code The verification code entered by the user to be checked
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationCheck($phone_number, $country_code, $verification_code)
    {
        $resp = $this->rest->get("phones/verification/check", array(
            'query' => array(
                "phone_number"      => $phone_number,
                "country_code"      => $country_code,
                "verification_code" => $verification_code
            )
        ));

        return new AuthyResponse($resp);
    }

    /**
     * Phone information. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function phoneInfo($phone_number, $country_code)
    {
        $resp = $this->rest->get("phones/info", array(
            'query' => array(
                "phone_number" => $phone_number,
                "country_code" => $country_code
            )
        ));

        return new AuthyResponse($resp);
    }

    private function __getUserAgent()
    {
        return sprintf(
            'AuthyPHP/%s (%s-%s-%s; PHP %s)',
            AuthyApi::VERSION,
            php_uname('s'),
            php_uname('r'),
            php_uname('m'),
            phpversion()
        );
    }

    private function __validateVerify($token, $authy_id)
    {
        $this->__validate_digit($token, "Invalid Token. Only digits accepted.");
        $this->__validate_digit($authy_id, "Invalid Authy id. Only digits accepted.");
        $length = strlen((string)$token);
        if( $length < 6 or $length > 10 ) {
            throw new AuthyFormatException("Invalid Token. Unexpected length.");
        }
    }

    private function __validate_digit($var, $message)
    {
        if( !is_int($var) && !is_numeric($var) ) {
            throw new AuthyFormatException($message);
        }
    }


}
