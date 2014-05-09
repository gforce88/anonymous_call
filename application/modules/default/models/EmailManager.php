<?php
require_once 'BaseManager.php';

class EmailManager extends BaseManager {

	const SQL_FIND_INVITE_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.inviteEmailSubject, partners.inviteEmailBody, partners.country,
			       inviter.email fromEmail, invitee.email toEmail, invitee.email inviteeName
			  from invites, users inviter, users invitee, partners
			 where invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.partnerInx = partners.inx
			   and invites.inx = :inx";

	const SQL_FIND_ACCEPT_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.acceptEmailSubject, partners.acceptEmailBody, partners.country,
			       inviter.email toEmail, invitee.email fromEmail, inviter.email inviterName
			  from invites, users inviter, users invitee, partners
			 where invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.partnerInx = partners.inx
			   and invites.inx = :inx";

	const SQL_FIND_DECLINE_EMAIL = "
			select invites.inx, invites.partnerInx, invites.inviteToken,
			       partners.name partnerName, partners.emailAddr partnerEmail, partners.declineEmailSubject, partners.declineEmailBody, partners.country,
			       inviter.email toEmail, invitee.email fromEmail, inviter.email inviterName
			  from invites, users inviter, users invitee, partners
			 where invites.inviterInx = inviter.inx
			   and invites.inviteeInx = invitee.inx
			   and invites.partnerInx = partners.inx
			   and invites.inx = :inx";

	public function findInviteEmail($inx) {
		return $this->db->fetchRow(self::SQL_FIND_INVITE_EMAIL, array (
			"inx" => $inx 
		));
	}

	public function findAcceptEmail($inx) {
		return $this->db->fetchRow(self::SQL_FIND_ACCEPT_EMAIL, array (
			"inx" => $inx 
		));
	}

	public function findDeclineEmail($inx) {
		return $this->db->fetchRow(self::SQL_FIND_DECLINE_EMAIL, array (
			"inx" => $inx 
		));
	}

}
