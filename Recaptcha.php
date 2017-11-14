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
            $settings = $this->config->getFromModule('recaptcha');
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
     * @raram array $settings
     * @return null|bool
     */
    protected function processRecaptcha($controller, $settings)
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
            return json_decode($curl->post($url, array('fields' => $fields)));
        } catch (\Exception $ex) {
            return null;
        }
    }

}
