<?php
require_once 'log/LoggerFactory.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';

class Widget_FollowingController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $callManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
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
		
		$call = $this->callManager->findcallByInx($_POST["callInx"]);
		$callStartTime = strtotime($call["callStartTime"]);
		$callEndTime = strtotime($call["callEndTime"]);
		if ($callStartTime <= 0) {
			// Call is not started
			$result = array (
				"status" => 0 
			);
		} else if ($callEndTime <= 0) {
			// Call is started but not completed
			$result = array (
				"status" => 1,
				"totalTime" => time() - $callStartTime 
			);
		} else {
			// Call is completed
			$result = array (
				"status" => 2,
				"totalTime" => $callEndTime - $callStartTime 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

}
