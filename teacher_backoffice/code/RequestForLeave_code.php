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
		case "login":login($conn);break;
        case "Save":Save($conn);break;
        case "getRFL":getRFL($conn);break;
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

/*============ SAVE DATA =============*/ 
 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];
		$txtFromDate=$_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate=$_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		$txtRemark=$_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$TEACHERID = ($_POST['TEACHERID'] == 'undefined' || $_POST['TEACHERID'] == '') ? 0 : $_POST['TEACHERID'];
    
		
		$actionid = $reqid == 0 ? 1 : 2;

		if($txtFromDate == '')
		{throw new Exception("Please select From date.");}
		if($txtToDate == '')
		{throw new Exception("Please select To date.");}
		if($txtRemark == '')
		{throw new Exception("Please enter Remark.");}
		
		$sql = "SELECT * FROM REQUEST_LEAVE WHERE STUDENT_TEACHER='TEACHER' AND REQ_BY_ID=$TEACHERID AND FROMDT='$txtFromDate' AND TODT='$txtToDate' AND REQID!=$reqid AND CANCELLED=0";

		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [REQUEST_LEAVE_SP] $actionid,$reqid,'TEACHER',$TEACHERID,'$txtFromDate','$txtToDate','$txtRemark',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($reqid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/*============ SAVE DATA =============*/ 






/*============ Get RFL =============*/ 
 function getRFL($mysqli){
	try
	{
		global $userid;
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$FOR = ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];

		// $UID = $ddlTeacher > 0 ? $ddlTeacher : $userid;
		

		$query = "SELECT REQID,REQ_BY_ID,(SELECT LOCID FROM USERS WHERE [UID]=RL.REQ_BY_ID)LOCID,
				(SELECT FIRSTNAME +' '+ LASTNAME FROM USERS WHERE [UID]=RL.REQ_BY_ID)TEACHER,
				CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,
				CANCELLED,CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS,
				(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.CANCELID)CANCELLEDBY
				FROM REQUEST_LEAVE RL WHERE STUDENT_TEACHER='TEACHER' 
				AND REQ_BY_ID IN (SELECT [UID] FROM USERS WHERE LOCID=$ddlLocation AND USERROLE='TEACHER')
				";

		if($FOR == 'ADMIN'){
			if($ddlTeacher > 0){
				$query .=" AND REQ_BY_ID=$ddlTeacher";
			}
		}
		else{
			$query .=" AND REQ_BY_ID=$userid";
		}

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($RCOUNT > 0){
			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REQID'] = (int) $row['REQID'];
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
/*============ Get RFL =============*/ 







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];

		$del = "DELETE FROM REQUEST_LEAVE WHERE REQID=$reqid";
		$data['del'] =$del;
		$stmt=sqlsrv_query($mysqli, $del);
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
/* =========== Delete =========== */ 






/*============ Get Locations =============*/ 
function getLocations($mysqli){
	try
	{
		global $userid;
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0
		AND LOC_ID IN (SELECT LOCID FROM TEACHER_LOCATION WHERE TEACHERID=$userid AND ISDELETED=0)";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
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
/*============ Get Locations =============*/ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







