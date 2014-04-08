<?php
require_once 'log/LoggerFactory.php';

class NextTime {
	private $logger;
	public $nextRemindTime;
	public $nextChargeTime;

	public function NextTime($time, $arr) {
		$this->logger = LoggerFactory::getIvrLogger();
		$this->logger->logInfo("NextTime", "arr", $arr);
		$this->nextChargeTime = $time->getTimestamp() + $arr["minCallBlkDur"];
		$this->logger->logInfo("NextTime", "nextChargeTime", $this->nextChargeTime);
		$this->nextRemindTime = $this->nextChargeTime - $arr["callRemindOffset"] - 5; // 5 seconds for tropo comunication time
		$this->logger->logInfo("NextTime", "nextRemindTime", $this->nextRemindTime);
	}

}