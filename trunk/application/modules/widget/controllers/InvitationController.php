<?php
require_once 'base/WedgitBaseController.php';

class TestController extends WedgitBaseController {
	
	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}
	
	public function indexAction() {
	
	}
}