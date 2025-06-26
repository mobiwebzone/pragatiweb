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
        case "getStudentPayment":getStudentPayment($conn);break;
        case "getStudents":getStudents($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get Student Payment =============*/ 
 function getStudentPayment($mysqli){
	try
	{
		$data = array();
        $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT RECID,CONVERT(VARCHAR,RECDATE,21)RECDATE,RECNO,RECNOFULL,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENTNAME,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID)PLANNAME,INSTALLMENT,AMOUNT,PMID,
		ISNULL((SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=SR.PMID),'')PAYMENTMODE,REFNO,REMARK,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.INSERTID)REC_BY 
		FROM STUDENT_RECEIPTS SR
		WHERE ISDELETED=0 AND CONVERT(DATE,RECDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";

		if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
		if($ddlStudent > 0){
			$query .=" AND REGID=$ddlStudent";
		}else{
			$query .=" AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";
		}
		$query .=" ORDER BY RECDATE DESC";

		$data['$query ']=$query;

		$TOTAL_AMOUNT = (float) 0.00;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$date = date_create($row['RECDATE']);
			$row['RECDATE'] = date_format($date,"M-d-Y (h:m A)");
			$row['RECDATE_ORDERBY'] = date_format($date,"mdYhm");
			$row['AMOUNT_ORDERBY'] = (int) $row['AMOUNT'];
			$TOTAL_AMOUNT += (float)$row['AMOUNT'];
			$data['data'][] = $row;
		}
		$data['TOTAL_AMOUNT'] = number_format($TOTAL_AMOUNT,2, '.', '');


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



/*============ Get Student =============*/ 
 function getStudents($mysqli){
	try
	{
		$data = array();
        // $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        // $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT DISTINCT REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENT,
		(SELECT P1_FIRSTNAME+' '+P1_LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)PARENT
		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";

		if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
		$query .=" ORDER BY STUDENT";

		$data['$query ']=$query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Student =============*/




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







