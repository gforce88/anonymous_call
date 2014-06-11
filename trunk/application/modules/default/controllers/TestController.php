<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'util/MultiLang.php';
require_once 'util/Protection.php';
require_once 'models/AdminManager.php';
require_once 'models/CallManager.php';
require_once 'models/InviteManager.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';

class TestController extends Zend_Controller_Action {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$data = "test";
		$key = "retry";
		$encryptedData = urlencode(Protection::encrypt($data, $key));
		echo $encryptedData . "<br>";
		$decryptedData = Protection::decrypt(urldecode($encryptedData), $key);
		echo $decryptedData . "<br>";
		
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

