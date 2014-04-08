<?php
require_once 'util/HttpUtil.php';

class TropoService {
	private $httpUtil;
	private $querystringManager;

	public function __construct() {
		$this->httpUtil = new HttpUtil();
		$this->setting = Zend_Registry::get("TROPO_SETTING");
	}

	public function initCall($paramArr) {
		$url = $this->setting["url"];
		$token = $this->setting["token"];
		$params = "action=create&token=$token&" . http_build_query($paramArr);
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function initConfCall($paramArr) {
		$url = $this->setting["url"];
		$token = $this->setting["conf"]["token"];
		$params = "action=create&token=$token&" . http_build_query($paramArr);
		
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}
	
	public function hangupCall($sessionId) {
		
	}

}
