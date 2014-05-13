<?php
require_once 'BaseManager.php';

class CallManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"inviteInx" => null,
		"callResult" => null,
		"paypalToken" => null,
		"firstLegSession" => null,
		"secondLegSession" => null,
		"callInitTime" => null,
		"callStartTime" => null,
		"callConnectTime" => null,
		"callEndTime" => null,
		"nextRemindTime" => null 
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
			select calls.inx, partners.inx as partnerInx, invites.inx as inviteInx, calls.firstLegSession, calls.secondLegSession, partners.minCallBlkDur, partners.callRemindOffset, partners.country
			  from calls, invites, partners
			 where calls.inviteInx = invites.inx
			   and invites.partnerInx = partners.inx
			   and callEndTime = 0
			   and nextRemindTime != 0
			   and nextRemindTime < :now";

	const SQL_COUNT_TOTAL_CALL = "
			select count(inx) as result
			  from calls
			 where inx = :inx";

	const SQL_COUNT_ACCEPTED_CALL = "
			select count(inx) as result
			  from calls
			 where calls.inx = :inx
			   and calls.callResult >= 3";

	const SQL_FIND_TOTAL_SECONDS = "
			select sum(callEndTime - callStartTime) as result
			  from calls
			 where inx = :inx";

	public function insert($call) {
		$this->db->insert("calls", array_intersect_key($call, self::$empty));
		$call["inx"] = $this->db->lastInsertId();
		return $call;
	}

	public function update($call) {
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('inx = ?', $call['inx']));
	}

	public function findCallByInx($inx) {
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

	public function countTotalCallByInx($inx) {
		return $this->db->fetchRow(self::SQL_COUNT_TOTAL_CALL, array (
			"inx" => $inx 
		));
	}

	public function countAcceptedCallByInx($inx) {
		return $this->db->fetchRow(self::SQL_COUNT_ACCEPTED_CALL, array (
			"inx" => $inx 
		));
	}

	public function FindTotalSecondsByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_TOTAL_SECONDS, array (
			"inx" => $inx 
		));
	}

}