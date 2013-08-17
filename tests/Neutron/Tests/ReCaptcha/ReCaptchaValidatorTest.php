<?php

namespace Neutron\Tests\ReCaptcha;

use \Neutron\ReCaptcha\Response;
use \Neutron\ReCaptcha\Laravel\ReCaptchaValidator;

class ReCaptchaValidatorTest extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function validationRuleShouldBeGood()
    {
        $recaptcha = $this->getReCaptchaMock();
        $translator = $this->getTranslator();
        $request = $this->getRequestMock();

        $validator = new ReCaptchaValidator($recaptcha, $request, $translator, [
            'recaptcha_challenge_field' => 'super-challenge',
            'recaptcha_response_field'  => 'super-response',
        ], [
            'recaptcha_response_field'  => 'recaptcha',
        ]);

        $this->assertFalse($validator->passes());
        $validator->messages()->setFormat(':message');
        $this->assertEquals('recaptcha!', $validator->messages()->first('recaptcha_response_field'));
    }

    protected function getReCaptchaMock()
    {
        $client = $this->getMock('Guzzle\Http\ClientInterface');
        $recaptcha = $this->getMockBuilder('Neutron\ReCaptcha\ReCaptcha')
                          ->setConstructorArgs(array($client, 'public key', 'private key'))
                          ->getMock();

        $response = new Response(false, 'error');
        $recaptcha->expects($this->once())
                  ->method('checkAnswer')
                  ->with($this->equalTo('super-ip'),
                         $this->equalTo('super-challenge'),
                         $this->equalTo('super-response'))
                  ->will($this->returnValue($response));

        return $recaptcha;
    }

    protected function getTranslator()
    {
        $translator = new \Symfony\Component\Translation\Translator('en', new \Symfony\Component\Translation\MessageSelector);
        $translator->addLoader('array', new \Symfony\Component\Translation\Loader\ArrayLoader);
        $translator->addResource('array', array('validation.recaptcha' => 'recaptcha!'), 'en', 'messages');

        return $translator;
    }

    protected function getRequestMock()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())
                  ->method('getClientIp')
                  ->will($this->returnValue('super-ip'));

        return $request;
    }
}
