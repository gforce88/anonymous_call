<?php

##########################################################################
$password = "t";  // Modify Password to suit for access, Max 10 Char.
##########################################################################
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>JMTY App Control Panel</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-style-Type" content="text/html; charset=UTF-8">
  
<link rel="stylesheet" type="text/css" href="../pc/css/style.css">

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.6/css/jquery.dataTables.css">



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
      
      <br class="c_both">
    </div>

	<a href="enableService.php">Disable service</a> <br>
	<a href="enableService.php">Enable service</a>
	
	
    
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
