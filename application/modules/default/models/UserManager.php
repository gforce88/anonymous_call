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

}