<?php
require_once 'base/TropoBaseController.php';

class TropoController extends TropoBaseController {

	public function init() {
		parent::init();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$hasJson = file_get_contents("php://input");
		if (empty($hasJson)) {
			$this->logInfo("TropoController", "indexAction", "Tropo check via HTTP Header request.");
			$tropo = new Tropo();
			$tropo->renderJson();
		} else {
			$this->logInfo("TropoController", "indexAction", "New Tropo session.");
			$session = new Session($hasJson);
			$paramarray = $this->initSessionParameters($session);
			$parameters = $this->generateInteractiveParameters($paramarray);
		}
	}

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
		$this->logInfo($paramarray["partnerInx"], $paramarray["inviteInx"], $session);
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

}

