<?php
require_once 'base/TropoBaseController.php';

class TropoController extends TropoBaseController {

	public function init() {
		parent::init();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		echo "Test";
		$this->logInfo("TestController", "indexAction", "test");
	}

}

