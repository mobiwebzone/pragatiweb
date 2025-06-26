<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "Save":Save($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$UID = ($_POST['UID'] == 'undefined' || $_POST['UID'] == '') ? 0 : $_POST['UID'];
		$txtReason=$_POST['txtReason'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtReason']);
    
	

		if($UID == 0)throw new Exception("Invalid UID.");
		if($txtReason == '')throw new Exception("Please enter your reason.");

		$query = "INSERT INTO ACCOUNT_DELETE_REQUEST (USERTYPE,USERID,REASON)
					VALUES ('STUDENT',$UID,'$txtReason')";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['message'] = 'Request Failed.';
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Request successfully submitted.';

		}
		echo json_encode($data);exit;		
	}
     catch(Exception $e)
     {
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
     }
 }




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







