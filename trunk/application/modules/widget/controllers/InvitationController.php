<?php
require_once 'base/WedgitBaseController.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'util/Validator.php';
require_once 'models/PartnerManager.php';

class Widget_InvitationController extends WedgitBaseController {

	private $partnerManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
	}

	public function indexAction() {
		$token = $_REQUEST["token"];
		$partner = $this->partnerManager->findPartnerByToken($token);
		
		$this->dispatchInvitation($token, $partner["language"]);
	}

	public function validateAction() {
		$token = $_POST["token"];
		$partner = $this->partnerManager->findPartnerByToken($token);
		$language = $partner["language"];
		
		$inviterName = $_POST["inviterName"];
		$inviterNumber = $_POST["inviterNumber"];
		$inviteeEmail = $_POST["inviteeEmail"];
		
		$this->logInfo("", "", "before validation");
		$isValidate = true;
		if (Validator::isValidPhoneNumber($inviterNumber)) {
			$msgInviterNumberStyle = "none";
		} else {
			$isValidate = false;
			$msgInviterNumberStyle = "block";
		}
		if (Validator::isValidEmail($inviteeEmail)) {
			$msgInviterEmailStyle = "none";
		} else {
			$isValidate = false;
			$msgInviterEmailStyle = "block";
		}
		
		if ($isValidate) {
			$this->logInfo("", "", "before sending email");
			$this->sendInviteeNotifyEmail($language, $partner["name"], $partner["email"], $inviteeEmail, $inviterName, "XXXXXXXXXX");
			$this->logInfo("", "", "after sending email");
			$this->dispatchResponse($language, $inviterNumber, $inviteeEmail);
			$this->logInfo("", "", "after dispatch page");
		} else {
			$this->dispatchInvitation($token, $language, $inviterName, $inviterNumber, $inviteeEmail, $msgInviterNumberStyle, $msgInviterEmailStyle);
		}
	}

	private function dispatchInvitation($token, $language, $inviterName = null, $inviterNumber = null, $inviteeEmail = null, $msgInviterNumberStyle = "none", $msgInviterEmailStyle = "none") {
		$this->view->assign("token", $token);
		$this->view->assign("language", $language);
		$this->view->assign("inviterName", $inviterName);
		$this->view->assign("inviterNumber", $inviterNumber);
		$this->view->assign("inviteeEmail", $inviteeEmail);
		$this->view->assign("msgInviterNumberStyle", $msgInviterNumberStyle);
		$this->view->assign("msgInviterEmailStyle", $msgInviterEmailStyle);
		$this->renderScript("/invitation.phtml");
	}

	private function dispatchResponse($language, $inviterNumber, $inviteeEmail) {
		$this->view->assign("language", $language);
		$this->view->assign("inviterNumber", $inviterNumber);
		$this->view->assign("inviteeEmail", $inviteeEmail);
		$this->renderScript("/inviteThanks.phtml");
	}

	private function sendInviteeNotifyEmail($language, $fromName, $fromMail, $inviteeEmail, $inviterName, $inviterMessage) {
		$titleParam = array (
			$inviterName 
		);
		$contentParam = array (
			$inviterName,
			$inviterMessage,
			"URL" 
		);
		
		$subject = MultiLang::getText("email.inviteeNotify.title", $language, $titleParam);
		$content = MultiLang::getText("email.inviteeNotify.content", $language, $contentParam);
		
		return EmailSender::sendHtmlEmail($fromName, $fromMail, "", $inviteeEmail, $subject, $content);
	}

}