<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'models/CallManager.php';
require_once 'data/NextTime.php';

class TimerController extends Zend_Controller_Action {
	private $logger;
	private $paypalService;
	private $tropoService;
	private $callManager;

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$this->logger = LoggerFactory::getSysLogger();
		$this->paypalService = new PaypalService();
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
		$charges = $this->callManager->findNextCharges($nowStr);
		
		// 1. Update database to avoid duplicate fire by the nest timer
		if ($reminds != null) {
			$this->logger->logInfo("Timer", "reminds", $reminds);
			foreach ($reminds as $remind) {
				$nextTime = new NextTime($now, $remind);
				$remind["nextRemindTime"] = date("Y-m-d H:i:s", $nextTime->nextRemindTime);
				$this->callManager->update($remind);
			}
		}
		if ($charges != null) {
			$this->logger->logInfo("Timer", "charges", $charges);
			$this->callManager->updateCharges($nowStr);
		}
		
		// 2. Invoke Tropo service for conference call
		foreach ($reminds as $remind) {
			$paramArr = array (
				"mainSessionId" => $remind["tropoSession"] 
			);
			$this->tropoService->initConfCall($paramArr);
		}
		
		// 3. Invoke Paypal service for charge
		foreach ($charges as $charge) {
			$this->paypalService->charge($charge["paypalToken"], $charge["chargeAmount"]);
		}
	}

}

