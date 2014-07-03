/*
source /root/workspace/dist/doc/Itr-01/ready_2.sql;
 */
update partners set
`readyEmailSubject` = '[username]さんから今にも電話が来ます，携帯電話を準備してください',
`readyEmailContent` = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
<title>AVC Email Template</title>
<style type="text/css"> 
html
{
	width: 100%;
}

body { 
   background-color: #f8f8f8; 
   margin: 0; 
   padding: 0; 
}

.ReadMsgBody
{
	width: 100%;
	background-color: #f8f8f8;
}
.ExternalClass
{
	width: 100%;
	background-color: #f8f8f8;
}

a { 
    color:#CC0000; 
	text-decoration:none;
	font-weight:normal;
} 
a:hover { 
    color:#818181; 
	text-decoration:underline;
	font-weight:normal;
}

a.top-link {
	color:#CC0000; 
	text-decoration:none;
	font-weight:normal;
}

a.top-link:hover {
	color:#888888; 
	text-decoration:underline;
	font-weight:normal;
}


p, div {
	margin: 0;
}
table {
	border-collapse: collapse;
}

@media only screen and (max-width: 640px)  {
	/*** below is style for body */
	body{width:auto !important;}
	
	/*** below is style for full width table */
	table table{width:100%!important; }
	
	/*** below is style for 660px table area */
	table[class="table-660"] {width: 460px !important; }
	
	/*** below is style for 660px image area */
	img[class="img-660"] {width: 460px !important;  line-height: 0 !important;}
	img[class="img-radius"] {width: 460px !important; height: 10px !important; line-height: 0 !important;}
	
	/*** below is style for 1/2 column area */
	td[class="one-half-first"] {width: 400px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-last"] {width: 400px !important; display: block !important; text-align: center !important; padding-left: 30px !important;}
	
	/*** below is style for 1/2 column with icon area */
	td[class="one-half-icon-first"] {width: 400px !important; display: block !important; text-align: left !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-icon-last"] {width: 400px !important; display: block !important; text-align: left !important; padding-left: 30px !important;}
	
	/*** below is style for 1/3 column area */
	td[class="one-third"] {width: 400px !important;  display: block !important; text-align: center !important; padding-bottom: 10px !important; padding-right: 30px !important;}
	td[class="one-third-middle"] {width: 400px !important; display: block !important; text-align: center !important; padding-bottom: 10px !important; padding-left: 30px !important; padding-right: 30px !important;}
	td[class="one-third-last"] {width: 400px !important; display: block !important; text-align: center; padding-left: 30px !important; }
	img[class="pro-img-180"] {width: 400px !important;}
	
	
	/*** below is style for 3/4 and 1/4 column area */
	td[class="one-fourth-right"] {width: 150px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-left"] {width: 250px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-left-last"] {width: 250px !important; text-align: left !important; padding-bottom: 40px !important;}
	td[class="one-fourth-right-last"] {width: 150px !important;  text-align: left !important; padding-bottom: 40px !important;}
	img[class="img-180"] {width: 150px !important;}
	
	/*** below is style for 1/4 and 3/4 column area */
	td[class="one-fourth-left"] {width: 150px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-right"] {width: 250px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-right-last"] {width: 250px !important; text-align: left !important; padding-bottom: 40px !important;}
	td[class="one-fourth-left-last"] {width: 150px !important;  text-align: left !important; padding-bottom: 40px !important;}
	img[class="img-180"] {width: 150px !important;}
	
	/*** below is style for 285px image area */
	img[class="img-285"] {width: 400px !important; text-align: center !important;}
	
	/*** below is style for left content and sidebar area */
	table[class="left-content"] {width: 220px !important; text-align: left !important; }
	td[class="left-content"] {width: 220px !important; text-align: left !important; }
	img[class="img-blog"] {width: 220px !important;}
	img[class="divider-350"] {width: 220px !important; height:1px !important}
	
	table[class="right-sidebar"] {width: 150px !important;  text-align: left !important; }
	td[class="right-sidebar"] {width: 150px !important; text-align: left !important; }
	img[class="img-gallery"] {width: 110px !important;}
	img[class="divider-220"] {width: 150px !important; height:2px !important}
	
	/*** below is style for left sidebar and right content area */
	table[class="right-content"] {width: 220px !important; text-align: left !important; }
	td[class="right-content"] {width: 220px !important; text-align: left !important; }
	img[class="img-blog"] {width: 220px !important;}
	img[class="divider-350"] {width: 220px !important; height:1px !important}
	
	table[class="left-sidebar"] {width: 150px !important; text-align: left !important; }
	td[class="left-sidebar"] {width: 150px !important; text-align: left !important; }
	img[class="img-gallery"] {width: 110px !important;}
	img[class="divider-220"] {width: 150px !important; height:2px !important}
}

@media only screen and (max-width: 568px)  {
	/*** below is style for body */
	body{width:auto !important;}
	
	/*** below is style for full width table */
	table table{width:100%!important; }
	
	/*** below is style for 660px table area */
	table[class="table-660"] {width: 460px !important; }
	
	/*** below is style for 660px image area */
	img[class="img-660"] {width: 460px !important;  line-height: 0 !important;}
	img[class="img-radius"] {width: 460px !important; height: 10px !important; line-height: 0 !important;}
	
	/*** below is style for 1/2 column area */
	td[class="one-half-first"] {width: 400px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-last"] {width: 400px !important; display: block !important; text-align: center !important; padding-left: 30px !important;}
	
	/*** below is style for 1/2 column with icon area */
	td[class="one-half-icon-first"] {width: 400px !important; display: block !important; text-align: left !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-icon-last"] {width: 400px !important; display: block !important; text-align: left !important; padding-left: 30px !important;}
	
	/*** below is style for 1/3 column area */
	td[class="one-third"] {width: 400px !important;  display: block !important; text-align: center !important; padding-bottom: 10px !important; padding-right: 30px !important;}
	td[class="one-third-middle"] {width: 400px !important; display: block !important; text-align: center !important; padding-bottom: 10px !important; padding-left: 30px !important; padding-right: 30px !important;}
	td[class="one-third-last"] {width: 400px !important; display: block !important; text-align: center; padding-left: 30px !important; }
	img[class="pro-img-180"] {width: 400px !important;}
	
	
	/*** below is style for 3/4 and 3/4 column area */
	td[class="one-fourth-right"] {width: 150px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-left"] {width: 250px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-left-last"] {width: 250px !important; text-align: left !important; padding-bottom: 40px !important;}
	td[class="one-fourth-right-last"] {width: 150px !important;  text-align: left !important; padding-bottom: 40px !important;}
	img[class="img-180"] {width: 150px !important;}
	
	/*** below is style for 1/4 and 3/4 column area */
	td[class="one-fourth-left"] {width: 150px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-right"] {width: 250px !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-right-last"] {width: 250px !important; text-align: left !important; padding-bottom: 40px !important;}
	td[class="one-fourth-left-last"] {width: 150px !important;  text-align: left !important; padding-bottom: 40px !important;}
	img[class="img-180"] {width: 150px !important;}
	
	/*** below is style for 285px image area */
	img[class="img-285"] {width: 400px !important; text-align: center !important;}
	
	/*** below is style for left content and sidebar area */
	table[class="left-content"] {width: 220px !important; text-align: left !important; }
	td[class="left-content"] {width: 220px !important; text-align: left !important; }
	img[class="img-blog"] {width: 220px !important;}
	img[class="divider-350"] {width: 220px !important; height:1px !important}
	
	table[class="right-sidebar"] {width: 150px !important;  text-align: left !important; }
	td[class="right-sidebar"] {width: 150px !important; text-align: left !important; }
	img[class="img-gallery"] {width: 110px !important;}
	img[class="divider-220"] {width: 150px !important; height:2px !important}
	
	/*** below is style for left sidebar and right content area */
	table[class="right-content"] {width: 220px !important; text-align: left !important; }
	td[class="right-content"] {width: 220px !important; text-align: left !important; }
	img[class="img-blog"] {width: 220px !important;}
	img[class="divider-350"] {width: 220px !important; height:1px !important}
	
	table[class="left-sidebar"] {width: 150px !important; text-align: left !important; }
	td[class="left-sidebar"] {width: 150px !important; text-align: left !important; }
	img[class="img-gallery"] {width: 110px !important;}
	img[class="divider-220"] {width: 150px !important; height:2px !important}
}

@media only screen and (max-width: 479px)  {
	/*** below is style for body */
	body{width:auto !important;}
	
	/*** below is style for full width table */
	table table{width:100%!important; }
	
	/*** below is style for 660px table area */
	table[class="table-660"] {width: 300px !important; }
	
	/*** below is style for logo area */
	td[class="logo"] {width: 240px !important; display: block !important; padding-bottom: 0px !important;}
	td[class="social"] {width: 240px !important; display: block !important; }
	
	/*** below is style for 660px image area */
	img[class="img-660"] {width: 300px !important;  line-height: 0 !important;}
	img[class="img-radius"] {width: 300px !important; height: 10px !important; line-height: 0 !important;}
	
	/*** below is style for 1/2 column area */
	td[class="one-half-first"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-last"] {width: 240px !important; display: block !important; text-align: center !important; padding-left: 30px !important;}
	
	/*** below is style for 1/2 column with icon area */
	td[class="one-half-icon-first"] {width: 240px !important; display: block !important; text-align: left !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-half-icon-last"] {width: 240px !important; display: block !important; text-align: left !important; padding-left: 30px !important;}
	
	/*** below is style for 1/3 column area */
	td[class="one-third"] {width: 240px !important; display: block !important; text-align: center; padding-bottom: 10px !important; padding-right: 30px !important;}
	td[class="one-third-middle"] {width: 240px !important; display: block !important; text-align: center; padding-bottom: 10px !important; padding-left: 30px !important; padding-right: 30px !important;}
	td[class="one-third-last"] {width: 240px !important; display: block !important; text-align: center; padding-left: 30px !important;}
	img[class="pro-img-180"] {width: 240px !important;}
	
	/*** below is style for 285px image area */
	img[class="img-285"] {width: 240px !important; text-align: center !important;}
	
	/*** below is style for 3/4 and 1/4 column area */
	td[class="three-fourth-left"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-fourth-right"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-left: 30px !important;}
	td[class="three-fourth-left-last"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important; padding-right: 30px !important;}
	td[class="one-fourth-right-last"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 40px !important; padding-left: 30px !important;}
	img[class="img-180"] {width: 240px !important; text-align: center !important;}
	
	
	/*** below is style for 1/4 and 3/4 column area */
	td[class="one-fourth-left"] {width: 240px !important; display: block !important; text-align: left !important; padding-bottom: 0px !important;}
	td[class="three-fourth-right"] {width: 240px !important; display: block !important; text-align: left !important; padding-bottom: 0px !important; padding-left: 30px !important;}
	td[class="three-fourth-right-last"] {width: 240px !important; display: block !important; text-align: left !important; padding-bottom: 40px !important; padding-left: 30px !important;}
	td[class="one-fourth-left-last"] {width: 240px !important; display: block !important;  text-align: left !important; padding-bottom: 40px !important;}
	img[class="img-180"] {width: 240px !important;display: block !important; }
	
	
	/*** below is style for left content and sidebar area */
	table[class="left-content"] {width: 240px !important; margin: 0;  text-align: left !important; padding-bottom: 40px !important;  display: block !important;}
	td[class="left-content"] {width: 240px !important; margin: 0; text-align: left !important; display: block !important; }
	img[class="img-blog"] {width: 240px !important; display: block !important;}
	img[class="divider-350"] {width: 240px !important; height:1px !important display: block !important;}
	
	table[class="right-sidebar"] {width: 240px !important; text-align: center !important;  display: block !important; }
	table[class="right-sidebar-last"] {width: 240px !important; text-align: center !important; margin-bottom: 40px !important; display: block !important; }
	td[class="right-sidebar"] {width: 240px !important; text-align: left !important; display: block !important; }
	img[class="img-gallery"] {width: 200px !important; display: block !important;}
	img[class="divider-220"] {width: 240px !important; height:2px !important display: block !important;}
	
	/*** below is style for left sidebar and right content area */
	table[class="right-content"] {width: 240px !important; margin: 0 !important;  text-align: right !important; padding-left: 30px !important; padding-bottom: 40px !important; display: block !important;}
	td[class="right-content"] {width: 240px !important; margin: 0 !important; text-align: left !important; display: block !important; }
	img[class="img-blog"] {width: 240px !important; display: block !important;}
	img[class="divider-350"] {width: 240px !important; height:1px !important display: block !important;}
	
	table[class="left-sidebar"] {width: 240px !important; text-align: center !important;  display: block !important; }
	table[class="left-sidebar-last"] {width: 240px !important;  text-align: center !important; margin-bottom: 40px !important; display: block !important; }
	td[class="left-sidebar"] {width: 240px !important; text-align: left !important; display: block !important; }
	img[class="img-gallery"] {width: 200px !important; display: block !important;}
	img[class="divider-220"] {width: 240px !important; height:2px !important display: block !important;}
	
	
	/*** below is style for footer area */
	td[class="footer-left"] {width: 240px !important; display: block !important; text-align: center !important; padding-bottom: 0px !important;}
	td[class="footer-right"] {width: 240px !important; display: block !important; text-align: center !important; }
}

</style>
</head>
<body bgcolor="#f8f8f8">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#f8f8f8">
	<tr>
		<td align="center">
		<!-- START OF TOP LINKS BLOCK-->
		<table class="table-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#f8f8f8" style="padding:0; margin: 0; ">
			<tr>
				<td bgcolor="#f8f8f8" align="left" style="padding: 30px 0px 10px 0px; font-size:12px ; font-family: Helvetica, Arial, sans-serif; line-height: 22px; font-style: italic;">
					
			  </td>
			</tr>
		</table>
		<!-- END OF TOP LINKS BLOCK-->
		
		
		
		<!-- START OF SUBJECT LINE AREA BLOCK-->
			<table class="table-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#CC0000" style="padding:0; margin: 0; ">
				<tr>
					<td width="660" bgcolor="#CC0000" align="center" valign="top" style="padding: 30px; font-size:32px ; font-family: Helvetica, Arial, sans-serif; line-height: 42px; color:#ffffff; text-transform: uppercase;">
						<span>
							携帯電話を準備してください！<br />
					  </span>
					</td>
				</tr>
			</table>
		<!-- END OF SUBJECT LINE AREA BLOCK-->
		
		
		<!-- START OF INTRO TEXT AREA BLOCK-->
	  <table class="table-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="padding:0; margin: 0; ">
				<tr>
					<td width="660" bgcolor="#ffffff" align="center" style="padding: 40px 30px; font-size:18px; font-family: Helvetica, Arial, sans-serif; line-height: 26px; color:#000;">
					  <p>まもなく</p>
						<span align="center" valign="top" style="padding: 30px; font-size:28px ; font-family: Helvetica, Arial, sans-serif; line-height: 42px; color:#CC0000; text-transform: uppercase;">[username]</span>さんとの
				  <p>電話が始まります</p></td>
				</tr>
			</table>
		<!-- END OF INTRO TEXT BLOCK-->
        
        <!-- START OF LOGO AND SOCIAL ICONS BLOCK-->
			<table class="table-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="padding:0; margin: 0; ">
				<tr>
				
					<!-- START OF LOGO HERE-->
					<td class="logo" colspan="2" width="330" bgcolor="#ffffff" align="center" valign="top" style="padding: 30px; font-size:12px ; font-family: Helvetica, Arial, sans-serif; line-height: 22px; font-style: italic;">
						<span>
							<a href="#" style="color:#ffffff;">
							<img src="[imgurl]/images/Phones_AVC.png" alt="AVC logo" />
							</a>
						</span>
					</td>
					<!-- END OF LOGO HERE-->

			  </tr>
			</table>
		<!-- END OF LOGO AND SOCIAL ICONS BLOCK-->
	
		<!-- START OF FOOTER AREA BLOCK-->
			<table class="table-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ececec" style="padding:0; margin: 0; ">
				<tr>					
				</tr>
			</table>
		<!-- END OF FOOTER AREA BLOCK-->
		
		<!-- START OF RADIUS IMAGE AREA BLOCK-->
			<table class="tablbe-660" width="660" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#f8f8f8" style="padding:0; margin: 0; ">
				<tr>
					<td width="660" bgcolor="#f8f8f8" align="center" style="padding-bottom: 30px; line-height: 0 !important;">
						<span>
							<img class="img-radius" src="[imgurl]/images/image-radius.jpg" alt="image radius" border="0" />
						</span>
					</td>
				</tr>
			</table>
		<!-- END OF RADIUS IMAGE AREA BLOCK-->
		</td>
	</tr>
</table>
</body>
</html>
' where inx = 2;
