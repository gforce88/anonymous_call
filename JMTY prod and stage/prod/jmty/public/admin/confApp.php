<?php
/*
error_reporting ( E_ALL );
ini_set ( 'display_errors', '1' );
*/

require_once "strings.php";

if (isset ( $_POST ["but"] )) {
	
	//echo 'got post: ' . $_POST ["but"].'<br>';
	$param = $_POST["but"];
	$pcPath = '../../application/views/scripts/pc/';
	$spPath = '../../application/views/scripts/sp/';
	$stepOneFile = 'stepone.phtml';
	$stepOneSave = 'stepone.phtml-EXE';
	$closeWMsgFile = 'closed.phtml';
	$closeNOMsgFile - 'closed.phtml';
	
	if (! strcmp ( $param, $disableWithMsg )) {
		
		$fromURL = $pcPath.$closeWMsgFile;
		$toPath = $pcPath.$stepOneFile;
		$cmd = $fromURL.' '.$toPath;
		
		//$output = shell_exec ( $cmd );
		$output = shell_exec ( 'cp ../../application/views/scripts/pc/pc_closed.phtml ../../application/views/scripts/pc/stepone.phtml' );
		$output = shell_exec ( 'cp ../../application/views/scripts/sp/closed.phtml ../../application/views/scripts/sp/stepone.phtml' );
	} 
	else if (! strcmp ( $param, $disableNoMsg )) {
		$output = shell_exec ( 'cp ../../application/views/scripts/pc/pc_maintenance.phtml ../../application/views/scripts/pc/stepone.phtml' );
		$output = shell_exec ( 'cp ../../application/views/scripts/sp/maintenance.phtml ../../application/views/scripts/sp/stepone.phtml' );
	} 
	else if (! strcmp ( $param, $enableMsg )) {
		$output = shell_exec ( 'cp ../../application/views/scripts/pc/stepone.phtml-EXE ../../application/views/scripts/pc/stepone.phtml' );
		$output = shell_exec ( 'cp ../../application/views/scripts/sp/stepone.phtml-EXE ../../application/views/scripts/sp/stepone.phtml' );
	} 
	else {
		//echo 'Invalid param<br>';
	}
}
else {
	//echo 'No Post<br>';
}
$stageURL = 'http://165.225.149.30/jmty/public/pc/stepone';
$prodURL = 'https://www.incognitosys.com/jmty/public/pc/stepone'; 

$hdrParam = 'Location: '.$prodURL;
header($hdrParam);

?>