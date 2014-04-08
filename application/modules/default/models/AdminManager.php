<?php
require_once 'models/BaseManager.php';

class AdminManager extends BaseManager {
	private static $empty = array (
		"inx" => null,
		"partnerInx" => null,
		"userName" => null,
		"pw" => null 
	);

	const SQL_FIND_ACCOUNT_BY_NAME_PW = "
			select *
			  from admins
			 where userName=:userName
			   and pw=PASSWORD(:pw)";

	public function findAccountByNameAndPw($userName, $pw) {
		return $this->db->fetchRow(self::SQL_FIND_ACCOUNT_BY_NAME_PW, array (
			"userName" => $userName,
			"pw" => $pw 
		));
	}

}