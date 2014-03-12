<?php
require_once 'base/BaseController.php';

class WedgitBaseController extends BaseController {

	protected $partner;

	public function init() {
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

	protected function getLanguage($key) {
		MultiLanguage::getLanguage($key, $this->partner["language"]);
	}
}

