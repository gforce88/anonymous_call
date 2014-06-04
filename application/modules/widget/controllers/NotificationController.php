<?php
require_once 'util/MultiLang.php';
require_once 'models/InviteManager.php';
require_once 'models/UserManager.php';
require_once 'BaseController.php';

class Widget_NotificationController extends BaseController {
	private static $INVITATION = 1;
	private static $RESPONSE = 2;
	private $inviteManager;
	private $userManager;

	public function init() {
		parent::init();
		$this->inviteManager = new InviteManager();
		$this->userManager = new UserManager();
	}

	public function refreshInvitationAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$_SESSION["notificationType"] = self::$INVITATION;
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
		
		$_SESSION["notificationType"] = self::$RESPONSE;
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

	public function invalidAction() {}

	public function readyAction() {
		$this->prepareUser();
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$this->view->assign("man", $inviter["name"]);
			$this->view->assign("woman", $invitee["name"]);
		} else {
			$this->view->assign("woman", $inviter["name"]);
			$this->view->assign("man", $invitee["name"]);
		}
	}

	public function declineAction() {
		$this->prepareUser();
	}

	public function sorryAction() {
		$this->prepareUser();
	}

	private function prepareUser() {
		if ($_SESSION["notificationType"] == self::$INVITATION) {
			$user = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		} else {
			$user = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		}
		$this->view->assign("name", $user["name"]);
	}

	private function prepareImg($isSame = false) {
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		if ($_SESSION["notificationType"] == self::$INVITATION) {
			if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
				
			} else {
				
			}
		} else {
			if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			
			} else {
			
			}
		}
		
	}

}
