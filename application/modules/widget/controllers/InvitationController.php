<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'util/Validator.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';
require_once 'models/EmailManager.php';

class Widget_InvitationController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;
	private $emailManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		$this->emailManager = new EmailManager();
		session_start();
	}

	public function indexAction() {
		$this->getstartAction();
	}

	public function getstartAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		
		$_SESSION["partnerInx"] = $partner["inx"];
		$_SESSION["country"] = $partner["country"];
		$_SESSION["inviteType"] = $_REQUEST["type"];
		$_SESSION["retry"] = 0;
		
		$this->renderScript("/invitation/getstart.phtml");
	}

	public function invitationAction() {
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
		// $paypalToken = PaypalService::regist($_POST["creditCardNumber"], $_POST["creditCardExp"], $_POST["creditCardCvc"]);
		// if ($paypalToken != null) {
		// $inviter["paypalToken"] = $paypalToken;
		// array_push($validFields, "creditCardInfoInvalid");
		// } else {
		// array_push($invalidFields, "creditCardInfoInvalid");
		// }
		
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
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->view->assign("name", $invitee["name"]);
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
			$this->view->assign("freeCallDur", round($partner["freeCallDur"] / 60));
			$this->view->assign("chargeAmount", $partner["chargeAmount"]);
			$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
			
			$this->renderScript("/invitation/acceptance.phtml");
		} else {
			$this->renderScript("/invitation/acknowlegment.phtml");
		}
	}

	public function confirmationAction() {
		$invite = array (
			"inx" => $_SESSION["inviteInx"],
			"inviteResult" => INVITE_RESULT_INVITE 
		);
		$this->inviteManager->update($invite);
		
		$email = $this->emailManager->findInviteEmail($_SESSION["inviteInx"]);
		EmailSender::sendInviteEmail($email);
		
		$this->view->assign("name", $email["inviteeName"]);
	}

}