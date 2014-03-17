<?php
require_once 'base/BaseController.php';

class TropoBaseController extends BaseController {

	public function init() {
		$this->logger = Zend_Registry::get('IVR_LOGGER');
	}

	public function log($infomations) {
		$this->logInfo($_GET["partnerInx"], $_GET["inviteInx"], $infomations);
	}

}

