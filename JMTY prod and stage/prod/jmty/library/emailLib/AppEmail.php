<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'genericEmail.php';

class AppEmails {
	private $emailSrvParams = array();
	

	public function __construct($emailHost,$emailPort,$userName,$password) {
		
		$this->emailSrvParams ['host'] = $emailHost;  // specify main and backup server
		$this->emailSrvParams['port'] = $emailPort; // or 587
		$this->emailSrvParams ['userName'] = $userName;
		$this->emailSrvParams ['password'] = $password; 
		
	}

public function sendUserDidNotAnswerEmail($emailAddr) {

$emailSubject = "ジモティー電話相談サービスからのお知らせ";

$mailcontent = '

<p>ジモティー電話相談サービスをご利用いただきありがとうございます。</p>
<br>
<br>
大変申し訳ございませんが、電話をお繋ぎすることができなかったため<br/>
お申込みをキャンセルとさせていただきました。<br/>
こちらのお電話については通話料金は発生いたしません<br>
<br/>

恐れ入りますが、下記ボタンより再度お申込みください。<br>
お電話させていただいた電話番号は、発信専用のため折り返しいただいても繋がりません。<br>

<br/>
PCから<br/>
<a href="http://jmty.jp/about/consultation">電話相談する</a>
<br/>
<br>
スマートフォンから<br/>
<a href="http://jmty.jp/s/about/consultation">電話相談する</a>
<br>
';

genSendEmail ($this->emailSrvParams, $emailAddr, $emailSubject, $mailcontent,APPLICATION_PATH."/configs/emailTemplate.html");
}
	
public function sendTherapistNotAvailEmail($emailAddr) {
	
$emailSubject = "ジモティー電話相談サービスからのお知らせ";

$mailcontent = '

<p>ジモティー電話相談サービスをご利用いただきありがとうございます。</p><br>
<br>
<p>大変申し訳ございませんが、電話が込み合っておりお繋ぎすることができませんでした。<br/>
こちらのお電話については通話料金は発生いたしません。</p><br>
<br>
<p>恐れ入りますが、下記ボタンより再度お申込みください。<br/>
お電話させていただいた電話番号は、発信専用のため折り返しいただいても繋がりません。
</p>

PCから<br/>
<a href="http://jmty.jp/about/consultation">電話相談する</a>
<br/>
<br>
スマートフォンから<br/>
<a href="http://jmty.jp/s/about/consultation">電話相談する</a>
<br>
';

genSendEmail ($this->emailSrvParams, $emailAddr, $emailSubject, $mailcontent,APPLICATION_PATH."/configs/emailTemplate.html");
	
}

public function sendThankYouEmail($emailAddr,$min,$chargeAmt) {
	
$emailSubject = "ジモティー電話相談サービス ご利用のお知らせ";

$mailcontent = '
<p>ジモティー電話相談サービスをご利用いただきありがとうございました。<br>
<br>

今回のご利用状況についてお知らせいたします。
<br>
<br>'.
'ご利用時間: '.$min.' 分<br>'.
'ご利用金額: '.$chargeAmt.' 円（税込み）'.
'<br><br>
またのご利用をお待ちしております。
<br>
<br>
<p>※ 本サービスの支払い先は、弊社パートナーのWolfcomm Studioとなります。<br>
※ お電話させていただいた電話番号は、発信専用のため折り返しいただいても繋がりません</p>
<br><br>
</p>


PCから<br/>
<a href="http://jmty.jp/about/consultation">電話相談する</a>
<br/>
<br>
スマートフォンから<br/>
<a href="http://jmty.jp/s/about/consultation">電話相談する</a>
<br>
';


genSendEmail ($this->emailSrvParams, $emailAddr, $emailSubject, $mailcontent,APPLICATION_PATH."/configs/emailTemplate.html" );
	
}


public function sendCardErrEmail($emailAddr) {
	
$emailSubject = "ジモティー電話相談サービス 決済失敗のお知らせ";

$mailcontent = '
<p>ジモティー電話相談サービスをご利用いただきありがとうございます。
<br>
<br>
たいへん申し訳ございませんが、<br>
入力いただいたクレジットカードの確認時に問題が発生しました。<br>
<br>'.
'下記ボタンより、<br>
別のクレジットカードを用いてのお申込みをお願いいたします。'.
'<br>
<br>
PCから<br/>
<a href="http://jmty.jp/about/consultation">電話相談する</a>
<br/>
<br>
スマートフォンから<br/>
<a href="http://jmty.jp/s/about/consultation">電話相談する</a>
<br>
';


genSendEmail ($this->emailSrvParams, $emailAddr, $emailSubject, $mailcontent,APPLICATION_PATH."/configs/emailTemplate.html" );
	
}

public function sendAdminCardErrEmail($emailAddr,$userEmailAddr,$min,$chargeAmt) {
	
$emailSubject = "[FAILED TO CHARGE CREDIT CARD]";

$mailcontent = '
<br>'.
'User email: '.$userEmailAddr.'<br>'.
'Mins used: '.$min.'<br>'.
'Failed charge amt: '.$chargeAmt.
'<br><br>
';


genSendEmail ($this->emailSrvParams, $emailAddr, $emailSubject, $mailcontent,APPLICATION_PATH."/configs/emailTemplate.html" );
	
}
}