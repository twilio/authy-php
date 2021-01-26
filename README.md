[![Packagist version](https://badge.fury.io/ph/authy%2Fphp.svg)](https://packagist.org/packages/authy/php) [![Build Status](https://travis-ci.org/twilio/authy-php.svg?branch=master)](https://travis-ci.org/twilio/authy-php)

# PHP Client for Twilio Authy Two-Factor Authentication (2FA) API

Documentation for PHP usage of the Authy API lives in the [official Twilio documentation](https://www.twilio.com/docs/authy/api/).

The Authy API supports multiple channels of 2FA:
* One-time passwords via SMS and voice.
* Soft token ([TOTP](https://www.twilio.com/docs/glossary/totp) via the Authy App)
* Push authentication via the Authy App

If you only need SMS and Voice support for one-time passwords, we recommend using the [Twilio Verify API](https://www.twilio.com/docs/verify/api) instead. [More on how to choose between Authy and Verify here.](https://www.twilio.com/docs/verify/authy-vs-verify)

### Authy Quickstart

For a full tutorial, check out the PHP/Laravel Authy Quickstarts in our docs:
* [PHP/Laravel Authy Quickstart](https://www.twilio.com/docs/authy/quickstart/two-factor-authentication-php-laravel)

## Authy PHP Installation

This library requires PHP 5.6+

Install with [composer](https://www.twilio.com/docs/usage/tutorials/how-to-set-up-your-php-development-environment). The [`authy/php`](http://packagist.org/packages/authy/php) package is available on [Packagist](https://packagist.org/packages/authy/php).

Include in your `composer.json` as follows:

	{
	    "require": {
	        "authy/php": "3.0.5"
	    }
	}

## Usage

To use the Authy client, import AuthyApiClient and initialize it with your production API Key found in the [Twilio Console](https://www.twilio.com/console/authy/applications/):

```php
$authy_api = new Authy\AuthyApi('#your_api_key');
```

![authy api key in console](https://s3.amazonaws.com/com.twilio.prod.twilio-docs/images/account-security-api-key.width-800.png)

## 2FA Workflow

1. [Create a user](https://www.twilio.com/docs/authy/api/users#enabling-new-user)
2. [Send a one-time password](https://www.twilio.com/docs/authy/api/one-time-passwords)
3. [Verify a one-time password](https://www.twilio.com/docs/authy/api/one-time-passwords#verify-a-one-time-password)

**OR**

1. [Create a user](https://www.twilio.com/docs/authy/api/users#enabling-new-user)
2. [Send a push authentication](https://www.twilio.com/docs/authy/api/push-authentications)
3. [Check a push authentication status](https://www.twilio.com/docs/authy/api/push-authentications#check-approval-request-status)


## <a name="phone-verification"></a>Phone Verification

[Phone verification now lives in the Twilio API](https://www.twilio.com/docs/verify/api) and has [PHP support through the official Twilio helper libraries](https://www.twilio.com/docs/libraries/php). 

[Legacy (V1) documentation here.](verify-legacy-v1.md) **Verify V1 is not recommended for new development. Please consider using [Verify V2](https://www.twilio.com/docs/verify/api)**.

## Contributing

You can use docker to run tests and develop locally without the need to install the dependencies directly in your machine:

```
git clone git@github.com:authy/authy-php.git
cd authy-php
make docker-build # Creates the docker image
make docker-deps  # Install dependencies (in the `vendor` directory)
make docker-test  # Runs the tests
```

To contribute, make your changes in a branch and send a Pull Request to the twilio/authy-php repo.

## Copyright

Copyright (c) 2011-2021 Authy Inc. See [LICENSE](https://github.com/twilio/authy-php/blob/master/LICENSE.txt) for further details.
