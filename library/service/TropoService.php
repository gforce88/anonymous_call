<?php
require_once 'log/LoggerFactory.php';
require_once 'util/HttpUtil.php';

class TropoService {
	private $logger;
	private $httpUtil;
	private $querystringManager;

	public function __construct() {
		$this->logger = LoggerFactory::getIvrLogger();
		$this->httpUtil = new HttpUtil();
		$this->setting = Zend_Registry::get("TROPO_SETTING");
	}

	public function initCall($paramArr) {
		$params = "action=create&token=" . $this->setting["token"] . "&" . http_build_query($paramArr);
		$response = $this->httpUtil->doHTTPPOST($this->setting["url"], $params);
	}

	public function initConfCall($paramArr) {
		$params = "action=create&token=" . $this->setting["conf"]["token"] . "&" . http_build_query($paramArr);
		$response = $this->httpUtil->doHTTPPOST($url, $params);
	}

	public function startConf($sessionId) {
		return $this->sendSignal($sessionId, $this->setting["token"], "startconf");
	}

	public function joinConf($sessionId) {
		return $this->sendSignal($sessionId, $this->setting["conf"]["token"], "joinconf");
	}

	public function playRemind($firstLegSessionId, $secondLegSessionId) {
		$this->sendSignal($firstLegSessionId, $this->setting["token"], "playremind");
		$this->sendSignal($secondLegSessionId, $this->setting["conf"]["token"], "playremind");
	}

	public function exit1stLeg($sessionId) {
		return $this->sendSignal($sessionId, $this->setting["token"], "exit");
	}

	public function exit2ndLeg($sessionId) {
		return $this->sendSignal($sessionId, $this->setting["conf"]["token"], "exit");
	}

	private function sendSignal($sessionId, $token, $action) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=" . $action . "&token=" . $token;
		$content = file_get_contents($url);
		$this->logger->logInfo("TropoService", $action, "Sent signal to : [$url] > content: $content");
		if (strpos($content, "NOTFOUND")) {
			return false;
		} else {
			return true;
		}
	}

}
