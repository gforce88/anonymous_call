<?php
require_once 'service/IVRConfigServiceInterface.php';
class IVRConfigTropoService implements IVRConfigServiceInterface{
	const ACCOUNT_PREFIX = "ACCOUNT_";
	const MP3_SUFFIX = ".mp3";
	const SERVICE_PROVIDER = "tropo";
	private $_ivrRootLocation;
	private $_config;
	private $_config_tts;
	private $_accountId;
	private $_languageId;
	private $_ivrPromptType;
	// private $customerManager;
	
	function IVRConfigTropoService($accountId){
		$customerManager = new CustomerManager();
		$customer = $customerManager->getById($accountId);
		$this->_accountId = $accountId;
		$this->_config = parse_ini_file(DEFAULT_IVR_CONFIG);
		// language setting
		$this->_languageId = $customer["language_id"];
		$this->_ivrPromptType = $customer["ivr_prompt_type"];
		$this->_ivrRootLocation = $this->initIvrRootLocation($accountId);
		
		switch ($this->_languageId) {
			case PORTUGUESE:
				$this->_config_tts = parse_ini_file(TTS_IVR_CONFIG_PORTUGUESE);
				break;	
			default:
				$this->_config_tts = parse_ini_file(TTS_IVR_CONFIG_ENGLISH);
				break;
		}
	}
	
	// will look if account specific setting is set. If not, will use system default as return value
	public function getTextValue($key){
		$tropoKey = self::ACCOUNT_PREFIX . $this->_accountId . ".$key";
		if(isset ($this->_config[$tropoKey])){
			return $this->_config[$tropoKey];
		}else{
			return $this->_config[self::SERVICE_PROVIDER . ".$key"];
		}
	}
	
	public function getTTSFieldValue ($key, $value){
		switch ($this->_languageId) {
			case PORTUGUESE:
				return " " . $key . " �� " . $value . ". ";
			default:
				return " Their " . $key . " is " . $value . ". ";
		}
	}
	
	// will check if this account has tts enabled. If tts is enabled, than use tts ivr configuration files
	// to load tts sentence. If find defined key in tts, use tts sentence. If key is not defined, use audio ivr.
	// If tts is not enabled, will load ivr audios from common ivr confgiuration
	public function getValue ($key){
		if (IVR_PROMPY_TYPE_AUDIO == $this->_ivrPromptType){
			return $this->getIvrAudio($key);
		}else{
			return $this->getIvrTTS($key);
		}
	}
	
	public function getLetterValue ($key){
		if (IVR_PROMPY_TYPE_AUDIO == $this->_ivrPromptType){
			return $this->getIvrAudio($key) . " ";
		}else{
			return $key . "";
		}
	}
	
	public function pause3ms(){
		if (IVR_PROMPY_TYPE_AUDIO == $this->_ivrPromptType){
			return $this->getIvrAudio("pause_3ms") . " ";
		}else{
			return "";
		}
	}
	
	public function pause1ms(){
		if (IVR_PROMPY_TYPE_AUDIO == $this->_ivrPromptType){
			return $this->getIvrAudio("pause_1ms");
		}else{
			return "";
		}
	}
	
	// Dedicate for getting the audio URL
	// will look if account specific setting is set. If not, will use system default as return value
	private function getIvrAudio($key){
		$tropoKey = self::ACCOUNT_PREFIX . $this->_accountId . ".$key";
		if(isset ($this->_config[$tropoKey])){
			return $this->_ivrRootLocation . $this->_config[$tropoKey] . self::MP3_SUFFIX;
		}else{
			return $this->_ivrRootLocation . $this->_config[self::SERVICE_PROVIDER . ".$key"] . self::MP3_SUFFIX;
		}
	}
	
	// Dedicate for getting the TTS sentence
	// will look if account specific setting is set. If not, will use system default as return value. 
	// IF EVEN system $key is not set, will call getIvrAudio to return the audio recordings. (such as calling music)
	private function getIvrTTS($key){
		$tropoKey = self::ACCOUNT_PREFIX . $this->_accountId . ".$key";
		if(isset ($this->_config_tts[$tropoKey])){
			return $this->_config_tts[$tropoKey] . ". ";
		}else if(isset ($this->_config_tts[self::SERVICE_PROVIDER . ".$key"])){
			return $this->_config_tts[self::SERVICE_PROVIDER . ".$key"] . ". ";
		}else{
			return $this->getIvrAudio($key);
		}
	}

	private function initIvrRootLocation(){
		$ivr_location = "";
		switch ($this->_languageId) {
				case SPANISH:
					$ivr_location = $this->_config[self::SERVICE_PROVIDER . ".ivr_root_location_spanish"];
					break;
				case PORTUGUESE:
					$ivr_location = $this->_config[self::SERVICE_PROVIDER . ".ivr_root_location_portuguese"];
					break;
				case BRITISH_ENGLISH:
					$ivr_location = $this->_config[self::SERVICE_PROVIDER . ".ivr_root_location_british"];
					break;	
				default:
					$ivr_location = $this->_config[self::SERVICE_PROVIDER . ".ivr_root_location"];
					break;
		}
		return $ivr_location;
	}
	
	public function getIvrRootLocation(){
		return $this->_ivrRootLocation;
	}
	
	public function getVoice(){
		$voice = '';		
		switch ($this->_languageId) {
			case SPANISH:
				$voice = $this->getTextValue("voice.spanish");
				break;
			case PORTUGUESE:
				$voice = $this->getTextValue("voice.portuguese");
				break;
			case BRITISH_ENGLISH:
				$voice = $this->getTextValue("voice.british_english");
				break;	
			default:
				$voice = $this->getTextValue("voice.english.default");
				break;
		}
		
		return $voice;
	}
}