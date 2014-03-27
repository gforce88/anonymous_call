<?php
require_once 'tropo/tropo.class.php';
require_once 'log/LoggerFactory.php';
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'models/CallManager.php';

class TropoController extends Zend_Controller_Action {
	private $logger;
	private $callManager;

	public function init() {
		$this->logger = LoggerFactory::getIvrLogger();
		$this->callManager = new CallManager();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	private function initSessionParameters($session) {
		// Parameters for call flow control
		$paramArr = array ();
		$paramArr["session_id"] = $session->getId();
		$tropoSessionTimestampstr = $session->getTimeStamp();
		$tropoSessionTimestamp = substr($tropoSessionTimestampstr, 0, 10) . " " . substr($tropoSessionTimestampstr, 11, 8);
		$paramArr["sessionTimeOffset"] = strtotime((new DateTime())->format("Y-m-d H:i:s")) - strtotime($tropoSessionTimestamp);
		
		// parameters introduced in response controller
		$paramArr["partnerInx"] = $session->getParameters("partnerInx");
		$paramArr["inviteInx"] = $session->getParameters("inviteInx");
		$paramArr["callInx"] = $session->getParameters("callInx");
		$paramArr["callType"] = $session->getParameters("callType");
		$paramArr["partnerNumber"] = $session->getParameters("partnerNumber");
		$paramArr["1stLegNumber"] = $session->getParameters("1stLegNumber");
		$paramArr["2ndLegNumber"] = $session->getParameters("2ndLegNumber");
		$paramArr["maxRingDur"] = $session->getParameters("maxRingDur");
		$paramArr["country"] = $session->getParameters("country");
		
		return $paramArr;
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
			$this->log($_GET);
			$this->log($paramArr);
			$this->call1stLeg();
		}
	}

	private function call1stLeg() {
		$this->log("Start call to 1st leg: " . $_GET["1stLegNumber"]);
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_INIT);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$options = array (
			"from" => $_GET["partnerNumber"],
			"allowSignals" => "",
			"timeout" => floatval($_GET["maxRingDur"]) 
		);
		$tropo->call($_GET["1stLegNumber"], $options);
		
		$this->setEvent($tropo, $parameters, "continue", "greeting");
		$this->setEvent($tropo, $parameters, "incomplete", "failedconnect");
		$tropo->renderJSON();
	}

	public function failedconnectAction() {
		$this->log("Failed to connect to 1st leg: " . $_GET["1stLegNumber"]);
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_1STLEG_NOANSWER);
		
		$this->hangupAction();
	}

	public function greetingAction() {
		$this->log("Start greeting for 1st leg");
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_1STLEG_ANSWERED);
		
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		if ($_GET["callType"] == CALL_TYPE_FIRST_CALL_INVITER) {
			$sentences = $ivrService->promptInviterGreeting() . " ";
		} else {
			$sentences = $ivrService->promptInviteeGreeting() . " ";
		}
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$askoptions = array (
			"attempts" => 1,
			"bargein" => true,
			"timeout" => 0.1,
			"allowSignals" => "" 
		);
		$tropo->ask($sentences, $askoptions);
		
		$this->setEvent($tropo, $parameters, "continue", "transfer");
		$tropo->RenderJson();
	}

	public function transferAction() {
		$this->log("Start transfer to 2nd leg: " . $_GET["2ndLegNumber"]);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$transferoptions = array (
			"from" => $_GET["partnerNumber"],
			"allowSignals" => "",
			"timeout" => floatval($_GET["maxRingDur"]),
			"ringRepeat" => 10 
		);
		$tropo->transfer($_GET["2ndLegNumber"], $transferoptions);
		
		$this->setEvent($tropo, $parameters, "continue", "transfercontinue");
		$this->setEvent($tropo, $parameters, "incomplete", "failedtransfer");
		$tropo->renderJson();
	}

	public function failedtransferAction() {
		$this->log("Failed transfer to 2nd leg: " . $_GET["2ndLegNumber"]);
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_2NDLEG_NOANSWER);
		
		$this->hangupAction();
	}

	public function transfercontinueAction() {
		$this->log("Connected to 2nd leg: " . $_GET["2ndLegNumber"]);
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_2NDLEG_ANSWERED);
	}

	private function initTropo($parameters, $appendError = true) {
		$tropo = new Tropo();
		if ($appendError) {
			$this->setEvent($tropo, $parameters, "hangup");
			$this->setEvent($tropo, $parameters, "error");
		}
		return $tropo;
	}

	private function setEvent($tropo, $parameters, $event, $handler = null) {
		if ($handler == null) {
			$handler = $event . ".php";
		}
		$tropo->on(array (
			"event" => $event,
			"next" => "$handler?$parameters" 
		));
	}

	private function generateInteractiveParameters($paramArr) {
		$i = 0;
		foreach ($paramArr as $k => $v) {
			if ($i != 0) {
				$parameters = $parameters . "&";
			}
			$parameters = $parameters . "$k=" . urlencode($v);
			$i++;
		}
		return $parameters;
	}

	public function hangupAction() {
		$result = new Result();
		$this->log("Call state is " . $result->getState());
		
		$tropo = new Tropo();
		$tropo->hangup();
		$tropo->renderJson();
	}

	public function errorAction() {
		$this->log("System error with below parameters:");
		foreach ($_GET as $k => $v) {
			$$k = $v;
			$this->log("$k = $v");
		}
		$tropo = new Tropo();
		$tropo->hangup();
		$tropo->renderJson();
	}

	private function updateCallResult($callInx, $callResult, $callStartTime = null, $callEndTime = null) {
		$call = $this->callManager->findcallByInx($callInx);
		$call["callResult"] = $callResult;
		if ($callStartTime != null) {
			$call["callStartTime"] = $callStartTime;
		}
		if ($callEndTime != null) {
			$call["callEndTime"] = $callEndTime;
		}
		$this->callManager->update($call);
	}

	private function log($infomations) {
		$this->logger->logInfo($_GET["partnerInx"], $_GET["inviteInx"], $infomations);
	}

}

