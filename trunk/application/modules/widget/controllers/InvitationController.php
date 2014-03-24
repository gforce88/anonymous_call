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
	}

	public function indexAction() {
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		$this->view->assign("partnerInx", $partner["inx"]);
		$this->view->assign("country", $partner["country"]);
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		// Init parameters
		$inviter = array (
			"userAlias" => $_POST["inviterName"],
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
				$inviter["paypalToken"] = $paypalToken;
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
				"inviteMsg" => $_POST["inviteMsg"],
				"inviteTime" => (new DateTime())->format("Y-m-d H:i:s") 
			);
			$invite = $this->inviteManager->insert($invite);
			$this->sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite);
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/invitation/thanks?country=" . $partner["country"] . "&phoneNum=" . $inviter["phoneNum"] . "&email=" . $invitee["email"] 
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
		
		$this->logger->logInfo($partner["inx"], $invite["inx"], "Sending invitation emal to: [" . $invitee["email"] . "] with URL: [$contentParam[2]]");
		$sendResult = EmailSender::sendHtmlEmail($partner["name"], $partner["emailAddr"], "", $invitee["email"], $subject, $content);
		$this->logger->logInfo($partner["inx"], $invite["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}