<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	switch ($type) {
        case "getTestReview":getTestReview($conn);break;
        case "getGradingData":getGradingData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ GET TEST REVIEW =============*/ 
 function getTestReview($mysqli){
	try
	{
		global $userid;
		$data = array();
		$testid=($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
		$tsecid=($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
		$attempt=($_POST['attempt'] == 'undefined' || $_POST['attempt'] == '') ? 0 : $_POST['attempt'];
		$assgroupno=($_POST['assgroupno'] == 'undefined' || $_POST['assgroupno'] == '') ? 0 : $_POST['assgroupno'];
		$assEssid=($_POST['assEssid'] == 'undefined' || $_POST['assEssid'] == '') ? 0 : $_POST['assEssid'];
		$assfor=($_POST['assfor'] == 'undefined' || $_POST['assfor'] == '') ? '' : $_POST['assfor'];

		if($assfor == ''){throw new Exception('assfor missing.');}
		if($assfor == 'SECTION'){
			if($attempt == 0){throw new Exception('attempt missing.');}
			if($assgroupno == 0){throw new Exception('groupno missing.');}
		}

		// ########### GET UPLOAD TEST REVIEW & FILE START
		$queryRev = "SELECT REVIEWED,DOCS FROM STUDENT_TESTS WHERE ISDELETED=0 AND REGID=$userid AND TESTID=$testid AND TSECID=$tsecid";
		$data['queryRev'] = $queryRev;
		$countRev = unique($queryRev);
		if($countRev>0){
			$resultRev = sqlsrv_query($mysqli, $queryRev);
			while ($rowRev = sqlsrv_fetch_array($resultRev,SQLSRV_FETCH_ASSOC)) {
				$data['DATA_REV'][] = $rowRev;
			}
			$data['successRev']=true;
		}else{
			$data['successRev']=false;
		}
		// ########### GET UPLOAD TEST REVIEW & FILE END

		if($assfor == 'SECTION'){
			$query = "SELECT RID,REGID,STID,TSECID,QUEID,
			(SELECT QUESTION FROM QUESTION_BANK WHERE QUEID=ST.QUEID)QUESTION,
			(SELECT QUEOPTIONS FROM QUESTION_BANK WHERE QUEID=ST.QUEID)QUE_OPTIONS,
			CORRECTANS,STUDENTANS,RESULT,
			(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=$testid AND ISDELETED=0)TEST,
			(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID = ST.TSECID AND ISDELETED=0)SECTION,
			(SELECT CONVERT(VARCHAR,TESTDATE,106) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$attempt AND ISDELETED=0)TESTDATE,
			(SELECT CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$attempt AND ISDELETED=0)STARTDATETIME,
			(SELECT CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$attempt AND ISDELETED=0)ENDDATETIME,
			(SELECT ANS_EXPLANATION FROM QUESTION_BANK WHERE QUEID=ST.QUEID)ANS_EXPLANATION,
			(SELECT ANS_EXPIMAGE FROM QUESTION_BANK WHERE QUEID=ST.QUEID)ANS_EXPIMAGE,WHITEBOARD_IMG
			FROM STUDENT_TEST_RESULTS ST
			WHERE ISDELETED=0 AND 
			STID IN ((SELECT STID FROM STUDENT_TESTS WHERE ISDELETED=0 AND ATTEMPT=$attempt AND REGID=$userid AND TESTID=$testid AND 
			TSECID IN (SELECT TSECID FROM TEST_SECTION_GROUPS WHERE ISDELETED=0 AND TESTID=$testid AND GROUPNO=$assgroupno)))
			AND REGID=$userid 
			AND TSECID IN ((SELECT TSECID FROM STUDENT_TESTS WHERE ISDELETED=0 AND ATTEMPT=$attempt AND REGID=$userid AND TESTID=$testid AND 
			TSECID IN (SELECT TSECID FROM TEST_SECTION_GROUPS WHERE ISDELETED=0 AND TESTID=$testid AND GROUPNO=$assgroupno)))";
		}
		else if($assfor == 'ESSAY'){
			// $query = "SELECT STESSID,ESSID,TESTID,TSECID,ESSAY,TOTAL_WORD,TOTAL_CHAR,
			// (SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=SE.TESTID AND ISDELETED=0)TEST,
			// (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID = SE.TSECID AND ISDELETED=0)SECTION,
			// (SELECT ESSTOPIC FROM ESSAYS WHERE ESSID=SE.ESSID)TOPIC,
			// (SELECT LIMITON FROM ESSAYS WHERE ESSID=SE.ESSID)LIMITON,
			// (SELECT [LIMIT] FROM ESSAYS WHERE ESSID=SE.ESSID)[LIMIT],
			// (SELECT TIMEALLOWED FROM ESSAYS WHERE ESSID=SE.ESSID)TIMEALLOWED,
			// CONVERT(VARCHAR,INSERTDATE,106)TESTDATE,
			// CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
			// CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME
			// FROM STUDENT_ESSAYS SE
			// WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND INSERTID=$userid";
			$query = "SELECT STESSID,ESSID,
			(SELECT LIMITON FROM ESSAYS WHERE ESSID=SE.ESSID)LIMITON,
			(SELECT [LIMIT] FROM ESSAYS WHERE ESSID=SE.ESSID)[LIMIT],
			(SELECT TIMEALLOWED FROM ESSAYS WHERE ESSID=SE.ESSID)TIMEALLOWED,
			TSECID,ESSAY,TOTAL_WORD,TOTAL_CHAR,
			CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
			CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME,
			(SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=SE.INSERTID)STUDENT,INSERTID,
			CONVERT(VARCHAR(8),STARTDATETIME,8)NEW_STARTTIME,
			CONVERT(VARCHAR(8),ENDDATETIME,8)NEW_ENDTIME,
			(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=SE.TESTID AND ISDELETED=0)TEST,
			(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID = SE.TSECID AND ISDELETED=0)SECTION,
			(SELECT ESSTOPIC FROM ESSAYS WHERE ESSID=SE.ESSID)TOPIC,
			CONVERT(VARCHAR,INSERTDATE,106)TESTDATE
			FROM STUDENT_ESSAYS SE
			WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND INSERTID=$userid AND ESSID=$assEssid";

			// $query = "SELECT STESSID,ESSID,
			// (SELECT LIMITON FROM ESSAYS WHERE ESSID=SE.ESSID)LIMITON,
			// (SELECT LIMIT FROM ESSAYS WHERE ESSID=SE.ESSID)LIMIT,
			// (SELECT TIMEALLOWED FROM ESSAYS WHERE ESSID=SE.ESSID)TIMEALLOWED,
			// TSECID,ESSAY,TOTAL_WORD,TOTAL_CHAR,
			// CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
			// CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME,
			// (SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=SE.INSERTID)STUDENT,INSERTID,
			// CONVERT(VARCHAR(8),STARTDATETIME,8)NEW_STARTTIME,
			// CONVERT(VARCHAR(8),ENDDATETIME,8)NEW_ENDTIME
			// FROM STUDENT_ESSAYS SE 
			// WHERE ISDELETED=0 AND TESTID=$TESTID ORDER BY STUDENT";
			// $result = sqlsrv_query($mysqli, $query);
			// $count = unique($query);
			// if($count > 0){
			// 	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			// 		$row['NEW_STARTTIME'] = date('h:i:s A', strtotime($row['NEW_STARTTIME']));
			// 		$row['NEW_ENDTIME'] = date('h:i:s A', strtotime($row['NEW_ENDTIME']));
			// 		$data['data'][] = $row;
			// 	}
			// 	$data['success'] = true;
			// }
			// else{
			// 	$data['success'] = false;
			// }
		}
		else{
			throw new Exception('assfor error.');
		}

		$data['$query']=$query;
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			$SECTION = '';
			$SECTIONNAME = array();
			$RESULT_RIGHT = 0;
			$RESULT_WRONG = 0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($assfor == 'SECTION'){
					// $row['QUE_OPTIONS'] = str_replace(";#;,",", ",$row['QUE_OPTIONS']);
					// $row['QUE_OPTIONS'] = rtrim($row['QUE_OPTIONS'],';#; ');
					$row['QUE_OPTIONS'] = str_replace(';#;', ' ', $row['QUE_OPTIONS']);
					$RESULT = $row['RESULT'];
					if($RESULT == 1){
						$RESULT_RIGHT++;
					}else{
						$RESULT_WRONG++;
					}
					$data['data'][] = $row;
	
					if($SECTION == '' || $SECTION !== $row['SECTION']){
						$SECTIONNAME[] = $row['SECTION'];
						$SECTION = $row['SECTION'];
					}
					$data['SECTIONNAME']=implode(", ",$SECTIONNAME);

				}else{
					$data['SECTIONNAME'] = $row['SECTION'];
					$row['NEW_STARTTIME'] = date('h:i:s A', strtotime($row['NEW_STARTTIME']));
					$row['NEW_ENDTIME'] = date('h:i:s A', strtotime($row['NEW_ENDTIME']));
					$data['data'][] = $row;
				}
			}
			$data['RESULT_RIGHT']=$RESULT_RIGHT;
			$data['RESULT_WRONG']=$RESULT_WRONG;
			$data['success'] = true;
		}else{
			$data['message'] = 'Data not Found.';
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
/*============ GET TEST REVIEW =============*/ 



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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







