<?php
require_once 'base/WedgitBaseController.php';
require_once 'Validator.php';
require_once 'EmailSender.php';
require_once 'models/PartnerManager.php';

class Widget_InvitationController extends WedgitBaseController {

	private $partnerManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
	}

	public function indexAction() {
		$token = $_REQUEST["token"];
// 		$partner = $this->partnerManager->findPartnerByToken($token);
// 		$language = $partner["language"];
		
		$this->dispatchInvitation($token, "JP");
	}

	public function validateAction() {
		$token = $_POST["token"];
		$partner = $this->partnerManager->findPartnerByToken($token);
		$language = $partner["language"];
		
		$inviterName = $_POST["inviterName"];
		$inviterNumber = $_POST["inviterNumber"];
		$inviteeEmail = $_POST["inviteeEmail"];
		
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
			$this->sendInviteeNotifyEmail($language, $partner["name"], $partner["email"], $inviterName, $inviteeEmail);
			$this->dispatchResponse($language, $inviterNumber, $inviteeEmail);
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
		echo "here";
		$this->renderScript("/invitation.phtml");
	}

	private function dispatchResponse($language, $inviterNumber, $inviteeEmail) {
		$this->view->assign("language", $language);
		$this->view->assign("inviterNumber", $inviterNumber);
		$this->view->assign("inviteeEmail", $inviteeEmail);
		$this->renderScript("/inviteThanks.phtml");
	}

	private function sendInviteeNotifyEmail($language, $fromName, $fromMail, $inviterName, $inviteeEmail) {
		$sender = new EmailSender();
		$subject = MultiLanguage::getText("email.inviteeNotify.title", $language);
		$content = MultiLanguage::getText("email.inviteeNotify.content", $language);
		$sender->sendHtmlEmail($fromName, $fromMail, "", $inviteeEmail, $subject, $content);
	}

}