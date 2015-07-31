<?php
 
/* error_reporting(E_ALL);
ini_set('display_errors', '1'); */

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
//$table = 'calls';
$table = 'testview';

// Table's primary key
$primaryKey = 'inx';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'firstName', 'dt' => 0 ),
    array( 'db' => 'lastName',  'dt' => 1 ),
	array( 'db' => 'patientNumber', 'dt' => 2 ),
    array( 'db' => 'patientEmail', 'dt' => 3 ),
	array( 'db' => 'trytimes', 'dt' => 4 ),
	array( 'db' => 'patientCallTime', 'dt' => 5 ),
	array( 'db' => 'specialistCallTime', 'dt' => 6 ),
	array( 'db' => 'grpCallEndTime', 'dt' => 7 ),
	array( 'db' => 'actualCallDuration', 'dt' => 8 ),
	array( 'db' => 'chargeCallDuration', 'dt' => 9 ),
	array( 'db' => 'callCharge', 'dt' => 10 ),
	array( 'db' => 'result', 'dt' => 11 )
    
);
 
// SQL server connection information
$sql_details = array(
    'user' => 'root',
	// local DB 'pass' => 'orl0Thaynmy',
	// Prod
    'pass' => ')orl0Thaynmy',
    'db'   => 'jmty',
    'host' => 'localhost'
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
//require( 'ssp.class.php' );
require( 'ssp.php' );


echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);