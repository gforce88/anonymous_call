<?php

abstract class BaseManager {

	protected $logger;

	public function __construct() {
		$this->db = Zend_Registry::get('dbAdapter');
		$this->logger = Zend_Registry::get('SYS_LOGGER');
	}

}