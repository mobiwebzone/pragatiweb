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
        case "save":save($conn);break;
        case "hideTopic":hideTopic($conn);break;
        case "getTopics":getTopics($conn);break;
        case "getUnderTopics":getUnderTopics($conn);break;
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


 function save($mysqli){
     try
     {
		global $userid;
		$data = array();

		$topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$txtSeqNo = ($_POST['txtSeqNo'] == 'undefined' || $_POST['txtSeqNo'] == '') ? 0 : $_POST['txtSeqNo'];
		$txtTopic = ($_POST['txtTopic'] == 'undefined' || $_POST['txtTopic'] == '') ? '' : $_POST['txtTopic'];
		$ddlUnderTopic = ($_POST['ddlUnderTopic'] == 'undefined' || $_POST['ddlUnderTopic'] == '') ? 0 : $_POST['ddlUnderTopic'];
		$showAssignment = ($_POST['showAssignment'] == 'undefined' || $_POST['showAssignment'] == '' || $_POST['showAssignment'] == '0') ? 0 : 1;

		// $data['$ddlLocation'] = $ddlLocation;
		// echo json_encode($data);exit;
		
		$actionid = $topicid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlGrade == 0)throw new Exception("Please Select Grade.");
		if($ddlSubject == 0)throw new Exception("Please Select Subject Name.");
		if($txtSeqNo == 0)throw new Exception("Please Enter SEQ No.");
		if($txtTopic == '')throw new Exception("Please Enter Topic.");
		
		$sql = "SELECT * FROM LA_TOPICS_MASTER WHERE LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
				AND SEQNO=$txtSeqNo AND TOPIC='$txtTopic' AND UNDERTOPICID=$ddlUnderTopic AND TOPICID!=$topicid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [LA_TOPICS_MASTER_SP] $actionid,$topicid,$ddlLocation,$ddlGrade,$ddlSubject,$txtSeqNo,'$txtTopic',$ddlUnderTopic,$showAssignment,$userid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($topicid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Topic already exists.';
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


/*============ HIDE TOPIC =============*/ 
function hideTopic($mysqli){
	try
	{
		global $userid;
		$data = array();

		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$VAL = ($_POST['VAL'] == 'undefined' || $_POST['VAL'] == '' || $_POST['VAL'] == 0) ? 0 : 1;

		
		if($TOPICID == 0)throw new Exception("Invalid TOPICID.");
		if($LOCID == 0)throw new Exception("Please Select Location.");

		$query="EXEC [LA_HIDE_TOPICS_LOCATIONWISE_SP] $LOCID, $TOPICID, $VAL, $userid";				
		   $data['$query'] = $query;
		   // echo json_encode($data);exit;
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['query'] = $query;
		   }
		   else
		   {
			   $data['query'] = $query;
			   $data['success'] = true;
			   if($VAL==0)$data['message'] = 'Topic show successfully';
			   else $data['message'] = 'Topic hide successfully.';
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

/*============ GET TOPICS =============*/ 
 function getTopics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$USERLOCID = ($_POST['USERLOCID'] =='undefined' || $_POST['USERLOCID'] =='') ? 0 : $_POST['USERLOCID'];
		$ddlGrade = ($_POST['ddlGrade'] =='undefined' || $_POST['ddlGrade'] =='') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] =='undefined' || $_POST['ddlSubject'] =='') ? 0 : $_POST['ddlSubject'];

		$query = "SELECT TOPICID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TM.LOCID)[LOCATION],
				GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=TM.GRADEID)GRADE,
				SUBID,(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=TM.SUBID)SUBJECTNAME,SEQNO,TOPIC,
				UNDERTOPICID,ISNULL((SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID),'-')UNDERTOPIC,
				SHOW_ASSIGNMENT,
				ISNULL((SELECT HIDE FROM LA_HIDE_TOPICS_LOCATIONWISE WHERE LOCID=$USERLOCID AND TOPICID=TM.TOPICID),0) HIDE_TOPIC
				FROM LA_TOPICS_MASTER TM 
				WHERE ISDELETED=0 AND LOCID=$ddlLocation";
		// if($ddlLocation>0)$query .= " AND LOCID=$ddlLocation";
		if($ddlGrade>0)$query .= " AND GRADEID=$ddlGrade";
		if($ddlSubject>0)$query .= " AND SUBID=$ddlSubject";
		// $query .= " ORDER BY [LOCATION],TOPIC";
		$query .= " ORDER BY SEQNO,TOPIC,UNDERTOPIC";
		$data['$query']=$query;
		// echo json_encode($data);exit;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TOPICID'] = (int) $row['TOPICID'];
				$row['SEQNO'] = (int) $row['SEQNO'];
				$row['SHOW_ASSIGNMENT'] = (string) $row['SHOW_ASSIGNMENT'];
				$row['HIDE_TOPIC'] = (string) $row['HIDE_TOPIC'];
				$row['GRADE_OR'] = is_numeric($row['GRADE']) ? (int)$row['GRADE'] : $row['GRADE'];
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
/*============ GET TOPICS =============*/ 


/*============ GET UNDER TOPIC =============*/ 
 function getUnderTopics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] =='undefined' || $_POST['ddlGrade'] =='') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] =='undefined' || $_POST['ddlSubject'] =='') ? 0 : $_POST['ddlSubject'];

		$query = "SELECT TOPICID,TOPIC FROM LA_TOPICS_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject 
				  ORDER BY TOPIC";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TOPICID'] = (int) $row['TOPICID'];
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
/*============ GET UNDER TOPIC =============*/ 


/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];  
		$query = "EXEC [LA_TOPICS_MASTER_SP] 3,$TOPICID,0,0,0,0,'',0,0,$userid";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
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
/* =========== DELETE =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







