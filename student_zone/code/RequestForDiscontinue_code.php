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
        case "getRFD":getRFD($conn);break;
        case "getStudentPlans":getStudentPlans($conn);break;
        // case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/*============ Save Data =============*/ 
 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
		$ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$txtRemark=$_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];

		if($REGID == 0)throw new Exception('Invalid REGID.');
		if($txtDate == '')
		{throw new Exception("Please select date.");}
		if($ddlPlan == 0)
		{throw new Exception("Please select Plan.");}
		if($txtRemark == '')
		{throw new Exception("Please enter Remark.");}
		

		$data = array();

			$query="UPDATE REGISTRATION_DETAILS SET DISCONTINUE_REQ=1,DISCONTINUE_REQ_DATE='$txtDate',DISCONTINUE_REMARK='$txtRemark' 
					WHERE REGID=$REGID AND PLANID=$ddlPlan AND CANCELLED=0";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Request successfully submitted.';
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
/*============ Save Data =============*/ 





/*============ Get Request For Discontinue =============*/ 
 function getRFD($mysqli){
	try
	{
		$data = array();
		global $userid;

		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// if($REGID == 0)throw new Exception('REGID Invalid');

		$query = "SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN],
		CONVERT(VARCHAR,DISCONTINUE_REQ_DATE,20)DISCONTINUE_REQ_DATE,DISCONTINUE_REMARK,DISCONTINUE_APPROVED,
		CONVERT(VARCHAR,DISCONTINUE_REQ_APPR_DATE,106)DISCONTINUE_REQ_APPR_DATE,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM USERS WHERE UID=RD.DISCONTINUE_APPROVE_BY_ID)REQ_APPR_BY,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)STUDENT_NAME
		FROM REGISTRATION_DETAILS RD 
		WHERE CANCELLED=0 AND DISCONTINUE_REQ=1";
		if($REGID>0){
			$query .= " AND REGID=$REGID";
		}else{
			$query .= " AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";
		}
		$data['$query']=$query;
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$date = date_create($row['DISCONTINUE_REQ_DATE']);
				$row['DISCONTINUE_REQ_DATE'] = date_format($date,"d M Y");
				$row['DISCONTINUE_REQ_DATE_ORDERBY'] = date_format($date,"mdYhm");
				$row['PLANID'] = (int) $row['PLANID'];
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
/*============ Get Request For Discontinue =============*/ 




/*============ Get Student Plans =============*/ 
 function getStudentPlans($mysqli){
	try
	{
		global $userid;
		$data = array();
		$GET_FOR = ($_POST['GET_FOR'] == 'undefined' || $_POST['GET_FOR'] == '') ? '' : $_POST['GET_FOR'];

		if($GET_FOR == 'ADMIN'){
			$query = "SELECT PLANID,PLANNAME [PLAN] FROM PLANS WHERE ISDELETED=0";
		}else if($GET_FOR == 'STUDENT'){
			$query = "SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN] FROM REGISTRATION_DETAILS RD 
				WHERE CANCELLED=0 AND REGID=$userid AND DISCONTINUE_APPROVED=0";
		}else{
			throw new Exception('Error.');
		}

		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANID'] = (int) $row['PLANID'];
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
/*============ Get Student Plans =============*/ 





/* =========== Delete =========== */ 
// function delete($mysqli){
// 	try{   
// 			global $userid;
// 			$data = array();     
//             $reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];

// 			$del = "DELETE FROM REQUEST_LEAVE WHERE REQID=$reqid";
// 			$data['del'] =$del;
// 			$stmt=sqlsrv_query($mysqli, $del);
// 			if( $stmt === false ) 
// 			{
// 				die( print_r( sqlsrv_errors(), true));
// 				throw new Exception( $mysqli->sqlstate );
// 			}
// 			else
// 			{
// 				$data['success'] = true;
// 				$data['message'] = 'Record successfully deleted';
// 			}
// 		echo json_encode($data);exit;
		
		
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false . $query;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }
/* =========== Delete =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







