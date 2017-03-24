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
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'reCAPTCHA',
            'version' => '1.0.0-dev',
            'description' => 'Enables Google reCAPTCHA spam protection on your GPL Cart site',
            'author' => 'Iurii Makukh <gplcart.software@gmail.com>',
            'core' => '1.x',
            'license' => 'GPL-3.0+',
            'configure' => 'admin/module/settings/recaptcha',
            'settings' => array(
                'key' => '',
                'secret' => ''
            )
        );
    }

    /**
     * Implements hook "construct.controller"
     * @param \gplcart\core\controllers\frontend\Controller $object
     */
    public function hookConstructControllerFrontend($object)
    {
        $settings = $this->config->module('recaptcha');

        if (empty($settings['key']) || empty($settings['secret'])) {
            return null;
        }

        $vars = array('recaptcha_key' => $settings['key']);
        $html = $object->render('recaptcha|recaptcha', $vars);
        $object->setData('captcha', $html);

        if (!$object->isPosted('g-recaptcha-response')) {
            return null;
        }

        /* @var $curl \gplcart\core\helpers\Curl */
        $curl = $this->getInstance('gplcart\\core\\helpers\\Curl');

        $fields = array(
            'remoteip' => $object->ip(),
            'secret' => $settings['secret'],
            'response' => $object->getPosted('g-recaptcha-response')
        );

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = json_decode($curl->post($url, array('fields' => $fields)));

        if (empty($response->success)) {
            $object->setError('recaptcha', 'You are spammer!');
        }
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        // Module settings page
        $routes['admin/module/settings/recaptcha'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\recaptcha\\controllers\\Settings', 'editSettings')
            )
        );
    }

}
