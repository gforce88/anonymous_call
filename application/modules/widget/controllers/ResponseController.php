<?php
require_once 'log/LoggerFactory.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/UserManager.php';
require_once 'models/InviteManager.php';

class Widget_ResponseController extends Zend_Controller_Action {
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
		$inviteInx = $_REQUEST["inx"];
		$token = $_REQUEST["token"];
		$invite = $this->inviteManager->findInviteByInxToken($inviteInx, $token);
		
		if ($invite == null) {
			$this->view->assign("country", $_REQUEST["country"]);
			$this->renderScript("/timeout.phtml");
		} else {
			$inviter = $this->userManager->findUserByInx($invite["inviterInx"]);
			$this->dispatchResponse($invite["inviteeInx"], $_REQUEST["country"], array (
				$inviter["userAlias"] 
			));
		}
	}

	private function dispatchResponse($inviteeInx, $country, $inviterName, $inviteeCcNumber = null, $inviteeCcExp = null, $inviteeCcCvc = null, $inviteeNumber = null, $msgInviteeNumberStyle = "none") {
		$this->view->assign("inviteeInx", $inviteeInx);
		$this->view->assign("country", $country);
		$this->view->assign("inviterName", $inviterName);
		$this->view->assign("inviteeCcNumber", $inviteeCcNumber);
		$this->view->assign("inviteeCcExp", $inviteeCcExp);
		$this->view->assign("inviteeCcCvc", $inviteeCcCvc);
		$this->view->assign("inviteeNumber", $inviteeNumber);
		$this->view->assign("msgInviteeNumberStyle", $msgInviteeNumberStyle);
		$this->renderScript("/response.phtml");
	}

} 