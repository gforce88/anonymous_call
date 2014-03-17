<?php

abstract class BaseManager {

	protected $db;
	protected $logger;

	public function __construct() {
		$this->db = Zend_Registry::get('DB_ADAPTER');
		$this->logger = LoggerFactory::getSysLogger();
	}

}