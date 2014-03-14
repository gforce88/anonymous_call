<?php
require_once 'base/WedgitBaseController.php';
require_once 'utils/Validator.php';
require_once 'utils/EmailSender.php';
require_once 'utils/MultiLang.php';
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

}