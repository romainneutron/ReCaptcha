<?php

namespace Neutron\ReCaptcha;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ReCaptchaServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['recaptcha.public-key'] = null;
        $app['recaptcha.private-key'] = null;

        $app['recaptcha'] = $app->share(function (Application $app) {
           return ReCaptcha::create($app['recaptcha.public-key'], $app['recaptcha.private-key']);
        });
    }

    public function boot(Application $app)
    {
    }
}
