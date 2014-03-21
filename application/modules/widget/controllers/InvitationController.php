<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'util/Validator.php';
require_once 'models/InviteManager.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';

class Widget_InvitationController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->inviteManager = new InviteManager();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
	}

	public function indexAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		$this->view->assign("partnerInx", $partner["inx"]);
		$this->view->assign("country", $partner["country"]);
	}

	public function validateAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		// Init parameters
		$inviter = array (
			"userAlias" => $_POST["inviterName"],
			"phoneNum" => $_POST["inviterPhoneNumber"],
			"email" => $_POST["inviterEmail"] 
		);
		$invitee = array (
			"email" => $_POST["inviteeEmail"] 
		);
		$invite = array (
			"inviteMsg" => $_POST["inviteMsg"] 
		);
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if ($inviter["userAlias"] != null && $inviter["userAlias"] != "") {
			array_push($validFields, "inviterNameNotNull");
		} else {
			array_push($invalidFields, "inviterNameNotNull");
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
		if ($_POST["payType"] == 1) {
			if (Validator::isValidEmail($inviter["email"])) {
				array_push($validFields, "inviterEmailInvalid");
			} else {
				array_push($invalidFields, "inviterEmailInvalid");
			}
			$paypalToken = PaypalService::regist($_POST["creditCardNumber"], $_POST["creditCardExp"], $_POST["creditCardCvc"]);
			if ($paypalToken != null) {
				$inviter["paypalTolen"] = $paypalToken;
				array_push($validFields, "creditCardInfoInvalid");
			} else {
				array_push($invalidFields, "creditCardInfoInvalid");
			}
		}
		
		// Dispatch
		$partner = $this->partnerManager->findPartnerByInx($_POST["partnerInx"]);
		if (count($invalidFields) == 0) {
			$inviter = $this->userManager->insert($inviter);
			$invitee = $this->userManager->insert($invitee);
			$invite = array (
				"partnerInx" => $partner["inx"],
				"inviterInx" => $inviter["inx"],
				"inviteeInx" => $invitee["inx"],
				"inviteToken" => md5(time()),
				"inviteMsg" => $_POST["inviteMsg"] 
			);
			$invite = $this->inviteManager->insert($invite);
			$this->sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite);
			$result["success"] = true;
			$result["url"] = APP_CTX . "/widget/invitation/thanks?country=" . $partner["country"] . "&phoneNum=" . $inviter["phoneNum"] . "&email=" . $invitee["email"];
			$this->_helper->json->sendJson($result);
		} else {
			$result["success"] = false;
			$result["validFields"] = $validFields;
			$result["invalidFields"] = $invalidFields;
			$this->_helper->json->sendJson($result);
		}
	}

	public function thanksAction() {
		$this->view->assign("country", $_REQUEST["country"]);
		$this->view->assign("phoneNum", $_REQUEST["phoneNum"]);
		$this->view->assign("email", $_REQUEST["email"]);
	}

	private function sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite) {
		$titleParam = array (
			$inviter["userAlias"] 
		);
		$contentParam = array (
			$inviter["userAlias"],
			$invite["inviteMsg"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/response?inx=" . $invite["inx"] . "&token=" . $invite["inviteToken"] . "&country=" . $partner["country"] 
		);
		
		$subject = MultiLang::replaceParams($partner["inviteEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($partner["inviteEmailBody"], $contentParam);
		
		return EmailSender::sendHtmlEmail($partner["name"], $partner["emailAddr"], "", $invitee["email"], $subject, $content);
	}

}