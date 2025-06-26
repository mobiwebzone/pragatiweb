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
        case "saveSubject":saveSubject($conn);break;
        case "getTeacherSubjects":getTeacherSubjects($conn);break;
        case "deleteSubject":deleteSubject($conn);break;

        case "saveTiming":saveTiming($conn);break;
        case "getTeacherTiming":getTeacherTiming($conn);break;		
        case "deleteTiming":deleteTiming($conn);break;

		case "getTeacherFeedback":getTeacherFeedback($conn);break;
		case "updateFeedback":updateFeedback($conn);break;

		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveSubject($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		
        $tsubid  = ($_POST['tsubid'] == 'undefined' || $_POST['tsubid'] == '') ? 0 : $_POST['tsubid'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $subjectIDs  = ($_POST['subjectIDs'] == 'undefined' || $_POST['subjectIDs'] == '') ? array() : explode(',',$_POST['subjectIDs']);
        $txtRatePerHour  = ($_POST['txtRatePerHour'] == 'undefined' || $_POST['txtRatePerHour'] == '') ? 0 : $_POST['txtRatePerHour'];
        $txtTeacherShare  = ($_POST['txtTeacherShare'] == 'undefined' || $_POST['txtTeacherShare'] == '') ? 0 : $_POST['txtTeacherShare'];
        $txtMEPShare  = ($_POST['txtMEPShare'] == 'undefined' || $_POST['txtMEPShare'] == '') ? 0 : $_POST['txtMEPShare'];
		
		$data['subjectIDs'] = $subjectIDs;
		// echo json_encode($data);exit;
		
		$actionid = $tsubid == 0 ? 1 : 2;
		
		if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
		if(count($subjectIDs) == 0 && $actionid==1)throw new Exception("Please Select Subject Name.");
		
		// $sql = "SELECT * FROM TEACHER_SUBJECTS WHERE TEACHERID=$ddlTeacher AND TSUBID!=$tsubid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			if($actionid==1){
				for($i=0;$i<count($subjectIDs);$i++){
					$CSUBID = $subjectIDs[$i];

					$sql = "SELECT * FROM TEACHER_SUBJECTS WHERE TEACHERID=$ddlTeacher AND CSUBID=$CSUBID AND TSUBID!=$tsubid AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count == 0)
					{
						$query="EXEC [TEACHER_SUBJECTS_SP] $actionid,$tsubid,$ddlTeacher,$CSUBID,$txtRatePerHour,$txtTeacherShare,$txtMEPShare,$userid";
						$data['query'][] = $query;
						$stmt=sqlsrv_query($mysqli, $query);
						if($stmt === false)
						{
							// die( print_r( sqlsrv_errors(), true));
							// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
							$data['success'] = false;
							echo json_encode($data);exit;
						}
					}
				}
			}else{
				$query="EXEC [TEACHER_SUBJECTS_SP] $actionid,$tsubid,$ddlTeacher,0,$txtRatePerHour,$txtTeacherShare,$txtMEPShare,$userid";
				$data['query'][] = $query;
				$stmt=sqlsrv_query($mysqli, $query);
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					echo json_encode($data);exit;
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


/*============ GET TEACHER SUBJECTS =============*/ 
 function getTeacherSubjects($mysqli){
	try
	{
		$data = array();
		$ddlTeacher = ($_POST['ddlTeacher'] =='undefined' || $_POST['ddlTeacher'] =='') ? 0 : $_POST['ddlTeacher'];
		$query = "SELECT TSUBID,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TS.TEACHERID)TEACHER,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=TS.CSUBID)[SUBJECT],
		RATE_PER_HOUR,TEACHER_SHARE,MEP_SHARE 
		FROM TEACHER_SUBJECTS TS WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher ORDER BY [SUBJECT]";
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
function deleteSubject($mysqli){
	try{   
		global $userid;
		$data = array();     
		$TSUBID = ($_POST['TSUBID'] == 'undefined' || $_POST['TSUBID'] == '') ? 0 : $_POST['TSUBID']; 
		if($TSUBID==0)throw new Exception('TSUBID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_SUBJECTS_SP] 3,$TSUBID,0,0,0,0,0,$userid");
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







 function saveTiming($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		
        $tdtid  = ($_POST['tdtid'] == 'undefined' || $_POST['tdtid'] == '') ? 0 : $_POST['tdtid'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $ddlDay  = ($_POST['ddlDay'] == 'undefined' || $_POST['ddlDay'] == '') ? '' : $_POST['ddlDay'];
        $txtFromTime  = ($_POST['txtFromTime'] == 'undefined' || $_POST['txtFromTime'] == '') ? '' : $_POST['txtFromTime'];
        $txtToTime  = ($_POST['txtToTime'] == 'undefined' || $_POST['txtToTime'] == '') ? '' : $_POST['txtToTime'];
		
		$actionid = $tdtid == 0 ? 1 : 2;
		
		if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
		if($ddlDay == '')throw new Exception("Please Select Day Name.");
		
		$sql = "SELECT * FROM TEACHER_DAYS_TIMINGS WHERE TEACHERID=$ddlTeacher AND DYNAME='$ddlDay' AND TDTID!=$tdtid AND ISDELETED=0";
		$row_count = unique($sql);
		if($row_count == 0)
		{
			$query="EXEC [TEACHER_DAYS_TIMINGS_SP] $actionid,$tdtid,$ddlTeacher,'$ddlDay','$txtFromTime','$txtToTime',$userid";
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
				if(!empty($tdtid))$data['message'] = 'Record successfully updated.';
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


/*============ GET TEACHER TIMING =============*/ 
 function getTeacherTiming($mysqli){
	try
	{
		$data = array();
		$ddlTeacher = ($_POST['ddlTeacher'] =='undefined' || $_POST['ddlTeacher'] =='') ? 0 : $_POST['ddlTeacher'];
		$query = "SELECT TDTID,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TD.TEACHERID)TEACHER,DYNAME,
		CASE WHEN FROMTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,FROMTIME,100) END FROMTIME,
		CONVERT(VARCHAR,FROMTIME,21)FROMTIME_SET,
		CASE WHEN TOTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,TOTIME,100) END TOTIME,
		CONVERT(VARCHAR,TOTIME,21)TOTIME_SET
		FROM TEACHER_DAYS_TIMINGS TD WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher ORDER BY DYNAME";
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
function deleteTiming($mysqli){
	try{   
		global $userid;
		$data = array();     
		$TDTID = ($_POST['TDTID'] == 'undefined' || $_POST['TDTID'] == '') ? 0 : $_POST['TDTID']; 
		if($TDTID==0)throw new Exception('TDTID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_DAYS_TIMINGS_SP] 3,$TDTID,0,'','','',$userid");
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


/*============ GET TEACHER FEEDBACK =============*/ 
function getTeacherFeedback($mysqli){
	try
	{
		$data = array();
		$ddlTeacher = ($_POST['ddlTeacher'] =='undefined' || $_POST['ddlTeacher'] =='') ? 0 : $_POST['ddlTeacher'];
		$query = "SELECT CONVERT(VARCHAR,FBDATE,106)FBDATE, RATING, COMMENT FROM  TEACHER_FEEDBACK  WHERE TEACHERID=$ddlTeacher";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			$row = sqlsrv_fetch_array($result);
			$data['data'] = $row;
			
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


/*============ UPDATE TEACHER FEEDBACK =============*/ 
function updateFeedback($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];	   	   
	   $txtRating  = ($_POST['txtRating'] == 'undefined' || $_POST['txtRating'] == '') ? 0 : $_POST['txtRating'];
	   $txtComment  = ($_POST['txtComment'] == 'undefined' || $_POST['txtComment'] == '') ? '' : $_POST['txtComment'];
	   
	   	   
	   if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
	   
	   
		$query="EXEC [TEACHER_FEEDBACK_SP] $ddlTeacher,$txtRating,'$txtComment',$userid";
		$data['query'][] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}
		else{
		   $data['success'] = true;
		   $data['message'] = 'Feedback successfully updated.';		   
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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







