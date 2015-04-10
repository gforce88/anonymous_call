<?php
require_once 'log/LoggerFactory.php';
require_once 'util/HttpUtil.php';

class TropoService {
	private $logger;
	private $httpUtil;
	private $querystringManager;

	public function __construct() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->httpUtil = new HttpUtil();
		$this->setting = Zend_Registry::get("TROPO_SETTING");
	}

	//拨专家电话
	public function callspecialist($paramArr) {
		$params = "action=create&token=" . $this->setting["specialist"]["token"] . "&" . http_build_query($paramArr);
		$response = $this->httpUtil->doHTTPPOST($this->setting["url"], $params);
	}
	
	//拨专家b电话
	public function callspecialistb($paramArr) {
		$params = "action=create&token=" . $this->setting["specialistb"]["token"] . "&" . http_build_query($paramArr);
		$response = $this->httpUtil->doHTTPPOST($this->setting["url"], $params);
	}
	
	//拨病人电话
	public function callpatient($paramArr) {
		$params = "action=create&token=" . $this->setting["patient"]["token"] . "&" . http_build_query($paramArr);
		$response = $this->httpUtil->doHTTPPOST($this->setting["url"], $params);
	}
	
	private function sendSignal($sessionId, $token, $action, $paramArr = null) {
		$url = $this->setting["url"] . "/" . $sessionId . "/signals?action=signal&value=" . $action . "&token=" . $token;
		if ($paramArr != null) {
			$url .= "&" . http_build_query($paramArr);
		}
		$content = file_get_contents($url);
		$this->logger->logInfo("TropoService", $action, "Sent signal to : [$url] > content: $content");
		if (strpos($content, "NOTFOUND")) {
			return false;
		} else {
			return true;
		}
	}
	
	public function specialistnoanswerRemind($patientSessionId = null){
		$this->sendSignal($patientSessionId, $this->setting["patient"]["token"], "specialistnoanswer");
	}
	

}
