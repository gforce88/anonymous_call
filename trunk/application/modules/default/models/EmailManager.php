<?php
require_once 'BaseManager.php';

class EmailManager extends BaseManager {

	const SQL_FIND_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.@emailType@EmailSubject, partners.@emailType@EmailBody, partners.country,
			       inviter.email fromEmail, invitee.email toEmail, invitee.email inviteeName
			  from invites, users inviter, users invitee, partners
			 where invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.partnerInx = partners.inx
			   and invites.inx = :inx";

	public function findInviteEmail($inx) {
		return $this->findEmail($inx, "invite");
	}

	public function findAcceptEmail($inx) {
		return $this->findEmail($inx, "accept");
	}

	public function findDeclineEmail($inx) {
		return $this->findEmail($inx, "decline");
	}

	public function findSorryEmail($inx) {
		return $this->findEmail($inx, "sorry");
	}

	public function findRetryEmail($inx) {
		return $this->findEmail($inx, "retry");
	}

	public function findThanksEmail($inx) {
		return $this->findEmail($inx, "thanks");
	}

	private function findEmail($inx, $type) {
		$sql = str_replace("@emailType@", $type, self::SQL_FIND_EMAIL);
		return $this->db->fetchRow($sql, array (
			"inx" => $inx 
		));
	}

}
