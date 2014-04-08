<?php
require_once 'models/BaseManager.php';

class CallManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"inviteInx" => null,
		"callType" => null,
		"callResult" => null,
		"paypalToken" => null,
		"tropoSession" => null,
		"callInitTime" => null,
		"callStartTime" => null,
		"callEndTime" => null,
		"nextRemindTime" => null,
		"nextChargeTime" => null 
	);

	const SQL_FIND_CALL_BY_INX = "
			select *
			  from calls
			 where inx=:inx";

	const SQL_FIND_ALL_CALLS_BY_INVITE = "
			select * 
			  from calls
			 where inviteInx=:inviteInx";

	const SQL_FIND_REMINDS = "
			select calls.*, partners.minCallBlkDur, partners.callRemindOffset
			  from calls, invites, partners
			 where calls.inviteInx = invites.inx
			   and invites.partnerInx = partners.inx
			   and callEndTime = 0
			   and nextRemindTime != 0
			   and nextRemindTime < :now";

	const SQL_FIND_CHARGES = "
			select *
			  from calls
			 where callEndTime = 0
			   and nextChargeTime != 0
			   and nextChargeTime < :now";

	public function insert($call) {
		$this->db->insert("calls", array_intersect_key($call, self::$empty));
		$call["inx"] = $this->db->lastInsertId();
		return $call;
	}

	public function update($call) {
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('inx = ?', $call['inx']));
	}

	public function updateReminds($nextRremindTime, $now) {
		$call = array("nextRemindTime" => $nextRremindTime);
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('callEndTime = 0 and nextRemindTime != 0 and nextChargeTime < ?', $now));
	}

	public function updateCharges($now) {
		$call = array("nextChargeTime" => '00:00:00');
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('callEndTime = 0 and nextChargeTime != 0 and nextChargeTime < ?', $now));
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

	public function findNextReminds($now) {
		return $this->db->fetchAll(self::SQL_FIND_REMINDS, array (
			"now" => $now 
		));
	}

	public function findNextCharges($now) {
		return $this->db->fetchAll(self::SQL_FIND_CHARGES, array (
			"now" => $now 
		));
	}

}