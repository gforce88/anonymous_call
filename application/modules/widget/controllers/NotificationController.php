<?php
require_once 'models/InviteManager.php';
require_once 'models/UserManager.php';

class Widget_NotificationController extends Zend_Controller_Action {
	private $inviteManager;
	private $userManager;

	public function init() {
		$this->inviteManager = new InviteManager();
		$this->userManager = new UserManager();
		session_start();
	}

	public function refreshInvitationAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$result = array (
			"redirect" => false 
		);
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/notification/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_ACCEPT) {
				// Invite is accepted by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/following/paypal";
			}
		} else {
			if ($invite["inviteResult"] == INVITE_RESULT_DECLINE) {
				// Invite is declined by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/notification/decline";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is paied by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/notification/ready1";
			} else if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
				// Invite is not paied by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/notification/sorry1";
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function refreshResponseAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$result = array (
			"redirect" => false 
		);
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		
		if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
			// Invite is paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/notification/ready2";
		} else if ($invite["inviteResult"] == INVITE_RESULT_NOPAY) {
			// Invite is not paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/notification/sorry2";
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function ready1Action() {
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->ready($invitee);
	}

	public function ready2Action() {
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$this->ready($inviter);
	}

	public function sorry1Action() {
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->sorry($invitee);
	}

	public function sorry2Action() {
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$this->sorry($inviter);
	}

	private function ready($user) {
		$this->view->assign("name", $user["name"]);
		$this->view->assign("country", $_SESSION["country"]);
		$this->renderScript("/notification/ready.phtml");
	}

	private function sorry($user) {
		$this->view->assign("name", $user["name"]);
		$this->view->assign("country", $_SESSION["country"]);
		$this->renderScript("/notification/sorry.phtml");
	}

}
