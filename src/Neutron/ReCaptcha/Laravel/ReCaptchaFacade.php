<?php

namespace Neutron\ReCaptcha\Laravel;

use Illuminate\Support\Facades\Facade;

class ReCaptchaFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'recaptcha';
    }

}