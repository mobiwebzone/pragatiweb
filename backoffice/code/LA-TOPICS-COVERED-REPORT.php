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
		//######## MASTER
        case "getReport":getReport($conn);break;
		
		//######## EXTRA DATA TABLE
		case "getTopics":getTopics($conn);break;

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET TEST MASTERS =========== */
 function getReport($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$batches = ($_POST['batches'] == 'undefined' || $_POST['batches'] == '') ? '' : $_POST['batches'];
		$chkAttended = ($_POST['chkAttended'] == 'undefined' || $_POST['chkAttended'] == '' || $_POST['chkAttended'] == '0') ? 0 : 1;

		if($batches == '') throw new Exception('Please Select Batches First.');

		$query = "EXEC [LA_TOPICS_ATTENDED_REPORT]'$txtFromDT','$txtToDT',$chkAttended,$ddlGrade,$ddlSubject,'$batches'";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ATTENDED_BY'] = rtrim($row['ATTENDED_BY'],',');
				$data['dataByTopic'][] = $row;
			}
		}
		
		 sqlsrv_next_result($result);
		if(sqlsrv_has_rows($result) !== false){
			while ($row2 = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {			
				$data['dataByStudent'][] = $row2;
			}
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
/* ========== GET TEST MASTERS =========== */






/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 


/*============ GET TOPICS =============*/ 
function getTopics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');
		if($ddlGrade == 0) throw new Exception('Select Grade First.');
		if($ddlSubject == 0) throw new Exception('Select Subject First.');

		$query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID 
				FROM LA_TOPICS_MASTER TM 
				WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
				ORDER BY UNDER,TOPIC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Topic not found.';
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
/*============ GET TOPICS =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







