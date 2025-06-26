<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}

// if($_SESSION['ROLE'] != 'SUPERADMIN')
// {
// 	if(!empty($_SESSION['CLID']))
// 	{$userclid=$_SESSION['CLID'];}
// 	else
// 	{$userclid=0;}
// }
// else
// {
// 	$userclid=0;
// }

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
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


 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$txtOldPassword=$_POST['txtOldPassword'] == 'undefined' ? '' : $_POST['txtOldPassword'];
		$txtNewPassword=$_POST['txtNewPassword'] == 'undefined' ? '' : $_POST['txtNewPassword'];
		$txtConfirmPassword=$_POST['txtConfirmPassword'] == 'undefined' ? '' : $_POST['txtConfirmPassword'];
    
	

		if($REGID == 0)
		{throw new Exception("REGID Error.");}
		if($txtOldPassword == '')
		{throw new Exception("Please enter old password.");}

		// Check Old Password
		$ChkOldPass = "SELECT DBO.GET_CLEAR_STUDENT_PASSWORD($REGID) AS SPASS FROM REGISTRATIONS WHERE REGID=$REGID";
		$CountOP = unique($ChkOldPass);
		$stmtOP=sqlsrv_query($mysqli, $ChkOldPass);
		if($CountOP > 0){
			$rowOP = sqlsrv_fetch_array($stmtOP);
			if($txtOldPassword != $rowOP['SPASS'])
			{throw new Exception("Please enter correct old password.");}
			
			
			if($txtNewPassword == '')
			{throw new Exception("Please enter new password.");}
			if($txtConfirmPassword == '')
			{throw new Exception("Please enter confirm password.");}
			
			if($txtNewPassword != $txtConfirmPassword)
			{throw new Exception("Please check confirm password.");}
			
			
			
			$ChangePass = "EXEC [CHANGE_STUDENT_PASS_SP] $REGID,'$txtConfirmPassword'";
			$stmt=sqlsrv_query($mysqli, $ChangePass);
			
			if($stmt === false)
			{
				$data['success'] = false;
				// $data['query'] = $ChangePass;
				echo json_encode($data);exit;
			}
			else
			{
				// $data['query'] = $ChangePass;
				$data['success'] = true;
				$data['message'] = 'Password successfully changed.';
				echo json_encode($data);exit;
			}
		}
		else{
			$data['success'] = false;
			$data['message'] = 'Old password not found.';
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
        // $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        // $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        // $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "SELECT REQID,CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,
				CANCELLED,CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS,
				(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.CANCELID)CANCELLEDBY
				FROM REQUEST_LEAVE RL WHERE REQ_BY_ID=$REGID AND STUDENT_TEACHER='STUDENT'";
		$data['$query']=$query;
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







