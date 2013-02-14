# Guzzled PHP ReCaptcha

[![Build Status](https://secure.travis-ci.org/romainneutron/ReCaptcha.png?branch=master)](http://travis-ci.org/romainneutron/ReCaptcha)

This is a Object Oriented PHP port of the original ReCaptcha lib.

It's been designed to be testable and uses [Guzzle](http://guzzlephp.org) as a
transport layer.

* see https://developers.google.com/recaptcha/docs/customization

# Example

On client side :

```php
<?php

use Neutron\ReCaptcha\ReCaptcha;

$recaptcha = ReCaptcha::create($publicKey, $privateKey);

?>
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

 <script type="text/javascript"
    src="http://www.google.com/recaptcha/api/challenge?k=<?=recaptcha->getPublicKey();?>">
 </script>
 <noscript>
   <iframe src="http://www.google.com/recaptcha/api/noscript?k=<?=recaptcha->getPublicKey();?>"
        height="300" width="500" frameborder="0"></iframe><br>
   <textarea name="recaptcha_challenge_field" rows="3" cols="40">
   </textarea>
   <input type="hidden" name="recaptcha_response_field"
        value="manual_challenge">
 </noscript>
```

Server side :

```php
use Neutron\ReCaptcha\ReCaptcha;

$recaptcha = ReCaptcha::create($publicKey, $privateKey);

$recaptcha->checkAnswer($_SERVER['REMOTE_HOST'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
```

##License

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).




