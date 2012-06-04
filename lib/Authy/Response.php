<?php

class AuthyResponse {
    protected $raw_response;
    protected $body;
    protected $errors;

    public function __construct($raw_response) {
        $this->raw_response = $raw_response;
        $this->body = $raw_response['body'];
        $this->errors = new stdClass();

        // Handle errors
        if(isset($this->body->errors)) {
            $this->errors = $this->body->errors; // when response is {errors: {}}
            unset($this->body->errors);
        } else if($raw_response['status'] == 400) {
            $this->errors = $this->body; // body here is a stdClass
            $this->body = new stdClass();
        } else if($raw_response['status'] != 200 && gettype($this->body) == 'string') {
            $this->errors = (object) array("error" => $this->body); // the response was an error so put the body as an error
            $this->body = new stdClass();
        }
    }

    public function ok() {
        return $this->raw_response['status'] == 200;
    }

    public function id() {
        return isset($this->body->id) ? $this->body->id : null;
    }

    public function errors() {
        return $this->errors;
    }
}