<?php
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
	private $partnerManager;
	private $userManager;
	private $inviteManager;
	private $callManager;

	public function init() {
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		if ($invite == null) {
			$this->view->assign("invalidReason", MultiLang::getText("This_link_is_invalid", $_REQUEST["country"]));
			return $this->renderScript("/response/invalidUrl.phtml");
		}
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		if ($partner == null || $this->inviteExpired($partner["inviteExpireDur"], $invite["inviteTime"])) {
			$this->view->assign("invalidReason", MultiLang::getText("This_link_is_no_longer_active", $_REQUEST["country"]));
			$this->renderScript("/response/invalidUrl.phtml");
		}
		$calls = $this->callManager->findAllCallsByInvite($invite["inx"]);
		if ($this->callCompleted($calls)) {
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
			if ($paypalToken == null) {
				// Pay by Inviter, first call inviter
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
			} else {
				// Pay by Invitee, first call invitee
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
			}
			$call = $this->callManager->insert($call);
			
			$tropoCall = array (
				"partnerInx" => $partner["inx"],
				"inviteInx" => $call["inviteInx"],
				"callInx" => $call["inx"],
				"callType" => $call["callType"] 
			);
			
			$tropoService = new TropoService();
			$tropoService->initCall($tropoCall);
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/following?callInx=" . $call["inx"] 
			);
			$this->_helper->json->sendJson($result);
		} else {
			$result = array (
				"success" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
			$this->_helper->json->sendJson($result);
		}
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

	private function initCall($tropoCall, $partner) {
		$tropoCall["partnerInx"] = $partner["minCallBlkDur"];
		$tropoCall["inviteInx"] = $tropoCall["inviteInx"];
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