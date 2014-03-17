<?php

class IvrService implements IVRConfigServiceInterface {

	const IVR_DEFAULT = "ivr.";

	const ACCOUNT_PREFIX = "ACCOUNT_";

	const MP3_SUFFIX = ".mp3";

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
			return $this->ivrLocation . $this->config[$ivrKey] . self::MP3_SUFFIX;
		} else {
			return $this->ivrLocation . $this->config[self::IVR_DEFAULT . ".$key"] . self::MP3_SUFFIX;
		}
	}

	public function pause1ms() {
		return $this->getIvrAudio("pause_1ms");
	}

	public function pause3ms() {
		return $this->getIvrAudio("pause_3ms") . " ";
	}

}
