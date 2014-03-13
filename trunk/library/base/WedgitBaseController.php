<?php
require_once 'base/BaseController.php';
require_once 'MultiLanguage.php';

class WedgitBaseController extends BaseController {

	protected $partner;

	public function init() {
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

	protected function getLanguage($key) {
		return MultiLanguage::getLanguage($key, $this->partner["language"]);
	}
}

