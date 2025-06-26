<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getStudentDisReq":getStudentDisReq($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Student Discontinue Report =============*/ 
 function getStudentDisReq($mysqli){
	try
	{
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		if($txtFromDT == '' || $txtToDT == ''){
			throw new Exception('Select date first.');	
		}

		$query = "SELECT REGDID,CONVERT(VARCHAR,DISCONTINUE_REQ_DATE,20)DISCONTINUE_REQ_DATE, 
		REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)STUDENTNAME,
		PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)PLANNAME
		FROM REGISTRATION_DETAILS RD WHERE CANCELLED=0 AND 
		REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND
		CONVERT(DATE,DISCONTINUE_REQ_DATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		ORDER BY (SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$date = date_create($row['DISCONTINUE_REQ_DATE']);
			$row['DISCONTINUE_REQ_DATE'] = date_format($date,"d M Y");
			$row['DISCONTINUE_REQ_DATE_ORDERBY'] = date_format($date,"ymd");
			$data['data'][] = $row;
		}
		$data['success'] = true;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
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







