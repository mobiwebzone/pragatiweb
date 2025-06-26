<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getPolicy":getPolicy($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ GET POLICY =============*/ 
function getPolicy($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PID,POLICY FROM POLICY_MASTER WHERE POLICY_TYPE='COOKIE'";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
			$data['data'] = $row;
			$data['success'] = true;
		}else{
			$data['message'] = 'Policy not found.';
			$data['success'] = false;
		}
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET POLICY =============*/ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







