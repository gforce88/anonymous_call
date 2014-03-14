<?php
require_once 'base/WedgitBaseController.php';
require_once 'utils/MultiLang.php';

class TestController extends WedgitBaseController {

	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$titleParam = array (
			"Name" 
		);
		$subject = MultiLang::getText("email.inviteeNotify.title", "JP", $titleParam);
		echo $subject;
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

}

