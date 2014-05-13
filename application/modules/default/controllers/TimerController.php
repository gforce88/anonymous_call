<?php
require_once 'log/LoggerFactory.php';
require_once 'service/TropoService.php';
require_once 'models/CallManager.php';

class TimerController extends Zend_Controller_Action {
	private $logger;
	private $tropoService;
	private $callManager;

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$this->logger = LoggerFactory::getSysLogger();
		$this->tropoService = new TropoService();
		$this->callManager = new CallManager();
	}
	
	/*
	 * This function is called by shell Timer.php
	 */
	public function fireAction() {
		$now = new DateTime();
		$nowStr = $now->format("Y-m-d H:i:s");
		$reminds = $this->callManager->findNextReminds($nowStr);
		
		// 1. Update database to avoid duplicate fire by the next timer
		if ($reminds != null) {
			$this->logger->logInfo("Timer", "reminds", $reminds);
			foreach ($reminds as $remind) {
				$remind["nextRemindTime"] = 0;
				$this->callManager->update($remind);
			}
		}
		
		// 2. Invoke Tropo service to remind conference call
		foreach ($reminds as $remind) {
			$paramArr = array (
				"callInx" => $remind["inx"],
				"partnerInx" => $remind["partnerInx"],
				"inviteInx" => $remind["inviteInx"],
				"country" => $remind["country"] 
			);
			$this->tropoService->playRemind($remind["firstLegSession"], $remind["secondLegSession"], $paramArr);
		}
	}

}

