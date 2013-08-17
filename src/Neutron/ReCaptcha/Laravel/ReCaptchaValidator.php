<?php

namespace Neutron\ReCaptcha\Laravel;

use Neutron\ReCaptcha\ReCaptcha;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class ReCaptchaValidator extends Validator
{

    /**
     * The Symfony Request object, used to get the client's IP
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The ReCaptcha object used to handle the requests
     *
     * @var \Neutron\ReCaptcha\ReCaptcha
     */
    protected $recaptcha;

    /**
     * Create a new Validator instance.
     *
     * @param  \Neutron\ReCaptcha\ReCaptcha                        $recaptcha
     * @param  \Symfony\Component\HttpFoundation\Request           $request
     * @param  \Symfony\Component\Translation\TranslatorInterface  $translator
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @return void
     */
    public function __construct(ReCaptcha $recaptcha, Request $request, TranslatorInterface $translator, $data, $rules, $messages = array())
    {
        $this->recaptcha = $recaptcha;
        $this->request = $request;

        parent::__construct($translator, $data, $rules, $messages);
    }

    /**
     * Validate that the recaptcha was correctly typed
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRecaptcha($attribute, $value, $parameters = array())
    {
        // We first run the required rule, this way we can maybe save some
        // unnecessary requests and not run the risk of validating a blank
        // response.
        if (! $this->validateRequired($attribute, $value)) {
            return false;
        }

        $challenge = reset($parameters) ?: 'recaptcha_challenge_field';
        $response = $this->recaptcha->checkAnswer($this->request->getClientIp(), $this->data[$challenge], $value);

        return $response->isValid();
    }

}