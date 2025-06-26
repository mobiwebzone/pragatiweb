<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getTeacherPlans":getStudentTest($conn);break;
        case "getStudentTest":getStudentTest($conn);break;
        case "getStudentWrongAnswers":getStudentWrongAnswers($conn);break;
        case "getStudentTopicWiseAnalysis":getStudentTopicWiseAnalysis($conn);break;
        case "getStudentCourseCoverage":getStudentCourseCoverage($conn);break;
        case "getStudentByPlanLocation_batchwise":getStudentByPlanLocation_batchwise($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET STUDENT TEST PROGRESS =============*/ 
 function getStudentTest($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('Error : REGID Invalid.');

		$query = "SELECT TS.SECID,
		SM.SECTION,
		CONVERT(VARCHAR,TESTDATE,106)TESTDATE,
		(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=ST.TESTID)TESTNAME,
		(QUESTIONS) AS TOTAL_Q,
		CASE WHEN SCORE = 0
			THEN (SELECT COUNT(RESULT) FROM STUDENT_TEST_RESULTS WHERE STID=ST.STID AND RESULT>0 AND ISDELETED=0)
			ELSE SCORE
		END RESULT,
		SCALE SCALE_OLD,
		ISNULL((SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=ST.TESTID AND TSG_GROUPNO=isnull((SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0),0)  AND ISDELETED=0 AND SCORE=ISNULL((SELECT SUM(CASE RESULT WHEN 0 THEN 0 ELSE 1 END) FROM STUDENT_TEST_RESULTS WHERE STID=ST.STID AND RESULT>0 AND ISDELETED=0),0)),0) SCALE
		FROM STUDENT_TESTS ST,TEST_SECTIONS TS,SECTION_MASTER SM
		WHERE ST.ISDELETED=0 AND TS.ISDELETED=0 AND ST.TSECID=TS.TSECID AND SM.SECID=TS.SECID AND SM.SECTION NOT LIKE '%Break%'
		AND REGID=$REGID --AND ST.TSECID NOT IN (SELECT TSECID FROM TEST_SECTIONS TS WHERE SECID NOT IN (SELECT SECID FROM SECTION_MASTER WHERE SECID=TS.SECID AND SECTION NOT LIKE '%Break%'))
		ORDER BY SECTION,CONVERT(DATE,TESTDATE,105)";

		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			$SECTION_NEW = $SECTION_OLD = '';
			$date_array = array();
			$raw_array = array();
			$scale_array = array();
			$idx = -1;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$SECTION_NEW = $row['SECTION'];
				
				if($SECTION_NEW!==$SECTION_OLD) $idx++;
				// $date_array[$idx][] = $row['TESTDATE'];
				// $raw_array[$idx][] = $row['RESULT'];
				// $scale_array[$idx][] = $row['SCALE'];
				$data['CHART_DATASET'][$idx]['DATE'][] = $row['TESTDATE'];
				$data['CHART_DATASET'][$idx]['RAW'][] = $row['RESULT'];
				$data['CHART_DATASET'][$idx]['TOTAL_Q'][] = $row['TOTAL_Q'];
				$data['CHART_DATASET'][$idx]['SCALE'][] = $row['SCALE'];

				$data['data'][] = $row;
				$SECTION_OLD = $SECTION_NEW;
			}
			// $data['CHART_DATASET']=['DATE'=>$date_array,'RAW'=>$raw_array,'SCALE'=>$scale_array];
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


/*============ GET STUDENT WRONG ANSWERS =============*/ 
 function getStudentWrongAnswers($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('Error : REGID Invalid.');

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
		FROM STUDENT_TEST_RESULTS SR,STUDENT_TESTS ST
		WHERE ST.ISDELETED=0 AND SR.ISDELETED=0  AND SR.REGID =$REGID AND SR.REGID=ST.REGID --AND SR.STID=ST.STID
		AND SR.TSECID=ST.TSECID
		AND SR.TSECID NOT IN ((SELECT TSECID FROM TEST_SECTIONS WHERE TESTSECTION like '%break%'))
		AND ST.TSECID NOT IN ((SELECT TSECID FROM TEST_SECTIONS WHERE TESTSECTION like '%break%'))
		AND SR.RESULT=0
		ORDER BY STUDENTNAME,TESTDATE DESC,MAINCATEGORYNAME,CATEGORYNAME,SUBCATEGORYNAME,TOPICNAME,(SELECT SEQNO FROM TEST_SECTIONS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0)";

		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


/*============ GET STUDENT TOPICWISE ANALYSIS =============*/ 
 function getStudentTopicWiseAnalysis($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('Error : REGID Invalid.');

		$query = "EXEC [STUDENT_TOPICWISE_WRONG_PERC] '$REGID'";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		// echo json_encode($data);exit;
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['WRONG_PERC'] > 0) $data['data'][] = $row;
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


/*============ GET STUDENT COURSE COVERAGE =============*/ 
 function getStudentCourseCoverage($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('Error : REGID Invalid.');

		$query = "SELECT SCCA.SCCID,CONVERT(VARCHAR,SCC.CDATE,106)CDATE,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,
		SCC.PAGEFROM,SCC.PAGETO,SCC.REMARK MAIN_REMARK,SCC.INVID,
		(SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY
		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA,STUDENT_COURSE_COVERAGE SCC 
		WHERE SCCA.ISDELETED=0 AND SCC.ISDELETED=0 AND SCCA.SCCID=SCC.SCCID AND REGID=$REGID 
		ORDER BY (SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID) ASC,INVENTORY ASC,
		(SELECT CHAPNO FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID) ASC,
		CONVERT(DATE,SCC.CDATE,105) DESC";

		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


/*============ GET STUDENT BY PLAN & LOCATION BATCHWISE =============*/
function getStudentByPlanLocation_batchwise($mysqli){
	try
	{	
		$data = array();
		$PLANID = (!isset($_POST['PLANID']) || $_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];
		if($PLANID == 0)throw new Exception('PLANID Not Found.');
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');
		$USERID = ($_POST['USERID'] == 'undefined' || $_POST['USERID'] == '') ? 0 : $_POST['USERID'];
		if($USERID == 0)throw new Exception('USERID Not Found.');

		// $query = "SELECT REGID, FIRSTNAME,LASTNAME
		// 	FROM REGISTRATIONS 
		// 	WHERE ISDELETED=0 AND LOCATIONID=$LOCID AND APPROVED=1 AND ARCHIVED=0
		// 	AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID IN ($PLANID) AND CANCELLED=0)
		// 	ORDER BY FIRSTNAME";
		$query = "SELECT REGID,(SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=BS.REGID)FIRSTNAME,
		(SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=BS.REGID)LASTNAME 
		FROM LA_BATCH_STUDENTS BS WHERE ISDELETED=0 
		AND BATCHID IN (SELECT BATCHID FROM LA_BATCH_TEACHERS WHERE ISDELETED=0 AND TEACHERID=$USERID)
		AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1) 
		AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID IN ($PLANID))";
		$data['$query'] = $query;
		
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['REGID'] = (int) $row['REGID'];
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
/*============ GET STUDENT BY PLAN & LOCATION BATCHWISE =============*/ 


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







