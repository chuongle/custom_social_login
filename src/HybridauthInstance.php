<?php

namespace Drupal\custom_social_login;

define('__ROOT__', (dirname(dirname(__FILE__)))); 

require_once __ROOT__ .'/vendor/autoload.php';

use Drupal\Core\Controller\ControllerBase;

/**
 * Class HybridauthInstance.
 *
 * @package Drupal\custom_social_login\HybridauthInstance
 */
class HybridauthInstance extends ControllerBase {
	/**
	 * Returns HybridAuth object or exception code.
	 */
	public static function getHybridauthInstance() {

		$controller = &drupal_static(__FUNCTION__, NULL);

		$config = \Drupal::service('config.factory')->getEditable('custom_social_login.hybridauth');
		if (!isset($controller)) {
    	$controller = FALSE;
    	try {
    	  $controller = new \Hybrid_Auth($config->get());
    	}
    	catch(Exception $e) {
    	  \Drupal::logger('custom_social_login')->error($e->getMessage);
    	  $controller = $e->getCode();
    	}
    }

		return $controller;
	}
}