<?php

namespace Neutron\ReCaptcha\Laravel;

use Neutron\ReCaptcha\ReCaptcha;
use Illuminate\Support\ServiceProvider;

class ReCaptchaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // We have to explicitly pass the path here, because Laravel can't
        // figure it out correctly. We could place `config` and `views` folder
        // inside Neutron/ReCaptcha/Laravel, but that would force the users
        // installing the package to explicitly set the path to the config
        // files when publishing them.
        $this->package('neutron/recaptcha', null, realpath(__DIR__ . '/../../..'));

        $this->registerFormMacro();
        $this->registerValidator();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerReCaptcha();
    }

    /**
     * Register the Recaptcha Facade
     *
     * @return void
     */
    public function registerReCaptcha()
    {
        $this->app['recaptcha'] = $this->app->share(function ($app) {
            $config = $app['config'];

            return ReCaptcha::create(
                $config->get('recaptcha::public_key'),
                $config->get('recaptcha::private_key')
            );
        });
    }

    /**
     * Register the form macro
     *
     * @return void
     */
    public function registerFormMacro()
    {
        $app = $this->app;

        $app['form']->macro('recaptcha', function ($options = array()) use ($app) {
            $config = $app['config'];

            $viewData = array(
                'public_key' => $config->get('recaptcha::public_key'),
                'options'    => array_merge($options, $config->get('recaptcha::options')),
            );

            return $app['view']->make('recaptcha::recaptcha', $viewData);
        });
    }

    /**
     * Register the validation recaptcha rule
     *
     * @return void
     */
    public function registerValidator()
    {
        $validator = $this->app['validator'];
        $request = $this->app['request'];
        $recaptcha = $this->app['recaptcha'];

        $validator->resolver(function ($translator, $data, $rules, $messages) use ($recaptcha, $request) {
            return new ReCaptchaValidator($recaptcha, $request, $translator, $data, $rules, $messages);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('recaptcha');
    }

}
