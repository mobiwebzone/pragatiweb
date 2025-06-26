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
        case "getStudentData":getStudentData($conn);break;
        case "ActivePlan":ActivePlan($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ GET DISCONTINUE STUDENT =============*/ 
 function getStudentData($mysqli){
	try
	{
		$data = array();

		$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtSearchStudentForDis = $_POST['txtSearchStudentForDis'] == 'undefined' ? '' : $_POST['txtSearchStudentForDis'];


		$query = "SELECT REGDID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)STUDENT_NAME,
		PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN],
		CONVERT(VARCHAR,DISCONTINUE_REQ_DATE,106)DISCONTINUE_REQ_DATE,DISCONTINUE_REMARK,DISCONTINUE_APPROVED,
		CONVERT(VARCHAR,DISCONTINUE_REQ_APPR_DATE,20)DISCONTINUE_REQ_APPR_DATE,DISCONTINUE_APPROVE_BY_ID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RD.DISCONTINUE_APPROVE_BY_ID)APPROVE_BY,
		ACTIVATE_BY_ID,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RD.ACTIVATE_BY_ID)ACTIVATE_BY,
		CONVERT(VARCHAR,ACTIVATE_DATE,106)ACTIVATE_DATE,ACTIVATE
		FROM REGISTRATION_DETAILS RD WHERE CANCELLED=0
		AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation AND APPROVED=1)";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID'].")";
		// }

		if($FOR == 'ACTIVATE'){
			$query .= " AND ACTIVATE = 0";
		}
		else{
			if($txtSearchStudentForDis != ''){
				$query .= " AND ACTIVATE = 1 AND (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID) LIKE '%$txtSearchStudentForDis%'";
			}else{
				$query .= " AND ACTIVATE = 1 AND DISCONTINUE_REQ=1";
			}
		}
		
		$data['query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$date = date_create($row['DISCONTINUE_REQ_DATE']);
			$row['DISCONTINUE_REQ_DATE'] = date_format($date,"d M Y");
			$row['DISCONTINUE_REQ_DATE_ORDERBY'] = date_format($date,"ymd");
			
			$date = date_create($row['DISCONTINUE_REQ_APPR_DATE']);
			$row['DISCONTINUE_REQ_APPR_DATE'] = date_format($date,"d M Y");
			$row['DISCONTINUE_REQ_APPR_DATE_ORDERBY'] = date_format($date,"ymd");
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



/* =========== Delete =========== */ 
function ActivePlan($mysqli){
	try{   
			global $userid;
			$data = array();     
            $REGDID = ($_POST['REGDID'] == 'undefined' || $_POST['REGDID'] == '') ? 0 : $_POST['REGDID'];  
            $FOR_ACT_DIS = $_POST['FOR_ACT_DIS'] == 'undefined' ? '' : $_POST['FOR_ACT_DIS'];  

			if($REGDID == 0){
				throw new Exception("REGDID Error.");
			}
			if($FOR_ACT_DIS == ''){
				throw new Exception("For_ACT_DIS Not Found.");
			}

			if($FOR_ACT_DIS == 'Activate'){
				$ActivePlanQuery="UPDATE REGISTRATION_DETAILS SET DISCONTINUE_REQ=0,DISCONTINUE_APPROVED=0,ACTIVATE=1,ACTIVATE_BY_ID=$userid,ACTIVATE_DATE=GETDATE()
				WHERE REGDID=$REGDID AND CANCELLED=0";
			}
			else if($FOR_ACT_DIS == 'Discontinue'){
				$ActivePlanQuery="UPDATE REGISTRATION_DETAILS SET DISCONTINUE_APPROVED=1,DISCONTINUE_REQ_APPR_DATE=GETDATE(),DISCONTINUE_APPROVE_BY_ID=$userid,ACTIVATE=0
				WHERE REGDID=$REGDID AND CANCELLED=0";
			}
			else{
				throw new Exception('For_ACT_DIS Not Found.');
			}

			$stmt=sqlsrv_query($mysqli, $ActivePlanQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Plan successfully activated.';
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







