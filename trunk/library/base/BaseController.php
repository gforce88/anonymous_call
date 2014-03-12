<?php
require_once 'Zend/Controller/Action.php';

class BaseController extends Zend_Controller_Action {

	protected $logger;

	public function logInfo($class, $function, $infomations) {
		$message = $this->formatMessage($class, $function, $infomations);
		$this->logger->info($message);
	}

	public function logWarn($class, $function, $infomations) {
		$message = $this->formatMessage($class, $function, $infomations);
		$this->logger->warn($message);
	}

	public function logError($class, $function, $infomations) {
		$message = $this->formatMessage($class, $function, $infomations);
		$this->logger->error($message);
	}

	private function formatMessage($class, $function, $infomations) {
		$date = date("Y-m-d");
		$time = date("H:i:s");
		if (is_array($infomations)) {
			$value = "";
			foreach ($infomations as $key => $info) {
				$value .= "[" . $key . ":" . $info . "]";
			}
		} else {
			$value = $infomations;
		}
		return "$date|$time|$class|$function|$value";
	}

}

