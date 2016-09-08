<?php
require_once __DIR__.'/TestHelper.php';

use Authy\AuthyApi;
use Authy\AuthyFormatException;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $invalid_token;
    private $valid_token;

    public function setUp()
    {
        $this->client = new AuthyApi($GLOBALS['test_api_key'], $GLOBALS['test_api_host']);
        $this->invalid_token = '1234567';
        $this->valid_token = '0000000';
    }

    public function testCreateUserWithValidData()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);

        $this->assertEquals("integer", gettype($user->id()));
        $this->assertEmpty((array) $user->errors());
    }

    public function testCreateUserWithInvalidData()
    {
        $user = $this->client->registerUser('user@example.com', '', 1);

        $this->assertEquals("NULL", gettype($user->id()));
        $this->assertNotEmpty((array) $user->errors());

        $errors = (array) $user->errors();

        $this->assertArrayHasKey("message", $errors);
        $this->assertArrayHasKey("cellphone", $errors);
        $this->assertEquals("User was not valid", $errors["message"]);
        $this->assertEquals("is invalid", $errors["cellphone"]);
    }

    public function testVerifyTokenWithValidUser()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $this->client->verifyToken($user->id(), $this->invalid_token);

        $this->assertEquals(false, $token->ok());
    }

    public function testVerifyTokenWithInvalidUser()
    {
        $token = $this->client->verifyToken(0, $this->invalid_token);

        $this->assertEquals(false, $token->ok());
        $this->assertNotEmpty((array) $token->errors());
        $this->assertEquals("User doesn't exist", $token->errors()->message);
    }

    public function testVerifyTokenWithInvalidToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $this->client->verifyToken($user->id(), $this->invalid_token);
        $this->assertEquals(false, $token->ok());
    }

    public function testVerifyTokenWithValidToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $this->client->verifyToken($user->id(), $this->valid_token);
        $this->assertEquals(true, $token->ok());
    }

    public function testVerifyTokenWithNonNumericToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $this->client->verifyToken($user->id(), '123456/1#');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Only digits accepted.');
            return;
        } 
        $this->fail('AuthyFormatException has not been raised.');            
    }

    public function testVerifyTokenWithNonNumericAuthyId()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $this->client->verifyToken('123456/1#', $this->valid_token);
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Authy id. Only digits accepted.');
            return;
        } 
        $this->fail('AuthyFormatException has not been raised.');            
    }

    public function testVerifyTokenWithSmallerToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $this->client->verifyToken($user->id(), '12345');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Unexpected length.');
            return;
        } 
        $this->fail('AuthyFormatException has not been raised.');            
    }

    public function testVerifyTokenWithLongerToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $this->client->verifyToken($user->id(), '12345678901');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Unexpected length.');
            return;
        } 
        $this->fail('AuthyFormatException has not been raised.');            
    }

    public function testRequestSmsWithInvalidUser()
    {
        $sms = $this->client->requestSms(0, array("force" => "true"));

        $this->assertEquals(false, $sms->ok());
    }

    public function testRequestSmsWithValidUser()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $sms = $this->client->requestSms($user->id(), array("force" => "true"));

        $this->assertEquals(true, $sms->ok());
        //$this->assertEquals("is not activated for this account", $sms->errors()->enable_sms);
    }

    public function testPhonceCallWithInvalidUser()
    {
        $call = $this->client->phoneCall(0, array());

        $this->assertEquals(false, $call->ok());
        $this->assertEquals("User not found.", $call->errors()->message);
    }

    public function testPhonceCallWithValidUser()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $call = $this->client->phoneCall($user->id(), array());

        $this->assertEquals(true, $call->ok());
        $this->assertRegExp('/Call started/i', $call->message());
    }

    public function testDeleteUserWithInvalidUser()
    {
        $response = $this->client->deleteUser(0);

        $this->assertEquals(false, $response->ok());
        $this->assertEquals("User not found.", $response->errors()->message);
    }

    public function testDeleteUserWithValidUser()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $response = $this->client->deleteUser($user->id());

        $this->assertEquals(true, $response->ok());
    }

    public function testUserStatusWithInvalidUser()
    {
        $response = $this->client->userStatus(0);

        $this->assertEquals(false, $response->ok());
        $this->assertEquals("User not found.", $response->errors()->message);
    }

    public function testUserStatusWithValidUser()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $response = $this->client->userStatus($user->id());

        $this->assertEquals(true, $response->ok());
    }

    public function testPhoneVerificationStartWithoutVia()
    {
        $response = $this->client->PhoneVerificationStart('111-111-1111', '1');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Text message sent/i', $response->message());
    }

    public function testPhoneVerificationStartWithVia()
    {
        $response = $this->client->PhoneVerificationStart('111-111-1111', '1', 'call');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Call to .* initiated/i', $response->message());
    }

    public function testPhoneVerificationCheck()
    {
        $response = $this->client->PhoneVerificationCheck('111-111-1111', '1', '0000');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Verification code is correct/i', $response->message());
    }

    public function testPhoneInfo()
    {
        $response = $this->client->PhoneInfo('111-111-1111', '1');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Phone number information/i', $response->message());
    }
}
