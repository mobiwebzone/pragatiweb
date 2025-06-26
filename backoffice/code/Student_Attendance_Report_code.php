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
        case "getStudentReport":getStudentReport($conn);break;
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
		$data = array();
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $txtAttDT  = $_POST['txtAttDT'] == 'undefined' ? '' : $_POST['txtAttDT'];

		$query = "SELECT CONVERT(VARCHAR,ATTDATE,106)ATTDATE,PLANID,TEACHERID,
		(SELECT FIRSTNAME + ' ' + LASTNAME FROM USERS WHERE ISDELETED=0 AND [UID]=SA.TEACHERID)TEACHER,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SA.PLANID)PLANNAME,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SA.REGID)STUDENT,PRESENT
		FROM STUDENT_ATTENDANCE SA WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105) = '$txtAttDT'";

		if($ddlPlan > 0){
			$query .=" AND PLANID=$ddlPlan";
		}
		$query .=" ORDER BY PLANID,TEACHERID,REGID";

		$data['$query ']=$query;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}


		// Get Attendance Summry
		// $querySummry = "SELECT COUNT(PLANID),(SELECT PLANNAME FROM PLANS WHERE PLANID=TA.PLANID)PLANNAME,
		// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TA.TEACHERID)FULLNAME,
		// CONVERT(VARCHAR,DATEADD(ms, SUM(DATEDIFF(ms, '00:00:00.000', convert(datetime,DATEADD(SECOND, - DATEDIFF(SECOND, TIME_OUT, TIME_IN), '00:00:00')))), '00:00:00.000'),8) AS NO_OF_HOURS 
		// FROM TEACHER_ATTENDANCE TA 
		// WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";

		// if($ddlTeacher > 0){
		// 	$querySummry .=" AND TEACHERID=$ddlTeacher";
		// }
		// $querySummry .=" GROUP BY PLANID,TEACHERID 
		// 				ORDER BY TEACHERID,PLANID";



		// $resultSummry = sqlsrv_query($mysqli, $querySummry);
		// while ($rowSummry = sqlsrv_fetch_array($resultSummry)) {

		// 	$rowSummry['NO_OF_HOURS'] = substr($rowSummry['NO_OF_HOURS'], 0, -3);;
		// 	$data['Summry'][] = $rowSummry;
		// }
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
/*============ Get Student Report =============*/




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







