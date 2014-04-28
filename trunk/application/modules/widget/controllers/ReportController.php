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
		$this->logger = LoggerFactory::getSysLogger();
		$this->adminManager = new AdminManager();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$this->_helper->layout->disableLayout();
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		$this->view->assign("partnerInx", $partner["inx"]);
		$this->view->assign("country", $partner["country"]);
	}

	public function loginAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
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
				session_start();
				$_SESSION["partnerInx"] = $account["partnerInx"];
				$result = array (
					"success" => true,
					"url" => APP_CTX . "/widget/report/report?country=" . $_POST["country"] 
				);
				return $this->_helper->json->sendJson($result);
			}
		} else {
			// No DB check performed, so set it as valid currently
			array_push($validFields, "invalidPassword");
		}
		
		// invalid result
		$result = array (
			"success" => false,
			"validFields" => $validFields,
			"invalidFields" => $invalidFields 
		);
		$this->_helper->json->sendJson($result);
	}

	public function reportAction() {
		// validateion
		session_start();
		$partnerInx = $_SESSION["partnerInx"];
		if ($partnerInx == null) {
			$partnerInx = $_REQUEST["partnerInx"];
			header("Location: " . APP_CTX . "/widget/report/login?inx=" . $indexInx);
			return;
		}
		
		$this->view->assign("country", $_REQUEST["country"]);
	}

}
