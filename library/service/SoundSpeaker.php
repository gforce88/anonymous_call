<?php

class SoundSpeaker {

	private $ivrService;

	public function SoundSpeaker($accountId, $language) {
		$this->accountId = $accountId;
		$this->language = $language;
		$this->ivrService = new IvrService($accountId, $language);
	}

	public function promptWelcome() {
		return $this->ivrService->getValue("we_are_now_connecting_you");
	}

}
