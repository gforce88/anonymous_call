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
		
		$inviter = array (
			"userAlias" => $_POST["inviterName"],
			"phoneNum" => $_POST["inviterNumber"] 
		);
		$invitee = array (
			"email" => $_POST["inviteeEmail"] 
		);
		
		$isValidate = true;
		if (Validator::isValidPhoneNumber($inviter["phoneNum"])) {
			$msgInviterNumberStyle = "none";
		} else {
			$isValidate = false;
			$msgInviterNumberStyle = "block";
		}
		if (Validator::isValidEmail($invitee["email"])) {
			$msgInviteeEmailStyle = "none";
		} else {
			$isValidate = false;
			$msgInviteeEmailStyle = "block";
		}
		
		if ($isValidate) {
			$inviter = $this->userManager->insert($inviter);
			$invitee = $this->userManager->insert($invitee);
			$invite = array (
				"partnerInx" => $partnerInx,
				"inviterInx" => $inviter["inx"],
				"inviteeInx" => $invitee["inx"],
				"inviteToken" => md5(time()),
				"inviteMsg" => "XXXXXXXX" 
			);
			$invite = $this->inviteManager->insert($invite);
			$this->sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite);
			$this->dispatchResponse($country, $inviter["phoneNum"], $invitee["email"]);
		} else {
			$this->dispatchInvitation($partnerInx, $country, $inviter["userAlias"], $inviter["phoneNum"], $invitee["email"], $msgInviterNumberStyle, $msgInviteeEmailStyle);
		}
	}

	private function sendInviteeNotifyEmail($partner, $inviter, $invitee, $invite) {
		$titleParam = array (
			$inviter["userAlias"] 
		);
		$contentParam = array (
			$inviter["userAlias"],
			$invite["inviteMsg"],
			"http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/response?inx=" . $invite["inx"] . "&token=" . $invite["inviteToken"] . "&country=" . $partner["country"] 
		);
		
		$subject = MultiLang::getText("email.inviteeNotify.title", $partner["country"], $titleParam);
		$content = MultiLang::getText("email.inviteeNotify.content", $partner["country"], $contentParam);
		
		echo $contentParam[2];
		
		return EmailSender::sendHtmlEmail($partner["name"], $partner["emailAddr"], "", $invitee["email"], $subject, $content);
	}

	private function dispatchInvitation($partnerInx, $country, $inviterName = null, $inviterNumber = null, $inviteeEmail = null, $msgInviterNumberStyle = "none", $msgInviteeEmailStyle = "none") {
		$this->view->assign("partnerInx", $partnerInx);
		$this->view->assign("country", $country);
		$this->view->assign("inviterName", $inviterName);
		$this->view->assign("inviterNumber", $inviterNumber);
		$this->view->assign("inviteeEmail", $inviteeEmail);
		$this->view->assign("msgInviterNumberStyle", $msgInviterNumberStyle);
		$this->view->assign("msgInviteeEmailStyle", $msgInviteeEmailStyle);
		$this->renderScript("/invitation.phtml");
	}

	private function dispatchResponse($country, $phoneNum, $email) {
		$this->view->assign("country", $country);
		$this->view->assign("phoneNum", $phoneNum);
		$this->view->assign("email", $email);
		$this->renderScript("/inviteThanks.phtml");
	}

}