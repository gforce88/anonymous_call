<?php
require_once 'base/BaseManager.php';

class UserManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"userAlias" => null,
		"phoneNum" => null,
		"email" => null,
		"paypalToken" => null,
		"createTime" => null 
	);

	const SQL_FIND_USER_BY_INX = "select * from users where inx=:inx";

	public function insert($user) {
		$this->db->insert("users", array_intersect_key($user, self::$empty));
		$user["inx"] = $this->db->lastInsertId();
		return $user;
	}

	public function findUserByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_USER_BY_INX, array (
			"inx" => $inx 
		));
	}

}