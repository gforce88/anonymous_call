<?php
require_once 'tropo/tropo.class.php';
require_once 'log/LoggerFactory.php';

class TropoController extends Zend_Controller_Action {
	private $logger;

	public function init() {
		$this->logger = LoggerFactory::getIvrLogger();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$hasJson = file_get_contents("php://input");
		if (empty($hasJson)) {
			$this->logger->logInfo("TropoController", "indexAction", "Tropo check via HTTP Header request.");
			$tropo = new Tropo();
			$tropo->renderJson();
		} else {
			$this->logger->logInfo("TropoController", "New Tropo session", $hasJson);
			$session = new Session($hasJson);
			$paramarray = $this->initSessionParameters($session);
			$parameters = $this->generateInteractiveParameters($paramarray);
			
			$_GET = array_merge($_GET, $paramarray);
			$this->callInviter();
		}
	}

	private function callInviter() {}

	private function callInvitee() {}

	private function initSessionParameters($session) {
		// Parameters for call flow control
		$paramarray = array ();
		$paramarray["session_id"] = $session->getId();
		$tropoSessionTimestampstr = $session->getTimeStamp();
		$tropoSessionTimestamp = substr($tropoSessionTimestampstr, 0, 10) . " " . substr($tropoSessionTimestampstr, 11, 8);
		
		// Parameters for partner
		$paramarray["partnerInx"] = $session->getParameters("partnerInx");
		
		// Parameters for invitation
		$paramarray["inviteInx"] = $session->getParameters("inviteInx");
		
		// log
		$this->logger->logInfo($paramarray["partnerInx"], $paramarray["inviteInx"], $session);
	}

	private function generateInteractiveParameters($paramarray) {
		$i = 0;
		foreach ($paramarray as $k => $v) {
			if ($i != 0) {
				$parameters = $parameters . "&";
			}
			$parameters = $parameters . "$k=" . urlencode($v);
			$i++;
		}
		return $parameters;
	}

	private function initTropo($paramarray, $appendHangup = true) {
		$tropo = new Tropo();
		
		if ($appendHangup) {
			$this->setEvent($tropo, $paramarray, "hangup");
			$this->setEvent($tropo, $paramarray, "error");
			$this->setEvent($tropo, $paramarray, "incomplete");
		}
		return $tropo;
	}

	private function setEvent($tropo, $paramArray, $event, $handler = null) {
		if ($handler == null) {
			$handler = $event;
		}
		$parameters = $this->generateInteractiveParameters($paramArray);
		$tropo->on(array (
			"event" => $event,
			"next" => APP_CTX . "/default/tropo/$event?$parameters" 
		));
	}

	private function log($infomations) {
		$this->logger->logInfo($_GET["partnerInx"], $_GET["inviteInx"], $infomations);
	}

}

