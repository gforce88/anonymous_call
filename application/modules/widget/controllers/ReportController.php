<?php
require_once 'log/LoggerFactory.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';

class Widget_ReportController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $callManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
	}

	public function loginAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		$this->view->assign("partnerInx", $partner["inx"]);
		$this->view->assign("country", $partner["country"]);
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		// TODO:
		
		$this->_helper->json->sendJson($result);
	}

	public function repqorAction() {
		// TODO:
	}

}
