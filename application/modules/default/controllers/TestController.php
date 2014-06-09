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
		$partnerManager = new PartnerManager();
		$partner = $partnerManager->findPartnerByInx(1);
		$callDuration = 432;
		$billableDuration = $callDuration - $partner["freeCallDur"];
		if ($billableDuration < 0) {
			$billableBlk = 0;
		} else {
			$billableBlk = ceil($billableDuration / $partner["minCallBlkDur"]);
		}
		$chargeAmount = $partner["chargeAmount"] * $billableBlk;
		
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

