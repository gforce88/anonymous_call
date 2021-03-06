<?php
require_once 'BaseManager.php';

class UserManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"userAlias" => null,
		"phoneNum" => null,
		"email" => null,
		"profileUrl" => null,
		"paypalToken" => null,
		"createTime" => null 
	);

	const SQL_FIND_USER_BY_INX = "
			select *
			  from users
			 where inx=:inx";

	const SQL_FIND_INVITER_BY_INVITE_INX = "
			select users.*, SUBSTRING_INDEX(users.email, '@', 1) name
			  from users, invites
			 where users.inx = invites.inviterInx
			   and invites.inx=:inviteInx";

	const SQL_FIND_INVITEE_BY_INVITE_INX = "
			select users.*, SUBSTRING_INDEX(users.email, '@', 1) name
			  from users, invites
			 where users.inx = invites.inviteeInx
			   and invites.inx=:inviteInx";

	const SQL_FIND_TOKEN_BY_INVITE = "
			select users.paypalToken
			  from users, invites
			 where users.inx = case invites.inviteType
			            when 1 then invites.inviterInx 
			                   else invites.inviteeInx 
			                    end
			   and invites.inx = :inviteInx";

	const SQL_FIND_EMAIL = "
			select invites.inx inviteInx, invites.partnerInx, invites.inviteToken, invites.inviteType,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.country,
			       partners.inviteEmailSubject, partners.inviteEmailContent,
			       partners.acceptEmailSubject, partners.acceptEmailContent,
			       partners.declineEmailSubject, partners.declineEmailContent,
			       partners.readyEmailSubject, partners.readyEmailContent,
			       partners.retryEmailSubject, partners.retryEmailContent,
			       partners.thanksEmailSubject, partners.thanksEmailContent,
			       inviter.email inviterEmail, SUBSTRING_INDEX(inviter.email, '@', 1) inviterName,
			       invitee.email inviteeEmail, SUBSTRING_INDEX(invitee.email, '@', 1) inviteeName
			  from invites, partners, users inviter, users invitee
			 where invites.partnerInx = partners.inx
			   and invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.inx = :inx";

	public function insert($user) {
		$this->db->insert("users", array_intersect_key($user, self::$empty));
		$user["inx"] = $this->db->lastInsertId();
		return $user;
	}

	public function update($user) {
		return $this->db->update('users', array_intersect_key($user, self::$empty), $this->db->quoteInto('inx = ?', $user['inx']));
	}

	public function findUserByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_USER_BY_INX, array (
			"inx" => $inx 
		));
	}

	public function findInviterByInviteInx($inviteInx) {
		return $this->db->fetchRow(self::SQL_FIND_INVITER_BY_INVITE_INX, array (
			"inviteInx" => $inviteInx 
		));
	}

	public function findInviteeByInviteInx($inviteInx) {
		return $this->db->fetchRow(self::SQL_FIND_INVITEE_BY_INVITE_INX, array (
			"inviteInx" => $inviteInx 
		));
	}

	public function findTokenByInvite($inviteInx) {
		return $this->db->fetchOne(self::SQL_FIND_TOKEN_BY_INVITE, array (
			"inviteInx" => $inviteInx 
		));
	}

	public function findEmail($inx) {
		return $this->db->fetchRow(self::SQL_FIND_EMAIL, array (
			"inx" => $inx 
		));
	}

}