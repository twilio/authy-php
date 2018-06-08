<?php
require_once __DIR__.'/TestHelper.php';

use Authy\AuthyApi;
use Authy\AuthyFormatException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Stream\Stream;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    private $invalid_token;
    private $valid_token;

    public function setUp()
    {
        $this->invalid_token = '1234567';
        $this->valid_token = '0000000';
    }

    public function testCreateUserWithValidData()
    {
        $mock_client = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);

        $this->assertEquals("integer", gettype($user->id()));
        $this->assertEmpty((array) $user->errors());
    }

    public function testCreateUserWithInvalidData()
    {
        $mock_client = $this->mockClient([[400, '{ "errors": { "message": "User was not valid", "email":"is invalid", "cellphone":"is invalid" } }']]);
        $user = $mock_client->registerUser('user@example.com', '', 1);

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
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [400, '{ "errors": { "message": "token invalid" } }']
        ]);

        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mock_client->verifyToken($user->id(), $this->invalid_token);

        $this->assertEquals(false, $token->ok());
    }

    public function testVerifyTokenWithInvalidUser()
    {
        $mock_client = $this->mockClient([[404, '{"errors": {"message": "User doesn\'t exist"}}']]);
        $token = $mock_client->verifyToken(0, $this->invalid_token);

        $this->assertEquals(false, $token->ok());
        $this->assertNotEmpty((array) $token->errors());
        $this->assertEquals("User doesn't exist", $token->errors()->message);
    }

    public function testVerifyTokenWithInvalidToken()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [400, '{ "token": "is invalid" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mock_client->verifyToken($user->id(), $this->invalid_token);
        $this->assertEquals(false, $token->ok());
    }

    public function testVerifyTokenWithValidToken()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "token": "is valid" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mock_client->verifyToken($user->id(), $this->valid_token);
        $this->assertEquals(true, $token->ok());
    }

    public function testVerifyTokenWithNonNumericToken()
    {
        $mock_client = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $mock_client->verifyToken($user->id(), '123456/1#');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Only digits accepted.');
            return;
        }
        $this->fail('AuthyFormatException has not been raised.');
    }

    public function testVerifyTokenWithNonNumericAuthyId()
    {
        $mock_client = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $mock_client->verifyToken('123456/1#', $this->valid_token);
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Authy id. Only digits accepted.');
            return;
        }
        $this->fail('AuthyFormatException has not been raised.');
    }

    public function testVerifyTokenWithSmallerToken()
    {
        $mock_client = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $mock_client->verifyToken($user->id(), '12345');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Unexpected length.');
            return;
        }
        $this->fail('AuthyFormatException has not been raised.');
    }

    public function testVerifyTokenWithLongerToken()
    {
        $mock_client = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        try {
            $token = $mock_client->verifyToken($user->id(), '12345678901');
        } catch (AuthyFormatException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Token. Unexpected length.');
            return;
        }
        $this->fail('AuthyFormatException has not been raised.');
    }

    public function testRequestSmsWithInvalidUser()
    {
        $mock_client = $this->mockClient([[400, '{ "token": "is invalid" }']]);
        $sms = $mock_client->requestSms(0, ["force" => "true"]);

        $this->assertEquals(false, $sms->ok());
    }

    public function testRequestSmsWithValidUser()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "SMS sent" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $sms = $mock_client->requestSms($user->id(), ["force" => "true"]);

        $this->assertEquals(true, $sms->ok());
        //$this->assertEquals("is not activated for this account", $sms->errors()->enable_sms);
    }

    /**
     *
     */
    public function testRequestOneTouchApprovalWithInvalidUser()
    {
        $mock_client = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $oneTouchApproval = $mock_client->createApprovalRequest(0, 'Request OneTouch Approval With Invalid User');

        $this->assertEquals(false, $oneTouchApproval->ok());
    }

    /**
     *
     */
    public function testRequestOneTouchApprovalWithValidUser()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "approval_request": { "uuid":"fd285c30-97f8-0135-cfa7-1241e5695bb0" }, "success": true }'],
        ]);

        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $oneTouchApproval = $mock_client->createApprovalRequest($user->id(), 'Request OneTouch Approval With Valid User');

        $this->assertEquals(true, $oneTouchApproval->ok());
    }

    /**
     *
     */
    public function testCheckOneTouchApprovalWithInvalidUuid()
    {
        $mock_client = $this->mockClient([
            [404, '{ "message": "Approval request not found: 1231", "success": false, }'],
        ]);

        $oneTouchApproval = $mock_client->getApprovalRequest('1231');

        $this->assertEquals(false, $oneTouchApproval->ok());
    }

    /**
     *
     */
    public function testCheckOneTouchApprovalWithValidUuid()
    {
        $mock_client = $this->mockClient([
            [200, '{ "approval_request": { "uuid":"fd285c30-97f8-0135-cfa7-1241e5695bb0" }, "success": true }'],
        ]);

        $oneTouchApproval = $mock_client->getApprovalRequest('fd285c30-97f8-0135-cfa7-1241e5695bb0');

        $this->assertEquals(true, $oneTouchApproval->ok());
    }

    public function testPhonceCallWithInvalidUser()
    {
        $mock_client = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $call = $mock_client->phoneCall(0, []);

        $this->assertEquals(false, $call->ok());
        $this->assertEquals("User not found.", $call->errors()->message);
    }

    public function testPhonceCallWithValidUser()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "Call started" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $call = $mock_client->phoneCall($user->id(), []);

        $this->assertEquals(true, $call->ok());
        $this->assertRegExp('/Call started/i', $call->message());
    }

    public function testDeleteUserWithInvalidUser()
    {
        $mock_client = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $response = $mock_client->deleteUser(0);

        $this->assertEquals(false, $response->ok());
        $this->assertEquals("User not found.", $response->errors()->message);
    }

    public function testDeleteUserWithValidUser()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "User deleted" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $response = $mock_client->deleteUser($user->id());

        $this->assertEquals(true, $response->ok());
    }

    public function testUserStatusWithInvalidUser()
    {
        $mock_client = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $response = $mock_client->userStatus(0);

        $this->assertEquals(false, $response->ok());
        $this->assertEquals("User not found.", $response->errors()->message);
    }

    public function testUserStatusWithValidUser()
    {
        $mock_client = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "User status" }']
        ]);
        $user = $mock_client->registerUser('user@example.com', '305-456-2345', 1);
        $response = $mock_client->userStatus($user->id());

        $this->assertEquals(true, $response->ok());
    }

    public function testPhoneVerificationStartWithoutVia()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Text message sent"}')]);
        $handler = HandlerStack::create($mock);
        $mock_client = new AuthyApi('test_api_key', $GLOBALS['test_api_host'], $handler);

        $response = $mock_client->PhoneVerificationStart('111-111-1111', '1');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Text message sent/i', $response->message());
    }

    public function testPhoneVerificationStartWithVia()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Call to xxx-xxx-1111 initiated"}')]);
        $handler = HandlerStack::create($mock);
        $mock_client = new AuthyApi('test_api_key', $GLOBALS['test_api_host'], $handler);

        $response = $mock_client->PhoneVerificationStart('111-111-1111', '1', 'call');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Call to .* initiated/i', $response->message());
    }

    public function testPhoneVerificationStartWithCodeLength()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Call to xxx-xxx-1111 initiated"}')]);
        $handler = HandlerStack::create($mock);
        $mock_client = new AuthyApi('test_api_key', $GLOBALS['test_api_host'], $handler);

        $response = $mock_client->PhoneVerificationStart('111-111-1111', '1', 'call', '6');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Call to .* initiated/i', $response->message());
    }

    public function testPhoneVerificationCheck()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Verification code is correct"}')]);
        $handler = HandlerStack::create($mock);
        $mock_client = new AuthyApi('test_api_key', $GLOBALS['test_api_host'], $handler);

        $response = $mock_client->PhoneVerificationCheck('111-111-1111', '1', '0000');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Verification code is correct/i', $response->message());
    }

    public function testPhoneInfo()
    {
        $mock_client = $this->mockClient([[200, '{"message": "Phone number information"}']]);
        $response = $mock_client->PhoneInfo('111-111-1111', '1');

        $this->assertEquals(true, $response->ok());
        $this->assertRegExp('/Phone number information/i', $response->message());
    }

    private function mockClient($_resp)
    {
        $responses = [];
        foreach($_resp as $r) {
            array_push($responses, new Response($r[0], [], $r[1]));
        }

        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return new AuthyApi('test_api_key', $GLOBALS['test_api_host'], $handler);
    }

}
