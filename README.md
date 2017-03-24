[![Build Status](https://scrutinizer-ci.com/g/gplcart/recaptcha/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/recaptcha/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/recaptcha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/recaptcha/?branch=master)

reCAPTCHA is a [GPL Cart](https://github.com/gplcart/gplcart) module that enables ["Google reCAPTCHA"](www.google.com/recaptcha) spam protection on your site. In order to use this module you must register an account on google.com/recaptcha


**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/recaptcha`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Go to google.com/recaptcha, create your site and server keys then paste them on `admin/module/settings/recaptcha`

To protect custom forms print reCAPTCHA widget manually using one of the following code:

- PHP templates: `<?php echo $captcha; ?>`
- TWIG templates: `{{ captcha|raw }}`