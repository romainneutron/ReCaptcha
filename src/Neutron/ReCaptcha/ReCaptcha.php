<?php

namespace Neutron\ReCaptcha;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Neutron\ReCaptcha\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

/** @see https://developers.google.com/recaptcha/docs/customization */
class ReCaptcha
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @param ClientInterface $client
     * @param string          $publicKey
     * @param string          $privateKey
     */
    public function __construct(ClientInterface $client, $publicKey, $privateKey)
    {
        $this->client = $client;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    /**
     * @return bool
     */
    public function isSetup()
    {
        return '' !== trim($this->privateKey) && '' !== trim($this->publicKey);
    }

    /**
     * @param Request $request
     * @param string  $challenge
     * @param string  $response
     * @return Response
     */
    public function bind(
        Request $request,
        $challenge = 'recaptcha_challenge_field',
        $response = 'recaptcha_response_field'
    )
    {
        return $this->checkAnswer(
            $request->getClientIp(),
            $request->request->get($challenge),
            $request->request->get($response)
        );
    }

    /**
     * @param string $ip
     * @param string $challenge
     * @param string $response
     * @return Response
     */
    public function checkAnswer($ip, $challenge, $response)
    {
        if ('' === trim($ip)) {
            throw new InvalidArgumentException(
                'For security reasons, you must pass the remote ip to reCAPTCHA'
            );
        }

        if ('' === trim($challenge) || '' === trim($response)) {
            return new Response(false, 'incorrect-captcha-sol');
        }

        $request = $this->client->post('/recaptcha/api/verify');
        $request->addPostFields(array(
            'privatekey' => $this->privateKey,
            'remoteip'   => $ip,
            'challenge'  => $challenge,
            'response'   => $response
        ));

        $response = $request->send();
        $data = explode("\n", $response->getBody(true));

        if ('true' === trim($data[0])) {
            return new Response(true);
        }

        return new Response(false, isset($data[1]) ? $data[1] : null);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     * @param string $privateKey
     * @return ReCaptcha
     */
    public static function create($publicKey, $privateKey)
    {
        return new ReCaptcha(
            new Client('https://www.google.com/recaptcha/api'),
            $publicKey,
            $privateKey
        );
    }
}
