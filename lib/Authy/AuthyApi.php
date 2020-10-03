<?php

namespace Authy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

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
 * Authy API interface.
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */
class AuthyApi
{
    private const VERSION = '3.0.4';

    /**
     * @var Client $client
     */
    protected Client $client;

    /**
     * @var string $apiUrl
     */
    protected string $apiUrl;

    /**
     * @var array|array[] the default options for the setup
     */
    private array $defaultOptions = [
        'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
    ];

    /**
     * Constructor.
     *
     * @param string $apiKey Api Key
     * @param string $apiUrl Optional api url, defaulting to https://api.authy.com
     * @param null|HandlerStack $httpHandlerStack Optional handler stack for the guzzle client
     */
    public function __construct(
        string $apiKey,
        string $apiUrl = "https://api.authy.com",
        ?HandlerStack $httpHandlerStack = null
    ) {
        $this->apiUrl = $apiUrl;

        $clientOptions = [
            'base_uri' => $this->getApiUrl() . '/',
            'headers' => [
                'User-Agent' => $this->getUserAgent(),
                'X-Authy-API-Key' => $apiKey
            ],
            'http_errors' => false
        ];

        if ($httpHandlerStack !== null) {
            $clientOptions['handler'] = $httpHandlerStack;
        }

        $this->client = new Client($clientOptions);
    }

    /**
     * Register a user.
     *
     * @param string $email New user's email
     * @param string $cellphone New user's cellphone
     * @param int $countryCode New user's country code. defaults to USA(1)
     * @param bool $sendInstallLink should the install link be sent by sms, defaults to true
     * @return AuthyUser the new registered user
     */
    public function registerUser(
        string $email,
        string $cellphone,
        int $countryCode = 1,
        bool $sendInstallLink = true
    ): AuthyUser {
        $response = $this->getClient()->post(
            'protected/json/users/new',
            array_merge(
                $this->getDefaultOptions(),
                [
                    'query' => [
                        'user' => [
                            "email" => $email,
                            "cellphone" => $cellphone,
                            "country_code" => $countryCode,
                            "send_install_link_via_sms" => $sendInstallLink,
                        ]
                    ]
                ]
            )
        );

        return new AuthyUser($response);
    }

    /**
     * Verify a given token.
     *
     * @param string $authyId User's id stored in your database
     * @param string $token The token entered by the user
     * @param array $options Array of options, for example: array("force" => "true")
     *
     * @return AuthyToken the AuthyToken Response
     * @throws AuthyFormatException
     */
    public function verifyToken(
        string $authyId,
        string $token,
        $options = []
    ): AuthyToken {
        if (!array_key_exists('force', $options)) {
            $options['force'] = 'true';
        } else {
            unset($options['force']);
        }

        $token = urlencode($token);
        $authyId = urlencode($authyId);
        $this->validateVerify($token, $authyId);

        $response = $this->getClient()->get(
            "protected/json/verify/{$token}/{$authyId}",
            array_merge(
                $this->getDefaultOptions(),
                ['query' => $options]
            )
        );

        return new AuthyToken($response);
    }

    /**
     * Request a valid token via SMS.
     *
     * @param string $authyId User's id stored in your database
     * @param array $options Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function requestSms(string $authyId, array $options = []): AuthyResponse
    {
        $authyId = urlencode($authyId);

        $response = $this->getClient()->get(
            "protected/json/sms/{$authyId}",
            array_merge(
                $this->getDefaultOptions(),
                ['query' => $options]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Cellphone call, usually used with SMS Token issues or if no smartphone is available.
     * This function needs the app to be on Starter Plan (free) or higher.
     *
     * @param string $authyId User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function phoneCall(string $authyId, array $opts = []): AuthyResponse
    {
        $authyId = urlencode($authyId);
        $response = $this->getClient()->get(
            "protected/json/call/{$authyId}",
            array_merge(
                $this->getDefaultOptions(),
                ['query' => $opts]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Deletes an user.
     *
     * @param string $authyId User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function deleteUser(string $authyId): AuthyResponse
    {
        $authyId = urlencode($authyId);
        $response = $this->getClient()->post(
            "protected/json/users/{$authyId}/remove",
            $this->getDefaultOptions()
        );
        return new AuthyResponse($response);
    }

    /**
     * Gets user status.
     *
     * @param string $authyId User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function userStatus(string $authyId): AuthyResponse
    {
        $authyId = urlencode($authyId);

        $response = $this->getClient()->get(
            "protected/json/users/{$authyId}/status",
            $this->getDefaultOptions()
        );

        return new AuthyResponse($response);
    }

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phoneNumber User's phone_number stored in your database
     * @param string $countryCode User's phone country code stored in your database
     * @param string $via optional method the token will be sent to user (sms or call), defaults to sms
     * @param int $codeLength optional length of the verification code to be sent to the user, defaults to 4
     * @param string|null $locale optional locale
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart(
        string $phoneNumber,
        string $countryCode,
        string $via = 'sms',
        int $codeLength = 4,
        ?string $locale = null
    ): AuthyResponse {
        $query = [
            "phone_number" => $phoneNumber,
            "country_code" => $countryCode,
            "via" => $via,
            "code_length" => $codeLength
        ];

        if ($locale != null) {
            $query["locale"] = $locale;
        }

        $response = $this->getClient()->post(
            "protected/json/phones/verification/start",
            array_merge(
                $this->defaultOptions,
                ['query' => $query]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Phone verification check. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phoneNumber User's phone_number stored in your database
     * @param string $countryCode User's phone country code stored in your database
     * @param string $verificationCode The verification code entered by the user to be checked
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationCheck(
        string $phoneNumber,
        string $countryCode,
        string $verificationCode
    ): AuthyResponse {
        $response = $this->getClient()->get(
            "protected/json/phones/verification/check",
            array_merge(
                $this->getDefaultOptions()
,                [
                    'query' => [
                        "phone_number" => $phoneNumber,
                        "country_code" => $countryCode,
                        "verification_code" => $verificationCode,
                    ],
                ]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Phone information. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phoneNumber User's phone_number stored in your database
     * @param string $countryCode User's phone country code stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function phoneInfo(string $phoneNumber, string $countryCode): AuthyResponse
    {
        $response = $this->getClient()->get(
            "protected/json/phones/info",
            array_merge(
                $this->getDefaultOptions(),
                [
                    'query' => [
                        "phone_number" => $phoneNumber,
                        "country_code" => $countryCode,
                    ],
                ]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Create a new approval request for a user
     *
     * @param string $authyId User's id stored in your database
     * @param string $message a message to include
     * @param array $options optional array of options
     *
     * @return AuthyResponse
     *
     * @link http://docs.authy.com/onetouch.html#create-approvalrequest
     */
    public function createApprovalRequest(
        string $authyId,
        string $message,
        array $options = []
    ): AuthyResponse {
        $opts['message'] = $message;

        $authyId = urlencode($authyId);
        $response = $this->getClient()->post(
            "onetouch/json/users/{$authyId}/approval_requests",
            array_merge(
                $this->getDefaultOptions(),
                ['query' => $options]
            )
        );

        return new AuthyResponse($response);
    }

    /**
     * Check the status of an approval request
     *
     * @param string $requestUuid The UUID of the approval request you want to check
     *
     * @return AuthyResponse
     *
     * @link http://docs.authy.com/onetouch.html#check-approvalrequest-status
     */
    public function getApprovalRequest(string $requestUuid): AuthyResponse
    {
        $requestUuid = urlencode($requestUuid);
        $response = $this->getClient()->get("onetouch/json/approval_requests/{$requestUuid}");

        return new AuthyResponse($response);
    }

    /**
     * @return string
     */
    private function getUserAgent(): string
    {
        return sprintf(
            'AuthyPHP/%s (%s-%s-%s; PHP %s)',
            self::VERSION,
            php_uname('s'),
            php_uname('r'),
            php_uname('m'),
            phpversion()
        );
    }

    /**
     * @param mixed $token
     * @param mixed $authyId
     * @throws AuthyFormatException
     */
    private function validateVerify($token, $authyId): void
    {
        $this->validateDigit($token, "Invalid Token. Only digits accepted.");
        $this->validateDigit($authyId, "Invalid Authy id. Only digits accepted.");

        $length = strlen((string)$token);

        if ($length < 6 || $length > 10) {
            throw new AuthyFormatException("Invalid Token. Unexpected length.");
        }
    }

    /**
     * @param mixed $value
     * @param string $message
     * @throws AuthyFormatException
     */
    private function validateDigit($value, string $message): void
    {
        if (!is_int($value) && !is_numeric($value)) {
            throw new AuthyFormatException($message);
        }
    }

    /**
     * @return string the api URL as set by the constructor
     */
    private function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * @return Client the Guzzle client for this library
     */
    private function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return array[] the default options as set by the constructor
     */
    private function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }
}
