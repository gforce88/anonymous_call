<?php
require_once 'BaseController.php';
require_once 'service/Mobile_Detect.php';

class Widget_LandingController extends BaseController {
	private $isMobileBrowser = true;

	public function init() {
		$detect = new Mobile_Detect();
		$this->isMobileBrowser = ($detect->isMobile() ? ($detect->isTablet() ? true : true) : false);
	}

	public function indexAction() {
		$inx = $_GET["inx"];
		if ($inx == null) {
			$inx = 1;
		}
		
		if ($this->isMobileBrowser) {
			$this->_redirect("/widget/invitation?inx=" . $inx);
		} else {
			// Disable layout for return json
			$this->_helper->layout->disableLayout();
			$this->view->assign("src", APP_CTX . "/widget/invitation?inx=" . $inx);
		}
	}

	public function continueAction() {
		foreach ($_GET as $key => $value) {
			if ($key == "action") {
				$action = $value;
				break;
			}
		}
		
		if ($this->isMobileBrowser) {
			$this->_redirect("/widget/" . $action . "?" . $_SERVER["QUERY_STRING"]);
		} else {
			// Disable layout for return json
			$this->_helper->layout->disableLayout();
			$this->view->assign("src", APP_CTX . "/widget/" . $action . "?" . $_SERVER["QUERY_STRING"]);
		}
	}

}
