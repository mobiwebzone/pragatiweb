<?php 
include("conn_main.php");

// define('DB_HOST', '142.132.174.104');
// define('DB_NAME', 'MYEXAMPREP');
// define('DB_USERNAME','mep');
// define('DB_PASSWORD','m@e#p@20#22');
// define('DB_HOST', 'THOR\THOR');
// define('DB_NAME', 'MYEXAMPREP');
// define('DB_USERNAME','sa');
// define('DB_PASSWORD','sasa');




$connection = array ("Database"=>DB_NAME, "UID"=>DB_USERNAME, "PWD"=>DB_PASSWORD, "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect(DB_HOST,$connection);
// if( $conn ) {
// 	echo "Connection established.<br />";
// }else{
// 	echo "Connection could not be established.<br />";
// 	die( print_r( sqlsrv_errors(), true));
// }

// if($connection)

function unique($sql){
	global  $conn;
	$sql = $sql;
	$prm= array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$stmt = sqlsrv_query( $conn, $sql, $prm, $options );
	$row_count = sqlsrv_num_rows( $stmt );
	return $row_count;
}

?>
          
 






