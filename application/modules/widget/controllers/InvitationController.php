<?php
require_once 'base/WedgitBaseController.php';
require_once 'log/LoggerFactory.php';
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
		$partnerInx = $_REQUEST["inx"];
		$partner = $this->partnerManager->findPartnerByInx($partnerInx);
		
		$this->dispatchInvitation($partnerInx, $partner["country"]);
	}

	public function validateAction() {
		$partnerInx = $_POST["partnerInx"];
		$partner = $this->partnerManager->findPartnerByInx($partnerInx);
		$country = $partner["country"];
		
		$user = array (
			"userAlias" => $_POST["userAlias"],
			"phoneNum" => $_POST["phoneNum"],
			"email" => $_POST["email"] 
		);
		
		$isValidate = true;
		if (Validator::isValidPhoneNumber($user["phoneNum"])) {
			$msgPhoneNumStyle = "none";
		} else {
			$isValidate = false;
			$msgPhoneNumStyle = "block";
		}
		if (Validator::isValidEmail($user["email"])) {
			$msgEmailStyle = "none";
		} else {
			$isValidate = false;
			$msgEmailStyle = "block";
		}
		
		if ($isValidate) {
			$user = $this->userManager->insert($user);
			$invite = array (
				"partnerInx" => $partnerInx,
				"inviterInx" => $user["inx"],
				"inviteeInx" => -1,
				"inviteMsg" => "XXXXXXXX" 
			);
			$this->inviteManager->insert($invite);
			$this->sendInviteeNotifyEmail($country, $partner["name"], $partner["emailAddr"], $user["email"], $user["userAlias"], $invite["inviteMsg"]);
			$this->dispatchResponse($country, $user["phoneNum"], $user["email"]);
		} else {
			$this->dispatchInvitation($partnerInx, $country, $user["userAlias"], $user["phoneNum"], $user["email"], $msgPhoneNumStyle, $msgEmailStyle);
		}
	}

	private function dispatchInvitation($partnerInx, $country, $userAlias = null, $phoneNum = null, $email = null, $msgPhoneNumStyle = "none", $msgEmailStyle = "none") {
		$this->view->assign("partnerInx", $partnerInx);
		$this->view->assign("country", $country);
		$this->view->assign("userAlias", $userAlias);
		$this->view->assign("phoneNum", $phoneNum);
		$this->view->assign("email", $email);
		$this->view->assign("msgPhoneNumStyle", $msgPhoneNumStyle);
		$this->view->assign("msgEmailStyle", $msgEmailStyle);
		$this->renderScript("/invitation.phtml");
	}

	private function dispatchResponse($country, $phoneNum, $email) {
		$this->view->assign("country", $country);
		$this->view->assign("phoneNum", $phoneNum);
		$this->view->assign("email", $email);
		$this->renderScript("/inviteThanks.phtml");
	}

	private function sendInviteeNotifyEmail($country, $fromName, $fromMail, $email, $userAlias, $inviterMsg) {
		$titleParam = array (
			$userAlias 
		);
		$contentParam = array (
			$userAlias,
			$inviterMsg,
			"URL" 
		);
		
		$subject = MultiLang::getText("email.inviteeNotify.title", $country, $titleParam);
		$content = MultiLang::getText("email.inviteeNotify.content", $country, $contentParam);
		
		return EmailSender::sendHtmlEmail($fromName, $fromMail, "", $email, $subject, $content);
	}

}