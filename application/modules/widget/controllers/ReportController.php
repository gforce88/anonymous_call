<?php
require_once 'log/LoggerFactory.php';
require_once 'util/MultiLang.php';
require_once 'models/AdminManager.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';

class Widget_ReportController extends Zend_Controller_Action {
	private $logger;
	private $adminManager;
	private $partnerManager;
	private $callManager;

	public function init() {
		session_start();
		$this->_helper->layout->disableLayout();
		$this->logger = LoggerFactory::getSysLogger();
		$this->adminManager = new AdminManager();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		if ($partner != null) {
			$_SESSION["country"] = $partner["country"];
			$this->view->assign("country", $partner["country"]);
		}
	}

	public function loginAction() {
		// Disable layout for return json
		$this->_helper->viewRenderer->setNeverRender();
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if ($_POST["userName"] != null && $_POST["userName"] != "") {
			array_push($validFields, "userNameNotNull");
		} else {
			array_push($invalidFields, "userNameNotNull");
		}
		if ($_POST["password"] != null && $_POST["password"] != "") {
			array_push($validFields, "passwordNotNull");
		} else {
			array_push($invalidFields, "passwordNotNull");
		}
		
		// Further validation
		if (count($invalidFields) == 0) {
			$account = $this->adminManager->findAccountByNameAndPw($_POST["userName"], $_POST["password"]);
			if ($account == null) {
				array_push($invalidFields, "invalidPassword");
			} else {
				$_SESSION["partnerInx"] = $account["partnerInx"];
				$result = array (
					"success" => true,
					"url" => APP_CTX . "/widget/report/report" 
				);
				return $this->_helper->json->sendJson($result);
			}
		}
		
		// invalid result
		$result = array (
			"success" => false,
			"validFields" => $validFields,
			"invalidFields" => $invalidFields 
		);
		$this->_helper->json->sendJson($result);
	}

	public function logoutAction() {
		$_SESSION["partnerInx"] = null;
		$this->renderScript("/report/index.phtml");
	}

	public function reportAction() {
		// validateion
		$partnerInx = $_SESSION["partnerInx"];
		if ($partnerInx == null) {
			$partnerInx = $_REQUEST["partnerInx"];
			header("Location: " . APP_CTX . "/widget/report?inx=" . $indexInx);
			return;
		}
		
		if ($_POST["startDate"] != null && $_POST["endDate"] != null) {
			$startDate = $_POST["startDate"];
			$endDate = $_POST["endDate"];
		} else {
			$startDate = (new DateTime())->format("m/01/Y");
			$endDate = (new DateTime())->format("m/d/Y");
		}
		
		$totalCalls = $this->callManager->countTotalCallByInx($partnerInx)["result"];
		$acceptedCalls = $this->callManager->countAcceptedCallByInx($partnerInx)["result"];
		if ($totalCalls == 0) {
			$acceptedPercent = 0;
		} else {
			$acceptedPercent = $acceptedCalls / $totalCalls * 100;
		}
		$totalSeconds = $this->callManager->FindTotalSecondsByInx($partnerInx)["result"];
		$totalMinutes = round($totalSeconds / 60);
		
		$this->view->assign("country", $_SESSION["country"]);
		$this->view->assign("startDate", $startDate);
		$this->view->assign("endDate", $endDate);
		$this->view->assign("totalCalls", $totalCalls);
		$this->view->assign("acceptedCalls", $acceptedCalls);
		$this->view->assign("acceptedPercent", $acceptedPercent);
		$this->view->assign("totalMinutes", $totalMinutes);
	}

}
