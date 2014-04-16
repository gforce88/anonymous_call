<?php

class Logger {

	protected $logger;

	public function Logger($logger) {
		$this->logger = $logger;
	}

	public function logInfo($class, $function, $info) {
		$message = $this->formatMessage($class, $function, $info);
		$this->logger->info($message);
	}

	public function logWarn($class, $function, $info) {
		$message = $this->formatMessage($class, $function, $info);
		$this->logger->warn($message);
	}

	public function logError($class, $function, $info) {
		$message = $this->formatMessage($class, $function, $info);
		$this->logger->error($message);
	}

	private function formatMessage($class, $function, $info) {
		$date = (new DateTime)->format("Y-m-d");
		$time = (new DateTime)->format("H:i:s");
		$resule = $this->formatInformation($info);
		return "$date|$time|$class|$function|$resule";
	}
	
	private function formatInformation($info) {
		if (is_array($info)) {
			foreach ($info as $key => $val) {
				$result .= "[" . $key . ":" . $this->formatInformation($val) . "]";
			}
		} else {
			$result = $info;
		}
		return $result;
	}

}

