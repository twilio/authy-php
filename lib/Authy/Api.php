<?php

class AuthyApi
{
    const VERSION = '0.0.1';
    protected $rest;
    protected $api_key;
    protected $api_url;

    public function __construct($api_key, $api_url = "http://sandbox-api.authy.com")
    {
        $this->rest = new Resty();
        $this->rest->setBaseURL($api_url);
        $this->rest->setUserAgent("authy-php v".AuthyApi::VERSION);

        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    public function register_user($email, $cellphone, $country_code) {
        $params = $this->default_params();
        $params['user'] = array(
            "email" => $email,
            "country_code" => $country_code,
            "cellphone" => $cellphone
        );

        $resp = $this->rest->post('/protected/json/users/new', $params);

        return new AuthyUser($resp);
    }

    public function verify_token($authy_id, $token, $opts = array()) {
        $params = array_merge($this->default_params(), $opts);
        $resp = $this->rest->get('/protected/json/verify/'. urlencode($token) .'/'. urlencode($authy_id), $params);

        return new AuthyResponse($resp);
    }

    public function request_sms($authy_id, $opts = array()) {
        $params = array_merge($this->default_params(), $opts);

        $resp = $this->rest->get('/protected/json/sms/'.urlencode($authy_id), $params);

        return new AuthyResponse($resp);
    }

    protected function default_params() {
        return array("api_key" => $this->api_key);
    }
};
