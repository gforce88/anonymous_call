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
			$this->logger->logInfo($_GET);
			$this->callUser();
		}
	}

	private function callUser() {
		$this->log("Start call to 1st leg");
		$this->updateCallResult($_GET["callInx"], CALL_RESULT_INIT);
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$options = array (
			// "from" => $_GET["partnerNumber"],
			"allowSignals" => "",
			"timeout" => floatval($_GET["maxRingDur"]) 
		);
		$tropo->call($_GET["1stLegNumber"], $options);
		
		$this->setEvent($tropo, $parameters, "continue", "greeting");
		$tropo->renderJSON();
	}

	public function greetingAction() {
		$this->log("Start greeting for 1st leg");
		
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
		
		$this->setEvent($tropo, $parameters, "continue", "holding");
		$tropo->RenderJson();
		
		$tropoService = new TropoService();
		$tropoService->init2ndLegCall($tropoCall);
	}

	public function holdingAction() {
		$this->log("Start holding for 1st leg");
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $sentences . $ivrService->promptHolding() . " ";
		$tropo->say($sentences);
		
		$this->setEvent($tropo, $parameters, "continue", "holding");
		$this->setEvent($tropo, $parameters, "startconf");
		$this->setEvent($tropo, $parameters, "noagent");
		$tropo->renderJson();
	}

	public function startconfAction() {
		$_GET["callStatusId"] = CALL_STATUS_ANSWERED;
		
		$parameters = $this->generateInteractiveParameters($_GET);
		$tropo = $this->initTropo($parameters);
		
		$recordingSetting = $this->callRecordingManager->findByCustomerId($_GET["accountId"]);
		if (isset($recordingSetting) && $recordingSetting["is_enabled"] == "1") {
			$recordingOptions = TropoUtil::startRecording($recordingSetting);
			$tropo->startRecording($recordingOptions);
			$inquiry = $this->inquiryManager->getById($_GET["inquiryId"]);
			$inquiry["callRecordingName"] = $recordingOptions["fileName"];
			$this->inquiryManager->update($inquiry);
		}
		$tropo->conference(null, array (
			"name" => "conference",
			"id" => "CONF." . $_GET["session_id"] 
		));
		// $tropo->conference("CONF." . $_GET["session_id"] , $confOptions);
		$tropo->renderJson();
		$sessionId = $this->reverseCallSessionManager->findSecondLegSessionId($_GET["first_leg_session_id"]);
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=joinconf&token=" . $this->setting["token"];
		$result = file_get_contents("$url");
		$this->log("sending signal to: [$url] with result: [$result]");
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
		$tropo = new Tropo();
		$tropo->hangup();
		$tropo->renderJson();
	}

	public function errorAction() {
		$this->log("System get error.");
		$this->log("==================== Parameters ====================");
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

