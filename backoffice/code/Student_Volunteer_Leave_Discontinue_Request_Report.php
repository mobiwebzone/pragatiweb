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
        case "getReport":getReport($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Student Test Report =============*/ 
function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		// $PLANIDS_ARRAY = (!isset($_POST['PLANIDS'])) ? array() : $_POST['PLANIDS'];
		// if(!$PLANIDS_ARRAY || count($PLANIDS_ARRAY)<=0) throw new Exception('Select Plans First.');
		// $PLANIDS_ARRAY = implode(",",$PLANIDS_ARRAY);
		// $data['PLANIDS'] = $PLANIDS_ARRAY;

		// $REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		// if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		// $REGIDS_ARRAY = implode(",",$REGIDS_ARRAY);
		// $data['REGIDS'] = $REGIDS_ARRAY;

		// ###############
		// GET VOLUNTEER
		// ###############
		// --REGID IN ($REGIDS_ARRAY) AND 
		$queryVol = "SELECT VRID,CONVERT(VARCHAR,REQDATE,106)REQDATE,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=VR.REGID)STUDENTNAME,
		CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,APPROVED,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=VR.APPROVEDBY)APPROVEDBY,APPR_REMARKS,
		CANCELLED,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=VR.CANCELID)CANCELBY,
		CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS
		FROM VOLUNTEER_REQUESTS VR
		WHERE REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)
		AND CONVERT(DATE,REQDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT' 
		ORDER BY CONVERT(DATE,REQDATE,105) DESC,STUDENTNAME ASC";
		$data['$queryVol']=$queryVol;
		$countVol = unique($queryVol);
		if($countVol>0){
			$resultVol = sqlsrv_query($mysqli, $queryVol);
			set_time_limit(0);
			while ($rowVol = sqlsrv_fetch_array($resultVol,SQLSRV_FETCH_ASSOC)) {
				$data['data_volunteer'][] = $rowVol;
			}
			$data['successVol'] = true;
		}else{
			$data['successVol'] = false;
			$data['messageVol'] = 'Volunteer Data not found.';
		}
		


		// #############
		// GET LEAVE
		// #############
		// --AND REQ_BY_ID IN ($REGIDS_ARRAY) 
		$queryLeave = "SELECT REQID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RL.REQ_BY_ID)STUDENTNAME,
		CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,CANCELLED,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.CANCELID)CANCELBY,
		CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS
		FROM REQUEST_LEAVE RL
		WHERE STUDENT_TEACHER='STUDENT' AND REQ_BY_ID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)
		AND CONVERT(DATE,FROMDT,105) BETWEEN '$txtFromDT' AND '$txtToDT'  
		AND CONVERT(DATE,TODT,105) BETWEEN '$txtFromDT' AND '$txtToDT'  
		ORDER BY CONVERT(DATE,FROMDT,105) DESC,CONVERT(DATE,TODT,105) DESC,STUDENTNAME ASC";
		$data['$queryLeave']=$queryLeave;
		// $data['success'] = false;
		// echo json_encode($data);exit;
		$countLeave = unique($queryLeave);
		if($countLeave>0){
			$resultLeave = sqlsrv_query($mysqli, $queryLeave);
			set_time_limit(0);
			while ($rowLeave = sqlsrv_fetch_array($resultLeave,SQLSRV_FETCH_ASSOC)) {
				$data['data_leave'][]=$rowLeave;
			}
			$data['successLeave'] = true;
		}else{
			$data['successLeave'] = false;
			$data['messageLeave'] = 'Leave Data not found.';
		}
		


		// #################
		// GET DISCONTINUE
		// #################
		// --AND REGID IN ($REGIDS_ARRAY)
		$queryDisc = "SELECT REGDID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)STUDENTNAME,
		CONVERT(VARCHAR,DISCONTINUE_REQ_DATE,106)DISCONTINUE_REQ_DATE,DISCONTINUE_REMARK,DISCONTINUE_APPROVED,
		CONVERT(VARCHAR,DISCONTINUE_REQ_APPR_DATE,106)DISCONTINUE_REQ_APPR_DATE,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RD.DISCONTINUE_APPROVE_BY_ID)DISCONTINUE_APPROVE_BY
		FROM REGISTRATION_DETAILS RD
		WHERE DISCONTINUE_REQ=1 AND CANCELLED=0 AND DISCONTINUE_REQ=1
		AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)
		AND CONVERT(DATE,DISCONTINUE_REQ_DATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";
		$data['$queryDisc']=$queryDisc;
		// $data['success'] = false;
		// echo json_encode($data);exit;
		$countDisc = unique($queryDisc);
		if($countDisc>0){
			$resultDisc = sqlsrv_query($mysqli, $queryDisc);
			set_time_limit(0);
			while ($rowDisc = sqlsrv_fetch_array($resultDisc,SQLSRV_FETCH_ASSOC)) {
				$data['data_discontinue'][]=$rowDisc;
			}
			$data['successDisc'] = true;
		}else{
			$data['successDisc'] = false;
			$data['messageDisc'] = 'Discontinue Data not found.';
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
/*============ Get Student Test Report =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







