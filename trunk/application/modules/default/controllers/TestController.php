<?php
require_once 'base/WedgitBaseController.php';

class TestController extends WedgitBaseController {

	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		echo "Test";
		$this->logInfo("TestController", "indexAction", "info");
		$this->logInfo("TestController", "indexAction", "warn");
		$this->logInfo("TestController", "indexAction", "error");
		$this->renderScript("/empty.phtml");
	}

	public function retrieveInvitationAction() {
		$this->partner = array (
			"language" => "JA" 
		);
		echo $this->getLanguage("HelloWorld");
		$this->renderScript("/empty.phtml");
	}

}

