<?php
require_once 'BaseController.php';

class Widget_LandingController extends BaseController {

	public function indexAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
	}

}

