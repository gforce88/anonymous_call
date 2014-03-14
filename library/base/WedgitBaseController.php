<?php
require_once 'base/BaseController.php';

class WedgitBaseController extends BaseController {

	public function init() {
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

}

