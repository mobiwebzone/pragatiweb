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


/*============ Get Report =============*/ 
function getReport($conn){
	try
	{
		global $userid;
		$data = array();
		$Status = ($_POST['ddlReportStatus'] == 'undefined' || $_POST['ddlReportStatus'] == '') ? '' : $_POST['ddlReportStatus'];
		if($Status == '') throw new Exception("Invalid Report Status.");

		$issue_remark = ($Status=='OPENED' || $Status=='CLOSED') ? 'ISSUE_REMARKS' : (($Status=='OPENED_ST' || $Status=='CLOSED_ST') ? 'ISSUE_REMARKS_STUDENT' : (($Status=='OPENED_TH' || $Status=='CLOSED_TH') ? 'ISSUE_REMARKS_TEACHER' : ''));
		if($issue_remark=='') throw new Exception('Invalid Report Status.');

		$query = "SELECT SLIDEID,TOPICID,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=(SELECT UNDERTOPICID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) UNDERTOPIC,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) TOPIC,
		(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=(SELECT GRADEID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) GRADE,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=(SELECT SUBID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) SUBJECT,
		SLIDEHEADING,$issue_remark AS ISSUE_REMARKS
		FROM LA_TOPIC_SLIDE_MASTER M WHERE ISDELETED=0";
		if($Status=='OPENED') $query.=" AND OPEN_ISSUE=1";
		if($Status=='CLOSED') $query.=" AND OPEN_ISSUE=0 AND ISSUE_REMARKS<>'' AND ISSUE_REMARKS IS NOT NULL";
		if($Status=='OPENED_ST') $query.=" AND OPEN_ISSUE_STUDENT=1";
		if($Status=='CLOSED_ST') $query.=" AND OPEN_ISSUE_STUDENT=0 AND ISSUE_REMARKS_STUDENT<>'' AND ISSUE_REMARKS_STUDENT IS NOT NULL";
		if($Status=='OPENED_TH') $query.=" AND OPEN_ISSUE_TEACHER=1";
		if($Status=='CLOSED_TH') $query.=" AND OPEN_ISSUE_TEACHER=0 AND ISSUE_REMARKS_TEACHER<>'' AND ISSUE_REMARKS_TEACHER IS NOT NULL";
		
		$data['$query ']=$query;
		// echo json_encode($data);exit;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($conn, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Report =============*/



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







