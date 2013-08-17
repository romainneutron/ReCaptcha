# Guzzled PHP ReCaptcha

[![Build Status](https://secure.travis-ci.org/romainneutron/ReCaptcha.png?branch=master)](http://travis-ci.org/romainneutron/ReCaptcha)

This is a Object Oriented PHP port of the original ReCaptcha lib.

It's been designed to be testable and uses [Guzzle](http://guzzlephp.org) as a
transport layer.

* see https://developers.google.com/recaptcha/docs/customization

## Install

The recommended way to use ReCaptcha is [through composer](http://getcomposer.org).

```json
{
    "require": {
        "neutron/recaptcha": "~0.1.0"
    }
}
```

## Silex Service Provider

A simple [Silex](http://silex.sensiolabs.org) service provider :

```php
use Neutron\ReCaptcha\ReCaptcha;
use Neutron\ReCaptcha\ReCaptchaServiceProvider;
use Silex\Application;

$app = new Application();
$app->register(new ReCaptchaServiceProvider(), array(
    'recaptcha.public-key'  => 'fdspoksqdpofdkpopgqpdskofpkosd',
    'recaptcha.private-key' => 'lsdmkzfqposfomkcqdsofmsdkfkqsdmfmqsdm',
));

// $captcha is an instance of Neutron\ReCaptcha\Response
$captcha = $app['recaptcha']->bind($app['request']);

if ($captcha->isValid()) {
    echo "YEAH !";
} else {
    echo "Too bad dude :( " . $captcha->getError();
}
```

## Laravel Integration

The package has built-in support for [Laravel](http://laravel.com). To make use of it, add the service provider to your `providers` array and the facade to your `aliases` array, on `app/config/app.php`:

```php
'providers' => array(
    'Neutron\ReCaptcha\Laravel\ReCaptchaServiceProvider',
    // ...
),
// ...
'aliases' => array(
    'ReCaptcha' => 'Neutron\ReCaptcha\Laravel\ReCaptchaFacade',
    // ...
),
```

## Usage Example

To display a captcha to the client :

 - Initialize your captcha object :

```php
use Neutron\ReCaptcha\ReCaptcha;

$recaptcha = ReCaptcha::create($publicKey, $privateKey);
```

 - In your template :

```html
<script type="text/javascript">
    var RecaptchaOptions = {
       theme : 'custom',
       custom_theme_widget: 'recaptcha_widget'
    };
</script>
<form method="post">
    <div id="recaptcha_widget" style="display:none">
        <div id="recaptcha_image"></div>
        <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>

        <span class="recaptcha_only_if_image">Enter the words above:</span>
        <span class="recaptcha_only_if_audio">Enter the numbers you hear:</span>

        <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />

        <div><a href="javascript:Recaptcha.reload()">Get another CAPTCHA</a></div>
        <div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">Get an audio CAPTCHA</a></div>
        <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">Get an image CAPTCHA</a></div>

        <div><a href="javascript:Recaptcha.showhelp()">Help</a></div>
    </div>

    <script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=<?=$recaptcha->getPublicKey();?>"></script>
    <noscript>
       <iframe src="http://www.google.com/recaptcha/api/noscript?k=<?=$recaptcha->getPublicKey();?>" height="300" width="500" frameborder="0"></iframe><br>
       <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
       <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
    </noscript>
    <button type="submit">Submit</button>
</form>
```

 - Server side :

```php
use Neutron\ReCaptcha\ReCaptcha;

$recaptcha = ReCaptcha::create($publicKey, $privateKey);
$response = $recaptcha->checkAnswer($_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);

if ($response->isValid()) {
    echo "YEAH !";
} else {
    echo "Too bad dude :(";
}
```

## Bind Symfony Request

A shortcut exists to bind a [Symfony Request](http://api.symfony.com/master/Symfony/Component/HttpFoundation/Request.html)
to ReCaptcha :

```php
use Neutron\ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;

$recaptcha = ReCaptcha::create($publicKey, $privateKey);
$response = $recaptcha->bind(Request::createFromGlobals());

if ($response->isValid()) {
    echo "YEAH !";
} else {
    echo "Too bad dude :( " . $response->getError();
}
```

##License

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).




