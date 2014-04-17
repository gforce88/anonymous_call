<?php
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'BaseTropoController.php';

class Tropo_SecondlegController extends BaseTropoController {

	public function init() {
		parent::init();
		$this->indicator = "2ndLeg";
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
		$this->log("Start call to 2nd leg: " . $_GET["2ndLegNumber"]);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$callOptions = array (
			"from" => $_GET["partnerNumber"],
			"allowSignals" => "",
			"timeout" => floatval($_GET["maxRingDur"]) 
		);
		$tropo->call($_GET["1stLegNumber"], $callOptions);
		
		$this->setEvent($tropo, $parameters, "continue", "joinconf");
		$this->setEvent($tropo, $parameters, "incomplete", "failedconnect");
		$tropo->renderJSON();
	}

	public function failedconnectAction() {
		$this->log("Failed to connect to 2nd leg: " . $_GET["2ndLegNumber"]);
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_2NDLEG_NOANSWER,
			"callEndTime" => new DateTime() 
		);
		$this->updateCallResult($call);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		$tropoService = new TropoService();
		$tropoService->exit1stLeg($call["firstLegSession"]);
		
		$this->hangupAction();
	}

	public function joinconfAction() {
		$this->log("$this->indicator join conference call");
		$call = array (
			"inx" => $_GET["callInx"],
			"callResult" => CALL_RESULT_2NDLEG_ANSWERED,
			"callEndTime" => new DateTime() 
		);
		$this->updateCallResult($call);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters, false);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		$confOptions = array (
			"name" => "conference",
			"id" => "CONF." . $call["firstLegSession"],
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
		parent::playremindAction();
	}

	public function completeAction() {
		$this->log("$this->indicator completed call: " . $_GET["1stLegNumber"] . "<-->" . $_GET["2ndLegNumber"]);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		if ($call["firstLegSession"] != null) {
			$tropoService = new TropoService();
			$tropoService->exit1stLeg($call["firstLegSession"]);
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

