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
		
		$_SESSION["notificationType"] = NOTIFICATION_TYPE_INVITATION;
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
				$result["url"] = APP_CTX . "/widget/notification/ready";
			} else if ($invite["inviteResult"] == INVITE_RESULT_NOPAY) {
				// Invite is not paied by invitee
				$result["redirect"] = true;
				$result["url"] = APP_CTX . "/widget/notification/sorry";
			}
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function refreshResponseAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$_SESSION["notificationType"] = NOTIFICATION_TYPE_RESPONSE;
		$result = array (
			"redirect" => false 
		);
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		
		if ($invite["inviteResult"] == INVITE_RESULT_PAYED) {
			// Invite is paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/notification/ready";
		} else if ($invite["inviteResult"] == INVITE_RESULT_NOPAY) {
			// Invite is not paied by inviter
			$result["redirect"] = true;
			$result["url"] = APP_CTX . "/widget/notification/sorry";
		}
		
		$this->_helper->json->sendJson($result);
	}
	
	public function expiredAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function declineAction() {
		$this->prepareScreen();
	}

	public function readyAction() {
		$this->prepareScreen();
	}

	public function sorryAction() {
		$this->prepareScreen();
	}
	
	private function prepareScreen() {
		$user = $this->getUser();
		$this->view->assign("name", $user["name"]);
		$this->view->assign("country", $_SESSION["country"]);
	}

	private function getUser() {
		if ($_SESSION["notificationType"] == NOTIFICATION_TYPE_INVITATION) {
			return $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		} else {
			return $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		}
	}

}
