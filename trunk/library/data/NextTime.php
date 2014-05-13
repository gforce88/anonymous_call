<?php

class NextTime {
	public $nextRemindTime;
	public $nextChargeTime;

	// Charge chargeAmount every minCallBlkDur
	// Remind callRemindOffset before every minCallBlkDur
// 	public function NextTime($time, $partner) {
// 		$this->nextChargeTime = $time->getTimestamp() + $partner["minCallBlkDur"];
// 		$this->nextRemindTime = $this->nextChargeTime - $partner["callRemindOffset"] - 5; // 5 seconds for tropo comunication time
// 	}

	// Charge chargeAmount every 1 minute
	// Remind only once at callRemindOffset before the end of freeCallDur
	public function NextTime($time, $arr) {
		$this->nextRemindTime = $time->getTimestamp() + $partner["freeCallDur"]; - $partner["callRemindOffset"] - 5; // 5 seconds for tropo comunication time
	}
}