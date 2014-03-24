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
		$tropoJson = file_get_contents("php://input");
		if ($tropoJson == null) {
			$this->logger->logInfo("TropoController", "indexAction", "Tropo check via HTTP Header request.");
			$tropo = new Tropo();
			$tropo->renderJson();
		} else {
			$this->logger->logInfo("TropoController", "New Tropo session", $tropoJson);
			$session = new Session($tropoJson);
			$paramArray = $this->initSessionParameters($session);
			$_GET = array_merge($_GET, $paramArray);
			
			if ($paramArray["callType"] == CALL_TYPE_FIRST_CALL_INVITER) {
				$this->callInviter();
			} else {
				$this->callInvitee();
			}
		}
	}

	private function callInviter() {
		$this->log("Start 1st leg call to inviter");
		$tropo = $this->initTropo($_GET);
	}

	private function callInvitee() {
		$this->log("Start 1st leg call to invitee");
		$tropo = $this->initTropo($_GET);
	}

	private function initSessionParameters($session) {
		// Parameters for call flow control
		$paramarray = array ();
		$paramarray["session_id"] = $session->getId();
		$tropoSessionTimestampstr = $session->getTimeStamp();
		$tropoSessionTimestamp = substr($tropoSessionTimestampstr, 0, 10) . " " . substr($tropoSessionTimestampstr, 11, 8);
		$paramarray["sessionTimeOffset"] = strtotime((new DateTime())->format("Y-m-d H:i:s")) - strtotime($tropoSessionTimestamp);
		
		// parameters introduced in response controller
		$paramarray["partnerInx"] = $session->getParameters("partnerInx");
		$paramarray["inviteInx"] = $session->getParameters("inviteInx");
		$paramarray["callInx"] = $session->getParameters("callInx");
		$paramarray["callType"] = $session->getParameters("callType");
		
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

	private function initTropo($paramArray, $appendError = true) {
		$tropo = new Tropo();
		
		if ($appendError) {
			$this->setEvent($tropo, $paramArray, "hangup");
			$this->setEvent($tropo, $paramArray, "error");
			$this->setEvent($tropo, $paramArray, "incomplete");
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

