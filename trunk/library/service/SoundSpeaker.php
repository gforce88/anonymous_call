<?php
require_once 'service/IVRConfigServiceFactory.php';
require_once 'models/CustomerManager.php';

class SoundSpeaker {

	private $accountId;

	private $ivrConfigService;

	private $paramSoundMapping = array (
		"Area of interests" => "area_of_interests",
		"Company" => "company",
		"Program" => "program",
		"representativeName" => "representative_name",
		"Location" => "location",
		"Campus" => "campus",
		"Comments" => "comments",
		"Lead phone number" => "lead_phone_number",
		"Lead Source" => "source",
		"Lead ID" => "lead_id" 
	);

	private $paramContainsDigit = array (
		"Location",
		"Campus",
		"Lead ID" 
	);

	private $customerManager;

	public function SoundSpeaker($accountId) {
		$this->accountId = $accountId;
		$this->ivrConfigService = IVRConfigServiceFactory::getIvrConfiguration($this->accountId);
		$this->customerManager = new CustomerManager();
	}

	public function promptWelcome() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['standard_greeting'])) {
			return $this->ivrConfigService->getValue("ivr_welcome");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['standard_greeting'];
		}
	}

	public function promptGreeting4Machine() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['standard_cpa_greeting'])) {
			return $this->ivrConfigService->getValue("pause_1ms");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['standard_cpa_greeting'];
		}
	}

	public function promptNewInq() {
		return $this->ivrConfigService->getValue("ivr_new_inquiry");
	}

	public function announceRepId($repId) {
		$sentence = $this->ivrConfigService->getValue("for");
		$numbers = str_split($repId);
		foreach ($numbers as $number) {
			$sentence .= " " . $number;
		}
		return $sentence . ". ";
	}

	public function promptRepName($repName) {
		return $this->ivrConfigService->getValue("for") . " " . $repName . ". ";
	}

	public function promptLeadName($leadName) {
		return $this->ivrConfigService->getValue("lead_name") . " " . $leadName . ". ";
	}

	public function promptReattemptTimes($notificationTimes) {
		switch ($notificationTimes) {
			case 1 :
				return $this->ivrConfigService->getValue("first_re");
				break;
			case 2 :
				return $this->ivrConfigService->getValue("second_re");
				break;
			case 3 :
				return $this->ivrConfigService->getValue("third_re");
				break;
			case 4 :
				return $this->ivrConfigService->getValue("fourth_re");
				break;
			case 5 :
				return $this->ivrConfigService->getValue("fifth_re");
				break;
			case 6 :
				return $this->ivrConfigService->getValue("sixrth_re");
				break;
			default :
				$value = "";
				// $value = $this->ivrConfigService->getValue("this_is_the") . " ";
				// $value = $value . $this->ivrConfigService->getValue($this->notificationTimesArray [$notificationTimes]) . " ";
				// $value = $value . $this->ivrConfigService->getValue("reattempt") . " ";
				return $value;
		}
	}

	public function playIvrMapping($announceContent) {
		$data = split("[~]", $announceContent);
		$sentences = "";
		foreach ($data as $value) {
			list($paramName, $paramValue) = explode("::", $value);
			if (array_key_exists($paramName, $this->paramSoundMapping) && !empty($this->paramSoundMapping[$paramName])) {
				if ($paramName == "Lead phone number") {
					$phoneNumber = " ";
					for ($i = 0; $i < strlen($paramValue); $i++) {
						$singleChar = substr($paramValue, $i, 1);
						if (($singleChar == 0 || !empty($singleChar)) && $singleChar != " ") {
							$phoneNumber .= substr($paramValue, $i, 1) . ". " . $this->ivrConfigService->pause3ms() . " ";
						}
					}
					$sentences = $sentences . $this->ivrConfigService->getValue($this->paramSoundMapping[$paramName]) . " " . $phoneNumber;
				} else if (in_array($paramName, $this->paramContainsDigit)) { // parse params that contains digit ( LA60, 876623 )
					
					$paramValueContainsDigit = $this->parseParamContainsDigit($paramValue);
					
					$sentences = $sentences . $this->ivrConfigService->getValue($this->paramSoundMapping[$paramName]) . " " . $paramValueContainsDigit;
				} else {
					$sentences = $sentences . $this->ivrConfigService->getValue($this->paramSoundMapping[$paramName]) . " " . $paramValue . ". ";
				}
			} else if (!empty($paramName)) {
				$sentences = $sentences . $this->ivrConfigService->getTTSFieldValue($paramName, $paramValue);
			}
		}
		return $sentences;
	}

	private function parseParamContainsDigit($paramValue) {
		$paramContainsDigit = " ";
		for ($i = 0; $i < strlen($paramValue); $i++) {
			$chr = substr($paramValue, $i, 1);
			if (is_numeric($chr)) {
				$paramContainsDigit .= " " . substr($paramValue, $i, 1) . ". " . $this->ivrConfigService->pause3ms() . " ";
			} else {
				$paramContainsDigit .= substr($paramValue, $i, 1);
			}
		}
		return $paramContainsDigit;
	}

	public function promptFirstName() {
		return $this->ivrConfigService->getValue("first_name");
	}

	public function promptLastName() {
		return $this->ivrConfigService->getValue("last_name");
	}

	public function promptLetter($letter) {
		return $this->ivrConfigService->getLetterValue($letter) . " " . $this->ivrConfigService->pause3ms();
	}

	public function promptPressOneHint() {
		return $this->ivrConfigService->getValue("press1_conn_inq");
	}

	public function promptPressTwoHint() {
		return $this->ivrConfigService->getValue("press_2_dec");
	}

	public function promptPressThreeHint() {
		return $this->ivrConfigService->getValue("press_3_dec_without_parking");
	}

	public function promptPressNineHint() {
		return $this->ivrConfigService->getValue("press9_hear_mess_ag");
	}

	public function promptOrPressNineHint() {
		return $this->ivrConfigService->getValue("or_press9_hear_mess_ag");
	}

	public function promptInvalidKeyPressed() {
		return $this->ivrConfigService->getValue("sorry_input_not_valid");
	}

	public function promptNoKeyPressed() {
		return $this->ivrConfigService->getValue("sorry_did_not_get");
	}

	public function promptConnecting() {
		return $this->ivrConfigService->getValue("connecting");
	}

	public function promptNoAnswer() {
		return $this->ivrConfigService->getValue("no_one_there");
	}

	public function promptBusy() {
		return $this->ivrConfigService->getValue("line_busy");
	}

	public function promptCongestion() {
		return $this->ivrConfigService->getValue("sorry_unable_connect_inq");
	}

	public function promptDumpCallHint() {
		return $this->ivrConfigService->getValue("press3_stop_re");
	}

	public function promptAfterCaptureResult() {
		return $this->ivrConfigService->getValue("input_after_capture_result");
	}

	public function promptCaptureCallResult() {
		return $this->ivrConfigService->getValue("press3_stop_re");
	}

	public function promptCallDumped() {
		return $this->ivrConfigService->getValue("no_more_reattempts");
	}

	public function promptGoodbye() {
		return $this->ivrConfigService->getValue("goodbye");
	}

	public function promptRepId() {
		return $this->ivrConfigService->getValue("Enter_ID");
	}

	public function promptFollowedPound() {
		return $this->ivrConfigService->getValue("Followed_Poundsign");
	}

	public function promptTransferBackground() {
		return $this->ivrConfigService->getValue("calling");
	}

	public function promptLeadWelcome() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['reverse_greeting'])) {
			return $this->ivrConfigService->getValue("reverse_greeting");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['reverse_greeting'];
		}
	}

	public function promptLeadGreeting4Machine() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['reverse_cpa_greeting'])) {
			return $this->ivrConfigService->getValue("pause_1ms");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['reverse_cpa_greeting'];
		}
	}

	public function promptLeadPress1() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['press1'])) {
			return $this->ivrConfigService->getValue("reverse_conn_press_2");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['press1'];
		}
	}

	public function promptPress3ToRejectCalls() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['press3'])) {
			return $this->ivrConfigService->getValue("reverse_press_3");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['press3'];
		}
	}

	public function promptHolding() {
		return $this->ivrConfigService->getValue("transfer_background");
	}

	public function promptSlient() {
		return $this->ivrConfigService->getValue("silent");
	}

	public function promptNoagentonline() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['unavailable_agent'])) {
			return $this->ivrConfigService->getValue("ALF_unavailable_agent");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['unavailable_agent'];
		}
	}

	public function promptLeadOnLine() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['someone_on_the_line'])) {
			return $this->ivrConfigService->getValue("reverse_call_whisper_someone_on_the_line");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['someone_on_the_line'];
		}
	}

	public function promptStandByConnecting() {
		$result = $this->customerManager->getReportType($this->accountId);
		if (empty($result['please_standby'])) {
			return $this->ivrConfigService->getValue("reverse_call_whisper_please_standby");
		} else {
			return $this->ivrConfigService->getIvrRootLocation() . $result['please_standby'];
		}
	}

}

?>