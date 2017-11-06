<?php

/**
 * @package reCAPTCHA
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\recaptcha;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for reCAPTCHA module
 */
class Recaptcha extends Module
{

    /**
     * Controller class instance
     * @var \gplcart\core\controllers\frontend\Controller $controller
     */
    protected $controller;

    /**
     * An array of module settings
     * @var array $settings
     */
    protected $settings;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!function_exists('curl_init')) {
            $result = $this->getLanguage()->text('CURL library is not enabled');
        }
    }

    /**
     * Implements hook "construct.controller"
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookConstructControllerFrontend($controller)
    {
        $this->controller = $controller;
        $this->settings = $this->config->getFromModule('recaptcha');

        if (!empty($this->settings['key']) && !empty($this->settings['secret'])) {
            $this->setCaptcha();
            $this->processResponse();
        }
    }

    /**
     * Render and add CAPTCHA
     */
    protected function setCaptcha()
    {
        $vars = array('recaptcha_key' => $this->settings['key']);
        $html = $this->controller->render('recaptcha|recaptcha', $vars);
        $this->controller->setData('_captcha', $html);
    }

    /**
     * Process reCAPTCHA's response
     * @return null|bool
     */
    protected function processResponse()
    {
        if (!$this->controller->isPosted('g-recaptcha-response')) {
            return null;
        }

        /* @var $curl \gplcart\core\helpers\Curl */
        $curl = $this->getHelper('Curl');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $this->getHelper('Request');

        $fields = array(
            'remoteip' => $request->ip(),
            'secret' => $this->settings['secret'],
            'response' => $this->controller->getPosted('g-recaptcha-response', '', true, 'string')
        );

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        try {
            $response = json_decode($curl->post($url, array('fields' => $fields)));
        } catch (\Exception $ex) {
            return null;
        }

        if (empty($response->success)) {
            $this->controller->setError('recaptcha', $this->controller->text('You are spammer!'));
            return false;
        }

        return true;
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/recaptcha'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\recaptcha\\controllers\\Settings', 'editSettings')
            )
        );
    }

}
