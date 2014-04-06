<?php
require_once 'models/BaseManager.php';

class TimerManager extends BaseManager {

	const SQL_FIND_ACCOUNT_BY_NAME_PW = "select * from admins where userName=:userName and pw=PASSWORD(:pw)";

	public function findNextCharge() {
		return $this->db->fetchRow(self::SQL_FIND_ACCOUNT_BY_NAME_PW, array (
			"userName" => $userName,
			"pw" => $pw 
		));
	}
	
	public function findNextRemind() {

	}
}