# PHP Client for Authy API

[![Build Status](https://travis-ci.org/twilio/authy-php.svg?branch=master)](https://travis-ci.org/twilio/authy-php)

A php library for using the Authy API.

## Installation

This library requires PHP 5.6+

### Install via composer:

[`authy/php`](http://packagist.org/packages/authy/php) package is available on [Packagist](https://packagist.org/packages/authy/php).

Include it in your `composer.json` as follows:

	{
	    "require": {
	        "authy/php": "3.0"
	    }
	}


## Usage

To use this client you just need to use Authy_Api and initialize it with your API KEY


    $authy_api = new Authy\AuthyApi('#your_api_key');

Now that you have an Authy API object you can start sending requests.

## Creating Users

__NOTE: User is matched based on cellphone and country code not e-mail.
A cellphone is uniquely associated with an authy_id.__

Creating users is very easy, you need to pass an email, a cellphone and _optionally_ a country code:

    $user = $authy_api->registerUser('new_user@email.com', '405-342-5699', 1); //email, cellphone, country_code

in this case `1` is the country code (USA). If no country code is specified, it defaults to USA.

You can easily see if the user was created by calling `ok()`.
If request went right, you need to store the authy id in your database. Use `user->id()` to get this `id` in your database.

    if($user->ok())
        // store user->id() in your user database

if something goes wrong `ok()` returns `false` and you can see the errors using the following code

    else
        foreach($user->errors() as $field => $message) {
          printf("$field = $message");
        }

it returns a dictionary explaining what went wrong with the request. Errors will be in plain English and can
be passed back to the user.


## Verifying Tokens


__NOTE: Token verification is only enforced if the user has completed registration. To change this behaviour see Forcing Verification section below.__

   >*Registration is completed once the user installs and registers the Authy mobile app or logins once successfully using SMS.*


To verify tokens you need the user id and the token. The token you get from the user through your login form.

    $verification = $authy_api->verifyToken('authy-id', 'token-entered-by-the-user');

Once again you can use `ok()` to verify whether the token was valid or not.

    if($verification->ok())
        // the user is valid

#### Forcing Verification

If you wish to verify tokens even if the user has not yet complete registration, pass force=true when verifying the token.

    $verification = $authy_api->verifyToken('authy-id', 'token-entered-by-the-user', array("force" => "true"));

## Requesting SMS Tokens
To be able to use this method you need to have activated the SMS plugin for your Authy App.

To request a SMS token you only need the user id.

	$sms = $authy_api->requestSms('authy-id');

As always, you can use `ok()` to verify if the token was sent.
This call will be ignored if the user is using the Authy Mobile App. If you still want to send
the SMS pass `force=>true` as an option

    $sms = $authy_api->requestSms('authy-id', array("force" => "true"));
    
Additional options can be passed into the array, such as [custom actions](https://www.twilio.com/docs/api/authy/rest/one-time-passwords#custom-actions-optional):

    $sms = $authy_api->requestSms('authy-id', array("action" => "login", "action_message" => "Login code"));

## Checking User Status

To check a user status, just pass the user id.

    $status = $authy_api->userStatus('authy_id');

## Phone Verification && Info

Authy has an API to verify users via phone calls or sms. Also, user phone information can be gethered
for support and verification purposes.

### Phone Verification Start

In order to start a phone verification, we ask the API to send a token to the user via sms or call:

    $authy_api->phoneVerificationStart('111-111-1111', '1', 'sms');

Optionally you can specify the language that you prefer the phone verification message to be sent. Supported
languages include: English (`en`), Spanish (`es`), Portuguese (`pt`), German (`de`), French (`fr`) and
Italian (`it`). If not specified, English will be used.

    $authy_api->phoneVerificationStart('111-111-1111', '1', 'sms', 'es');
    // This will send a message in spanish

### Phone Verification Check

Once you get the verification from user, you can check if it's valid with:

    $authy_api->phoneVerificationCheck('111-111-1111', '1', '0000');

### Phone Info

If you want to gather additional information about user phone, use phones info.

    $authy_api->phoneInfo('111-111-1111', '1');

## Tests

You will need to install composer `https://getcomposer.org/download/`
and install dependencies with `composer install --no-dev`. Also
You will need to install phpunit `https://phpunit.de/manual/current/en/installation.html`

Then you can run test by executing this command `make`

## Contribute
You can use docker to run tests and develop locally without the need to install the dependencies directly in your machine:

```
git clone git@github.com:authy/authy-php.git
cd authy-php
make docker-build # Creates the docker image
make docker-deps  # Install dependencies (in the `vendor` directory)
make docker-test  # Runs the tests
```

To contribute, just make your changes and send a Pull Request to the authy/authy-php repo.

### More…

You can find the full API documentation in the [official documentation](https://docs.authy.com) page.

## Copyright

Copyright (c) 2011-2020 Authy Inc.
