<?php
require_once 'util/MultiLang.php';

class Widget_FollowingController extends Zend_Controller_Action {

	public function indexAction() {
		$this->view->assign("country", $_REQUEST["country"]);
	}

}
