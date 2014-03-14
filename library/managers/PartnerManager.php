<?php
require_once 'base/BaseManager.php';

class PartnerManager extends BaseManager {

	public function findPartnerByToken($token) {
		$partner = array (
			"id" => "1001",
			"token" => $token,
			"language" => "JP",
			"name" => "Japanese Partner",
			"email" => "JpPartner@email.com" 
		);
		
		return $partner;
	}

}