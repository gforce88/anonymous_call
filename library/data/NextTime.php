<?php

class NextTime {
	public $nextRemindTime;
	public $nextChargeTime;

	public function NextTime($now, $arr) {
		$this->nextChargeTime = $now->getTimestamp() + $arr["minCallBlkDur"];
		$this->nextRemindTime = $this->nextChargeTime - $arr["callRemindOffset"] - 5; // 5 seconds for tropo comunication time
	}

}