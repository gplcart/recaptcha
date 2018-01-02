<?php

/**
 * @package reCAPTCHA
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\recaptcha;

use gplcart\core\Module,
    gplcart\core\Container;

/**
 * Main class for reCAPTCHA module
 */
class Main
{

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Implements hook "construct.controller"
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookConstructControllerFrontend($controller)
    {
        $this->setRecaptcha($controller);
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

    /**
     * Render and add CAPTCHA
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    protected function setRecaptcha($controller)
    {
        if (!$controller->isInternalRoute()) {
            $settings = $this->module->getSettings('recaptcha');
            if (!empty($settings['key']) && !empty($settings['secret'])) {
                $html = $controller->render('recaptcha|recaptcha', array('recaptcha_key' => $settings['key']));
                $controller->setData('_captcha', $html);
                $this->processRecaptcha($controller, $settings);
            }
        }
    }

    /**
     * Process reCAPTCHA's response
     * @param \gplcart\core\controllers\frontend\Controller $controller
     * @param $settings
     * @return bool|null
     * @raram array $settings
     */
    protected function processRecaptcha($controller, array $settings)
    {
        if ($controller->isPosted('g-recaptcha-response')) {
            $response = $this->queryRecaptcha($controller, $settings);
            if (empty($response->success)) {
                $controller->setError('recaptcha', $controller->text('You are spammer!'));
                return false;
            }
            return true;
        }

        return null;
    }

    /**
     * Post query to Recaptcha service
     * @param \gplcart\core\controllers\frontend\Controller $controller
     * @param array $settings
     * @return object|null
     */
    protected function queryRecaptcha($controller, array $settings)
    {
        $options = array(
            'method' => 'POST',
            'data' => array(
                'secret' => $settings['secret'],
                'remoteip' => $controller->getIp(),
                'response' => $controller->getPosted('g-recaptcha-response', '', true, 'string')
            ),
        );

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        try {
            $response = $this->getSocketClient()->request($url, $options);
            return json_decode($response['data']);
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
            return null;
        }
    }

    /**
     * Returns Socket client helper class instance
     * @return \gplcart\core\helpers\SocketClient
     */
    protected function getSocketClient()
    {
        return Container::get('gplcart\\core\\helpers\\SocketClient');
    }

}
