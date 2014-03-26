<?php
require_once 'util/HttpUtil.php';
// require_once 'models/TempQuerystringManager.php';
class TropoService {

	private $httpUtil;

	private $querystringManager;

	public function __construct() {
		$this->httpUtil = new HttpUtil();
		// $this->querystringManager = new TempQuerystringManager();
		$this->setting = Zend_Registry::get("TROPO_SETTING");
	}

	public function initCall($paramArr) {
		$url = $this->setting["url"];
		$token = $this->setting["token"];
		$params = "action=create&token=$token&" . http_build_query($paramArr);
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function init1stLegCall($paramArr) {
		$url = $this->setting["url"];
		$token = $this->setting["1stLegToken"];
		$params = "action=create&token=$token&" . http_build_query($paramArr);
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function init2ndLegCall($paramArr) {
		$url = $this->setting["url"];
		$token = $this->setting["2ndLegToken"];
		$params = "action=create&token=$token&" . http_build_query($paramArr);
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function initCpaHack($numberToCall, $callerID, $sentences, $parameters) {
		$url = $this->setting["url"];
		$hackToken = urlencode($this->setting["cpaHackToken"]);
		$s2lSipNum = urlencode($this->setting["cpaHackNumber"]);
		$sentences = str_replace(" ", "~", $sentences);
		$querystringId = $this->querystringManager->insert(http_build_query($parameters));
		$params = "action=create&token=$hackToken&numberToCall=$numberToCall&callerID=$callerID&initialMessage=$sentences&s2lSipNum=$s2lSipNum&querystringId=$querystringId";
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

}
