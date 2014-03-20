<?php

class Widget_ResponseController extends Zend_Controller_Action {

	public function indexAction() {
		$this->view->assign("country", $_REQUEST["country"]);
	}

}
