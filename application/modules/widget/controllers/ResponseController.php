<?php
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';
require_once 'models/CallManager.php';
require_once 'BaseController.php';

class Widget_ResponseController extends BaseController {
	private $partnerManager;
	private $userManager;
	private $inviteManager;
	private $callManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		$this->callManager = new CallManager();
	}

	public function indexAction() {
		$this->responseAction();
	}

	public function responseAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		if ($invite == null || $partner == null) {
			// The URL is invalid
			return $this->renderScript("/notification/wrong.phtml");
		}
		
		$_SESSION["inviteInx"] = $invite["inx"];
		$_SESSION["inviteType"] = $invite["inviteType"];
		$_SESSION["partnerInx"] = $invite["partnerInx"];
		$_SESSION["inviterInx"] = $invite["inviterInx"];
		$_SESSION["inviteeInx"] = $invite["inviteeInx"];
		$_SESSION["country"] = $partner["country"];
		$_SESSION["retry"] = 0;
		
		$inviter = $this->userManager->findInviterByInviteInx($invite["inx"]);
		$this->view->assign("name", $inviter["name"]);
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$_SESSION["currentUserSex"] = WOMAN;
			$this->view->assign("inviterPay", "block");
			$this->view->assign("inviteePay", "none");
		} else {
			// Display the fee description
			$_SESSION["currentUserSex"] = MAN;
			$this->view->assign("inviteePay", "block");
			$this->view->assign("inviterPay", "none");
			$this->view->assign("freeCallDur", $this->sec2min($partner["freeCallDur"]));
			$this->view->assign("chargeAmount", $partner["chargeAmount"]);
			$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
		}
		
		return $this->renderScript("/response/response.phtml");
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$calls = $this->callManager->findAllCallsByInvite($_SESSION["inviteInx"]);
		// The URL is expired or the call is already completed. It can NOT be inited again
		if (Validator::isExpired($partner["inviteExpireDur"], $invite["inviteTime"]) || Validator::isCompleted($calls)) {
			// Switch the inviter and invitee for further generate new invitation
			if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
				$_SESSION["inviteType"] = INVITE_TYPE_INVITEE_PAY;
			} else {
				$_SESSION["inviteType"] = INVITE_TYPE_INVITER_PAY;
			}
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/widget/notification/invalid" 
			);
		} else {
			// Validation
			$validFields = array ();
			$invalidFields = array ();
			if (Validator::isValidPhoneNumber($_POST["inviteePhoneNumber"])) {
				array_push($validFields, "inviteePhoneNumberInvalid");
			} else {
				array_push($invalidFields, "inviteePhoneNumberInvalid");
			}
			
			if (count($invalidFields) == 0) {
				$invite = array (
					"inx" => $_SESSION["inviteInx"],
					"inviteResult" => INVITE_RESULT_ACCEPT 
				);
				$this->inviteManager->update($invite);
				
				$invitee = array (
					"inx" => $_SESSION["inviteeInx"],
					"phoneNum" => $_POST["inviteePhoneNumber"] 
				);
				$this->userManager->update($invitee);
				
				if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
					$result = array (
						"redirect" => true,
						"url" => APP_CTX . "/widget/response/accept" 
					);
				} else {
					$result = array (
						"redirect" => true,
						"url" => APP_CTX . "/widget/following/paypal" 
					);
				}
			} else {
				$result = array (
					"redirect" => false,
					"validFields" => $validFields,
					"invalidFields" => $invalidFields 
				);
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function acceptAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$invite = array (
			"inx" => $_SESSION["inviteInx"],
			"inviteResult" => INVITE_RESULT_ACCEPT 
		);
		$this->inviteManager->update($invite);
		
		$email = $this->userManager->findEmail($_SESSION["inviteInx"]);
		EmailSender::sendAcceptEmail($email);
		
		$this->view->assign("name", $email["inviterName"]);
		$this->view->assign("img", APP_CTX . "/images/Phones_M1.png");
	}

	public function declineAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$invite = array (
			"inx" => $_SESSION["inviteInx"],
			"inviteResult" => INVITE_RESULT_DECLINE 
		);
		$this->inviteManager->update($invite);
		
		$email = $this->userManager->findEmail($_SESSION["inviteInx"]);
		if ($_SESSION["currentUserSex"] == MAN) {
			EmailSender::sendDeclineEmail($email, "Phones_M2");
			$this->view->assign("img", APP_CTX . "/images/Phones_W2.png");
		} else {
			EmailSender::sendDeclineEmail($email, "Phones_W2");
			$this->view->assign("img", APP_CTX . "/images/Phones_M2.png");
		}
		$this->view->assign("name", $email["inviterName"]);
	}

}
