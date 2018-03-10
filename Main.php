<?php

/**
 * @package reCAPTCHA
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\recaptcha;

use Exception;
use gplcart\core\Container;
use gplcart\core\controllers\frontend\Controller;
use gplcart\core\Module;

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
     * @param Controller $controller
     */
    public function hookConstructControllerFrontend(Controller $controller)
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
     * Returns rendered rECAPTCHA widget
     * @param Controller $controller
     * @param array $settings
     * @return string
     */
    public function getWidget(Controller $controller, array $settings)
    {
        return $controller->render('recaptcha|recaptcha', array('recaptcha_key' => $settings['key']));
    }

    /**
     * Post query to Recaptcha service
     * @param Controller $controller
     * @param array $settings
     * @return mixed|null
     */
    public function request(Controller $controller, array $settings)
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
            $response = $this->getHttpModel()->request($url, $options);
            return json_decode($response['data']);
        } catch (Exception $ex) {
            trigger_error($ex->getMessage());
            return null;
        }
    }

    /**
     * Process reCAPTCHA response
     * @param Controller $controller
     * @param array $settings
     * @return null|bool
     */
    public function process(Controller $controller, array $settings)
    {
        if ($controller->isPosted('g-recaptcha-response')) {
            $response = $this->request($controller, $settings);
            return !empty($response->success);
        }

        return null;
    }

    /**
     * Render and add CAPTCHA
     * @param Controller $controller
     */
    protected function setRecaptcha(Controller $controller)
    {
        if (!$controller->isInternalRoute()) {

            $settings = $this->module->getSettings('recaptcha');

            if (!empty($settings['key']) && !empty($settings['secret'])) {
                $controller->setData('_captcha', $this->getWidget($controller, $settings));
                $result = $this->process($controller, $settings);
                if (isset($result) && empty($result)) {
                    $controller->setError('_captcha', 'Spam submission');
                }
            }
        }
    }

    /**
     * Returns Http model class instance
     * @return \gplcart\core\models\Http
     */
    protected function getHttpModel()
    {
        /** @var \gplcart\core\models\Http $instance */
        $instance = Container::get('gplcart\\core\\models\\Http');
        return $instance;
    }

}
