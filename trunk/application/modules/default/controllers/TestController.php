<?php
require_once 'util/MultiLang.php';
require_once 'service/TropoService.php';

class TestController extends Zend_Controller_Action {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		echo date("H:i:s", 1)."<br>";
		echo date("H:i:s", 10)."<br>";
		echo date("H:i:s", 50)."<br>";
		echo date("H:i:s", 100)."<br>";
		echo date("H:i:s", 150)."<br>";
		echo date("H:i:s", 200)."<br>";
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

