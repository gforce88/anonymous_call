<?php
require_once 'util/HttpUtil.php';
require_once 'service/IVRServiceInterface.php';
//require_once 'models/TempQuerystringManager.php';

class TropoService implements IVRServiceInterface {

	private $httpUtil;

	private $querystringManager;

	public function __construct() {
		$this->httpUtil = new HttpUtil();
		//$this->querystringManager = new TempQuerystringManager();
		$this->setting = Zend_Registry::get('TROPO_SETTING');
	}

	public function initCall($parameters) {
		$url = $this->setting['url'];
		$token = $this->setting['token'];
		$params = "action=create&token=$token&" . http_build_query($parameters);
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function initCpaHack($numberToCall, $callerID, $sentences, $parameters) {
		$url = $this->setting['url'];
		$hackToken = urlencode($this->setting["cpaHackToken"]);
		$s2lSipNum = urlencode($this->setting["cpaHackNumber"]);
		$sentences = str_replace(" ", "~", $sentences);
		$querystringId = $this->querystringManager->insert(http_build_query($parameters));
		$params = "action=create&token=$hackToken&numberToCall=$numberToCall&callerID=$callerID&initialMessage=$sentences&s2lSipNum=$s2lSipNum&querystringId=$querystringId";
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

}
