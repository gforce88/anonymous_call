<?php

class IvrService {

	const IVR_DEFAULT = "ivr.";

	const ACCOUNT_PREFIX = "ACCOUNT_";

	private $accountId;

	private $config;

	private $ivrLocation;

	public function IvrService($accountId, $country) {
		$this->accountId = $accountId;
		$this->config = Zend_Registry::get("IVR_SETTING");
		$this->ivrLocation = $this->config["rootlocation"] . "$country/";
	}
	
	public function promptGreeting() {
		return $this->getIvrAudio("greeting");
	}
	
	public function promptRemind() {
		return $this->getIvrAudio("remind");
	}

	private function getIvrAudio($key) {
		$ivrKey = self::ACCOUNT_PREFIX . $this->accountId . "$key";
		if (isset($this->config[$ivrKey])) {
			return $this->ivrLocation . $this->config[$ivrKey];
		} else {
			return $this->ivrLocation . $this->config[self::IVR_DEFAULT . "$key"];
		}
	}

}
