<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['ROLE']))
{$role=$_SESSION['ROLE'];}
else
{$role=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "getTeacher":getTeacher($conn);break;
		case "saveOnline":saveOnline($conn);break;
		case "setAlwaysOnline":setAlwaysOnline($conn);break;
		case "getTeacher_Online_Status":getTeacher_Online_Status($conn);break;
		case "GetActiveStatus":GetActiveStatus($conn);break;
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




/*============ Get Teacher =============*/ 
function getTeacher($mysqli){
	try
	{
		global $role;
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		
		$query = "SELECT UID,FIRSTNAME+' '+LASTNAME +' — '+' ('+USERROLE+')' AS FULLNAME FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0 ORDER BY USERROLE";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Teacher =============*/


/*============ Get Teacher Online Status =============*/ 
function getTeacher_Online_Status($mysqli){
	try
	{
		global $role;
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		
		$query = "SELECT TOP 50 TOSID,LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TOS.LOCID AND ISDELETED=0)LOCATION,
		(SELECT ALWAYS_ACTIVE FROM USERS WHERE UID=TOS.TEACHERID)ALWAYS_ACTIVE
		,TEACHERID,(SELECT FIRSTNAME+' '+LASTNAME  FROM USERS WHERE UID=TOS.TEACHERID AND LOCID=TOS.LOCID)TEACHER
		,FORMAT(ONTIME,'hh:mm tt')ONTIME,FORMAT(ONTIME,'HH:mm:ss')ONTIME_SET,
		FORMAT(OUTTIME,'hh:mm tt')OUTTIME,FORMAT(OUTTIME,'HH:mm:ss')OUTTIME_SET,
		CONVERT(VARCHAR,ONTIME,20) ONTIME_SET, 
		CONVERT(VARCHAR,OUTTIME,20) OUTTIME_SET ,
		CONVERT(VARCHAR,INSERTDATE,105) INSERTDATE ,
		CASE WHEN CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105) THEN 1 ELSE 0 END ONLINE_STATUS
		FROM TEACHER_ONLINE_STATUS TOS WHERE ISDELETED=0  AND LOCID=$ddlLocation ";
		if($ddlTeacher>0) $query .= " AND TEACHERID=$ddlTeacher";
		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC, TOSID DESC";
		$data['query'] = $query;

		$COUNT = unique($query);
		if($COUNT>0){
			$result = sqlsrv_query($mysqli, $query);
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
/*============ Get Teacher =============*/ 



 /*============ SAVE ONINE/OFFLINE =============*/ 
 function saveOnline($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $TOSID  = ($_POST['TOSID'] == 'undefined' || $_POST['TOSID'] == '') ? 0 : $_POST['TOSID'];
	   $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
	   $LOC_ID  = ($_POST['LOC_ID'] == 'undefined' || $_POST['LOC_ID'] == '') ? 0 : $_POST['LOC_ID'];
	   $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
	   $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
	   
	   $actionid = $TOSID == 0 ? 1 : 2;

	   if($ddlTeacher == 0) throw new Exception("Invalid Teacher.");
	   if($LOC_ID == 0) throw new Exception("Invalid Location.");
	   if($txtFromDT == '') throw new Exception("Invalid From Time.");
	   if($txtToDT == '') throw new Exception("Invalid To Time.");


		$query="EXEC [TEACHER_ONLINE_STATUS_SP] $actionid,$TOSID,$ddlTeacher,$LOC_ID,'$txtFromDT','$txtToDT'";
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
			if(!empty($TOSID))$data['message'] = 'Status update successfully.';
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
	   $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query="UPDATE USERS SET ALWAYS_ACTIVE=$chkAlwaysActive WHERE UID=$ddlTeacher";
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


 /*============ Get Active STtaus OF TEacher =============*/ 
 function GetActiveStatus($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$query="SELECT UID,ALWAYS_ACTIVE FROM USERS WHERE UID=$ddlTeacher";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}
		$data['query'] = $query;
		$data['success'] = true;
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
        	  	$TOSID = ($_POST['TOSID'] == 'undefined' || $_POST['TOSID'] == '') ? 0 : $_POST['TOSID'];  
			  
			$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_ONLINE_STATUS_SP] 3,$TOSID,0,0,'',''");
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
		$data['success'] = false . $stmt;
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







