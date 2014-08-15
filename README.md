# PHP Client for Authy API

A php library for using the Authy API.

## Installation

This library requires PHP 5.3+

### Install via composer:

[`authy/php`](http://packagist.org/packages/authy/php) package is available on [Packagist](http://packagist.org).

Include it in your `composer.json` as follows:

	{
	    "require": {
	        "authy/php": "2.*"
	    }
	}


## Usage

To use this client you just need to use Authy_Api and initialize it with your API KEY


    $authy_api = new Authy\AuthyApi('#your_api_key');

Now that you have an Authy API object you can start sending requests.

NOTE: if you want to make requests to sandbox you have to pass the sandbox url as the second argument:

	$authy_api = new Authy\AuthyApi('#your_api_key', 'http://sandbox-api.authy.com');


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
the SMS pass force=>true as an option

	$sms = $authy_api->requestSms('authy-id', array("force" => "true"));


### More…

You can find the full API documentation in the [official documentation](https://docs.authy.com) page.






