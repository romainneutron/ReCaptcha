<?php

namespace Neutron\Tests\ReCaptcha;

use Neutron\ReCaptcha\ReCaptchaServiceProvider;
use Silex\Application;

class ReCaptchaServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceProvider()
    {
        $app = new Application();
        $app->register(new ReCaptchaServiceProvider(), [
            'recaptcha.public-key'  => 'super-public-key',
            'recaptcha.private-key' => 'super-private-key',
        ]);

        $this->assertInstanceOf('Neutron\ReCaptcha\ReCaptcha', $app['recaptcha']);
        $this->assertEquals('super-public-key', $app['recaptcha']->getPublicKey());
        $this->assertEquals($app['recaptcha'], $app['recaptcha']);
    }
}
