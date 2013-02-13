<?php

namespace Neutron\ReCaptcha;

class Response
{
    private $isValid;
    private $error;

    public function __construct($isValid, $error = null)
    {
        $this->isValid = (Boolean) $isValid;
        $this->error = $error;
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function getError()
    {
        return $this->error;
    }
}
