<?php

abstract class BaseManager {

	protected $db;
	protected $logger;

	public function __construct() {
		$this->db = Zend_Registry::get('DB_ADAPTER');
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

}