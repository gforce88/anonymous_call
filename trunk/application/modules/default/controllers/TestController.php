<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'util/MultiLang.php';
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
		$emailContent = file_get_contents(APPLICATION_PATH . "/configs/emailTemplate/1/Invite.html");
		$emailContent = MultiLang::replaceParams($emailContent, array (
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/",
			"name",
			"url" 
		));
		echo $emailContent;
		
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

