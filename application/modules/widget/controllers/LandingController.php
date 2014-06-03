<?php
require_once 'BaseController.php';

class Widget_LandingController extends BaseController {

	public function indexAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		
		$inx = $_GET["inx"];
		if ($inx == null) {
			$inx = 1;
		}
		
		$this->view->assign("src", APP_CTX . "/widget/invitation?inx=" . $inx);
	}

	public function continueAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		
		$src = "?";
		foreach ($_GET as $key => $value) {
			if ($key == "action") {
				$action = $value;
				break;
			}
		}
		
		$this->view->assign("src", APP_CTX . "/widget/" . $action . "?" . $_SERVER["QUERY_STRING"]);
	}

}
