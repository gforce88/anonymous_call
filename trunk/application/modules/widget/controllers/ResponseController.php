<?php
require_once 'log/LoggerFactory.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';
require_once 'models/CallManager.php';
require_once 'models/EmailManager.php';

class Widget_ResponseController extends Zend_Controller_Action {
	private $logger;
	private $partnerManager;
	private $userManager;
	private $inviteManager;
	private $callManager;
	private $emailManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerManager = new PartnerManager();
		$this->userManager = new UserManager();
		$this->inviteManager = new InviteManager();
		$this->callManager = new CallManager();
		$this->emailManager = new EmailManager();
		session_start();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		if ($invite == null || $partner == null) {
			// The URL is invalid
			$this->view->assign("invalidReason", MultiLang::getText("This_link_is_invalid", $_REQUEST["country"]));
			return $this->renderScript("/response/invalid.phtml");
		}
		
		$_SESSION["inviteInx"] = $invite["inx"];
		$_SESSION["inviteType"] = $invite["inviteType"];
		$_SESSION["partnerInx"] = $invite["partnerInx"];
		$_SESSION["inviterInx"] = $invite["inviterInx"];
		$_SESSION["inviteeInx"] = $invite["inviteeInx"];
		$_SESSION["country"] = $partner["country"];
		
		$inviter = $this->userManager->findUserByInx($invite["inviterInx"]);
		$this->view->assign("inviter", $inviter["email"]);
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$this->view->assign("inviterPay", "block");
			$this->view->assign("inviteePay", "none");
		} else {
			// Display the fee description
			$this->view->assign("inviteePay", "block");
			$this->view->assign("inviterPay", "none");
			$this->view->assign("freeCallDur", $partner["freeCallDur"]);
			$this->view->assign("chargeAmount", $partner["chargeAmount"]);
			$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
		}
	}

	public function declineAction() {
		$email = $this->emailManager->findAcceptEmail($_SESSION["inviteInx"]);
		$this->sendDeclineEmail($email);
		
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function responseValidateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$calls = $this->callManager->findAllCallsByInvite($_SESSION["inviteInx"]);
		if ($this->inviteExpired($partner["inviteExpireDur"], $invite["inviteTime"]) || $this->callCompleted($calls)) {
			// The URL is expired or the call is already completed. It can NOT be inited again
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/response/invalid" 
			);
			return;
		}
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if (Validator::isValidPhoneNumber($_POST["inviteePhoneNumber"])) {
			array_push($validFields, "inviteePhoneNumberInvalid");
		} else {
			array_push($invalidFields, "inviteePhoneNumberInvalid");
		}
		if ($_POST["agreement"] == "on") {
			array_push($validFields, "agreementInvalid");
		} else {
			array_push($invalidFields, "agreementInvalid");
		}
		
		$result = array (
			"redirect" => false,
			"validFields" => $validFields,
			"invalidFields" => $invalidFields 
		);
		if (count($invalidFields) == 0) {
			$invitee = array (
				"inx" => $_SESSION["inviteeInx"],
				"phoneNum" => $_POST["inviteePhoneNumber"] 
			);
			$this->userManager->update($invitee);
			
			if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
				$email = $this->emailManager->findAcceptEmail($_SESSION["inviteInx"]);
				$this->sendAcceptEmail($email);
			} else {
				$result = array (
					"redirect" => true,
					"url" => APP_CTX . "/widget/following" 
				);
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function invalidAction() {
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
		if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
			// Invite is paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/following/ready";
		} else if ($invite["inviteResult"] == INVITE_RESULT_NOPAY) {
			// Invite is not paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/following/problem";
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

	private function sendAcceptEmail($email) {
		$titleParam = array (
			$email["fromEmail"] 
		);
		$contentParam = array (
			$email["fromEmail"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/following?inx=" . $email["inx"] . "&token=" . $email["inviteToken"] 
		);
		
		$subject = MultiLang::replaceParams($email["acceptEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($email["acceptEmailBody"], $contentParam);
		
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Sending accept email to: [" . $email["toEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($email["name"], $email["emailAddr"], "", $email["toEmail"], $subject, $content);
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

	private function sendDeclineEmail($email) {
		$titleParam = array (
			$email["fromEmail"] 
		);
		$contentParam = array (
			$email["fromEmail"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/following?inx=" . $email["inx"] . "&token=" . $email["inviteToken"] 
		);
		
		$subject = MultiLang::replaceParams($email["declineEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($email["declineEmailBody"], $contentParam);
		
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Sending decline email to: [" . $email["toEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($email["name"], $email["emailAddr"], "", $email["toEmail"], $subject, $content);
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}
