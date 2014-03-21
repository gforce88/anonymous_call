<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/InviteManager.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/CallManager.php';

class Widget_ResponseController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->inviteManager = new InviteManager();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		if ($invite == null) {
			echo "Incorrect inx or token";
			// Incorrect inx or token
			$this->view->assign("country", $_REQUEST["country"]);
			return $this->renderScript("/response/timeout.phtml");
		}
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		if ($partner == null || $this->inviteExpired($partner["inviteExpireTimeDur"], $invite["inviteTime"])) {
			echo "No partner or invite expired";
			// No partner or invite expired
			$this->view->assign("country", $_REQUEST["country"]);
			$this->renderScript("/response/timeout.phtml");
		}
		$calls = $this->callManager->findAllCallsByInvite($invite["inx"]);
		if ($this->callCompleted($calls)) {
			echo "Already completed the call";
			// Already completed the call
			$this->view->assign("country", $_REQUEST["country"]);
			$this->renderScript("/response/timeout.phtml");
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
		$this->view->assign("inviterName", array (
			$inviter["userAlias"] 
		));
	}

	public function validateAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
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
			$invitee["phoneNum"] = $_POST["inviteePhoneNumber"];
			$invitee["paypalToken"] = $paypalToken;
			$this->userManager->update($invitee);
			
			$call = array (
				"inviteInx" => $_POST["inviteInx"] 
			);
			$tropoCall = array (
				"callerId" => $partner["phoneNumber"] 
			);
			
			if ($paypalToken == null) {
				// Pay by Inviter, first call inviter
				$tropoCall["numberToDial"] = $inviter["phoneNum"];
				$tropoCall["2ndLegNumber"] = $invitee["phoneNum"];
				$tropoCall["paypalToken"] = $inviter["paypalToken"];
				$tropoCall["email"] = $inviter["email"];
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
			} else {
				// Pay by Invitee, first call invitee
				$tropoCall["numberToDial"] = $invitee["phoneNum"];
				$tropoCall["2ndLegNumber"] = $inviter["phoneNum"];
				$tropoCall["paypalToken"] = $paypalToken;
				$tropoCall["email"] = $invitee["email"];
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
			}
			$call = $this->callManager->insert($call);
			$tropoCall["callInx"] = $call["inx"];
			
			$this->initCall($tropoCall, $partner);
			
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

	private function inviteExpired($expHour, $inviteTime) {
		$interval = strtotime(date("Y-m-d H:i:s")) - strtotime($inviteTime);
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

	private function initCall($tropoCall, $partner) {
		$tropoCall["minCallBlkDur"] = $partner["minCallBlkDur"];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
		$tropoCall[""] = $partner[""];
	}

} 