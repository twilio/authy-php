<?php
use Authy\AuthyApi;
class ApiTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $invalid_token;
    private $valid_token;

    public function setUp()
    {
        $this->client = new AuthyApi('bf12974d70818a08199d17d5e2bae630', 'http://sandbox-api.authy.com');
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
        $this->assertEquals("User was not valid.", $errors["message"]);
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
        $this->assertEquals("User doesn't exist.", $token->errors()->message);
    }

    public function testVerifyTokenWithInvalidToken()
    {
        $user = $this->client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $this->client->verifyToken($user->id(), $this->valid_token);
        $this->assertEquals(true, $token->ok());
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

        $this->assertEquals(false, $call->ok());
        $this->assertEquals("Call was NOT done", $call->errors()->message);
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
}
