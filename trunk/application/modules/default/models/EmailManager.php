<?php
require_once 'BaseManager.php';

class EmailManager extends BaseManager {

	const SQL_FIND_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.@emailType@EmailSubject emailSubject, partners.@emailType@EmailBody emailBody, partners.country,
			       inviter.email inviterEmail, inviter.email inviterName, 
			       invitee.email inviteeEmail, invitee.email inviteeName
			  from invites, partners, users inviter, users invitee
			 where invites.partnerInx = partners.inx
			   and invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
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

	public function findReadyEmail($inx) {
		return $this->findEmail($inx, "ready");
	}

	public function findSorryEmail($inx) {
		return $this->findEmail($inx, "sorry");
	}

	public function findRetryEmail($inx) {
		return $this->findEmail($inx, "retry");
	}

	public function findThanksEmail($inx, $inviteType) {
		return $this->findEmail($inx, "thanks");
	}

	private function findEmail($inx, $type) {
		$sql = str_replace("@emailType@", $type, self::SQL_FIND_EMAIL);
		return $this->db->fetchRow($sql, array (
			"inx" => $inx 
		));
	}

}