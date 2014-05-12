<?php
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
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
		$tropo->conference(null, $conference);
		
		$this->setEvent($tropo, $parameters, "playremind");
		$this->setEvent($tropo, $parameters, "exit", "complete");
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->renderJson();
	}

	public function playremindAction() {
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $ivrService->promptRemind() . " ";
		$this->log("$this->indicator play remind audio " . $sentences);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters, false);
		
		$sayOptions = array (
			"allowSignals" => "" 
		);
		$tropo->say($sentences, $sayOptions);
		
		$this->setEvent($tropo, $parameters, "continue", "joinconf");
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->RenderJson();
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
			$billableDuration = 0;
		}
		$this->log("Call Duration: $callDuration");
		$this->log("Billalbe Duration: $billableDuration");
		$this->log($_GET);
		
		// Send thanks email
		$email = $this->userManager->findEmail($_GET["inviteInx"]);
		EmailSender::sendThanksEmail($email, $email["inviteType"] == INVITE_TYPE_INVITER_PAY);
		if ($email != null) $this->log($email);
		else $this->log("email is null");
		
		// Charge Paypal
		$paypalToken = $this->userManager->findTokenByInvite($_GET["inviteInx"]);
		if ($callDuration > 0) {
			$paypalService = new PaypalService();
			$paypalToken = $paypalService->charge($paypalToken, $partner["chargeAmount"] * $callDuration / 60, $partner["chargeCurrency"]);
		}
		
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

