<?php
require_once 'tropo/tropo.class.php';
require_once 'log/LoggerFactory.php';
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'models/CallManager.php';

class Tropo_ConfController extends Zend_Controller_Action {
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
		$paramArr["callInx"] = $session->getParameters("callInx");
		$paramArr["partnerInx"] = $session->getParameters("partnerInx");
		$paramArr["inviteInx"] = $session->getParameters("inviteInx");
		$paramArr["mainCallSession"] = $session->getParameters("mainCallSession");
		$paramArr["country"] = $session->getParameters("country");
		
		return $paramArr;
	}

	public function indexAction() {
		$tropoJson = file_get_contents("php://input");
// 		if ($tropoJson == null) {
// 			$this->logger->logInfo("ConfController", "indexAction", "Tropo check via HTTP Header request.");
// 			$tropo = new Tropo();
// 			$tropo->renderJson();
// 		} else {
			$this->logger->logInfo("ConfController", "New Tropo session", $tropoJson);
			$session = new Session($tropoJson);
			$paramArr = $this->initSessionParameters($session);
			$_GET = array_merge($_GET, $paramArr);
			$this->log($_GET);
			
			$parameters = $this->generateInteractiveParameters($_GET);
			$tropo = $this->initTropo($parameters);
			
			$confOptions = array (
					"name" => "conference",
					"id" => "CONF." . $_GET["mainCallSession"],
					"mute" => false,
					"terminator" => "#",
					"allowSignals" => "exit"
			);
			$tropo->conference(null, $confOptions);
			
			$this->setEvent($tropo, $parameters, "continue", "playremind");
			$tropo->renderJson();
				
			$tropoService = new TropoService($this->logger);
			$response = $tropoService->startConf($_GET["mainCallSession"]);
			if (!$response) {
				$this->log("Main call exit. conference not started");
				$this->hangupAction();
				return;
			}
// 		}
	}

	public function playremindAction() {
		$this->log("Play remind audio");
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
		
		$this->setEvent($tropo, $parameters, "continue", "hangup");
		$tropo->RenderJson();
	}

	public function hangupAction() {
		$this->log("Conf call is hungup");
		
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
			$handler = $event;
		}
		$tropo->on(array (
			"event" => $event,
			"next" => "$handler.php?$parameters" 
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

	private function log($infomations) {
		$this->logger->logInfo($_GET["partnerInx"], $_GET["inviteInx"], $infomations);
	}

}

