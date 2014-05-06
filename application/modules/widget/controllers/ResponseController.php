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
			return $this->renderScript("/response/invalidUrl.phtml");
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
		// TODO: send decline email
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
		
		if (count($invalidFields) == 0) {
			$invitee = array (
				"inx" => $_SESSION["inviteeInx"],
				"phoneNum" => $_POST["inviteePhoneNumber"] 
			);
			$this->userManager->update($invitee);
			
			$result = array (
				"redirect" => true 
			);
			if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
				$result["url"] = APP_CTX . "/widget/response/accept";
			} else {
				$result["url"] = APP_CTX . "/widget/following";
			}
		} else {
			$result = array (
				"redirect" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function invalidAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function acceptAction() {
		// TODO: send accept email
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
			$result["url"] = APP_CTX . "/widget/invitation/accept";
		} else if ($invite["inviteResult"] == INVITE_RESULT_NOPAY) {
			// Invite is not paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/following/problem?inx=" . $invite["inx"];
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

	private function sendAcceptEmail($accept4Email) {
		$titleParam = array (
			$accept4Email["inviteeEmail"] 
		);
		$contentParam = array (
			$accept4Email["inviteeEmail"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/following?inx=" . $accept4Email["inx"] . "&token=" . $accept4Email["inviteToken"] 
		);
		
		$subject = MultiLang::replaceParams($accept4Email["inviteEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($accept4Email["inviteEmailBody"], $contentParam);
		
		$this->logger->logInfo($accept4Email["partnerInx"], $accept4Email["inx"], "Sending invitation email to: [" . $accept4Email["inviteeEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($accept4Email["name"], $accept4Email["emailAddr"], "", $accept4Email["inviteeEmail"], $subject, $content);
		$this->logger->logInfo($accept4Email["partnerInx"], $accept4Email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}
