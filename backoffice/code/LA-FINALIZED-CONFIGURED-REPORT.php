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
		$ddlReportType = ($_POST['ddlReportType'] == 'undefined' || $_POST['ddlReportType'] == '' ) ? '' : $_POST['ddlReportType'];
		if($ddlReportType == '') throw new Exception('Error : Invalid Report Type.');

		$query = "SELECT SLIDEID,TOPICID,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=(SELECT UNDERTOPICID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) UNDERTOPIC,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) TOPIC,
		(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=(SELECT GRADEID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) GRADE,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=(SELECT SUBID FROM LA_TOPICS_MASTER WHERE TOPICID=M.TOPICID) ) SUBJECT,
		SLIDEHEADING,CONVERT(VARCHAR,FINALIZED_DT,106)FINALIZED_DT,CONVERT(VARCHAR,CONFIGURED_DT,106)CONFIGURED_DT
		FROM LA_TOPIC_SLIDE_MASTER M WHERE ISDELETED=0
		AND TOPICID NOT IN (SELECT TOPICID FROM LA_TOPICS_MASTER WHERE ISDELETED=1) ";

		if($ddlReportType=='FINALIZED') $query .=" AND FINALIZED_DT IS NOT NULL";
		if($ddlReportType=='NOT FINALIZED') $query .=" AND (FINALIZED_DT='' OR FINALIZED_DT IS NULL OR CONVERT(DATE,FINALIZED_DT,105)='01-01-1900')";
		if($ddlReportType=='CONFIGURED') $query .=" AND CONFIGURED_DT IS NOT NULL";
		if($ddlReportType=='NOT CONFIGURED') $query .=" AND (CONFIGURED_DT='' OR CONFIGURED_DT IS NULL OR CONVERT(DATE,CONFIGURED_DT,105)='01-01-1900')";

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







