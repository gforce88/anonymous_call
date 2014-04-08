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

	public function sendJoinconfSignal($sessionId) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=joinconf&token=" . $this->setting["token"];
		$this->log("sending signal to : [$url]");
		$content = file_get_contents("$url");
	}

	public function sendStartconfSignal($sessionId) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=startconf&token=" . $this->setting["token"];
		$content = file_get_contents("$url");
		$this->log("sending signal to : [$url] > content $content");
		if (strpos($content, "NOTFOUND")) {
			return false;
		} else {
			return true;
		}
	}

}
