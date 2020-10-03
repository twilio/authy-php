<?php

use Authy\AuthyApi;
use Authy\AuthyFormatException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 */
class ApiTest extends TestCase
{
    /**
     * @var string $invalidToken a placeholder for an invalid token in tests
     */
    private string $invalidToken = '1234567';

    /**
     * @var string $validToken a placeholder for valid token in tests
     */
    private string $validToken = '000000';

    /**
     * @var string $testApiHost the api host for testing
     */
    private string $testApiHost = "http://sandbox-api.authy.com";

    public function testCreateUserWithValidDataWillReturnUserIdAsExpectedAndHaveNoErrors(): void
    {
        $mockClient = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);

        $this->assertIsInt($user->id(), 'The returned user ID is not an integer.');
        $this->assertEquals(2, $user->id(), 'Unexpected value returned for user id');
        $this->assertEmpty((array)$user->errors());
    }


    public function testCreateUserWithInvalidData()
    {
        $mockClient = $this->mockClient([[400, '{ "errors": { "message": "User was not valid", "email":"is invalid", "cellphone":"is invalid" } }']]);
        $user = $mockClient->registerUser('user@example.com', '', 1);

        $this->assertNull($user->id());
        $this->assertNotEmpty((array)$user->errors());

        $errors = (array)$user->errors();

        $this->assertArrayHasKey("message", $errors, 'The message key was not found');
        $this->assertArrayHasKey("cellphone", $errors, 'The cellphone error was not found');
        $this->assertEquals("User was not valid", $errors["message"], 'The message was incorrect');
        $this->assertEquals("is invalid", $errors["cellphone"], 'The cellphone was registered as valid');
    }

    public function testVerifyTokenWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [400, '{ "errors": { "message": "token invalid" } }']
        ]);

        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mockClient->verifyToken($user->id(), $this->invalidToken);

        $this->assertFalse($token->ok(), 'The token was valid.');
    }

    public function testVerifyTokenWithInvalidUser()
    {
        $mockClient = $this->mockClient([[404, '{"errors": {"message": "User doesn\'t exist"}}']]);
        $token = $mockClient->verifyToken(0, $this->invalidToken);

        $this->assertFalse($token->ok(), 'The token was valid');
        $this->assertNotEmpty((array)$token->errors(), 'The errors was empty');
        $this->assertEquals("User doesn't exist", $token->errors()->message, 'The message was incorrect');
    }

    public function testVerifyTokenWithInvalidToken()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [400, '{ "token": "is invalid" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mockClient->verifyToken($user->id(), $this->invalidToken);
        $this->assertFalse($token->ok(), 'The token was valid');
    }

    public function testVerifyTokenWithValidToken()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "token": "is valid" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $token = $mockClient->verifyToken($user->id(), $this->validToken);
        $this->assertTrue($token->ok(), 'The token was invalid');
    }

    public function testVerifyTokenWithNonNumericToken()
    {
        $mockClient = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);

        $this->expectException(AuthyFormatException::class);
        $this->expectExceptionMessage('Invalid Token. Only digits accepted.');

        $mockClient->verifyToken($user->id(), '123456/1#');
    }

    public function testVerifyTokenWithNonNumericAuthyId()
    {
        $mockClient = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $mockClient->registerUser('user@example.com', '305-456-2345', 1);

        $this->expectException(AuthyFormatException::class);
        $this->expectExceptionMessage('Invalid Authy id. Only digits accepted.');

        $mockClient->verifyToken('123456/1#', $this->validToken);
    }

    public function testVerifyTokenWithSmallerToken()
    {
        $mockClient = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);

        $this->expectException(AuthyFormatException::class);
        $this->expectExceptionMessage('Invalid Token. Unexpected length.');

        $mockClient->verifyToken($user->id(), '12345');
    }

    public function testVerifyTokenWithLongerToken()
    {
        $mockClient = $this->mockClient([[200, '{ "user": { "id": 2 } }']]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);

        $this->expectException(AuthyFormatException::class);
        $this->expectExceptionMessage('Invalid Token. Unexpected length.');

        $mockClient->verifyToken($user->id(), '12345');
    }

    public function testRequestSmsWithInvalidUser()
    {
        $mockClient = $this->mockClient([[400, '{ "token": "is invalid" }']]);
        $sms = $mockClient->requestSms(0, ["force" => "true"]);

        $this->assertFalse($sms->ok(), 'the SMS was not successful');
    }

    public function testRequestSmsWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "SMS sent" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $sms = $mockClient->requestSms($user->id(), ["force" => "true"]);

        $this->assertTrue($sms->ok(), 'The SMS was not successful');
    }

    public function testRequestOneTouchApprovalWithInvalidUser()
    {
        $mockClient = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $oneTouchApproval = $mockClient->createApprovalRequest(0, 'Request OneTouch Approval With Invalid User');

        $this->assertFalse($oneTouchApproval->ok(), 'The approval request was successful');
    }

    public function testRequestOneTouchApprovalWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "approval_request": { "uuid":"fd285c30-97f8-0135-cfa7-1241e5695bb0" }, "success": true }'],
        ]);

        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $oneTouchApproval = $mockClient->createApprovalRequest($user->id(), 'Request OneTouch Approval With Valid User');

        $this->assertTrue($oneTouchApproval->ok(), 'The approval was unsuccessful');
    }

    public function testCheckOneTouchApprovalWithInvalidUuid()
    {
        $mockClient = $this->mockClient([
            [404, '{ "message": "Approval request not found: 1231", "success": false, }'],
        ]);

        $oneTouchApproval = $mockClient->getApprovalRequest('1231');

        $this->assertFalse($oneTouchApproval->ok(), 'The approval was successful');
    }

    public function testCheckOneTouchApprovalWithValidUuid()
    {
        $mockClient = $this->mockClient([
            [200, '{ "approval_request": { "uuid":"fd285c30-97f8-0135-cfa7-1241e5695bb0" }, "success": true }'],
        ]);

        $oneTouchApproval = $mockClient->getApprovalRequest('fd285c30-97f8-0135-cfa7-1241e5695bb0');

        $this->assertTrue($oneTouchApproval->ok(), 'The approval was unsuccessful');
    }

    public function testPhoneCallWithInvalidUser()
    {
        $mockClient = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $call = $mockClient->phoneCall(0, []);

        $this->assertFalse($call->ok(), 'The phone call was successfulß');
        $this->assertEquals("User not found.", $call->errors()->message, 'The message was incorrect');
    }

    public function testPhoneCallWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "Call started" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $call = $mockClient->phoneCall($user->id(), []);

        $this->assertTrue($call->ok(), 'The call was unsuccessful');
        $this->assertMatchesRegularExpression('/Call started/i', $call->message());
    }

    public function testDeleteUserWithInvalidUser()
    {
        $mockClient = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $response = $mockClient->deleteUser(0);

        $this->assertFalse($response->ok(), 'The user was deleted');
        $this->assertEquals("User not found.", $response->errors()->message, 'The message was incorrect');
    }

    public function testDeleteUserWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "User deleted" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $response = $mockClient->deleteUser($user->id());

        $this->assertTrue($response->ok(), 'The user was not deleted');
    }

    public function testUserStatusWithInvalidUser()
    {
        $mockClient = $this->mockClient([[404, '{"errors": {"message": "User not found."}}']]);
        $response = $mockClient->userStatus(0);

        $this->assertFalse($response->ok(), 'The service successfully responded');
        $this->assertEquals("User not found.", $response->errors()->message, 'The message was incorrect');
    }

    public function testUserStatusWithValidUser()
    {
        $mockClient = $this->mockClient([
            [200, '{ "user": { "id": 2 } }'],
            [200, '{ "message": "User status" }']
        ]);
        $user = $mockClient->registerUser('user@example.com', '305-456-2345', 1);
        $response = $mockClient->userStatus($user->id());

        $this->assertTrue($response->ok(), 'The status was not returned');
    }

    public function testPhoneVerificationStartWithoutVia()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Text message sent"}')]);
        $handler = HandlerStack::create($mock);
        $mockClient = new AuthyApi('test_api_key', $this->testApiHost, $handler);

        $response = $mockClient->PhoneVerificationStart('111-111-1111', '1');

        $this->assertTrue($response->ok(), 'The phone verification failed');
        $this->assertMatchesRegularExpression('/Text message sent/i', $response->message(), 'The message was incorrect');
    }

    public function testPhoneVerificationStartWithVia()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Call to xxx-xxx-1111 initiated"}')]);
        $handler = HandlerStack::create($mock);
        $mockClient = new AuthyApi('test_api_key', $this->testApiHost, $handler);

        $response = $mockClient->PhoneVerificationStart('111-111-1111', '1', 'call');

        $this->assertTrue($response->ok(), 'The phone verification failed to start');
        $this->assertMatchesRegularExpression('/Call to .* initiated/i', $response->message(), 'The message was incorrect');
    }

    public function testPhoneVerificationStartWithCodeLength()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Call to xxx-xxx-1111 initiated"}')]);
        $handler = HandlerStack::create($mock);
        $mockClient = new AuthyApi('test_api_key', $this->testApiHost, $handler);

        $response = $mockClient->PhoneVerificationStart('111-111-1111', '1', 'call', '6');

        $this->assertTrue($response->ok(), 'The phone verification failed to start');
        $this->assertMatchesRegularExpression('/Call to .* initiated/i', $response->message(), 'The message was incorrect');
    }

    public function testPhoneVerificationCheck()
    {
        $mock = new MockHandler([new Response(200, [], '{"message": "Verification code is correct"}')]);
        $handler = HandlerStack::create($mock);
        $mockClient = new AuthyApi('test_api_key', $this->testApiHost, $handler);

        $response = $mockClient->PhoneVerificationCheck('111-111-1111', '1', '0000');

        $this->assertTrue($response->ok(), 'The phone verification check failed');
        $this->assertMatchesRegularExpression('/Verification code is correct/i', $response->message(), 'The message was incorrect');
    }

    public function testPhoneInfo()
    {
        $mockClient = $this->mockClient([[200, '{"message": "Phone number information"}']]);
        $response = $mockClient->PhoneInfo('111-111-1111', '1');

        $this->assertTrue($response->ok(), 'The phone number information check failed');
        $this->assertMatchesRegularExpression('/Phone number information/i', $response->message(), 'The message was incorrect');
    }

    private function mockClient($responseDatas)
    {
        $responses = [];
        foreach($responseDatas as $responseData) {
            array_push($responses, new Response($responseData[0], [], $responseData[1]));
        }

        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return new AuthyApi('test_api_key', $this->testApiHost, $handler);
    }
}
