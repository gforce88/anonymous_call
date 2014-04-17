<?php
require_once 'BaseManager.php';

class CallManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"inviteInx" => null,
		"callType" => null,
		"callResult" => null,
		"paypalToken" => null,
		"firstLegSession" => null,
		"secondLegSession" => null,
		"callInitTime" => null,
		"callConnectTime" => null,
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
			select calls.inx, partners.inx as partnerInx, invites.inx as inviteInx, calls.firstLegSession, calls.secondLegSession, partners.minCallBlkDur, partners.callRemindOffset, partners.country
			  from calls, invites, partners
			 where calls.inviteInx = invites.inx
			   and invites.partnerInx = partners.inx
			   and callEndTime = 0
			   and nextRemindTime != 0
			   and nextRemindTime < :now";

	const SQL_FIND_CHARGES = "
			select users.paypalToken, partners.chargeAmount
			  from calls, invites, partners, users
			 where calls.inviteInx = invites.inx
			   and invites.partnerInx = partners.inx
			   and users.inx = case calls.callType
                        when 0 then invites.inviterInx 
                               else invites.inviteeInx 
                                end
			   and calls.callEndTime = 0
			   and calls.nextChargeTime != 0
			   and calls.nextChargeTime < :now";

	public function insert($call) {
		$this->db->insert("calls", array_intersect_key($call, self::$empty));
		$call["inx"] = $this->db->lastInsertId();
		return $call;
	}

	public function update($call) {
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('inx = ?', $call['inx']));
	}

	public function updateReminds($nextRremindTime, $now) {
		$call = array (
			"nextRemindTime" => $nextRremindTime 
		);
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('callEndTime = 0 and nextRemindTime != 0 and nextChargeTime < ?', $now));
	}

	public function updateCharges($now) {
		$call = array (
			"nextChargeTime" => '00:00:00' 
		);
		return $this->db->update('calls', array_intersect_key($call, self::$empty), $this->db->quoteInto('callEndTime = 0 and nextChargeTime != 0 and nextChargeTime < ?', $now));
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

}