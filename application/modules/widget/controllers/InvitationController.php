<?php
require_once 'base/WedgitBaseController.php';
require_once 'Validator.php';

class Widget_InvitationController extends WedgitBaseController {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$token = $_REQUEST["token"];
		
		$language = "JP";
		
		$this->dispatchInvitation($token, $language);
	}

	public function validateAction() {
		$token = $_POST["token"];
		$inviterName = $_POST["inviterName"];
		$inviterNumber = $_POST["inviterNumber"];
		$inviteeEmail = $_POST["inviteeEmail"];
		
		$language = "JP";
		
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
		$this->renderScript("/invitation.phtml");
	}

	private function dispatchResponse($language, $inviterNumber, $inviteeEmail) {
		$this->view->assign("language", $language);
		$this->view->assign("inviterNumber", $inviterNumber);
		$this->view->assign("inviteeEmail", $inviteeEmail);
		$this->renderScript("/inviteThanks.phtml");
	}

}