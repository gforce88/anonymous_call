<?php
require_once 'base/WedgitBaseController.php';
require_once 'Validator.php';

class Widget_InvitationController extends WedgitBaseController {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$this->view->assign("token", "31415926");
		$this->view->assign("language", "JP");
		$this->renderScript("/invitation.phtml");
	}

	public function validateAction() {}

}