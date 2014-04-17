<?php
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'BaseTropoController.php';

class Tropo_SecondlegController extends BaseTropoController {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$tropoJson = file_get_contents("php://input");
		if ($tropoJson == null) {
			$this->logger->logInfo("ConfController", "indexAction", "Tropo check via HTTP Header request.");
			$tropo = new Tropo();
			$tropo->renderJson();
		} else {
			$this->logger->logInfo("ConfController", "New Tropo session", $tropoJson);
			$session = new Session($tropoJson);
			$paramArr = $this->initSessionParameters($session);
			$_GET = array_merge($_GET, $paramArr);
			$this->call2ndLeg();
		}
	}

	private function call2ndLeg() {
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
		$this->log("Start conference call");
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
		$this->log("Play remind audio for 2nd leg");
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $ivrService->promptInviterGreeting() . " ";
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$askOptions = array (
			"attempts" => 1,
			"bargein" => true,
			"timeout" => 5,
			"allowSignals" => "" 
		);
		$tropo->ask($sentences, $askOptions);
		$this->log("Play audio " . $sentences);
		
		$this->setEvent($tropo, $parameters, "continue", "joinconf");
		$this->setEvent($tropo, $parameters, "hangup", "complete");
		$this->setEvent($tropo, $parameters, "error");
		$tropo->RenderJson();
	}

public function completeAction() {
		$this->log("Call completed from 2nd leg: " . $_GET["1stLegNumber"] . "<-->" . $_GET["2ndLegNumber"]);
		
		$call = $this->callManager->findCallByInx($_GET["callInx"]);
		$tropoService = new TropoService();
		$tropoService->exit1stLeg($call["firstLegSession"]);
		
		$this->exitAction();
	}

	public function exitAction() {
		$this->log("2nd leg exit the confrence call");
		$this->hangupAction();
	}
}

