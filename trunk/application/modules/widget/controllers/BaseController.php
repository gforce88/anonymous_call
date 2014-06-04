<?php
require_once 'log/LoggerFactory.php';

class BaseController extends Zend_Controller_Action {
	protected $logger;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		session_start();
	}

	protected function isSessionValid() {
		if ($_SESSION["country"] == null) {
			$this->renderScript("/notification/invalid.phtml");
			return false;
		} else {
			return true;
		}
	}

}
