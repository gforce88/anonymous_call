<?php
require_once 'log/LoggerFactory.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';
require_once 'models/InviteManager.php';

class Widget_FollowingController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $callManager;
	private $inviteManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
		$this->inviteManager = new InviteManager();
	}

	public function indexAction() {
		$partner = $this->partnerManager->findPartnerByCall($_REQUEST["callInx"]);
		$this->view->assign("minCallBlkDur", $partner["minCallBlkDur"]);
		$this->view->assign("callRemindOffset", $partner["callRemindOffset"]);
		$this->view->assign("country", $partner["country"]);
		$this->view->assign("callInx", $_REQUEST["callInx"]);
	}

	public function refreshAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$call = $this->callManager->findCallByInx($_POST["callInx"]);
		$callConnectTime = strtotime($call["callConnectTime"]);
		$callEndTime = strtotime($call["callEndTime"]);
		if ($callConnectTime <= 0) {
			// Call is not started
			$result = array (
				"status" => 0 
			);
		} else if ($callEndTime <= 0) {
			// Call is connected but not completed
			$result = array (
				"status" => 1,
				"totalTime" => time() - $callConnectTime 
			);
		} else {
			// Call is completed
			$result = array (
				"status" => 2,
				"totalTime" => $callEndTime - $callConnectTime 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

}
