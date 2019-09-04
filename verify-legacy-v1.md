# Phone Verification V1

[Version 2 of the Verify API is now available!](https://www.twilio.com/docs/verify/api) V2 has an improved developer experience and new features. Some of the features of the V2 API include:

* Updated Twilio helper libraries in JavaScript, Java, C#, Python, Ruby, and PHP
* PSD2 Secure Customer Authentication Support
* Improved Visibility and Insights

You are currently viewing Version 1. V1 of the API will be maintained for the time being, but any new features and development will be on Version 2. We strongly encourage you to do any new development with API V2. Check out the migration guide or the API Reference for more information.

### API Reference

API Reference is available at https://www.twilio.com/docs/verify/api/v1

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