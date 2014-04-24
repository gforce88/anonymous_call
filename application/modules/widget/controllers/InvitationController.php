<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'util/Validator.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';

class Widget_InvitationController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		session_start();
	}

	public function indexAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		
		$_SESSION["partnerInx"] = $partner["inx"];
		$_SESSION["country"] = $partner["country"];
		
		$this->view->assign("country", $partner["country"]);
	}

	public function invitationformAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function invitationValidateAction() {
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
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		if (count($invalidFields) == 0) {
			$inviter = $this->userManager->insert($inviter);
			$invitee = $this->userManager->insert($invitee);
			$invite = array (
				"partnerInx" => $partner["inx"],
				"inviterInx" => $inviter["inx"],
				"inviteeInx" => $invitee["inx"],
				"inviteToken" => md5(time()),
				"inviteTime" => (new DateTime())->format("Y-m-d H:i:s") 
			);
			$invite = $this->inviteManager->insert($invite);
			$_SESSION["inviteInx"] = $invite["inx"];
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/invitation/acknowlegment?&freeCallDur=" . $partner["freeCallDur"] . "&chargeAmount=" . $partner["chargeAmount"] . "&minCallBlkDur=" . $partner["minCallBlkDur"] 
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

	public function acknowlegmentAction() {
		$this->view->assign("country", $_SESSION["country"]);
		$this->view->assign("freeCallDur", $_REQUEST["freeCallDur"]);
		$this->view->assign("chargeAmount", $_REQUEST["chargeAmount"]);
		$this->view->assign("minCallBlkDur", $_REQUEST["minCallBlkDur"]);
	}

	public function acknowlegmentValidateAction() {
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if ($_POST["agreement"] == "on") {
			array_push($validFields, "agreementInvalid");
		} else {
			array_push($invalidFields, "agreementInvalid");
		}
		
		// Dispatch
		if (count($invalidFields) == 0) {
			$invite4Email = $this->inviteManager->findInvite4Email($_SESSION["inviteInx"]);
			$this->sendInviteeNotifyEmail($invite4Email);
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/invitation/confirmation"
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

	public function confirmationAction() {
		$this->sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite);
		$this->view->assign("country", $_SESSION["country"]);
	}

	private function sendInviteeNotifyEmail($invite4Email) {
		$titleParam = array (
			$invite4Email["inviterEmail"] 
		);
		$contentParam = array (
			$invite4Email["inviterEmail"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/response?inx=" . $invite4Email["inx"] . "&token=" . $invite4Email["inviteToken"] . "&country=" . $invite4Email["country"] 
		);
		
		$subject = MultiLang::replaceParams($partner["inviteEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($partner["inviteEmailBody"], $contentParam);
		
		$this->logger->logInfo($partner["inx"], $invite["inx"], "Sending invitation emal to: [" . $invitee["email"] . "] with URL: [$contentParam[2]]");
		$sendResult = EmailSender::sendHtmlEmail($partner["name"], $partner["emailAddr"], "", $invitee["email"], $subject, $content);
		$this->logger->logInfo($partner["inx"], $invite["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}