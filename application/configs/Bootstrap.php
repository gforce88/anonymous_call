<?php
require_once 'Constant.php';
require_once 'vendor/autoload.php';

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	// Init Application
	protected function _initApplication() {
		$app = $this->getOption("app");
		defined("APP_CTX") || define("APP_CTX", $app["ctx"]);
		defined("APP_TITLE") || define("APP_TITLE", $app["title"]);
		
		// Set default timezone
		date_default_timezone_set("UTC");
	}
	
	// Init DB
	protected function _initDB() {
		$resources = $this->getPluginResource("db");
		$db = $resources->getDbAdapter();
		Zend_Db_Table::setDefaultAdapter($db);
		Zend_Registry::set("DB_ADAPTER", $db);
	}
	
	// Init Router
	protected function _initRouter() {
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		
		$route = new Zend_Controller_Router_Route_Regex("landing", array (
			"module" => "widget",
			"controller" => "landing",
			"action" => "index" 
		));
		$router->addRoute("lainding", $route);
		
		$route = new Zend_Controller_Router_Route_Regex("continue", array (
			"module" => "widget",
			"controller" => "landing",
			"action" => "continue" 
		));
		$router->addRoute("continue", $route);
		
		$route = new Zend_Controller_Router_Route_Regex("firstleg/(.+)\.php", array (
			"module" => "tropo",
			"controller" => "firstleg" 
		), array (
			1 => "action" 
		), "firstleg/%s.php");
		$router->addRoute("firstleg", $route);
		
		$route = new Zend_Controller_Router_Route_Regex("secondleg/(.+)\.php", array (
			"module" => "tropo",
			"controller" => "secondleg" 
		), array (
			1 => "action" 
		), "secondleg/%s.php");
		$router->addRoute("secondleg", $route);
	}
	
	// Init Logger
	protected function _initLog() {
		$adminFormat = "%message%" . PHP_EOL;
		$adminFormatter = new Zend_Log_Formatter_Simple($adminFormat);
		$logSetting = $this->getOption("log");
		
		$ivrLogWriter = new Zend_Log_Writer_Stream($logSetting["ivr_log_path"] . "." . (new DateTime())->format("Y-m-d"));
		$ivrLogWriter->setFormatter($adminFormatter);
		$ivrLogger = new Zend_Log($ivrLogWriter);
		Zend_Registry::set("IVR_LOGGER", $ivrLogger);
		
		$sysLogWriter = new Zend_Log_Writer_Stream($logSetting["sys_log_path"] . "." . (new DateTime())->format("Y-m-d"));
		$sysLogWriter->setFormatter($adminFormatter);
		$sysLogger = new Zend_Log($sysLogWriter);
		Zend_Registry::set("SYS_LOGGER", $sysLogger);
	}
	
	// Init Language
	protected function _initLanguage() {
		$englishTexts = parse_ini_file(APPLICATION_PATH . "/configs/multiLanguage/English.ini");
		Zend_Registry::set("ENGLISH_TEXTS", $englishTexts);
		$japaneseTexts = parse_ini_file(APPLICATION_PATH . "/configs/multiLanguage/Japanese.ini");
		Zend_Registry::set("JAPANESE_TEXTS", $japaneseTexts);
	}
	
	// Init IVR
	protected function _initIVRSetting() {
		$ivr_setting = parse_ini_file(APPLICATION_PATH . "/configs/ivr.ini");
		Zend_Registry::set("IVR_SETTING", $ivr_setting);
		$tropo_setting = $this->getOption("tropo");
		Zend_Registry::set("TROPO_SETTING", $tropo_setting);
		$cpa_setting = $this->getOption("cpa");
		Zend_Registry::set("CPA_SETTING", $cpa_setting);
	}
	
	// Init PayPal app context
	protected function _initPaypalAppContext() {
		$paypalSetting = $this->getOption("paypal");
		$paypalApiCtx = new ApiContext(new OAuthTokenCredential($paypalSetting["client_id"], $paypalSetting["secret"]));
		Zend_Registry::set('PAYPAL_API_CTX', $paypalApiCtx);
	}

}