<?php
require_once 'base/BaseManager.php';

class UserManager extends BaseManager {

	static $empty = array (
		"inx" => null,
		"userAlias" => null,
		"phoneNum" => null,
		"email" => null,
		"paypalToken" => null 
	);

	public function insert($user) {
		$this->db->insert("users", array_intersect_key($user, self::$empty));
		$user["inx"] = $this->db->lastInsertId();
		return $user;
	}

}