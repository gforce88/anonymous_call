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
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$paypalService = new PaypalService();
		$param = Array (
			"firstName" => "Daniel",
			"lastName" => "Ding",
			"cardType" => "visa",
			"cardNumber" => "4111111111111111",
			"cvv" => "111",
			"expMonth" => "12",
			"expYear" => "2015" 
		);
		$paypalToken = $paypalService->regist($param);
		echo $paypalToken;
		phpinfo();
	}

}

