# PHP Client for Authy API

A php library for using the Authy public API.


## Installation

TODO

## Usage

To use this client you just need to use AuthyApi and initialize it with your API KEY


    $authy_api = new AuthyApi('#your_api_key');

Now that you have an Authy API object you can start sending requests.


### Creating Users

Creating users is very easy, you need to pass an email, a cellphone and _optionally_ a country code:
   
    $user = $authy_api->registerUser('new_user@email.com', '405-342-5699', 57); //email, cellphone, area_code

in this case `57` is the country code(Colombia), use `1` for USA. If non are specified it defaults to USA.

You can easily see if the user was created by calling `ok()`.
If request went right, you need to store the authy id in your database. Use `user->id()` to get this `id` in your database.

    if($user->ok())
        // store user.id in your user database

if something goes wrong `ok()` returns `false` and you can see the errors using the following code

    $user->errors();

it returns a dictionary explaining what went wrong with the request.


### Verifying Tokens

To verify users you need the user id and a token. The token you get from the user through your login form. 

    $verification = $authy_api->verifyToken('authy-id', 'token-entered-by-the-user')

Once again you can use `ok()` to verify whether the token was valid or not.

    if($verification->ok())
        // the user is valid


### Requesting SMS Tokens

To request a SMS token you only need the user id.

	$sms = $authy_api->requestSms('authy-id');

As always, you can use `ok()` to verify if the token was sent. To be able to use this method you need to have activated the SMS plugin for your Authy App.


### More…

You can fine the full API documentation in the [official documentation](https://docs.authy.com) page.






