<?php
require_once 'service/IvrService.php';
require_once 'tropo/tropo.class.php';
require_once 'log/LoggerFactory.php';
require_once 'models/CallManager.php';
require_once 'models/PartnerManager.php';

class BaseTropoController extends Zend_Controller_Action {
	protected $logger;
	protected $indicator;
	protected $callManager;
	protected $partnerManager;

	public function init() {
		$this->logger = LoggerFactory::getIvrLogger();
		$this->callManager = new CallManager();
		$this->partnerManager = new PartnerManager();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	protected function initSessionParameters($session) {
		// Parameters for call flow control
		$paramArr = array ();
		$paramArr["session_id"] = $session->getId();
		$paramArr["callInx"] = $session->getParameters("callInx");
		$paramArr["partnerInx"] = $session->getParameters("partnerInx");
		$paramArr["inviteInx"] = $session->getParameters("inviteInx");
		$paramArr["partnerNumber"] = $session->getParameters("partnerNumber");
		$paramArr["1stLegNumber"] = $session->getParameters("1stLegNumber");
		$paramArr["2ndLegNumber"] = $session->getParameters("2ndLegNumber");
		$paramArr["maxRingDur"] = $session->getParameters("maxRingDur");
		$paramArr["country"] = $session->getParameters("country");
		
		return $paramArr;
	}

	public function playremindAction() {
		$ivrService = new IvrService($_GET["partnerInx"], $_GET["country"]);
		$sentences = $ivrService->promptRemind() . " ";
		$this->log("Play remind audio $sentences");
		
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

	public function exitAction() {
		$this->log("Exit the confrence call");
		$this->hangupAction();
	}

	public function hangupAction() {
		$this->log("Call is hungup");
		
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

	protected function initTropo($parameters = null, $appendError = true) {
		$tropo = new Tropo();
		if ($appendError) {
			$this->setEvent($tropo, $parameters, "hangup");
			$this->setEvent($tropo, $parameters, "error");
		}
		return $tropo;
	}

	protected function setEvent($tropo, $parameters, $event, $handler = null) {
		if ($handler == null) {
			$handler = $event;
		}
		$tropo->on(array (
			"event" => $event,
			"next" => "$handler.php?$parameters" 
		));
	}

	protected function generateInteractiveParameters($paramArr) {
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

	protected function updateCallResult($call) {
		if ($call["callInitTime"] != null) {
			$call["callInitTime"] = $call["callInitTime"]->format("Y-m-d H:i:s");
		}
		if ($call["callStartTime"] != null) {
			$call["callStartTime"] = $call["callStartTime"]->format("Y-m-d H:i:s");
			$partner = $this->partnerManager->findPartnerByInx($_GET["partnerInx"]);
			$nextRemindTime = $call["callStartTime"]->getTimestamp() + $partner["freeCallDur"]; - $partner["callRemindOffset"] - 5; // 5 seconds for tropo comunication time
			$call["nextRemindTime"] = date("Y-m-d H:i:s", $nextRemindTime);
		}
		if ($call["callConnectTime"] != null) {
			$call["callConnectTime"] = $call["callConnectTime"]->format("Y-m-d H:i:s");
		}
		if ($call["callEndTime"] != null) {
			$call["callEndTime"] = $call["callEndTime"]->format("Y-m-d H:i:s");
		}
		$this->callManager->update($call);
	}

	protected function log($infomations) {
		$this->logger->logInfo($_GET["partnerInx"], $_GET["inviteInx"] . "|" . $this->indicator, $infomations);
	}

}

