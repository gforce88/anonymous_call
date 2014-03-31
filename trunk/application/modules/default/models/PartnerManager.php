<?php
require_once 'models/BaseManager.php';

class PartnerManager extends BaseManager {

	const SQL_FIND_PARTNER_BY_INX = "select * from partners where inx=:inx";

	const SQL_FIND_PARTNER_BY_CALL = "
			select *
			  from partners, invites, calls
			 where partners.inx = invites.partnerInx
			   and invites.inx = calls.inviteInx
			   and calls.inx = :callInx";

	public function insert($partner) {
		$this->db->insert("partners", array_intersect_key($partner, self::$empty));
		$partner["inx"] = $this->db->lastInsertId();
		return $partner;
	}

	public function update($partner) {
		return $this->db->update('partners', array_intersect_key($partner, self::$empty), $this->db->quoteInto('inx = ?', $partner['inx']));
	}

	public function findPartnerByInx($inx) {
		return $this->db->fetchRow(self::SQL_FIND_PARTNER_BY_INX, array (
			"inx" => $inx 
		));
	}

	public function findPartnerByCall($callInx) {
		return $this->db->fetchRow(self::SQL_FIND_PARTNER_BY_CALL, array (
			"callInx" => $callInx 
		));
	}

}