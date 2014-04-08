<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';
require_once 'models/CallManager.php';

class Widget_ResponseController extends Zend_Controller_Action {
	private $logger;
	private $paypalService;
	private $tropoService;
	private $partnerManager;
	private $userManager;
	private $inviteManager;
	private $callManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->paypalService = new PaypalService();
		$this->tropoService = new TropoService();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		if ($invite == null) {
			// The URL is invalid
			$this->view->assign("invalidReason", MultiLang::getText("This_link_is_invalid", $_REQUEST["country"]));
			return $this->renderScript("/response/invalidUrl.phtml");
		}
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		if ($partner == null || $this->inviteExpired($partner["inviteExpireDur"], $invite["inviteTime"])) {
			// The URL is valid but expired
			$this->view->assign("invalidReason", MultiLang::getText("This_link_is_no_longer_active", $_REQUEST["country"]));
			$this->renderScript("/response/invalidUrl.phtml");
		}
		$calls = $this->callManager->findAllCallsByInvite($invite["inx"]);
		if ($this->callCompleted($calls)) {
			// The call is already completed. It can NOT be inited again
			$this->view->assign("invalidReason", MultiLang::getText("The_call_is_already_completed", $_REQUEST["country"]));
			$this->renderScript("/response/invalidUrl.phtml");
		}
		
		$inviter = $this->userManager->findUserByInx($invite["inviterInx"]);
		if ($inviter["paypalToken"] == null) {
			$this->view->assign("ccInfo", "block");
			$this->view->assign("hasCcInfo", 1);
		} else {
			$this->view->assign("ccInfo", "none");
			$this->view->assign("hasCcInfo", 0);
		}
		$this->view->assign("country", $_REQUEST["country"]);
		$this->view->assign("inviteInx", $invite["inx"]);
		$this->view->assign("partnerInx", $invite["partnerInx"]);
		$this->view->assign("inviterInx", $invite["inviterInx"]);
		$this->view->assign("inviteeInx", $invite["inviteeInx"]);
		$this->view->assign("inviterName", $inviter["userAlias"]);
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if ($_POST["hasCcInfo"] == 1) {
			// Register paypal credit card ID as payapl token
			$paypalToken = $this->paypalService->regist($_POST["creditCardNumber"], $_POST["creditCardType"], $_POST["creditCardExp"], $_POST["creditCardCvc"], $_POST["firstName"], $_POST["lastName"]);
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
			$invitee["phoneNum"] = $_POST["inviteePhoneNumber"];
			$invitee["paypalToken"] = $paypalToken;
			$this->userManager->update($invitee);
			
			$call = array (
				"inviteInx" => $_POST["inviteInx"] 
			);
			$paramArr = array (
				"partnerInx" => $partner["inx"],
				"maxRingDur" => $partner["maxRingDur"],
				"inviteInx" => $call["inviteInx"],
				"partnerNumber" => $partner["phoneNum"],
				"country" => $partner["country"] 
			);
			
			if ($paypalToken == null) {
				// Pay by Inviter, first call inviter
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
				$paramArr["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
				$paramArr["1stLegNumber"] = $inviter["phoneNum"];
				$paramArr["2ndLegNumber"] = $invitee["phoneNum"];
			} else {
				// Pay by Invitee, first call invitee
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
				$paramArr["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
				$paramArr["1stLegNumber"] = $invitee["phoneNum"];
				$paramArr["2ndLegNumber"] = $inviter["phoneNum"];
			}
			$call = $this->callManager->insert($call);
			$paramArr["callInx"] = $call["inx"];
			
			// Init a Tropo call
			$this->tropoService->initCall($paramArr);
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/following?callInx=" . $call["inx"] 
			);
		} else {
			$result = array (
				"success" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

	private function inviteExpired($expHour, $inviteTime) {
		$interval = strtotime((new DateTime())->format("Y-m-d H:i:s")) - strtotime($inviteTime);
		if ($interval > $expHour * 3600) {
			return true;
		} else {
			return false;
		}
	}

	private function callCompleted($calls) {
		foreach ($calls as $call) {
			if ($call["callResult"] >= CALL_RESULT_2NDLEG_ANSWERED) {
				return true;
			}
		}
		return false;
	}

}
