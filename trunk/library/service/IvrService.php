<?php

class IvrService {

	const IVR_DEFAULT = "ivr.";

	const ACCOUNT_PREFIX = "ACCOUNT_";

	private $accountId;

	private $config;

	private $ivrLocation;

	public function IvrService($accountId, $language) {
		$this->accountId = $accountId;
		$this->language = $language;
		$this->config = Zend_Registry::get("IVR_SETTING");
		$this->$ivrLocation = $this->config["rootlocation"] . "$language/";
	}

	private function getIvrAudio($key) {
		$ivrKey = self::ACCOUNT_PREFIX . $this->_accountId . ".$key";
		if (isset($this->config[$ivrKey])) {
			return $this->ivrLocation . $this->config[$ivrKey];
		} else {
			return $this->ivrLocation . $this->config[self::IVR_DEFAULT . ".$key"];
		}
	}

}
