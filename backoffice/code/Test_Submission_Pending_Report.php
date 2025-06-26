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
        case "getTestSubmissionPending":getTestSubmissionPending($conn);break;
        case "TestSubmit":TestSubmit($conn);break;
        case "DeleteTest":DeleteTest($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET Student Submission Pendings =========== */
 function getTestSubmissionPending($mysqli){
	try
	{
		$data = array();
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

		// if($txtFromDT == '' || $txtToDT == ''){
		// 	throw new Exception('Select date first.');	
		// }

		$query = "SELECT STID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,TESTID,
		(SELECT TESTDESC+' ('+CONVERT(VARCHAR,TESTYEAR)+')' FROM TEST_MASTER WHERE TESTID=ST.TESTID)TESTDESC,TSECID,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID)TESTSECTION,
		ATTEMPT,CONVERT(VARCHAR,TESTDATE,20)TESTDATE,CONVERT(VARCHAR,STARTDATETIME,22)STARTDATETIME,
		CONVERT(VARCHAR,ENDDATETIME,22)ENDDATETIME,TGID,QUESTIONS,SCORE,
		(SELECT COUNT(*) FROM STUDENT_TEST_RESULTS WHERE RESULT=1 AND ISDELETED=0 AND STID=ST.STID)CORRECT_SCORE_COUNT,
		SCALE 
		FROM STUDENT_TESTS ST
		WHERE ISDELETED=0 AND ENDDATETIME IS NULL AND REGID>0
		ORDER BY STUDENTNAME";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$date = date_create($row['TESTDATE']);
				$row['TESTDATE'] = date_format($date,"d M Y");
				$row['TESTDATE_ORDERBY'] = date_format($date,"mdYhm");
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
/* ========== GET Student Submission Pendings =========== */





// =============== TEST SUBMIT ==================
function TestSubmit($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $STID  = ($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];
	   $CORRECT_SCORE_COUNT  = ($_POST['CORRECT_SCORE_COUNT'] == 'undefined' || $_POST['CORRECT_SCORE_COUNT'] == '') ? 0 : $_POST['CORRECT_SCORE_COUNT'];
	   $TGID  = ($_POST['TGID'] == 'undefined' || $_POST['TGID'] == '') ? 0 : $_POST['TGID'];
	   $TESTID  = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
	   
	   if($STID == 0){throw new Exception("STID Not Found.");}
	//    if($CORRECT_SCORE_COUNT == 0){throw new Exception("Please Enter Test Year.");}
	   if($TGID == 0){throw new Exception("TGID Not Found.");}
	   if($TESTID == 0){throw new Exception("TESTID Not Found.");}
		
	   	// GET SCALE
		$getScale = "SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=$TESTID AND SCORE=$CORRECT_SCORE_COUNT AND 
		TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TGID=$TGID)";
		$ScaleStmt=sqlsrv_query($mysqli, $getScale);
		$countScale = unique($getScale);
		if($countScale > 0){
			$rowScale = sqlsrv_fetch_array($ScaleStmt,SQLSRV_FETCH_ASSOC);
			$SCALE = (int)$rowScale['SCALE'];

			$query="UPDATE STUDENT_TESTS SET SCORE=$CORRECT_SCORE_COUNT,SCALE=$SCALE,ENDDATETIME=GETDATE() WHERE STID=$STID";
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
	
				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Test successfully submitted.';
				
			}
		}else{
			$data['success'] = false;
			$data['message'] = 'Scale Not Found.';
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
// =============== TEST SUBMIT ==================





/* =========== DELETE TEST =========== */ 
function DeleteTest($mysqli){
	try{   
		global $userid;
		$data = array();     
		$STID = ($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];  
		if($STID <= 0)throw new Exception('STID Not Found.');

		$delQuery = "UPDATE STUDENT_TESTS SET ISDELETED=1 WHERE STID=$STID;
					UPDATE STUDENT_TEST_RESULTS SET ISDELETED=1 WHERE STID=$STID";

		$stmt=sqlsrv_query($mysqli,$delQuery);
		if(!$stmt) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
			$data['success'] = true;
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Test successfully deleted.';
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
/* =========== DELETE TEST =========== */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







