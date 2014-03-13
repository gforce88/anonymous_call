<?php
require_once 'base/WedgitBaseController.php';

class TestController extends WedgitBaseController {

	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$this->partner = array (
			"language" => "JA" 
		);
		echo $this->getLanguage("HelloWorld");
		
		$this->logInfo("TestController", "indexAction", "info");
		$this->logInfo("TestController", "indexAction", "warn");
		$this->logInfo("TestController", "indexAction", "error");
		
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

