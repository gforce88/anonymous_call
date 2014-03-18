<?php
require_once 'base/BaseManager.php';

class InviteManager extends BaseManager {

	static $empty = array (
		"inx" => null,
		"partnerInx" => null,
		"inviterInx" => null,
		"inviteeInx" => null,
		"inviteMsg" => null 
	);

	public function insert($invite) {
		$this->db->insert("invites", array_intersect_key($invite, self::$empty));
		$invite["inx"] = $this->db->lastInsertId();
		return $invite;
	}

}