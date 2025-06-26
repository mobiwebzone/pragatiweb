<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if(!empty($_SESSION['ROLE'])){
	$ROLE = $_SESSION['ROLE'];
	$LOC = $_SESSION['LOCATION'];}
else{$ROLE = '';
	$LOC = '';}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getStudentReport":getStudentReport($conn);break;
        case "getTeacher":getTeacher($conn);break;
        case "getPlans":getPlans($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Student Report =============*/ 
function getStudentReport($mysqli){
	try
	{
		global $userid;
		$data = array();
		
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

		if($txtFromDT == ''){throw new Exception('Select "FROM" date first.');}
		if($txtToDT == ''){throw new Exception('Select "TO" date first.');}

		$query = "SELECT CONVERT(VARCHAR,HWDATE,106)HWDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=CH.PLANID)PLANNAME,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=CH.TEACHERID AND ISDELETED=0)TEACHER,
		CLASSWORK,HOMEWORK
		FROM CLASSWORKS_HOMEWORKS CH WHERE ISDELETED=0 AND 
		TEACHERID IN (SELECT UID FROM USERS WHERE ISDELETED=0) AND 
		CONVERT(DATE,HWDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT' AND TEACHERID=$userid";

		if($ddlPlan > 0){
			$query .=" AND PLANID=$ddlPlan";
		}
		$query .=" ORDER BY PLANID,TEACHERID";

		$data['$query ']=$query;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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


/*============ Get Plans =============*/ 
function getPlans($mysqli){
	try
	{
		global $userid;
		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0 AND
		PLANID IN (SELECT PLANID FROM TEACHER_PLAN WHERE TEACHERID=$userid AND ISDELETED=0)";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PLANID'] = (int) $row['PLANID'];
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







