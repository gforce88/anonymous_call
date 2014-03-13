<?php
require_once 'base/BaseController.php';
require_once 'MultiLanguage.php';

class WedgitBaseController extends BaseController {

	public function init() {
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

}

