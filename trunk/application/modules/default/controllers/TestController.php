<?php
require_once 'util/MultiLang.php';
require_once 'service/TropoService.php';

class TestController extends Zend_Controller_Action {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

	public function inviteAction() {
		$partner = array ();
		$partner["inx"] = "1001";
		$invite = array ();
		$invite["numberToDial"] = "15167346602";
		$invite["callerId"] = "1020304050'";
		$invite["inx"] = "9001";
		$invite["partner"] = $partner;
		
		$tropoService = new TropoService();
		$tropoService->initCall($invite);
		
		$this->renderScript("/empty.phtml");
	}

}

