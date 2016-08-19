<?php

namespace Neutron\Tests\ReCaptcha;

use Neutron\ReCaptcha\Exception\InvalidArgumentException;
use Neutron\ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;

class ReCaptchatest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function instanciationShouldBeGood()
    {
        new ReCaptcha($this->getClientMock(), 'pub', 'priv');
    }

    /** @test */
    public function createShouldBeGood()
    {
        ReCaptcha::create('pub', 'priv');
    }

    /**
     * @test
     * @dataProvider provideBadIps
     * @expectedException InvalidArgumentException
     */
    public function checkAnswerShouldFailWithoutIp($ip)
    {
        $recaptcha = new ReCaptcha($this->getClientMock(), 'pub', 'priv');
        $recaptcha->checkAnswer($ip, 'challenge', 'response');
    }

    /**
     * @test
     * @dataProvider provideBadChallengeResponse
     */
    public function checkAnswerShouldReturnErroredResponseWithWrongChallengeOrResponse($challenge, $response)
    {
        $recaptcha = new ReCaptcha($this->getClientMock(), 'pub', 'priv');
        $recaptcha->checkAnswer('ip', $challenge, $response);
    }

    /**
     * @test
     * @dataProvider provideBadChallengeResponse
     */
    public function checkIsNotSetup($private, $public)
    {
        $recaptcha = new ReCaptcha($this->getClientMock(), $private, $public);
        $this->assertFalse($recaptcha->isSetup());
    }

    /** @test */
    public function checkIsSetup()
    {
        $recaptcha = new ReCaptcha($this->getClientMock(), 'private', 'public');
        $this->assertTrue($recaptcha->isSetup());
    }

    /** @test */
    public function checkAnswerShouldSendARequestAndParseAGoodResponse()
    {
        $private = 'private-'.mt_rand();
        $userresponse = 'userresponse-'.mt_rand();
        $challenge = 'challenge-'.mt_rand();
        $ip = 'ip-'.mt_rand();

        $catchType = null;
        $catchUri = null;
        $catchParameters = null;

        $responseData = 'true';

        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($responseData));

        $client = $this->getClientMock();
        $client->expects($this->once())
            ->method('request')
            ->will($this->returnCallback(function ($type, $uri, $parameters) use (&$catchParameters, &$catchType, &$catchUri, $response) {
                $catchType = $type;
                $catchUri = $uri;
                $catchParameters = $parameters['form_params'];
                return $response;
            }));

        $recaptcha = new ReCaptcha($client, 'pub', $private);
        $answer = $recaptcha->checkAnswer($ip, $challenge, $userresponse);

        $this->assertInternalType('array', $catchParameters);

        $this->assertArrayHasKey('privatekey', $catchParameters);
        $this->assertArrayHasKey('remoteip', $catchParameters);
        $this->assertArrayHasKey('challenge', $catchParameters);
        $this->assertArrayHasKey('response', $catchParameters);

        $this->assertEquals($private, $catchParameters['privatekey']);
        $this->assertEquals($ip, $catchParameters['remoteip']);
        $this->assertEquals($challenge, $catchParameters['challenge']);
        $this->assertEquals($userresponse, $catchParameters['response']);

        $this->assertEquals('POST', $catchType);
        $this->assertEquals('/recaptcha/api/verify', $catchUri);

        $this->assertInstanceOf('Neutron\ReCaptcha\Response', $answer);
        $this->assertTrue($answer->isValid());
        $this->assertNull($answer->getError());
    }


    /** @test */
    public function checkAnswerShouldSendARequestAndParseABadResponse()
    {
        $private = 'private-'.mt_rand();
        $userresponse = 'userresponse-'.mt_rand();
        $challenge = 'challenge-'.mt_rand();
        $ip = 'ip-'.mt_rand();

        $catchType = null;
        $catchUri = null;
        $catchParameters = null;

        $errormsg = 'this is unit tests dude';
        $responseData = "false\n$errormsg";

        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($responseData));

        $client = $this->getClientMock();
        $client->expects($this->once())
            ->method('request')
            ->will($this->returnCallback(function ($type, $uri, $parameters) use (&$catchParameters, &$catchType, &$catchUri, $response) {
                $catchType = $type;
                $catchUri = $uri;
                $catchParameters = $parameters['form_params'];
                return $response;
            }));

        $recaptcha = new ReCaptcha($client, 'pub', $private);
        $answer = $recaptcha->checkAnswer($ip, $challenge, $userresponse);

        $this->assertInternalType('array', $catchParameters);

        $this->assertArrayHasKey('privatekey', $catchParameters);
        $this->assertArrayHasKey('remoteip', $catchParameters);
        $this->assertArrayHasKey('challenge', $catchParameters);
        $this->assertArrayHasKey('response', $catchParameters);

        $this->assertEquals($private, $catchParameters['privatekey']);
        $this->assertEquals($ip, $catchParameters['remoteip']);
        $this->assertEquals($challenge, $catchParameters['challenge']);
        $this->assertEquals($userresponse, $catchParameters['response']);

        $this->assertEquals('POST', $catchType);
        $this->assertEquals('/recaptcha/api/verify', $catchUri);

        $this->assertInstanceOf('Neutron\ReCaptcha\Response', $answer);
        $this->assertFalse($answer->isValid());
        $this->assertEquals($errormsg, $answer->getError());
    }

    /** @test */
    public function getPublicKeyShouldReturnPublicKey()
    {
        $publicKey = 'pub-'.  mt_rand();
        $recaptcha = new ReCaptcha($this->getClientMock(), $publicKey, 'priv');
        $this->assertEquals($publicKey, $recaptcha->getPublicKey());
    }

    /** @test */
    public function bindShouldMapTheCorrectFields()
    {
        $challenge = 'challenger';
        $userresponse = 'responser';
        $ip = '192.168.17.24';

        $httprequest = new Request(
            array(), // $_GET
            array(
                'recaptcha_challenge_field' => $challenge,
                'recaptcha_response_field'  => $userresponse,
            ), // $_POST
            array(),
            array(),
            array(),
            array('REMOTE_ADDR' => $ip)  // $_SERVER
        );

        $catchType = null;
        $catchUri = null;
        $catchParameters = null;

        $responseData = "true";

        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($responseData));

        $client = $this->getClientMock();
        $client->expects($this->once())
            ->method('request')
            ->will($this->returnCallback(function ($type, $uri, $parameters) use (&$catchParameters, &$catchType, &$catchUri, $response) {
                $catchType = $type;
                $catchUri = $uri;
                $catchParameters = $parameters['form_params'];
                return $response;
            }));

        $recaptcha = new ReCaptcha($client, 'pub', 'private');
        $answer = $recaptcha->bind($httprequest);

        $this->assertInternalType('array', $catchParameters);

        $this->assertArrayHasKey('challenge', $catchParameters);
        $this->assertArrayHasKey('response', $catchParameters);

        $this->assertEquals($challenge, $catchParameters['challenge']);
        $this->assertEquals($userresponse, $catchParameters['response']);
        $this->assertEquals($ip, $catchParameters['remoteip']);

        $this->assertEquals('POST', $catchType);
        $this->assertEquals('/recaptcha/api/verify', $catchUri);

        $this->assertInstanceOf('Neutron\ReCaptcha\Response', $answer);
        $this->assertTrue($answer->isValid());
    }

    private function getClientMock()
    {
        return $this->getMock('GuzzleHttp\ClientInterface');
    }

    public function provideBadIps()
    {
        return array(
            array(null),
            array(''),
            array('  '),
        );
    }

    public function provideBadChallengeResponse()
    {
        return array(
            array('', 'response'),
            array('challenge', ''),
            array('', ''),
            array(null, 'response'),
            array('challenge', null),
            array(null, null),
            array(' ', 'response'),
            array('challenge', ' '),
            array(' ', ' '),
        );
    }
}
