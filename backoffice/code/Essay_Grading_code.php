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
        case "SaveEssay":SaveEssay($conn);break;
        case "saveGrading":saveGrading($conn);break;
        case "getGradingData":getGradingData($conn);break;
        case "getStudentEssay":getStudentEssay($conn);break;
        case "getRubericData":getRubericData($conn);break;
        case "delete":delete($conn);break;
        case "DeleteEssay":DeleteEssay($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



// =============== SAVE ESSAY ==============
 function SaveEssay($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $STESSID=($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
		$txtEssay=($_POST['txtEssay'] == 'undefined' || $_POST['txtEssay'] == '') ? '' : $_POST['txtEssay'];
		$total_words=($_POST['total_words'] == 'undefined' || $_POST['total_words'] == '') ? 0 : $_POST['total_words'];
		$total_chars=($_POST['total_chars'] == 'undefined' || $_POST['total_chars'] == '') ? 0 : $_POST['total_chars'];

		if($STESSID == 0){throw new Exception("STESSID Error.");}
		$txtEssay=str_replace("'", "''", $txtEssay);

		$query="EXEC [STUDENT_ESSAYS_SP] 2,$STESSID,0,0,0,'$txtEssay',$total_words,$total_chars,$userid";
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
			$data['message'] = 'Essay successfully updated.';
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
// =============== SAVE ESSAY ==============





// =============== SAVE GRADING ==================
 function saveGrading($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $egid  = ($_POST['egid'] == 'undefined' || $_POST['egid'] == '') ? 0 : $_POST['egid'];
        $RMID  = ($_POST['RMID'] == 'undefined' || $_POST['RMID'] == '') ? 0 : $_POST['RMID'];
        $STESSID  = ($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
        $REGID  = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
        $txtScore  = ($_POST['txtScore'] == 'undefined' || $_POST['txtScore'] == '') ? 0 : $_POST['txtScore'];
        $txtComment  = $_POST['txtComment'] == 'undefined' ? '' : $_POST['txtComment'];
		$actionid = $egid == 0 ? 1 : 2;

		if($RMID == 0){throw new Exception("Please Select 'Criteria'.");}
		// if($txtScore == 0){throw new Exception("Please Enter 'Score'.");}
		if($txtComment == ''){throw new Exception("Please Enter 'Comment'.");}

		$sql = "SELECT * FROM ESSAY_GRADING WHERE STESSID=$STESSID AND REGID=$REGID AND RMID=$RMID AND SCORE=$txtScore AND EGID!=$egid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ESSAY_GRADING_SP] $actionid,$egid,$STESSID,$REGID,$RMID,$txtScore,'$txtComment',$userid";
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
				if(!empty($egid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
 // =============== SAVE GRADING ==============





/*============ GET GRADING DATA =============*/ 
 function getGradingData($mysqli){
	try
	{
		$data = array();
		$STESSID = ($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
		if($STESSID == 0)throw new Exception('STESSID Missing.');
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('REGID Missing.');

		$query = "SELECT EGID,RMID,
		(SELECT CRITERIA FROM RUBERIC_MASTER WHERE RMID=EG.RMID)CRITERIA,
		(SELECT ALLOTEDMARKS FROM RUBERIC_MASTER WHERE RMID=EG.RMID)ALLOTEDMARKS,
		SCORE,REMARK 
		FROM ESSAY_GRADING EG WHERE ISDELETED=0 AND STESSID=$STESSID AND REGID=$REGID";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ALLOTEDMARKS'] = (int)$row['ALLOTEDMARKS'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Grading Data not found.';
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
/*============ GET GRADING DATA =============*/ 





/*============ GET RUBERIC DATA =============*/ 
 function getRubericData($mysqli){
	try
	{
		$data = array();
		$TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
		if($TESTID == 0)throw new Exception('TestId Missing.');

		$query = "SELECT RMID,CRITERIA,ALLOTEDMARKS FROM RUBERIC_MASTER RM 
		WHERE ISDELETED=0 AND TESTID=$TESTID ORDER BY CRITERIA";
		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ALLOTEDMARKS'] = (int)$row['ALLOTEDMARKS'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Ruberic not found.';
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
/*============ GET RUBERIC DATA =============*/ 





/*============ GET STUDENT ESSAY =============*/ 
function getStudentEssay($mysqli){
	try
	{
		$data = array();
		$ESSID = ($_POST['ESSID'] == 'undefined' || $_POST['ESSID'] == '') ? 0 : $_POST['ESSID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($ESSID == 0)throw new Exception('Error : Select Essay First.');
		if($LOCID == 0)throw new Exception('Error : Select Location First.');

		$query = "SELECT STESSID,ESSID,
		(SELECT LIMITON FROM ESSAYS WHERE ESSID=SE.ESSID)LIMITON,
		(SELECT LIMIT FROM ESSAYS WHERE ESSID=SE.ESSID)LIMIT,
		(SELECT TIMEALLOWED FROM ESSAYS WHERE ESSID=SE.ESSID)TIMEALLOWED,
		TSECID,ESSAY,TOTAL_WORD,TOTAL_CHAR,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME,
		(SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=SE.INSERTID)STUDENT,INSERTID,
		CONVERT(VARCHAR(8),STARTDATETIME,8)NEW_STARTTIME,
		CONVERT(VARCHAR(8),ENDDATETIME,8)NEW_ENDTIME,TESTID
		FROM STUDENT_ESSAYS SE 
		WHERE ISDELETED=0 AND INSERTID>0 AND ESSID=$ESSID AND INSERTID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$LOCID) ORDER BY STUDENT";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['NEW_STARTTIME'] = date('h:i:s A', strtotime($row['NEW_STARTTIME']));
				$row['NEW_ENDTIME'] = date('h:i:s A', strtotime($row['NEW_ENDTIME']));
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
			$data['success'] = false;
		}
		$data['query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET STUDENT ESSAY =============*/ 





/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $egid = ($_POST['egid'] == 'undefined' || $_POST['egid'] == '') ? 0 : $_POST['egid'];
			if($egid == 0){throw new Exception('EGID Error.');}
			$delQuery = "EXEC [ESSAY_GRADING_SP] 3,$egid,0,0,0,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 




/* =========== Delete =========== */ 
function DeleteEssay($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STESSID = ($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
			if($STESSID == 0){throw new Exception('Invalid STESSID.');}
			$delQuery = "EXEC [STUDENT_ESSAYS_SP] 3,$STESSID,0,0,0,'',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







