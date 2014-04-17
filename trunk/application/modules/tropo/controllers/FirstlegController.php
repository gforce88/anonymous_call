<?php
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'BaseTropoController.php';

class Tropo_FirstlegController extends BaseTropoController {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$tropoJson = file_get_contents("php://input");
		if ($tropoJson == null) {
			$this->logger->logInfo("TropoController", "indexAction", "Tropo check via HTTP Header request.");
			$tropo = new Tropo();
			$tropo->renderJson();
		} else {
			$this->logger->logInfo("TropoController", "New Tropo session", $tropoJson);
			$session = new Session($tropoJson);
			$paramArr = $this->initSessionParameters($session);
			$_GET = array_merge($_GET, $paramArr);
			$this->call1stLeg();
		}
	}

	private function call1stLeg() {
		$this->log("Start call to 1st leg: " . $_GET["1stLegNumber"]);
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
		if ($_GET["callType"] == CALL_TYPE_FIRST_CALL_INVITER) {
			$sentences = $ivrService->promptInviterGreeting() . " ";
		} else {
			$sentences = $ivrService->promptInviteeGreeting() . " ";
		}
		
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
		$this->log("Failed to connect to 1st leg: " . $_GET["1stLegNumber"]);
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_1STLEG_NOANSWER,
			"callEndTime" => new DateTime() 
		);
		$this->updateCallResult($call);
		$this->hangupAction();
	}

	public function cpadetectAction() {
		$this->log("Start CPA detection for 1st leg");
		
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
				
				$this->startconfAction();
			}
		}
	}

	private function startconfAction() {
		$this->log("Start conference call");
		
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
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->renderJson();
	}

	public function playremindAction() {
		$this->log("Play remind audio for 1st leg");
		
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $ivrService->promptInviterGreeting() . " ";
		$this->log("Play remind audio " . $sentences);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters, false);
		
		$sayOptions = array (
			"allowSignals" => "" 
		);
		$tropo->say($sentences, $sayOptions);
		
		$this->setEvent($tropo, $parameters, "continue", "startconf");
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->RenderJson();
	}

	public function completeAction() {
		$this->log("Call completed from 1st leg: " . $_GET["1stLegNumber"] . "<-->" . $_GET["2ndLegNumber"]);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		$tropoService = new TropoService();
		$tropoService->exit2ndLeg($call["secondLegSession"]);
		
		$this->exitAction();
	}

	public function exitAction() {
		$this->log("1st leg exit the confrence call");
		$this->hangupAction();
	}

}

