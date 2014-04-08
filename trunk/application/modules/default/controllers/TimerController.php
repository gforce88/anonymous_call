<?php
require_once 'data/NextTime.php';
require_once 'models/CallManager.php';

class TimerController extends Zend_Controller_Action {
	private $callManager;

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
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
		foreach ($reminds as $remind) {
			$nextTime = new NextTime($now, $remind);
			$remind["nextRemindTime"] = date("Y-m-d H:i:s", $nextTime->nextRemindTime);
			$this->callManager->update($remind);
		}
		if ($charges != null) {
			$this->callManager->updateCharges($nowStr);
		}
		
		// 2. TODO: invoke Tropo conference call
	}

}

