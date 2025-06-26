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
        case "getStudentTest":getStudentTest($conn);break;
        case "getStudentWrongAnswers":getStudentWrongAnswers($conn);break;
        case "getStudentTopicWiseAnalysis":getStudentTopicWiseAnalysis($conn);break;
        case "getStudentCourseCoverage":getStudentCourseCoverage($conn);break;

        case "getLaTopics":getLaTopics($conn);break;
        case "getLaSlideHeads":getLaSlideHeads($conn);break;
        case "getLaSlidesbyHead":getLaSlidesbyHead($conn);break;
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
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		$FINAL_REGID = implode(",",$REGIDS_ARRAY);

		$query = "SELECT TS.SECID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
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
		AND REGID IN ($FINAL_REGID) --AND ST.TSECID NOT IN (SELECT TSECID FROM TEST_SECTIONS TS WHERE SECID NOT IN (SELECT SECID FROM SECTION_MASTER WHERE SECID=TS.SECID AND SECTION NOT LIKE '%Break%'))
		ORDER BY STUDENTNAME,SECTION,CONVERT(DATE,TESTDATE,105)";

		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			$STUDENT_NEW = $STUDENT_OLD = '';
			$SECTION_NEW = $SECTION_OLD = '';
			$date_array = array();
			$raw_array = array();
			$scale_array = array();
			$idx = -1;
			$idx_ST = -1;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$STUDENT_NEW = $row['STUDENTNAME'];
				$SECTION_NEW = $row['SECTION'];
				
				if($STUDENT_NEW!==$STUDENT_OLD){
					$idx_ST++;
					$idx = -1;
				}
				if($SECTION_NEW!==$SECTION_OLD) $idx++;
				// $date_array[$idx][] = $row['TESTDATE'];
				// $raw_array[$idx][] = $row['RESULT'];
				// $scale_array[$idx][] = $row['SCALE'];
				$data['CHART_DATASET'][$idx_ST][$idx]['DATE'][] = $row['TESTDATE'];
				$data['CHART_DATASET'][$idx_ST][$idx]['RAW'][] = $row['RESULT'];
				$data['CHART_DATASET'][$idx_ST][$idx]['TOTAL_Q'][] = $row['TOTAL_Q'];
				$data['CHART_DATASET'][$idx_ST][$idx]['SCALE'][] = $row['SCALE'];

				$data['data'][] = $row;
				$STUDENT_OLD = $STUDENT_NEW;
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
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		// $FINAL_REGID = implode(",",$REGIDS_ARRAY);
		// $data['FINAL_REGID'] = $REGIDS_ARRAY;
		// echo json_encode($data);exit;
		for($i=0;$i<count($REGIDS_ARRAY);$i++){
			$REGID = $REGIDS_ARRAY[$i];

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
			WHERE ST.ISDELETED=0 AND SR.ISDELETED=0  AND SR.REGID=$REGID AND SR.REGID=ST.REGID --AND SR.STID=ST.STID
			AND SR.TSECID=ST.TSECID
			AND SR.TSECID NOT IN ((SELECT TSECID FROM TEST_SECTIONS WHERE TESTSECTION LIKE '%break%'))
			AND ST.TSECID NOT IN ((SELECT TSECID FROM TEST_SECTIONS WHERE TESTSECTION LIKE '%break%'))
			AND SR.RESULT=0
			ORDER BY TESTDATE DESC,MAINCATEGORYNAME,CATEGORYNAME,SUBCATEGORYNAME,TOPICNAME,(SELECT SEQNO FROM TEST_SECTIONS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0)";

			$data['$query'][]=$query;

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
		}


		// echo json_encode($data);exit;
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
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		$FINAL_REGID = implode(",",$REGIDS_ARRAY);

		// $query = "SELECT STUDENTNAME,
		// (SELECT SECTION FROM SECTION_MASTER WHERE SECID=
		// 	(SELECT SECID FROM SECTION_CATEGORIES WHERE CATID=
		// 	(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=TT.TOPICID)))) MAINCATEGORY,
	
		// (SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=
		// (SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=TT.TOPICID))) CATEGORY,
		// (SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=TT.TOPICID)) SUBCATEGORY,
		// TOPICID,TOPIC,SUM(TOTAL) TOTAL,SUM(CORRECT) CORRECT,SUM(WRONG) INCORRECT,(SUM(WRONG)*100)/SUM(TOTAL) WRONG_PERC
		// FROM
		// (
		// 	SELECT 
		// 	(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
		// 	(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=ST.QUEID) TOPICID,
		// 	(SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=ST.QUEID))TOPIC,
		// 	1 TOTAL, CASE RESULT WHEN 0 THEN 1 ELSE 0 END WRONG,CASE RESULT WHEN 1 THEN 1 ELSE 0 END CORRECT
		// 	FROM STUDENT_TEST_RESULTS ST WHERE REGID IN ($FINAL_REGID) AND ISDELETED=0
		// ) TT 
		// WHERE  TOPIC NOT LIKE '%Break%'
		// GROUP BY STUDENTNAME,TOPICID,TOPIC
		// ORDER BY STUDENTNAME,MAINCATEGORY,WRONG_PERC DESC,CATEGORY,SUBCATEGORY";
		// // ORDER BY STUDENTNAME,WRONG_PERC DESC,MAINCATEGORY,CATEGORY,SUBCATEGORY";
		$query = "EXEC [STUDENT_TOPICWISE_WRONG_PERC] '$FINAL_REGID'";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$result = sqlsrv_query($mysqli, $query);
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
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		$FINAL_REGID = implode(",",$REGIDS_ARRAY);

		$query = "SELECT SCCA.SCCID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)STUDENTNAME,
		CONVERT(VARCHAR,SCC.CDATE,106)CDATE,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,
		SCC.PAGEFROM,SCC.PAGETO,SCC.REMARK MAIN_REMARK,SCC.INVID,
		(SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY
		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA,STUDENT_COURSE_COVERAGE SCC 
		WHERE SCCA.ISDELETED=0 AND SCC.ISDELETED=0 AND SCCA.SCCID=SCC.SCCID AND REGID IN ($FINAL_REGID)
		ORDER BY STUDENTNAME,(SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID) ASC,INVENTORY ASC,
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




/*============ GET LA TOPICS BY SECTION TOPIC =============*/ 
function getLaTopics($mysqli){
	try
	{
		$data = array();
		$SECTION_TOPICID = ($_POST['SECTION_TOPICID'] == 'undefined' || $_POST['SECTION_TOPICID'] == '') ? 0 : $_POST['SECTION_TOPICID'];

		$query = "SELECT T.TOPIC,LA_TOPICID,T.SEQNO,T.GRADEID,T.SUBID
		FROM SECTION_TOPICS_LA_TOPICS S,LA_TOPICS_MASTER T 
		WHERE S.ISDELETED=0 AND T.ISDELETED=0 AND S.LA_TOPICID=T.TOPICID AND SECTION_TOPICID=$SECTION_TOPICID ORDER BY T.SEQNO";

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




/*============ GET LA SLIDE HEADS =============*/ 
function getLaSlideHeads($mysqli){
	try
	{
		$data = array();
		$LA_TOPICID = ($_POST['LA_TOPICID'] == 'undefined' || $_POST['LA_TOPICID'] == '') ? 0 : $_POST['LA_TOPICID'];

		$query = "SELECT SLIDEID,SLIDEHEADING FROM LA_TOPIC_SLIDE_MASTER WHERE ISDELETED=0 AND TOPICID=$LA_TOPICID ORDER BY SLIDEHEADING";

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




/*============ GET LA SLIDES =============*/ 
function getLaSlidesbyHead($mysqli){
	try
	{
		$data = array();
		$SLIDEID = ($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];

		$query = "SELECT CONTENT_TYPE,CONTENT,CONTENTFILE FROM LA_TOPIC_SLIDES WHERE ISDELETED=0 AND SLIDEID=$SLIDEID ORDER BY LEN(SEQNO),SEQNO";
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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}
