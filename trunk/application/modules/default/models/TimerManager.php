<?php
require_once 'models/BaseManager.php';

class TimerManager extends BaseManager {

	const SQL_FIND_REMINDS = "select tropoSession from calls where callEndTime = 0 and nextRemindTime != 0 and nextRemindTime < :now";

	public function findNextCharges() {
		return $this->db->fetchAll(self::SQL_FIND_ACCOUNT_BY_NAME_PW, array (
			"userName" => $userName,
			"pw" => $pw 
		));
	}

	public function findNextReminds() {
		return $this->db->fetchAll(self::SQL_FIND_ACCOUNT_BY_NAME_PW, array (
			"now" => (new DateTime())->format("Y-m-d H:i:s") 
		));
	}

}