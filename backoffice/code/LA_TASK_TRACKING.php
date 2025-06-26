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
		case "getTaskTracking":getTaskTracking($conn);break;
        case "getTaskCategory":getTaskCategory($conn);break;
		case "getAssignedTo":getAssignedTo($conn);break;
		case "getGrade":getGrade($conn);break;
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
		$data = array();
        global $userid;
		
		$data = array();
        $TTID  = ($_POST['TTID'] == 'undefined' || $_POST['TTID'] == '') ? 0 : $_POST['TTID'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlTask_Category  = ($_POST['ddlTask_Category'] == 'undefined' || $_POST['ddlTask_Category'] == '') ? '' : $_POST['ddlTask_Category'];
        $txtTaskDT  = ($_POST['txtTaskDT'] == 'undefined' || $_POST['txtTaskDT'] == '') ? '' : $_POST['txtTaskDT'];
        $txtAssignedTo  = ($_POST['txtAssignedTo'] == 'undefined' || $_POST['txtAssignedTo'] == '') ? '' : $_POST['txtAssignedTo'];
        $ddlGrade  = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? '' : $_POST['ddlGrade'];
        $ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? '' : $_POST['ddlSubject'];
        $txtTask  = ($_POST['txtTask'] == 'undefined' || $_POST['txtTask'] == '') ? '' : $_POST['txtTask'];
        $txtUploaddate  = ($_POST['txtUploaddate'] == 'undefined' || $_POST['txtUploaddate'] == '') ? '' : $_POST['txtUploaddate'];
        $txtlink  = ($_POST['txtlink'] == 'undefined' || $_POST['txtlink'] == '') ? '' : $_POST['txtlink'];
      



		
		$actionid = $TTID == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlTask_Category == '')throw new Exception("Please select Category.");
		
		$sql = "SELECT * FROM TASK_TRACKING WHERE LOCID=$ddlLocation AND TASKCATID=$ddlTask_Category AND ASSIGNEDTO_ID='$txtAssignedTo' AND GRADEID=$ddlGrade 
		 AND CSUBID=$ddlSubject AND  TASK='$txtTask' AND TTID!=$TTID AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [TASK_TRACKING_SP] $actionid,$TTID,'$txtTaskDT',$ddlLocation,$ddlTask_Category,$txtAssignedTo,$ddlGrade,
			$ddlSubject,'$txtTask','$txtlink','$txtUploaddate','','','','','','','','','','OPEN',$userid";
			// $data['$query'] = $query;
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
				if(!empty($TTID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Data already exists.';
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



/*============ GET GRADE =============*/ 
 function getTaskCategory($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT TASKCATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKCAT 
				FROM TASK_CATEGORIES GM 
				WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY [LOCATION],LEN(TASKCAT),TASKCAT";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			
			$idx=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKCATID'] = (int) $row['TASKCATID'];
				$data['data'][] = $row;

				$idx++;
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
/*============ GET GRADE =============*/ 



/*============ Get Assigned TO  =============*/ 
function getAssignedTo($mysqli){
	try
	{
		$data = array();
		$ddlTask_Category = ($_POST['ddlTask_Category'] =='undefined' || $_POST['ddlTask_Category'] =='') ? '' : $_POST['ddlTask_Category'];
		
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
	
		$query = "SELECT ASSIGNEDTO_ID,
		CASE WHEN ASSIGNEDTO='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.ASSIGNEDTO_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SR.ASSIGNEDTO_ID) 
		END ASSIGNEDTO_NAME
		FROM TASK_USERS SR WHERE ISDELETED=0 AND TASKCATID=$ddlTask_Category AND LOCID=$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
		while ($row = sqlsrv_fetch_array($result)) {
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


/*============ Get Task Tracking =============*/ 
function getTaskTracking($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT TTID,CONVERT(varchar,TTDATE,(106))TTDATE,
		LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TT.LOCID)LOCATIONS,
		TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=TT.TASKCATID)TASKCAT,
		(SELECT TASKMAINCATID FROM TASK_CATEGORIES WHERE TASKCATID=TT.TASKCATID)TASKMAINCATID,
		ASSIGNEDTO_ID,
		CASE WHEN ASSIGNEDTO_ID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO='STUDENTS' AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID) 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
		END ASSIGNEDTO_NAME ,
		GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=TT.GRADEID)GRADE,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=TT.CSUBID)SUBJECT,
		TASK,TASKFILE,CONVERT(varchar,TASKUPLOADEDON,(106))TASKUPLOADEDON,TASKSTATUS
		FROM TASK_TRACKING TT  WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY  CONVERT(DATE,TTDATE,(106))  DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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

/*============ Get Grade =============*/ 
function getGrade($mysqli){
	try
	{
		$data = array();
		
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
	
		$query = "SELECT GRADEID,LOCID,GRADE
				FROM LA_GRADE_MASTER  WHERE ISDELETED=0  AND LOCID=1";

		$result = sqlsrv_query($mysqli, $query);
		$data['query'] = $query;

		// echo json_encode($data);
		// exit;
		while ($row = sqlsrv_fetch_array($result)) {
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


/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TTID = ($_POST['TTID'] == 'undefined' || $_POST['TTID'] == '') ? 0 : $_POST['TTID'];  
			$query = "EXEC [TASK_TRACKING_SP] 3,$TTID,'',0,0,0,0,
			0,'','','','','','','','','','','','','',$userid";
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







