<?php
require_once 'base/BaseManager.php';

class CallManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"inviteInx" => null,
		"callResult" => null,
		"callDuration" => null
	);

	const SQL_FIND_CALL_BY_INX = "select * from calls where inx=:inx";

	public function insert($call) {
		$this->db->insert("calls", array_intersect_key($call, self::$empty));
		$call["inx"] = $this->db->lastInsertId();
		return $call;
	}

	public function findUserByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_CALL_BY_INX, array (
			"inx" => $inx 
		));
	}

}