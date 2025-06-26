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


/*============ Get Student Test Report =============*/ 
function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];

		$PLANIDS_ARRAY = (!isset($_POST['PLANIDS'])) ? array() : $_POST['PLANIDS'];
		if(!$PLANIDS_ARRAY || count($PLANIDS_ARRAY)<=0) throw new Exception('Select Plans First.');
		$PLANIDS_ARRAY = implode(",",$PLANIDS_ARRAY);
		$data['PLANIDS'] = $PLANIDS_ARRAY;

		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		$REGIDS_ARRAY = implode(",",$REGIDS_ARRAY);
		$data['REGIDS'] = $REGIDS_ARRAY;

		$query = "SELECT (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENTNAME,
		(SELECT TESTDESC+' ('+CAST(TESTYEAR AS VARCHAR)+')' FROM TEST_MASTER WHERE TESTID=ST.TESTID)TESTNAME,
		LEN((SELECT TESTDESC+' ('+CAST(TESTYEAR AS VARCHAR)+')' FROM TEST_MASTER WHERE TESTID=ST.TESTID))TESTNAME_LEN,
		CONVERT(VARCHAR,TESTDATE,106)TESTDATE,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID)TESTSECTIONNAME,
		LEN((SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID))TESTSECTIONNAME_LEN,
		(SELECT (SELECT (SELECT (SELECT (SELECT SECTION FROM SECTION_MASTER WHERE SECID=SCAT.SECID) FROM SECTION_CATEGORIES SCAT WHERE CATID=SC.CATID) FROM SECTION_SUB_CATEGORIES SC WHERE SUBCATID=T.SUBCATID) FROM SECTION_TOPICS T WHERE TOPICID=Q.TOPICID) FROM QUESTION_BANK Q WHERE QUEID=SR.QUEID)MAINCATEGORYNAME,
		(SELECT (SELECT (SELECT (SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=SC.CATID) FROM SECTION_SUB_CATEGORIES SC WHERE SUBCATID=T.SUBCATID) FROM SECTION_TOPICS T WHERE TOPICID=Q.TOPICID) FROM QUESTION_BANK Q WHERE QUEID=SR.QUEID)CATEGORYNAME,
		(SELECT (SELECT (SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=T.SUBCATID) FROM SECTION_TOPICS T WHERE TOPICID=Q.TOPICID) FROM QUESTION_BANK Q WHERE QUEID=SR.QUEID)SUBCATEGORYNAME,
		(SELECT (SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=Q.TOPICID) FROM QUESTION_BANK Q WHERE QUEID=SR.QUEID)TOPICNAME,
		(SELECT QUESTION FROM QUESTION_BANK WHERE QUEID=SR.QUEID)QUESTION,
		LEN((SELECT QUESTION FROM QUESTION_BANK WHERE QUEID=SR.QUEID))QUESTION_LEN,
		STUDENTANS,CORRECTANS
		FROM STUDENT_TESTS ST,STUDENT_TEST_RESULTS SR 
		WHERE ST.ISDELETED=0 AND SR.ISDELETED=0 AND ST.STID=SR.STID AND SR.REGID IN ($REGIDS_ARRAY) 
		AND SR.TSECID NOT IN ((SELECT TSECID FROM TEST_SECTIONS WHERE TESTSECTION like '%break%'))
		AND SR.RESULT=0 AND CONVERT(DATE,ST.TESTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		ORDER BY STUDENTNAME,TESTDATE DESC,MAINCATEGORYNAME,CATEGORYNAME,SUBCATEGORYNAME,TOPICNAME,(SELECT SEQNO FROM TEST_SECTIONS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0)";
		// --ORDER BY STUDENTNAME,TESTNAME_LEN,TESTNAME,TESTSECTIONNAME_LEN,TESTSECTIONNAME,QUESTION_LEN,QUESTION
		$data['$query']=$query;
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			set_time_limit(0);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Data not found.';
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
/*============ Get Student Test Report =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







