<?php
require_once 'base/BaseController.php';

class TropoBaseController extends BaseController {

	public function init() {
		$this->logger = Zend_Registry::get('IVR_LOGGER');
	}

}

