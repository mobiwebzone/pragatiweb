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
        case "save":save($conn);break;
		case "getAssignedToUser":getAssignedToUser($conn);break;
		case "getReview1":getReview1($conn);break;
		case "getReview2":getReview2($conn);break;
		case "getReview3":getReview3($conn);break;
		case "getPlans":getPlans($conn);break;
		case "getTaskUsers":getTaskUsers($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
        $TASKMGMTID  = ($_POST['TASKMGMTID'] == 'undefined' || $_POST['TASKMGMTID'] == '') ? 0 : $_POST['TASKMGMTID'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlTask_Category  = ($_POST['ddlTask_Category'] == 'undefined' || $_POST['ddlTask_Category'] == '') ? '' : $_POST['ddlTask_Category'];
        $txtAssignedTo  = ($_POST['txtAssignedTo'] == 'undefined' || $_POST['txtAssignedTo'] == '') ? '' : $_POST['txtAssignedTo'];
        $ddlAssignedToID  = ($_POST['ddlAssignedToID'] == 'undefined' || $_POST['ddlAssignedToID'] == '') ? '' : $_POST['ddlAssignedToID'];
        $txtStartDT  = ($_POST['txtStartDT'] == 'undefined' || $_POST['txtStartDT'] == '') ? '' : $_POST['txtStartDT'];
        $txtEndDT  = ($_POST['txtEndDT'] == 'undefined' || $_POST['txtEndDT'] == '') ? '' : $_POST['txtEndDT'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$txtTaskDesc = ($_POST['txtTaskDesc'] == 'undefined' || $_POST['txtTaskDesc'] == '') ? '' : $_POST['txtTaskDesc'];
		$txtlink = ($_POST['txtlink'] == 'undefined' || $_POST['txtlink'] == '') ? '' : $_POST['txtlink'];
		$txtUpdateDate = ($_POST['txtUpdateDate'] == 'undefined' || $_POST['txtUpdateDate'] == '') ? '' : $_POST['txtUpdateDate'];

        $ddlReviewBy1  = ($_POST['ddlReviewBy1'] == 'undefined' || $_POST['ddlReviewBy1'] == '') ? 0 : $_POST['ddlReviewBy1'];
        $ddlReviewBy2  = ($_POST['ddlReviewBy2'] == 'undefined' || $_POST['ddlReviewBy2'] == '') ? 0 : $_POST['ddlReviewBy2'];
        $ddlReviewBy3  = ($_POST['ddlReviewBy3'] == 'undefined' || $_POST['ddlReviewBy3'] == '') ? 0 : $_POST['ddlReviewBy3'];
        $txtAssignReview1  = ($_POST['txtAssignReview1'] == 'undefined' || $_POST['txtAssignReview1'] == '') ? '' : $_POST['txtAssignReview1'];
        $txtAssignReview2  = ($_POST['txtAssignReview2'] == 'undefined' || $_POST['txtAssignReview2'] == '') ? '' : $_POST['txtAssignReview2'];
        $txtAssignReview3  = ($_POST['txtAssignReview3'] == 'undefined' || $_POST['txtAssignReview3'] == '') ? '' : $_POST['txtAssignReview3'];
		
		$actionid = $TASKMGMTID == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlTask_Category == '')throw new Exception("Please select Category.");
		
		$sql = "SELECT * FROM TASK_USERS WHERE LOCID=$ddlLocation AND TASKCATID=$ddlTask_Category AND ASSIGNEDTO='$txtAssignedTo' AND ASSIGNEDTO_ID=$ddlAssignedToID 
		AND CONVERT(DATE,STARTDATE,105)='$txtStartDT' AND CONVERT(DATE,ENDDATE,105)='$txtEndDT'  AND TASKMGMTID!=$TASKMGMTID AND ISDELETED=0";
		$row_count = unique($sql);
		$data['sql'] = $sql;
		
		if($row_count == 0)
		{
			$query="EXEC [TASK_USERS_SP] $actionid,$TASKMGMTID,$ddlLocation,$ddlTask_Category,'$txtAssignedTo',$ddlAssignedToID,
			'$txtAssignReview1',$ddlReviewBy1,'$txtAssignReview2',$ddlReviewBy2,'$txtAssignReview3',$ddlReviewBy3,'$txtStartDT','$txtEndDT',
			$ddlGrade,$ddlSubject,'$txtTaskDesc','$txtlink','$txtUpdateDate','OPEN',$userid";
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($TASKMGMTID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'data already exists.';
			echo json_encode($data);exit;
		}

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


/*============ GET TASK USERS =============*/ 
 function getTaskUsers($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT  TASKMGMTID,LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=SR.LOCID)LOCATION,
		TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=SR.TASKCATID)TASKCAT,
		(SELECT TASKMAINCATID FROM TASK_CATEGORIES WHERE TASKCATID=SR.TASKCATID)TASKMAINCATID,
		(SELECT (SELECT TASKMAINCAT FROM TASK_MAIN_CATEGORIES WHERE TASKMAINCATID=TC.TASKMAINCATID) FROM TASK_CATEGORIES TC WHERE TASKCATID=SR.TASKCATID)TASKMAINCAT,
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
		
		,CONVERT(VARCHAR,STARTDATE,(106))STARTDATE,CONVERT(VARCHAR,ENDDATE,(106))ENDDATE,
		GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=SR.GRADEID)GRADE,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=SR.CSUBID)SUBJECT,
		TASK_DESC,TASKFILE,
		CASE WHEN TASKUPDATEON='1970-01-01 00:00:00.000' OR TASKUPDATEON='1900-01-01 00:00:00.000' OR TASKUPDATEON IS NULL 
			THEN '' ELSE CONVERT(varchar,TASKUPDATEON,106) 
		END TASKUPDATEON,
		TASKSTATUS,
		CLOSEDBY,CLOSEDBYID,
		CASE WHEN CLOSEDBY='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.CLOSEDBYID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.CLOSEDBYID) 
		END CLOSEDBYNAME,
		CASE WHEN CLOSEDON='1900-01-01 00:00:00.000' OR CLOSEDON IS NULL THEN '' ELSE FORMAT(CLOSEDON,'dd MMM yyyy hh:mm tt') END CLOSEDON
		FROM TASK_USERS SR WHERE ISDELETED=0 AND LOCID=$ddlLocation";
			$data['query'] = $query;
			// echo json_encode($data);exit;
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








/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$TASKMGMTID = ($_POST['TASKMGMTID'] == 'undefined' || $_POST['TASKMGMTID'] == '') ? 0 : $_POST['TASKMGMTID'];  
		$query = "EXEC [TASK_USERS_SP] 3,$TASKMGMTID,0,0,'',0,'',0,'',0,'',0,'','',0,0,'','','','',$userid";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted';
		}
		echo json_encode($data);exit;
	}catch (Exception $e){
		$data = array();
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== DELETE =========== */ 


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
			$query = "SELECT DISTINCT REGID AS ID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)USERNAME
			FROM REGISTRATION_DETAILS SR WHERE CANCELLED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND  PLANID=$ddlPlan ORDER BY USERNAME";

		}
		else if ($txtAssignedTo=='TEACHERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER'";
		}
		else if ($txtAssignedTo=='VOLUNTEERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='VOLUNTEER'";
		}
		else if ($txtAssignedTo=='ADMINS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE In('ADMINISTRATOR','LA_MASTER','SUPERADMIN')";
		}
		else
		{
			throw new Exception('Invalid Assigned To.');
		
		}
		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
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
/*============ Get Assigned TO user =============*/ 
function getReview1($mysqli){
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
			$query = "SELECT DISTINCT REGID AS ID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)USERNAME
			FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND  PLANID=$ddlPlan ORDER BY USERNAME";

		}
		else if ($txtAssignedTo=='TEACHERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER'";
		}
		else if ($txtAssignedTo=='VOLUNTEERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='VOLUNTEER'";
		}
		else if ($txtAssignedTo=='ADMINS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE In('ADMINISTRATOR','LA_MASTER','SUPERADMIN')";
		}
		else
		{
			throw new Exception('Invalid Assigned To.');
		
		}
		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
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
/*============ Get Assigned TO user =============*/ 
function getReview2($mysqli){
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
			$query = "SELECT DISTINCT REGID AS ID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)USERNAME
			FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND  PLANID=$ddlPlan ORDER BY USERNAME";

		}
		else if ($txtAssignedTo=='TEACHERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER'";
		}
		else if ($txtAssignedTo=='VOLUNTEERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='VOLUNTEER'";
		}
		else if ($txtAssignedTo=='ADMINS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE In('ADMINISTRATOR','LA_MASTER','SUPERADMIN')";
		}
		else
		{
			throw new Exception('Invalid Assigned To.');
		
		}
		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
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
/*============ Get Assigned TO user =============*/ 
function getReview3($mysqli){
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
			$query = "SELECT DISTINCT REGID AS ID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)USERNAME
			FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND  PLANID=$ddlPlan ORDER BY USERNAME";

		}
		else if ($txtAssignedTo=='TEACHERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER'";
		}
		else if ($txtAssignedTo=='VOLUNTEERS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='VOLUNTEER'";
		}
		else if ($txtAssignedTo=='ADMINS')
		{
			$query="SELECT UID AS ID,FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' as USERNAME FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE In('ADMINISTRATOR','LA_MASTER','SUPERADMIN')";
		}
		else
		{
			throw new Exception('Invalid Assigned To.');
		
		}
		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
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







