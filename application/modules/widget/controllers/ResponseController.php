<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';

class Widget_ResponseController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		if ($invite == null) {
			$this->view->assign("country", $_REQUEST["country"]);
			$this->renderScript("/response/timeout.phtml");
		} else {
			$inviter = $this->userManager->findUserByInx($invite["inviterInx"]);
			if ($inviter["paypalToken"] == null) {
				$this->view->assign("ccInfo", "block");
				$this->view->assign("hasCcInfo", 1);
			} else {
				$this->view->assign("ccInfo", "none");
				$this->view->assign("hasCcInfo", 0);
			}
			$this->view->assign("country", $_REQUEST["country"]);
			$this->view->assign("partnerInx", $invite["partnerInx"]);
			$this->view->assign("inviterInx", $invite["inviterInx"]);
			$this->view->assign("inviteeInx", $invite["inviteeInx"]);
			$this->view->assign("inviterName", array (
				$inviter["userAlias"] 
			));
		}
	}

	public function validateAction() {
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if ($_POST["hasCcInfo"] == 1) {
			$paypalToken = PaypalService::regist($_POST["creditCardNumber"], $_POST["creditCardExp"], $_POST["creditCardCvc"]);
			if ($paypalToken != null) {
				array_push($validFields, "creditCardInfoInvalid");
			} else {
				array_push($invalidFields, "creditCardInfoInvalid");
			}
		}
		if (Validator::isValidPhoneNumber($_POST["inviteePhoneNumber"])) {
			array_push($validFields, "inviteePhoneNumberInvalid");
		} else {
			array_push($invalidFields, "inviteePhoneNumberInvalid");
		}
	
		// Dispatch
		if (count($invalidFields) == 0) {
			$partner = $this->partnerManager->findPartnerByInx($_POST["partnerInx"]);
			$inviter = $this->userManager->findUserByInx($_POST["inviterInx"]);
			$invitee = $this->userManager->findUserByInx($_POST["inviteeInx"]);
			$result["success"] = true;
			$result["url"] = APP_CTX . "/widget/following?country=" . $partner["country"];
			$this->_helper->json->sendJson($result);
		} else {
			$result["success"] = false;
			$result["validFields"] = $validFields;
			$result["invalidFields"] = $invalidFields;
			$this->_helper->json->sendJson($result);
		}
	}
} 