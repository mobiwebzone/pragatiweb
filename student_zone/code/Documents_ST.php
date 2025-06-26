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
		case "getDocuments":getDocuments($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ GET DOCUMENTS =============*/ 
 function getDocuments($mysqli){
	try
	{
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$docFor=($_POST['docFor'] == 'undefined' || $_POST['docFor'] == '') ? 0 : $_POST['docFor'];
		$userid=($_POST['userid'] == 'undefined' || $_POST['userid'] == '') ? 0 : $_POST['userid'];

		if($ddlLocation == 0) throw new Exception('Invalid LOCID.');
		if($userid == 0) throw new Exception('Invalid USERID.');
		if($docFor == '') throw new Exception('Invalid Doc For.');

		$query = "SELECT STDID,DOCTYPE,DOCDESC,DOCUMENT,CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE
		FROM STUDENT_TEACHER_DOCUMENTS ST
		WHERE ISDELETED=0 AND LOCID=$ddlLocation AND DOCFOR='$docFor' AND DOCFORID=$userid
		ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";
		$data['$query']=$query;
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
/*============ GET PAYMENT TRANSACTION =============*/ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







