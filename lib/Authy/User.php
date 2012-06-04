<?php

class AuthyUser extends \AuthyResponse {
    public function __construct($raw_response) {
        if(isset($raw_response['body']->user)) {
            $raw_response['body'] = $raw_response['body']->user; // response is {user: {id: id}}
        }

        parent::__construct($raw_response);
    }
}
