<?php
require_once 'service/PaypalService.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'util/Validator.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';
require_once 'BaseController.php';

class Widget_InvitationController extends BaseController {
	private $partnerManager;
	private $userManager;
	private $inviteManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
	}

	public function indexAction() {
		$this->getstartAction();
	}

	public function getstartAction() {
		$_SESSION["retry"] = 0;
		
		if ($_SESSION["country"] == null) {
			$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
			$_SESSION["partnerInx"] = $partner["inx"];
			$_SESSION["country"] = $partner["country"];
		}
		
		$this->renderScript("/invitation/getstart.phtml");
	}

	public function invitationAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$_SESSION["inviteType"] = $_POST["inviteType"];
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		// Init parameters
		$inviter = array (
			"phoneNum" => $_POST["inviterPhoneNumber"],
			"email" => $_POST["inviterEmail"],
			"createTime" => (new DateTime())->format("Y-m-d H:i:s") 
		);
		$invitee = array (
			"email" => $_POST["inviteeEmail"],
			"createTime" => (new DateTime())->format("Y-m-d H:i:s") 
		);
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if (Validator::isValidEmail($inviter["email"])) {
			array_push($validFields, "inviterEmailInvalid");
		} else {
			array_push($invalidFields, "inviterEmailInvalid");
		}
		if (Validator::isValidPhoneNumber($inviter["phoneNum"])) {
			array_push($validFields, "inviterPhoneNumberInvalid");
		} else {
			array_push($invalidFields, "inviterPhoneNumberInvalid");
		}
		if (Validator::isValidEmail($invitee["email"])) {
			array_push($validFields, "inviteeEmailInvalid");
		} else {
			array_push($invalidFields, "inviteeEmailInvalid");
		}
		
		// Dispatch
		if (count($invalidFields) == 0) {
			$inviter = $this->userManager->insert($inviter);
			$invitee = $this->userManager->insert($invitee);
			$invite = array (
				"partnerInx" => $_SESSION["partnerInx"],
				"inviterInx" => $inviter["inx"],
				"inviteeInx" => $invitee["inx"],
				"inviteType" => $_SESSION["inviteType"],
				"inviteToken" => md5(time()),
				"inviteTime" => (new DateTime())->format("Y-m-d H:i:s") 
			);
			$invite = $this->inviteManager->insert($invite);
			
			$_SESSION["inviteInx"] = $invite["inx"];
			$_SESSION["inviterInx"] = $inviter["inx"];
			$_SESSION["inviteeInx"] = $invitee["inx"];
			
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/widget/invitation/agreement" 
			);
		} else {
			$result = array (
				"redirect" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function agreementAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->view->assign("name", $invitee["name"]);
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
			$this->view->assign("freeCallDur", round($partner["freeCallDur"] / 60));
			$this->view->assign("chargeAmount", $partner["chargeAmount"]);
			$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
			$this->view->assign("inviterPay", "block");
			$this->view->assign("inviteePay", "none");
		} else {
			$this->view->assign("inviteePay", "block");
			$this->view->assign("inviterPay", "none");
		}
	}

	public function confirmationAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$invite = array (
			"inx" => $_SESSION["inviteInx"],
			"inviteResult" => INVITE_RESULT_INVITE 
		);
		$this->inviteManager->update($invite);
		
		$inviter = array (
			"inx" => $_SESSION["inviterInx"],
			"profileUrl" => $_POST["inviterProfile"] 
		);
		$this->userManager->update($inviter);
		
		$email = $this->userManager->findEmail($_SESSION["inviteInx"]);
		EmailSender::sendInviteEmail($email);
		
		$this->view->assign("name", $email["inviteeName"]);
	}

}