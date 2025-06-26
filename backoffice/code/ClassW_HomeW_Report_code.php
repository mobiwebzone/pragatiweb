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
        case "getTeacher":getTeacher($conn);break;
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
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		if($txtFromDT == ''){throw new Exception('Select "FROM" date first.');}
		if($txtToDT == ''){throw new Exception('Select "TO" date first.');}

		$query = "SELECT CONVERT(VARCHAR,HWDATE,20)HWDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=CH.PLANID)PLANNAME,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=CH.TEACHERID AND ISDELETED=0)TEACHER,
		(SELECT USERROLE FROM USERS WHERE UID=CH.TEACHERID AND ISDELETED=0)USERROLE,
		CLASSWORK,HOMEWORK
		FROM CLASSWORKS_HOMEWORKS CH WHERE ISDELETED=0 AND 
		CONVERT(DATE,HWDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";

		if($ddlPlan > 0){
			$query .=" AND PLANID=$ddlPlan";
		}
		if($ddlTeacher > 0){
			$query .=" AND TEACHERID=$ddlTeacher";
		}else{
			$query .=" TEACHERID IN (SELECT [UID] FROM USERS WHERE ISDELETED=0 AND (USERROLE='TEACHER' || USERROLE='VOLUNTEER') AND LOCID=$ddlLocation)";
		}
		$query .=" ORDER BY PLANID,TEACHERID";

		$data['$query ']=$query;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$date = date_create($row['HWDATE']);
			$row['HWDATE'] = date_format($date,"d M Y");
			$row['HWDATE_ORDERBY'] = date_format($date,"ymd");
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


/*============ Get Teacher by Plan =============*/ 
 function getTeacher($mysqli){
	try
	{
		$data = array();
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		if($ddlPlan == 0){throw new Exception('Select "Plan" first.');}

		$query = "SELECT TPLID,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TP.TEACHERID)TEACHER
		FROM TEACHER_PLAN TP WHERE PLANID=$ddlPlan AND ISDELETED=0 AND 
		TEACHERID IN (SELECT [UID] FROM USERS WHERE (USERROLE='TEACHER' OR USERROLE='VOLUNTEER') AND LOCID=$ddlLocation)
		ORDER BY (SELECT FIRSTNAME FROM USERS WHERE UID=TP.TEACHERID)";


		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}

		$data['$query ']=$query;
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







