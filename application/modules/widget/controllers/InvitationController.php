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
		
		$this->view->assign("token", $token);
		$this->view->assign("language", $language);
		$this->view->assign("msgInviterNumberStyle", "none");
		$this->view->assign("msgInviterEmailStyle", "none");
		$this->renderScript("/invitation.phtml");
	}

	public function validateAction() {
		$token = $_POST["token"];
		$inviterName = $_POST["inviterName"];
		$inviterNumber = $_POST["inviterNumber"];
		$inviterEmail = $_POST["inviterEmail"];
		
		$language = "JP";
		
		$isValidate = true;
		if (Validator::isValidPhoneNumber($inviterNumber)) {
			$msgInviterNumberStyle = "none";
		} else {
			$isValidate = false;
			$msgInviterNumberStyle = "block";
		}
		if (Validator::isValidEmail($inviterEmail)) {
			$msgInviterEmailStyle = "none";
		} else {
			$isValidate = false;
			$msgInviterEmailStyle = "block";
		}
		
		if ($isValidate) {
			// TODO: save values and redirect to Thank you Page
		} else {
			$this->dispatchInvitation($token, $language, $msgInviterNumberStyle, $msgInviterEmailStyle);
		}
	}

	private function dispatchInvitation($token, $language, $msgInviterNumberStyle, $msgInviterEmailStyle) {
		$this->view->assign("token", $token);
		$this->view->assign("language", $language);
		$this->view->assign("msgInviterNumberStyle", $msgInviterNumberStyle);
		$this->view->assign("msgInviterEmailStyle", $msgInviterEmailStyle);
		$this->renderScript("/invitation.phtml");
		
	}
}