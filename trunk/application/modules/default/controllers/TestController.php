<?php
require_once 'base/WedgitBaseController.php';

class TestController extends WedgitBaseController {

	public function init() {
		parent::init();
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		echo "Test";
		$this->logInfo("TestController", "indexAction", "info");
		$this->logInfo("TestController", "indexAction", "warn");
		$this->logInfo("TestController", "indexAction", "error");
		$this->renderScript("/empty.phtml");
	}

	public function retrieveInvitationAction() {
		echo "test";
//		$this->partner = array("language" => "EN");
//		echo $this->getLanguage("HelloWorld");
		$this->renderScript("/empty.phtml");
	}

	public function getAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$resp = $this->httpRequest($_SERVER["HTTP_HOST"], 80, "GET", "/main/api/v1/accounts/" . $_GET["id"]);
		echo $resp;
	}

	public function createAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$resp = $this->httpRequest($_SERVER["HTTP_HOST"], 80, "POST", "/main/api/v1/accounts/create", $_GET);
		echo $resp;
	}

	public function updateAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$resp = $this->httpRequest($_SERVER["HTTP_HOST"], 80, "POST", "/main/api/v1/accounts/update/" . $_GET["id"], $_GET);
		echo $resp;
	}

	private function httpRequest($host, $port, $method, $path, $params = null) {
		echo $path . "<br>";
		
		// Params are a map from names to values
		$paramStr = "";
		foreach ($params as $name => $val) {
			$paramStr .= $name . "=";
			$paramStr .= urlencode($val);
			$paramStr .= "&";
		}
		
		// Assign defaults to $method and $port, if needed
		if (empty($method)) {
			$method = "GET";
		}
		$method = strtoupper($method);
		if (empty($port)) {
			$port = 80; // Default HTTP port
		}
		
		// Create the connection
		$sock = fsockopen($host, $port);
		if ($method == "GET") {
			$path .= "?" . $paramStr;
		}
		
		fputs($sock, "$method $path HTTP/1.1\r\n");
		fputs($sock, "Host: $host\r\n");
		fputs($sock, "Content-type: " . "application/x-www-form-urlencoded\r\n");
		if ($method == "POST") {
			fputs($sock, "Content-length: " . strlen($paramStr) . "\r\n");
		}
		fputs($sock, "Connection: close\r\n\r\n");
		if ($method == "POST") {
			fputs($sock, $paramStr);
		}
		
		// Buffer the result
		$result = "";
		while (!feof($sock)) {
			$result .= fgets($sock, 1024);
		}
		
		fclose($sock);
		return $result;
	}

}

