<?php
require_once 'models/BaseManager.php';

class InviteManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"partnerInx" => null,
		"inviterInx" => null,
		"inviteeInx" => null,
		"inviteToken" => null,
		"inviteMsg" => null,
		"inviteTime" => null 
	);

	const SQL_FIND_INVITE_BY_INX = "
			select *
			  from invites
			 where inx = :inx";

	const SQL_FIND_INVITE_BY_INX_TOKEN = "
			select *
			  from invites
			 where inx = :inx
			   and inviteToken = :token";

	public function insert($invite) {
		$this->db->insert("invites", array_intersect_key($invite, self::$empty));
		$invite["inx"] = $this->db->lastInsertId();
		return $invite;
	}

	public function update($invite) {
		return $this->db->update('invites', array_intersect_key($invite, self::$empty), $this->db->quoteInto('inx = ?', $invite['inx']));
	}

	public function findInviteByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_INVITE_BY_INX, array (
			"inx" => $inx 
		));
	}

	public function findInviteByInxToken($inx, $token) {
		return $this->db->fetchRow(self::SQL_FIND_INVITE_BY_INX_TOKEN, array (
			"inx" => $inx,
			"token" => $token 
		));
	}

}