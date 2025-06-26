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
        case "getTutoringRequests":getTutoringRequests($conn);break;
        case "delete":delete($conn);break;

        case "saveDet":saveDet($conn);break;
        case "getTutoringReqDetails":getTutoringReqDetails($conn);break;
        case "deleteDet":deleteDet($conn);break;
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
		
        $reqid  = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];
        $ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
        $txtReqDate  = ($_POST['txtReqDate'] == 'undefined' || $_POST['txtReqDate'] == '') ? '' : $_POST['txtReqDate'];
        $subjectIDs  = ($_POST['subjectIDs'] == 'undefined' || $_POST['subjectIDs'] == '') ? array() : explode(',',$_POST['subjectIDs']);
        $ddlPriority  = ($_POST['ddlPriority'] == 'undefined' || $_POST['ddlPriority'] == '') ? 1 : $_POST['ddlPriority'];
        $txtStartDate  = ($_POST['txtStartDate'] == 'undefined' || $_POST['txtStartDate'] == '') ? '' : $_POST['txtStartDate'];
        $txtEndDate  = ($_POST['txtEndDate'] == 'undefined' || $_POST['txtEndDate'] == '') ? '' : $_POST['txtEndDate'];
        $txtComments  = ($_POST['txtComments'] == 'undefined' || $_POST['txtComments'] == '') ? '' : $_POST['txtComments'];
        $ddlStatus  = ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? '' : $_POST['ddlStatus'];
		
		$data['subjectIDs'] = $subjectIDs;
		// echo json_encode($data);exit;
		
		$actionid = $reqid == 0 ? 1 : 2;
		
		if($ddlStudent == 0)throw new Exception("Please Select Student Name.");
		if($txtReqDate == '')throw new Exception("Please Select Request Date.");
		if(count($subjectIDs) == 0 && $actionid==1)throw new Exception("Please Select Subject Name.");
		if($txtStartDate == '')throw new Exception("Please Select Start Date.");
		if($ddlStatus == '')throw new Exception("Please Select Status.");
		
		// $sql = "SELECT * FROM STUDENT_TUTORING_REQUEST WHERE REGID=$ddlStudent AND CONVERT(DATE,REQDATE,105)='$txtReqDate' AND REQPRIORITY=$ddlPriority AND REQID!=$reqid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		// if($row_count == 0)
		// {
			if($actionid==1){
				for($i=0;$i<count($subjectIDs);$i++){
					$CSUBID = $subjectIDs[$i];
					$sql = "SELECT * FROM STUDENT_TUTORING_REQUEST WHERE REGID=$ddlStudent AND CONVERT(DATE,REQDATE,105)='$txtReqDate' AND CSUBID=$CSUBID AND 
							REQPRIORITY=$ddlPriority AND REQSTATUS='$ddlStatus' AND REQID!=$reqid AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count==0){
						if($CSUBID>0){
							$query="EXEC [STUDENT_TUTORING_REQUEST_SP] $actionid,$reqid,$ddlStudent,'$txtReqDate',$CSUBID,$ddlPriority,'$txtStartDate','$txtEndDate','$txtComments','$ddlStatus',$userid";
							$data['query'][] = $query;
							$stmt=sqlsrv_query($mysqli, $query);
							if($stmt === false)
							{
								// die( print_r( sqlsrv_errors(), true));
								// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
								$data['success'] = false;
								echo json_encode($data);exit;
							}else{
								$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
								$data['REQID'] = $row['REQID'];
								
							}
						}
					}
		
				}
			}else{
				$query="EXEC [STUDENT_TUTORING_REQUEST_SP] $actionid,$reqid,$ddlStudent,'$txtReqDate',0,$ddlPriority,'$txtStartDate','$txtEndDate','$txtComments','$ddlStatus',$userid";
				$data['query'] = $query;
				$stmt=sqlsrv_query($mysqli, $query);
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					echo json_encode($data);exit;
				}else{
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$data['REQID'] = $row['REQID'];
					
				}
			}


			$data['success'] = true;
			if(!empty($tsubid))$data['message'] = 'Record successfully updated.';
			else $data['message'] = 'Record successfully inserted.';
			
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists.';
		// }
		echo json_encode($data);exit;
		
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


/*============ GET TUTORING REQUESTS =============*/ 
 function getTutoringRequests($mysqli){
	try
	{
		$data = array();
		$ddlStudent = ($_POST['ddlStudent'] =='undefined' || $_POST['ddlStudent'] =='') ? 0 : $_POST['ddlStudent'];
		$query = "SELECT REQID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
		FORMAT(REQDATE,'MMddyyyyhm')REQDATE_ORDERBY,
		CONVERT(VARCHAR,REQDATE,106)REQDATE,CSUBID,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)[SUBJECT],
		REQPRIORITY,
		CONVERT(VARCHAR,STARTDATE,106)STARTDATE,FORMAT(STARTDATE,'MMddyyyyhm')STARTDATE_ORDERBY,
		CASE WHEN FORMAT(ENDDATE,'yyyy')='1900' THEN '' ELSE CONVERT(VARCHAR,ENDDATE,106) END ENDDATE,
		FORMAT(ENDDATE,'MMddyyyyhm')ENDDATE_ORDERBY,
		COMMENTS,REQSTATUS 
		FROM STUDENT_TUTORING_REQUEST ST
		WHERE ISDELETED=0 AND REGID=$ddlStudent ORDER BY CONVERT(DATE,REQDATE,105)";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
		$REQID = ($_POST['REQID'] == 'undefined' || $_POST['REQID'] == '') ? 0 : $_POST['REQID']; 
		if($REQID==0)throw new Exception('REQID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_REQUEST_SP] 3,$REQID,0,'',0,0,'','','','',$userid");
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







 function saveDet($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		
        $reqdetid  = ($_POST['reqdetid'] == 'undefined' || $_POST['reqdetid'] == '') ? 0 : $_POST['reqdetid'];
        $reqid  = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];
        $ddlDay  = ($_POST['ddlDay'] == 'undefined' || $_POST['ddlDay'] == '') ? '' : $_POST['ddlDay'];
        $txtDayHours  = ($_POST['txtDayHours'] == 'undefined' || $_POST['txtDayHours'] == '') ? 0 : $_POST['txtDayHours'];
        $txtFromTime  = ($_POST['txtFromTime'] == 'undefined' || $_POST['txtFromTime'] == '') ? '' : $_POST['txtFromTime'];
        $txtToTime  = ($_POST['txtToTime'] == 'undefined' || $_POST['txtToTime'] == '') ? '' : $_POST['txtToTime'];
		
		$actionid = $reqdetid == 0 ? 1 : 2;
		
		if($reqid == 0)throw new Exception("Error : Invalid REQID.");
		if($ddlDay == '')throw new Exception("Please Select Day Name.");
		if($txtDayHours == 0)throw new Exception("Please Select Hours.");
		
		$sql = "SELECT * FROM STUDENT_TUTORING_REQUEST_DETAILS WHERE REQID=$reqid AND DYNAME='$ddlDay' AND DYHOURS=$txtDayHours AND REQDETID!=$reqdetid AND ISDELETED=0";
		$row_count = unique($sql);
		if($row_count == 0)
		{
			$query="EXEC [STUDENT_TUTORING_REQUEST_DETAILS_SP] $actionid,$reqdetid,$reqid,'$ddlDay',$txtDayHours,'$txtFromTime','$txtToTime',$userid";
			$data['query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				echo json_encode($data);exit;
			}else{
				$data['success'] = true;
				if(!empty($reqdetid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
		}
		echo json_encode($data);exit;
		
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


/*============ GET TUTORING REQUEST DETAILS =============*/ 
 function getTutoringReqDetails($mysqli){
	try
	{
		$data = array();
		$reqid = ($_POST['reqid'] =='undefined' || $_POST['reqid'] =='') ? 0 : $_POST['reqid'];
		$query = "SELECT REQDETID,REQID,DYNAME,DYHOURS,
		CASE WHEN FROMTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,FROMTIME,100) END FROMTIME,
		CONVERT(VARCHAR,FROMTIME,21)FROMTIME_SET,
		CASE WHEN TOTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,TOTIME,100) END TOTIME,
		CONVERT(VARCHAR,TOTIME,21)TOTIME_SET
		FROM STUDENT_TUTORING_REQUEST_DETAILS ST WHERE ISDELETED=0 AND REQID=$reqid ORDER BY DYNAME";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
function deleteDet($mysqli){
	try{   
		global $userid;
		$data = array();     
		$REQDETID = ($_POST['REQDETID'] == 'undefined' || $_POST['REQDETID'] == '') ? 0 : $_POST['REQDETID']; 
		if($REQDETID==0)throw new Exception('REQDETID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_REQUEST_DETAILS_SP] 3,$REQDETID,0,'',0,'','',$userid");
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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







