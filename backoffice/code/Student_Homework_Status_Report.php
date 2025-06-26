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
        case "getReport":getReport($conn);break;
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



/*============ Get Report =============*/ 
 function getReport($mysqli){
	try
	{
		$data = array();
        $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        // $ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "EXEC [RPT_HOMEWORK_STATUS]$ddlLocation,$ddlPlan,'$txtFromDT','$txtToDT'";
		$data['$query ']=$query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result)) {
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

		$query = "SELECT DISTINCT REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENT 
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







