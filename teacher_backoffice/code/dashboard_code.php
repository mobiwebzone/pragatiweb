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
        case "saveOnline":saveOnline($conn);break;
        case "setAlwaysOnline":setAlwaysOnline($conn);break;
        case "gotoOffline":gotoOffline($conn);break;
        case "getOnlineStatus":getOnlineStatus($conn);break;
        case "getTeacherAtt":getTeacherAtt($conn);break;
        case "getAnnouncement":getAnnouncement($conn);break;
        case "getTotals":getTotals($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 /*============ SAVE ONINE/OFFLINE =============*/ 
function saveOnline($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $tosid  = ($_POST['tosid'] == 'undefined' || $_POST['tosid'] == '') ? 0 : $_POST['tosid'];
	   $LOC_ID  = ($_POST['LOC_ID'] == 'undefined' || $_POST['LOC_ID'] == '') ? 0 : $_POST['LOC_ID'];
	   $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
	   $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
	   
	   $actionid = $tosid == 0 ? 1 : 2;

	   if($LOC_ID == 0) throw new Exception("Invalid Location.");
	   if($txtFromDT == '') throw new Exception("Invalid From Time.");
	   if($txtToDT == '') throw new Exception("Invalid To Time.");


		$query="EXEC [TEACHER_ONLINE_STATUS_SP] $actionid,$tosid,$userid,$LOC_ID,'$txtFromDT','$txtToDT'";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}
		else
		{
			// GET TOSID
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_TOSID'] = $row['TOSID'];
			$data['success'] = true;
			if(!empty($tosid))$data['message'] = 'Status update successfully.';
			else $data['message'] = 'Status online successfully.';
		}
		
		echo json_encode($data);exit;

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

 /*============ SAVE ALWAYS ONLINE =============*/ 
function setAlwaysOnline($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $chkAlwaysActive  = ($_POST['chkAlwaysActive'] == 'undefined' || $_POST['chkAlwaysActive'] == '') ? 0 : $_POST['chkAlwaysActive'];

		$query="UPDATE USERS SET ALWAYS_ACTIVE=$chkAlwaysActive WHERE UID=$userid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Status update successfully.';
		}
		
		echo json_encode($data);exit;

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

 /*============ GOTO OFFLINE =============*/ 
function gotoOffline($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $tosid  = ($_POST['tosid'] == 'undefined' || $_POST['tosid'] == '') ? 0 : $_POST['tosid'];
	   $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

	   if($txtToDT == '') throw new Exception("Invalid To Time.");


		$query="UPDATE TEACHER_ONLINE_STATUS SET OUTTIME='$txtToDT' WHERE TOSID=$tosid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}
		else
		{
			$data['message'] = 'Status offline successfully.';
		}
		
		echo json_encode($data);exit;

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




/*============ Get Online Status =============*/ 
function getOnlineStatus($mysqli){
	try
	{
		global $userid;
		$data = array();

		// GET ISONLINE
		$queryAA = "SELECT ALWAYS_ACTIVE FROM USERS WHERE UID=$userid";
		$resultAA = sqlsrv_query($mysqli, $queryAA);
		$rowAA = sqlsrv_fetch_array($resultAA,SQLSRV_FETCH_ASSOC);
		$data['queryAA'] = $queryAA;
		$data['ALWAYS_ACTIVE'] = $rowAA['ALWAYS_ACTIVE'];

		$query = "SELECT TOP 1 TOSID,FORMAT(ONTIME,'hh:mm tt')ONTIME,FORMAT(ONTIME,'HH:mm:ss')ONTIME_SET,
		FORMAT(OUTTIME,'hh:mm tt')OUTTIME,FORMAT(OUTTIME,'HH:mm:ss')OUTTIME_SET,
		CASE WHEN CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105) THEN 1 ELSE 0 END ONLINE_STATUS
		FROM TEACHER_ONLINE_STATUS WHERE ISDELETED=0 AND TEACHERID=$userid ORDER BY TOSID DESC";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($count > 0){
			$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$data['data'] = $row;
			$data['success'] = true;
		}else{
			
			$data['success'] = false;
		}

		// TOP 5
		$query2 = "SELECT TOP 5 TOSID,CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE,FORMAT(ONTIME,'hh:mm tt')ONTIME,FORMAT(ONTIME,'HH:mm:ss')ONTIME_SET,
		FORMAT(OUTTIME,'hh:mm tt')OUTTIME,FORMAT(OUTTIME,'HH:mm:ss')OUTTIME_SET,
		CASE WHEN CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105) THEN 1 ELSE 0 END ONLINE_STATUS
		FROM TEACHER_ONLINE_STATUS WHERE ISDELETED=0 AND TEACHERID=$userid ORDER BY TOSID DESC";
		$count2 = unique($query2);
		$result2 = sqlsrv_query($mysqli, $query2);
		if($count2 > 0){
			while($row2 = sqlsrv_fetch_array($result2,SQLSRV_FETCH_ASSOC)){
				$data['data2'][] = $row2;
			}
			$data['success2'] = true;
		}else{
			
			$data['success2'] = false;
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




/*============ Get TeacherAtt =============*/ 
function getTeacherAtt($mysqli){
	try
	{
		global $userid;
		$data = array();

		$query = "SELECT TAID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=TA.PLANID)PLANNAME,
		CONVERT(VARCHAR,TIME_IN,100)TIME_IN,CONVERT(VARCHAR,TIME_OUT,100)TIME_OUT,GETDATE()+2,
		REMARKS FROM TEACHER_ATTENDANCE TA WHERE ISDELETED=0 AND 
		CONVERT(DATE,ATTDATE,105) BETWEEN CONVERT(DATE,GETDATE()-45,105) AND CONVERT(DATE,GETDATE(),105)
		AND TEACHERID=$userid
		ORDER BY CONVERT(DATE,ATTDATE,105) DESC";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TAID'] = (int) $row['TAID'];
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


/*============ Get Announcement =============*/ 
function getAnnouncement($mysqli){
	try
	{
		$data = array();
		$LOC_ID = ($_POST['LOC_ID'] == 'undefined' || $_POST['LOC_ID'] == '') ? 0 : $_POST['LOC_ID'];

		$query = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS 
				WHERE ISDELETED=0 AND LOCID =
				CASE 
				WHEN LOCID <= 0 THEN 0
				WHEN LOCID >0 THEN $LOC_ID
				END";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ANID'] = (int) $row['ANID'];
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


/*============ Get Totals =============*/ 
function getTotals($mysqli){
	try
	{
		global $userid;
		$data = array();

		$userid_locid = $_POST['LOC_ID'] == 'undefined' ? 0 : $_POST['LOC_ID'];
		
		// ========== TASK REVIEW COUNT===========
		// $queryTaskReviewCount = "SELECT COUNT(*)TOTAL FROM TASK_TRACKING TT WHERE ISDELETED=0 AND  LOCID IN
		// (SELECT LOCID FROM TASK_USERS WHERE LOCID=$userid_locid AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID AND TASKCATID=TT.TASKCATID AND REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid )
		// AND  TASKSTATUS!='CLOSED'";
		$queryTaskReviewCount = "SELECT COUNT(*)TOTAL FROM TASK_USERS  WHERE ISDELETED=0 AND 
		TASKSTATUS IN ('OPEN','WIP','CLOSED') AND
		ASSIGNEDTO IN ('ADMINS','TEACHERS','VOLUNTEERS') AND 
		LOCID=$userid_locid AND (ASSIGNEDTO_ID=$userid OR REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid)";
		$data['queryTaskReviewCount']=$queryTaskReviewCount;
		$resultTaskReviewCount = sqlsrv_query($mysqli, $queryTaskReviewCount);
		while ($rowTaskReviewCount = sqlsrv_fetch_array($resultTaskReviewCount,SQLSRV_FETCH_ASSOC)) {
			$data['TotalTaskReviewCount'] = $rowTaskReviewCount;
		}
		// ========== TASK REVIEW COUNT===========


		// ========== TASK REVIEW ===========
		// $queryTaskReview = "SELECT TTID,CONVERT(varchar,TTDATE,(106))TTDATE,
		// LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TT.LOCID)LOCATIONS,
		// TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=TT.TASKCATID)TASKCAT,
		// ASSIGNEDTO_ID,
		// CASE WHEN ASSIGNEDTO_ID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO='STUDENTS' AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID) 
		// 	THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
		// 	ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
		// END ASSIGNEDTO_NAME ,
		// GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=TT.GRADEID)GRADE,
		// CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=TT.CSUBID)SUBJECT,
		// TASK,TASKFILE,CONVERT(varchar,TASKUPLOADEDON,(106))TASKUPLOADEDON 
		// FROM TASK_TRACKING TT WHERE ISDELETED=0 AND TASKSTATUS IN ('OPEN','WIP') AND
		// LOCID IN (SELECT LOCID FROM TASK_USERS WHERE LOCID=$userid_locid AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID AND TASKCATID=TT.TASKCATID AND REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid )";

		$queryTaskReview = "SELECT TASKMGMTID, LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TU.LOCID)LOCATIONS,
		TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=TU.TASKCATID)TASKCAT,
		CONVERT(VARCHAR,STARTDATE,(106))STARTDATE,CONVERT(VARCHAR,ENDDATE,(106))ENDDATE, 
		(SELECT (SELECT TASKMAINCAT FROM TASK_MAIN_CATEGORIES WHERE TASKMAINCATID=TC.TASKMAINCATID) FROM TASK_CATEGORIES TC WHERE TASKCATID=TU.TASKCATID)TASKMAINCAT,
		ASSIGNEDTO_ID,
		CASE WHEN ASSIGNEDTO='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TU.ASSIGNEDTO_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TU.ASSIGNEDTO_ID) 
		END ASSIGNEDTO_NAME ,
		GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=TU.GRADEID)GRADE,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=TU.CSUBID)SUBJECT,TASKSTATUS,
		CLOSEDBY,CLOSEDBYID,
		CASE WHEN CLOSEDBY='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TU.CLOSEDBYID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TU.CLOSEDBYID) 
		END CLOSEDBYNAME,
		CASE WHEN CLOSEDON='1900-01-01 00:00:00.000' OR CLOSEDON IS NULL THEN '' ELSE FORMAT(CLOSEDON,'dd MMM yyyy hh:mm tt') END CLOSEDON,
		TASK_DESC,TASKFILE--,CONVERT(VARCHAR,TASKUPDATEON,(106))TASKUPDATEON 
		FROM TASK_USERS TU
		WHERE ISDELETED=0 AND TASKSTATUS IN ('OPEN','WIP','CLOSED') AND
		LOCID=$userid_locid AND ASSIGNEDTO IN ('ADMINS','TEACHERS','VOLUNTEERS') AND (ASSIGNEDTO_ID=$userid OR REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid)";


			// $data['queryTaskReview'] = $queryTaskReview;
			// echo json_encode($data);exit;
		$resultTaskReview = sqlsrv_query($mysqli, $queryTaskReview);
		while ($rowTaskReview = sqlsrv_fetch_array($resultTaskReview,SQLSRV_FETCH_ASSOC)) {
			$data['TotalTaskView'][] = $rowTaskReview;
		}
		// ========== TASK REVIEW  ===========

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
/*============ Get Totals =============*/ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







