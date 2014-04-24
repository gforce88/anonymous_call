<?php
require_once 'BaseManager.php';

class InviteManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"partnerInx" => null,
		"inviterInx" => null,
		"inviteeInx" => null,
		"inviteToken" => null,
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

	const SQL_FIND_INVITE_4_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name, partners.emailAddr, partners.country, partners.inviteEmailSubject, partners.inviteEmailBody,
			       inviter.email inviterEmail, invitee.email inviteeEmail
			  from invites, users inviter, users invitee, partners
			 where invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.partnerInx = partners.inx";

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

	public function findInvite4Email($inx) {
		return $this->db->fetchRow(self::SQL_FIND_INVITE_4_EMAIL, array (
			"inx" => $inx 
		));
	}

}