<?php
require_once 'util/MultiLang.php';
require_once 'service/TropoService.php';

class TestController extends Zend_Controller_Action {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$paramarray = array (
			"test" => "TEST" 
		);
		$_GET = array_merge($_GET, $paramarray);
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

