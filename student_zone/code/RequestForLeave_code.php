<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "Save":Save($conn);break;
        case "getRFL":getRFL($conn);break;
        case "getStudentByPlan":getStudentByPlan($conn);break;
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
    
		
		$actionid = $reqid == 0 ? 1 : 2;

		if($txtFromDate == '')
		{throw new Exception("Please select From date.");}
		if($txtToDate == '')
		{throw new Exception("Please select To date.");}
		if($txtRemark == '')
		{throw new Exception("Please enter Remark.");}
		
		$sql = "SELECT * FROM REQUEST_LEAVE WHERE STUDENT_TEACHER='STUDENT' AND REQ_BY_ID=$REGID AND FROMDT='$txtFromDate' AND TODT='$txtToDate' AND REQID!=$reqid AND CANCELLED=0";
		// throw new Exception($sql);
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [REQUEST_LEAVE_SP] $actionid,$reqid,'STUDENT',$REGID,'$txtFromDate','$txtToDate','$txtRemark',$userid";
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




/*============ Get RFL =============*/ 
 function getRFL($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$GET_FOR=($_POST['GET_FOR'] == 'undefined' || $_POST['GET_FOR'] == '') ? '' : $_POST['GET_FOR'];
		if($REGID == 0)throw new Exception('REGID Invalid.');
		if($GET_FOR == '')throw new Exception('Get For Missing.');
		if($GET_FOR==='ADMIN'){
			$query = "SELECT REQID,CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,
					CANCELLED,CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS,
					(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.CANCELID)CANCELLEDBY,REQ_BY_ID,
					(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RL.REQ_BY_ID)STUDENT_NAME,
					(SELECT (SELECT TOP 1 PLANID FROM REGISTRATION_DETAILS WHERE REGID=R.REGID AND CANCELLED=0) FROM REGISTRATIONS R WHERE REGID=RL.REQ_BY_ID)PLANID
					FROM REQUEST_LEAVE RL WHERE REQ_BY_ID=$REGID AND STUDENT_TEACHER='STUDENT'";
		}else{
			$query = "SELECT REQID,CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,
					CANCELLED,CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS,
					(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.CANCELID)CANCELLEDBY
					FROM REQUEST_LEAVE RL WHERE REQ_BY_ID=$REGID AND STUDENT_TEACHER='STUDENT'";
		}
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REQID'] = (int) $row['REQID'];
				$date = date_create($row['FROMDT']);
				$row['FROMDT'] = date_format($date,"d M Y");
				$row['FROMDT_ORDERBY'] = date_format($date,"mdYhm");
				$date = date_create($row['TODT']);
				$row['TODT'] = date_format($date,"d M Y");
				$row['TODT_ORDERBY'] = date_format($date,"mdYhm");

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





/* ========== GET STUDENT BY PLAN =========== */
 function getStudentByPlan($mysqli){
	try
	{
		$data = array();
		$ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		if($ddlPlan == 0){throw new Exception('PlanID Missing.');}
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT REGID,FIRSTNAME+' '+LASTNAME AS STUDENT_NAME FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1";
		if($ddlLocation>0){
			$query .=" AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID=$ddlPlan AND CANCELLED=0 AND 
		 							REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation))";
		}else{
			if($_SESSION['USER_LOCID'] != '1'){
				$query .=" AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID=$ddlPlan AND CANCELLED=0 AND 
										REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID']."))";
			}else{
				$query .=" AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID=$ddlPlan AND CANCELLED=0)";
			}
		}
		$query .= " ORDER BY FIRSTNAME";
		$data['$query']=$query;
		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
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
/* ========== GET STUDENT BY PLAN =========== */






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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

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



function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['MEP_USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







