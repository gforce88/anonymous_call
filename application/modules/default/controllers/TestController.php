<?php
require_once 'base/WedgitBaseController.php';
require_once 'util/MultiLang.php';
require_once 'service/TropoService.php';

class TestController extends WedgitBaseController {

	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$titleParam = array (
			"Name" 
		);
		$subject = MultiLang::getText("email.inviteeNotify.title", "JP", $titleParam);
		echo $subject;
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

