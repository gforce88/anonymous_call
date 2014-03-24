<?php
require_once 'base/BaseManager.php';

class CallManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"inviteInx" => null,
		"callType" => null,
		"callResult" => null,
		"callStartTime" => null,
		"callEndTime" => null 
	);

	const SQL_FIND_CALL_BY_INX = "select * from calls where inx=:inx";

	const SQL_FIND_ALL_CALLS_BY_INVITE = "select * from calls where inviteInx=:inviteInx";

	public function insert($call) {
		$this->db->insert("calls", array_intersect_key($call, self::$empty));
		$call["inx"] = $this->db->lastInsertId();
		return $call;
	}

	public function update($call) {
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('inx = ?', $call['inx']));
	}

	public function findcallByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_CALL_BY_INX, array (
			"inx" => $inx 
		));
	}

	public function findAllCallsByInvite($inviteInx) {
		return $this->db->fetchAll(self::SQL_FIND_ALL_CALLS_BY_INVITE, array (
			"inviteInx" => $inviteInx 
		));
	}

}