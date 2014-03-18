<?php
require_once 'base/BaseManager.php';

class PartnerManager extends BaseManager {

	const SQL_FIND_BY_INX = "select * from partners where inx=:inx";

	public function findPartnerByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_BY_INX, array (
			"inx" => $inx 
		));
	}

}