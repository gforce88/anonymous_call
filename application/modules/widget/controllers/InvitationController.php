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
				"inviteType" => 1, // TODO: how to detemine the invite type?
				"inviteToken" => md5(time()),
				"inviteTime" => (new DateTime())->format("Y-m-d H:i:s") 
			);
			$invite = $this->inviteManager->insert($invite);
			$_SESSION["inviteInx"] = $invite["inx"];
			
			$result = array (
				"success" => true,
				"url" => APP_CTX . "/widget/invitation/agreement?&freeCallDur=" . $partner["freeCallDur"] . "&chargeAmount=" . $partner["chargeAmount"] . "&minCallBlkDur=" . $partner["minCallBlkDur"] 
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

	public function agreementAction() {
		$this->view->assign("country", $_SESSION["country"]);
		$this->view->assign("freeCallDur", $_REQUEST["freeCallDur"]);
		$this->view->assign("chargeAmount", $_REQUEST["chargeAmount"]);
		$this->view->assign("minCallBlkDur", $_REQUEST["minCallBlkDur"]);
		
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
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
				"inviterResult" => INVITE_RESULT_INVITE 
			);
			$invite = $this->inviteManager->update($invite);
			
			$invite4Email = $this->inviteManager->findInvite4Email($invite["inx"]);
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
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function refreshAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$result = array (
			"url" => "" 
		);
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["url"] = APP_CTX . "/widget/invitation/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_ACCEPT) {
				// Invite is accepted by invitee
				$result["url"] = APP_CTX . "/widget/following";
			}
		} else {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["url"] = APP_CTX . "/widget/invitation/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is paied by invitee
				$result["url"] = APP_CTX . "/widget/invitation/acceptready";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is paied by invitee
				$result["url"] = APP_CTX . "/widget/following/problem";
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function declineAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function acceptAction() {
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
		
		$subject = MultiLang::replaceParams($invite4Email["inviteEmailSubject"], $titleParam);
		$content = MultiLang::replaceParams($invite4Email["inviteEmailBody"], $contentParam);
		
		$this->logger->logInfo($invite4Email["partnerInx"], $invite4Email["inx"], "Sending invitation emal to: [" . $invite4Email["inviteeEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($invite4Email["name"], $invite4Email["emailAddr"], "", $invite4Email["inviteeEmail"], $subject, $content);
		$this->logger->logInfo($invite4Email["partnerInx"], $invite4Email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}