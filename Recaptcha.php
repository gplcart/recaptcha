<?php

/**
 * @package reCAPTCHA
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\recaptcha;

use gplcart\core\Module;

/**
 * Main class for reCAPTCHA module
 */
class Recaptcha extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implements hook "module.install.before"
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
        $settings = $this->config->module('recaptcha');

        if (empty($settings['key']) || empty($settings['secret'])) {
            return null;
        }

        $html = $controller->render('recaptcha|recaptcha', array('recaptcha_key' => $settings['key']));
        $controller->setData('_captcha', $html);

        if (!$controller->isPosted('g-recaptcha-response')) {
            return null;
        }

        /* @var $curl \gplcart\core\helpers\Curl */
        $curl = $this->getHelper('Curl');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $this->getHelper('Request');

        $fields = array(
            'remoteip' => $request->ip(),
            'secret' => $settings['secret'],
            'response' => $controller->getPosted('g-recaptcha-response', '', true, 'string')
        );

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        try {
            $response = json_decode($curl->post($url, array('fields' => $fields)));
        } catch (\Exception $ex) {
            $controller->setError('recaptcha', $ex->getMessage());
            return null;
        }

        if (empty($response->success)) {
            $controller->setError('recaptcha', $this->getLanguage()->text('You are spammer!'));
        }
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
