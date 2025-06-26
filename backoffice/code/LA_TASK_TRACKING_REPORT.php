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
        case "Get_RPT_TASK_TRACKING":Get_RPT_TASK_TRACKING($conn);break;
        case "getTaskCategory":getTaskCategory($conn);break;
		case "getAssignedToUser":getAssignedToUser($conn);break;
		case "getPlans":getPlans($conn);break;
		case "getTaskUsers":getTaskUsers($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET REPORT TASK TRACKING =============*/ 
 function Get_RPT_TASK_TRACKING($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlTask_Category = ($_POST['ddlTask_Category'] =='undefined' || $_POST['ddlTask_Category'] =='') ? '' : $_POST['ddlTask_Category'];
		$txtFromDT = ($_POST['txtFromDT'] =='undefined' || $_POST['txtFromDT'] =='') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] =='undefined' || $_POST['txtToDT'] =='') ? '' : $_POST['txtToDT'];
		$ddlAssignedToID = ($_POST['ddlAssignedToID'] =='undefined' || $_POST['ddlAssignedToID'] =='') ? '' : $_POST['ddlAssignedToID'];
		$txtStatus = ($_POST['txtStatus'] =='undefined' || $_POST['txtStatus'] =='') ? '' : $_POST['txtStatus'];

		$query = "EXEC[RPT_TASK_TRACKING]$ddlLocation,'$ddlTask_Category','$txtFromDT','$txtToDT','$ddlAssignedToID','$txtStatus'";
		$data['query'] = $query;
		// echo json_encode($data);
		// exit;
		$result = sqlsrv_query($mysqli, $query);
	
		if(sqlsrv_has_rows($result) !== false){
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
/*============ GET REPORT TASK TRACKING =============*/ 


/*============ GET TASK USERS =============*/ 
 function getTaskUsers($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT  TASKMGMTID,LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=SR.LOCID)LOCATION,
		TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=SR.TASKCATID)TASKCAT,
		ASSIGNEDTO,
		CASE WHEN ASSIGNEDTO='STUDENTS' 
			THEN (SELECT TOP(1) PLANID FROM STUDENT_RECEIPTS WHERE REGID=SR.ASSIGNEDTO_ID AND PLANID>0 AND ISDELETED=0) 
			ELSE 0
		END PLANID
		,ASSIGNEDTO_ID,
		CASE WHEN ASSIGNEDTO='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.ASSIGNEDTO_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.ASSIGNEDTO_ID) 
		END ASSIGNEDTO_NAME,
		
		ASSIGNEDTO_R1,
		CASE WHEN ASSIGNEDTO_R1='STUDENTS' 
		THEN (SELECT TOP(1) PLANID FROM STUDENT_RECEIPTS WHERE REGID=SR.REVIEW1_ID AND PLANID>0 AND ISDELETED=0) 
		ELSE 0
		END REVIEW1_PLANID,REVIEW1_ID,
		CASE WHEN ASSIGNEDTO_R1='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REVIEW1_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.REVIEW1_ID) 
		END REVIEW_NAME1,
		
		ASSIGNEDTO_R2,
		CASE WHEN ASSIGNEDTO_R2='STUDENTS' 
			THEN (SELECT TOP(1) PLANID FROM STUDENT_RECEIPTS WHERE REGID=SR.REVIEW2_ID AND PLANID>0 AND ISDELETED=0) 
			ELSE 0
		END REVIEW2_PLANID,REVIEW2_ID,
		CASE WHEN ASSIGNEDTO_R2='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REVIEW2_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.REVIEW2_ID) 
		END REVIEW_NAME2,
		
		ASSIGNEDTO_R3,
		REVIEW3_ID,
		CASE WHEN ASSIGNEDTO_R3='STUDENTS' 
			THEN (SELECT TOP(1) PLANID FROM STUDENT_RECEIPTS WHERE REGID=SR.REVIEW3_ID AND PLANID>0 AND ISDELETED=0) 
			ELSE 0
		END REVIEW3_PLANID,
		CASE WHEN ASSIGNEDTO_R3='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REVIEW3_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.REVIEW3_ID) 
		END REVIEW_NAME3	
		
		,CONVERT(VARCHAR,STARTDATE,(106))STARTDATE,(SELECT TASKSTATUS FROM TASK_TRACKING WHERE LOCID=SR.LOCID AND TASKCATID=SR.TASKCATID AND ASSIGNEDTO_ID=SR.ASSIGNEDTO_ID)TASKSTATUS
		FROM TASK_USERS SR WHERE ISDELETED=0 AND LOCID=$ddlLocation";
			// $data['query'] = $query;
			// echo json_encode($data);
			// exit;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			
			$idx=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKMGMTID'] = (int) $row['TASKMGMTID'];
				$data['data'][] = $row;

				$idx++;
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
/*============ GET TASK USERS =============*/ 


/*============ GET GRADE =============*/ 
 function getTaskCategory($mysqli){
	try
	{
		$data = array();
		$TASKMAINCATID = ($_POST['TASKMAINCATID'] =='undefined' || $_POST['TASKMAINCATID'] =='') ? 0 : $_POST['TASKMAINCATID'];
		$query = "SELECT TASKCATID,TASKCATID as id,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKCAT ,
		TASKCAT AS label
		FROM TASK_CATEGORIES GM 
		WHERE ISDELETED=0 AND TASKMAINCATID=$TASKMAINCATID ORDER BY [LOCATION],LEN(TASKCAT),TASKCAT";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKCATID'] = (int) $row['TASKCATID'];
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
/*============ GET GRADE =============*/ 



/*============ Get Plans =============*/ 
function getPlans($mysqli){
	try
	{
		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0";
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

/*============ Get Assigned TO user =============*/ 
function getAssignedToUser($mysqli){
	try
	{
		$data = array();
		$txtAssignedTo = ($_POST['txtAssignedTo'] =='undefined' || $_POST['txtAssignedTo'] =='') ? '' : $_POST['txtAssignedTo'];
		$ddlPlan = ($_POST['ddlPlan'] =='undefined' || $_POST['ddlPlan'] =='') ? 0 : $_POST['ddlPlan'];
		$data['txtAssignedTo'] = $txtAssignedTo=='STUDENTS' && $ddlPlan>0;
		if($txtAssignedTo=='STUDENTS' && $ddlPlan==0)
		{
			throw new Exception('Invalid Plan Id.');
		}

		// echo json_encode($data);
		// exit;
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		if($txtAssignedTo=='STUDENTS' && $ddlPlan>0)
		{
			$query = "SELECT DISTINCT REGID AS id,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)label
			FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND  PLANID=$ddlPlan ORDER BY label";

		}
		else if ($txtAssignedTo=='TEACHERS')
		{
			$query="SELECT UID AS id,FIRSTNAME+' '+LASTNAME as label FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER'";
		}
		else if ($txtAssignedTo=='VOLUNTEERS')
		{
			$query="SELECT UID AS id,FIRSTNAME+' '+LASTNAME as label FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='VOLUNTEER'";
		}
		else if ($txtAssignedTo=='ADMINS')
		{
			$query="SELECT UID AS id,FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' as label FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE In('ADMINISTRATOR','LA_MASTER')";
		}
		else
		{
			throw new Exception('Invalid Assigned To.');
		
		}
		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

	
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







