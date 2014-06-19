<?php
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'util/EmailSender.php';
require_once 'models/UserManager.php';
require_once 'BaseTropoController.php';

class Tropo_FirstlegController extends BaseTropoController {
	private $userManager;

	public function init() {
		parent::init();
		$this->indicator = "1stLeg";
		$this->userManager = new UserManager();
	}

	public function indexAction() {
		$tropoJson = file_get_contents("php://input");
		$this->logger->logInfo($this->indicator, "New Tropo session", $tropoJson);
		
		$session = new Session($tropoJson);
		$paramArr = $this->initSessionParameters($session);
		$_GET = array_merge($_GET, $paramArr);
		$this->initCall();
	}

	private function initCall() {
		$this->log("Start call to number " . $_GET["1stLegNumber"]);
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_INIT,
			"callInitTime" => new DateTime(),
			"firstLegSession" => $_GET["session_id"] 
		);
		$this->updateCallResult($call);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $ivrService->promptGreeting() . " ";
		$this->log("Play audio: " . $sentences);
		
		$callOptions = array (
			"from" => $_GET["partnerNumber"],
			"allowSignals" => "",
			"timeout" => floatval($_GET["maxRingDur"]),
			"machineDetection" => $sentences 
		);
		$tropo->call($_GET["1stLegNumber"], $callOptions);
		
		$this->setEvent($tropo, $parameters, "continue", "cpadetect");
		$this->setEvent($tropo, $parameters, "incomplete", "failedconnect");
		$tropo->renderJSON();
	}

	public function failedconnectAction() {
		$this->log("Failed to connect to number " . $_GET["1stLegNumber"]);
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_1STLEG_NOANSWER,
			"callEndTime" => new DateTime() 
		);
		$this->updateCallResult($call);
		$this->hangupAction();
	}

	public function cpadetectAction() {
		$this->log("Start CPA detection");
		
		$json = file_get_contents("php://input");
		$this->log($json);
		$result = new Result($json);
		
		$cpaType = $result->getUserType();
		$cpaState = $result->getState();
		$this->log("CPA type: " . $cpaType . ", CPA state: " . $cpaState);
		
		if ($cpaState == "DISCONNECTED") {
			// Call ended
			$call = array (
				"inx" => $_GET["callInx"],
				"callResult" => CALL_RESULT_1STLEG_NOANSWER,
				"callEndTime" => new DateTime() 
			);
			$this->updateCallResult($call);
			$this->hangupAction();
		} else {
			if ($cpaType == "MACHINE" || $cpaType == "FAX") {
				// Call ended
				$call = array (
					"inx" => $_GET["callInx"],
					"callResult" => CALL_RESULT_1STLEG_ANSWERMACHINE,
					"callEndTime" => new DateTime() 
				);
				$this->updateCallResult($call);
				$this->hangupAction();
			} else {
				// Call started
				$call = array (
					"inx" => $_GET["callInx"],
					"callResult" => CALL_RESULT_1STLEG_ANSWERED,
					"callStartTime" => new DateTime() 
				);
				$this->updateCallResult($call);
				
				$tropoService = new TropoService();
				$tropoService->initConfCall($_GET);
				
				$this->joinconfAction();
			}
		}
	}

	public function joinconfAction() {
		$this->log("Join conference call");
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters, false);
		
		$confOptions = array (
			"name" => "conference",
			"id" => "CONF." . $_GET["session_id"],
			"mute" => false,
			"allowSignals" => array (
				"playremind",
				"exit" 
			) 
		);
		$tropo->conference(null, $confOptions);
		$this->log("Conference call ID is " . $confOptions["id"]);
		
		$this->setEvent($tropo, $parameters, "playremind");
		$this->setEvent($tropo, $parameters, "exit", "complete");
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->renderJson();
	}

	public function completeAction() {
		$this->log("Completed call: " . $_GET["1stLegNumber"] . "<-->" . $_GET["2ndLegNumber"]);
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_COMPLETED,
			"callEndTime" => new DateTime() 
		);
		$this->updateCallResult($call);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		if ($call["secondLegSession"] != null) {
			$tropoService = new TropoService();
			$tropoService->exit2ndLeg($call["secondLegSession"]);
		}
		
		// Calculate call duration & billable duration
		$partner = $this->partnerManager->findPartnerByInx($_GET["partnerInx"]);
		$callDuration = strtotime($call["callEndTime"]) - strtotime($call["callStartTime"]);
		$billableDuration = $callDuration - $partner["freeCallDur"];
		if ($billableDuration < 0) {
			$chargeBlk = 0;
		} else {
			$chargeBlk = ceil($billableDuration / $partner["minCallBlkDur"]);
		}
		$this->log($_GET);
		$this->log("Call Duration: $callDuration");
		$this->log("Billalbe Duration: $billableDuration");
		
		$email = $this->userManager->findEmail($_GET["inviteInx"]);
		$email["callDuration"] = $callDuration;
		$email["billableDuration"] = $billableDuration;
		$email["chargeCurrency"] = $partner["chargeCurrency"];
		
		// Charge Paypal
		if ($chargeBlk > 0) {
			$paypalToken = $this->userManager->findTokenByInvite($_GET["inviteInx"]);
			$this->log("Paypal token: $paypalToken");
			
			$paypalService = new PaypalService($_GET["partnerInx"], $_GET["inviteInx"]);
			
			$chargeAmount = $paypalService->adjustAmount($partner["chargeAmount"] * $chargeBlk, $partner["chargeCurrency"]);
			$this->log("Charge Amount: $chargeAmount");
			$email["chargeAmount"] = $chargeAmount;
			
			$paypalService->charge($paypalToken, $chargeAmount, $partner["chargeCurrency"]);
		} else {
			$email["billableDuration"] = 0;
			$email["chargeAmount"] = 0;
		}
		
		// Send thanks email
		EmailSender::sendThanksEmail($email, $email["inviteType"] == INVITE_TYPE_INVITER_PAY);
		
		$this->exitAction();
	}

	public function exitAction() {
		parent::exitAction();
	}

	public function hangupAction() {
		parent::hangupAction();
	}

	public function errorAction() {
		parent::errorAction();
	}

}

