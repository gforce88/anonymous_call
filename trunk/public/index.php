<?php

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array (
	realpath(APPLICATION_PATH . '/../library'),
	realpath(APPLICATION_PATH . '/../application/modules/default'),
	get_include_path() 
)));

// initialize PayPal library
require __DIR__ . '/../vendor/autoload.php';
define("PP_CONFIG_PATH", __DIR__);

// Zend_Application
require_once 'Zend/Application.php';

// Create application
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');

// Bootstrap, and Run
$application->bootstrap()->run();