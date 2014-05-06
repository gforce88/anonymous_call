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
		$partner = $this->partnerManager->findPartnerByInx($_REQUEST["inx"]);
		
		$_SESSION["partnerInx"] = $partner["inx"];
		$_SESSION["country"] = $partner["country"];
		$_SESSION["inviteType"] = $_REQUEST["type"];
		
		$this->view->assign("country", $partner["country"]);
	}

	public function invitationAction() {
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
		$this->view->assign("country", $_SESSION["country"]);
		
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$this->view->assign("freeCallDur", $partner["freeCallDur"]);
		$this->view->assign("chargeAmount", $partner["chargeAmount"]);
		$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$this->renderScript("/invitation/acceptance.phtml");
		} else {
			$this->renderScript("/invitation/acknowlegment.phtml");
		}
	}

	public function agreementValidateAction() {
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
			$invite = array (
				"inx" => $_SESSION["inviteInx"],
				"inviteResult" => INVITE_RESULT_INVITE 
			);
			$this->inviteManager->update($invite);
			
			$email = $this->emailManager->findInviteEmail($_SESSION["inviteInx"]);
			$this->sendInviteEmail($email);
			
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/widget/invitation/confirmation" 
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

	public function confirmationAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function refreshAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$result = array (
			"redirect" => "false" 
		);
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/invitation/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_ACCEPT) {
				// Invite is accepted by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/following/paypal";
			}
		} else {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/invitation/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is paied by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/following/ready";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is not paied by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/following/problem";
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function declineAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	private function sendInviteEmail($email) {
		$titleParam = array (
			$email["fromEmail"] 
		);
		$contentParam = array (
			$email["fromEmail"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/response?inx=" . $email["inx"] . "&token=" . $email["inviteToken"] 
		);
		
		$subject = MultiLang::replaceParams($email["inviteEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($email["inviteEmailBody"], $contentParam);
		
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Sending invitation email to: [" . $email["toEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($email["name"], $email["emailAddr"], "", $email["toEmail"], $subject, $content);
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}