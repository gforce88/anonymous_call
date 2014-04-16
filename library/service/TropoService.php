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

	public function startConf($sessionId) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=startconf&token=" . $this->setting["token"];
		$content = file_get_contents($url);
		$this->logger->logInfo("Sent startconf signal to : [$url] > content: $content");
		if (strpos($content, "NOTFOUND")) {
			return false;
		} else {
			return true;
		}
	}

	public function joinConf($sessionId) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=joinconf&token=" . $this->setting["conf"]["token"];
		$content = file_get_contents($url);
		$this->logger->logInfo("Sent joinconf signal to : [$url] > content: $content");
	}

	public function playRemind($sessionId) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=playremind&token=" . $this->setting["token"];
		$content = file_get_contents($url);
		$this->logger->logInfo("Sent joinconf signal to : [$url] > content: $content");
	}

}
