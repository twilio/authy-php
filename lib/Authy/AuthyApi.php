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
    const VERSION = '3.0.4';

    protected $rest;
    protected $api_url;

    /**
     * Constructor.
     *
     * @param string $api_key Api Key
     * @param string $api_url Optional api url
     */
    public function __construct($api_key, $api_url = "https://api.authy.com", $http_handler = null)
    {
        $client_opts = [
            'base_uri'      => "{$api_url}/",
            'headers'       => ['User-Agent' => $this->__getUserAgent(), 'X-Authy-API-Key' => $api_key],
            'http_errors'   => false
        ];

        if($http_handler != null)
        {
            $client_opts['handler'] = $http_handler;
        }

        $this->rest = new \GuzzleHttp\Client($client_opts);

        $this->api_url = $api_url;
        $this->default_options = ['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]];
    }

    /**
     * Register a user.
     *
     * @param  string    $email        New user's email
     * @param  string    $cellphone    New user's cellphone
     * @param  int       $country_code New user's country code. defaults to USA(1)
     * @return AuthyUser the new registered user
     */
    public function registerUser($email, $cellphone, $country_code = 1, $send_install_link = True)
    {
        $resp = $this->rest->post('protected/json/users/new', array_merge(
            $this->default_options,
            [
                'query' => [
                    'user' => [
                        "email"                     => $email,
                        "cellphone"                 => $cellphone,
                        "country_code"              => $country_code,
                        "send_install_link_via_sms" => $send_install_link,
                    ]
                ]
            ]
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
    public function verifyToken($authy_id, $token, $opts = [])
    {
        if (! array_key_exists("force", $opts)) {
            $opts["force"] = "true";
        } else {
            unset($opts["force"]);
        }

        $token = urlencode($token);
        $authy_id = urlencode($authy_id);
        $this->__validateVerify($token, $authy_id);

        $resp = $this->rest->get("protected/json/verify/{$token}/{$authy_id}", array_merge(
            $this->default_options,
            ['query' => $opts]
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
    public function requestSms($authy_id, $opts = [])
    {
        $authy_id = urlencode($authy_id);

        $resp = $this->rest->get("protected/json/sms/{$authy_id}", array_merge(
            $this->default_options,
            ['query' => $opts]
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
    public function phoneCall($authy_id, $opts = [])
    {
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->get("protected/json/call/{$authy_id}", array_merge(
            $this->default_options,
            ['query' => $opts]
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
        $resp = $this->rest->post("protected/json/users/{$authy_id}/remove", $this->default_options);
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
        $resp = $this->rest->get("protected/json/users/{$authy_id}/status", $this->default_options);
        return new AuthyResponse($resp);
    }

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $via The method the token will be sent to user (sms or call)
     * @param int $code_length The length of the verifcation code to be sent to the user
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart($phone_number, $country_code,
                                           $via='sms', $code_length=4,
                                           $locale=null)
    {

        $query = [
            "phone_number" => $phone_number,
            "country_code" => $country_code,
            "via"          => $via,
            "code_length"  => $code_length
        ];

        if ($locale != null) {
            $query["locale"] = $locale;
        }

        $resp = $this->rest->post("protected/json/phones/verification/start", array_merge(
            $this->default_options,
            ['query' => $query]
        ));

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
        $resp = $this->rest->get("protected/json/phones/verification/check", array_merge(
            $this->default_options,
            [
                'query' => [
                    "phone_number"      => $phone_number,
                    "country_code"      => $country_code,
                    "verification_code" => $verification_code
                ]
            ]
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
        $resp = $this->rest->get("protected/json/phones/info", array_merge(
            $this->default_options,
            [
                'query' => [
                    "phone_number" => $phone_number,
                    "country_code" => $country_code
                ]
            ]
        ));

        return new AuthyResponse($resp);
    }

    /**
     * Create a new approval request for a user
     *
     * @param string $authy_id User's id stored in your database
     * @param array  $opts     Array of options
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#create-approvalrequest
     */
    public function createApprovalRequest($authy_id, $message, $opts = [])
    {
        $opts['message'] = $message;
        
        $authy_id = urlencode($authy_id);
        $resp = $this->rest->post("onetouch/json/users/{$authy_id}/approval_requests", array_merge(
            $this->default_options,
            ['query' => $opts]
        ));

        return new AuthyResponse($resp);
    }

    /**
     * Check the status of an approval request
     *
     * @param string $request_uuid The UUID of the approval request you want to check
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#check-approvalrequest-status
     */
    public function getApprovalRequest($request_uuid)
    {
        $request_uuid = urlencode($request_uuid);
        $resp = $this->rest->get("onetouch/json/approval_requests/{$request_uuid}");

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
