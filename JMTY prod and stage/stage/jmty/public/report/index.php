<?php

##########################################################################
$password = "jmty@6min";  // Modify Password to suit for access, Max 10 Char.
##########################################################################
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>JMTY Report</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-style-Type" content="text/html; charset=UTF-8">
  
<link rel="stylesheet" type="text/css" href="../pc/css/style.css">

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.6/css/jquery.dataTables.css">
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">

var _0xf7e8=["\x68\x74\x74\x70\x3A\x2F\x2F\x63\x64\x6E\x2E\x64\x61\x74\x61\x74\x61\x62\x6C\x65\x73\x2E\x6E\x65\x74\x2F\x70\x6C\x75\x67\x2D\x69\x6E\x73\x2F\x39\x64\x63\x62\x65\x63\x64\x34\x32\x61\x64\x2F\x69\x31\x38\x6E\x2F\x4A\x61\x70\x61\x6E\x65\x73\x65\x2E\x6A\x73\x6F\x6E","\x64\x62\x70\x72\x6F\x63\x2E\x70\x68\x70","\x64\x61\x74\x61\x54\x61\x62\x6C\x65","\x23\x72\x65\x70\x6F\x72\x74","\x72\x65\x61\x64\x79","\x65\x72\x72\x4D\x6F\x64\x65","\x65\x78\x74","\x66\x6E","\x6E\x6F\x6E\x65","\x65\x72\x72\x6F\x72\x2E\x64\x74","\x41\x6E\x20\x65\x72\x72\x6F\x72\x20\x68\x61\x73\x20\x62\x65\x65\x6E\x20\x72\x65\x70\x6F\x72\x74\x65\x64\x20\x62\x79\x20\x44\x61\x74\x61\x54\x61\x62\x6C\x65\x73\x3A\x20","\x6C\x6F\x67","\x6F\x6E"];$(document)[_0xf7e8[4]](function(){$(_0xf7e8[3])[_0xf7e8[2]]({"\x6C\x61\x6E\x67\x75\x61\x67\x65":{"\x75\x72\x6C":_0xf7e8[0]},"\x70\x72\x6F\x63\x65\x73\x73\x69\x6E\x67":true,"\x73\x65\x72\x76\x65\x72\x53\x69\x64\x65":true,"\x61\x6A\x61\x78":_0xf7e8[1],"\x62\x46\x69\x6C\x74\x65\x72":false,"\x70\x61\x67\x69\x6E\x67":false})});$[_0xf7e8[7]][_0xf7e8[2]][_0xf7e8[6]][_0xf7e8[5]]=_0xf7e8[8];$(_0xf7e8[3])[_0xf7e8[12]](_0xf7e8[9],function(_0xa645x1,_0xa645x2,_0xa645x3,_0xa645x4){console[_0xf7e8[11]](_0xf7e8[10],_0xa645x4)}).DataTable();

</script>


</head>
<body>
<?php 
  print "<br><br><br><h1 align=\"center\">JMTY Admin Portal</h1><br><br>";
// If password is valid let the user get access
if (isset($_POST["password"]) && ($_POST["password"]=="$password")) {
?>

<div class="container">
  <div id="header"></div>
  <div class="container_inner" style="width:1000px; background-image:none !important;">
    <div class="clearfix c_both" id="logo">
      <div class="header_logo"><img alt="ジモティー" height="33" src="../pc/images/jmty_thin.png" style="float:left" width="120">
        <br class="c_both">
      </div>
      <br class="c_both">
    </div>

    <div class="page_content">
      <div class="section">
        <div class="section_title">
          <div class="section_title_inner" align="center">
            <h2><span class="p">Call Records</span></h2>
          </div>
        </div>
        
        <div class="section_content">
          <!--  Report  -->
          <table id="report" class="display" align="center" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Phone #</th>
					<th>Email</th>
					<th># calls to User</th>
					<th>User Answer Time</th>
					<th>Therapist Answer Time</th>
					<th>Session Call End Time</th>
					<th>Actual Call duration (sec)</th>
					<th>Charge Call duration (mins)</th>
					<th>Call charge</th>
					<th>Result</th>
				</tr>
			</thead>
	
			<tfoot>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Phone #</th>
					<th>Email</th>
					<th># calls to User</th>
					<th>User Answer Time</th>
					<th>Therapist Answer Time</th>
					<th>Session Call End Time</th>
					<th>Actual Call duration (sec)</th>
					<th>Charge Call duration (mins)</th>
					<th>Call charge</th>
					<th>Result</th>
				</tr>
			</tfoot>
		</table>
          
          
          </div>
        </div>
        <br class="clear">

      </div>
    </div>
    <div id="footer">
      
    </div>
  </div>

<?php 
}
else
{
// Wrong password or no password entered display this message
if (isset($_POST['password']) || $password == "") {
  print "<p align=\"center\"><font color=\"red\"><b>Incorrect Password</b><br></font></p><br>";}
  print "<form method=\"post\"><p align=\"center\">Please enter your password for access<br>";
  print "<br><br>";
  print "<input name=\"password\" type=\"password\" size=\"25\" maxlength=\"10\"><input value=\"Login\" type=\"submit\"></p></form>";
}
 
?>
<BR>
</body>
</html>
