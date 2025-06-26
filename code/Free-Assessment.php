<?php
session_start();
require_once 'connection.php';

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require '../PHPMailer/src/Exception.php';
// require '../PHPMailer/src/PHPMailer.php';
// require '../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "SaveTest":SaveTest($conn);break;
        case "saveEssay":saveEssay($conn);break;
        case "UpdateEssay":UpdateEssay($conn);break;
        case "UpdateAnswer":UpdateAnswer($conn);break;
        case "getTestByStudentProducts":getTestByStudentProducts($conn);break;
        case "getStudentPlans":getStudentPlans($conn);break;
        case "getPlanProduct":getPlanProduct($conn);break;
        case "getTestSections":getTestSections($conn);break;
        case "getSectionQuestions":getSectionQuestions($conn);break;
        case "getEssayResult":getEssayResult($conn);break;
        case "getResultBySections":getResultBySections($conn);break;
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


/*============ Get Test By Student Products =============*/ 
function getTestByStudentProducts($mysqli){
	try
	{
		$data = array();
		global $userid;

		$data['$userid']=$userid;

		$userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
		// GET ALL TEST BY STUDENT PLANS
		$totalTest_query = "SELECT DISTINCT TM.TESTID,TESTDESC,LEN(TESTDESC)ss,	
		ISNULL((SELECT TGID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TESTID=S.TESTID),0)TGID
		FROM TEST_MASTER TM,TEST_SECTIONS S
		WHERE TM.TESTID=S.TESTID AND TM.ISDELETED=0 AND S.ISDELETED=0 AND FREE_TEST=1
		ORDER BY LEN(TESTDESC),TESTDESC";
		$RCOUNT_TT = unique($totalTest_query);
		$result_TT = sqlsrv_query($mysqli, $totalTest_query);
		$TT_INDEX = 0;

		if($RCOUNT_TT > 0){
			while ($row_TT = sqlsrv_fetch_array($result_TT, SQLSRV_FETCH_ASSOC)) {
				$testid = (int) $row_TT['TESTID'];
				$TGID = (int) $row_TT['TGID'];
	
				$data['data'][$TT_INDEX]['TEST'] = $row_TT;
	

				// $getMaxScore = "SELECT GROUPNO,SECTIONS,MAX(SCORES) SCORE,MAX(SCALE) SCALE
				// FROM
				// (
				// SELECT TESTID,GROUPNO,SECTIONS,SCORES,
				// (SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=TTT.TESTID AND TSG_GROUPNO=TTT.GROUPNO AND ISDELETED=0 AND SCORE=TTT.SCORES) SCALE
				// FROM
				// (
				// SELECT TESTID,GROUPNO,SUM(SCORE) SCORES,
				// (SELECT (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=TSG.TSECID) +' | '  FROM TEST_SECTION_GROUPS TSG WHERE TESTID=TT.TESTID AND GROUPNO=TT.GROUPNO AND ISDELETED=0 FOR XML PATH('')) SECTIONS
				// FROM
				// (
				// SELECT TESTID,TSECID,
				// SCORE,
				// ISNULL((SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0),0) GROUPNO
				// FROM STUDENT_TESTS ST
				// WHERE REGID=$userid AND TGID=$TGID AND ISDELETED=0) TT
				// GROUP BY TESTID,GROUPNO
				// )TTT
				// GROUP BY TESTID,GROUPNO,SECTIONS,SCORES
				// ) TTTT
				// GROUP BY GROUPNO,SECTIONS
				// HAVING GROUPNO>0
				// ORDER BY GROUPNO";


				$getMaxScore = "SELECT GROUPNO,SECTIONS,MAX(SCORES) SCORE,MAX(SCALE) SCALE
				--(SELECT (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=TSG.TSECID) +','  FROM TEST_SECTION_GROUPS TSG WHERE TESTID=TTT.TESTID AND GROUPNO=TTT.GROUPNO AND ISDELETED=0 FOR XML PATH('')) SECTIONS
				FROM
				(
					SELECT TESTID,GROUPNO,SECTIONS,SCORES,
					(SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=TTT.TESTID AND TSG_GROUPNO=TTT.GROUPNO AND ISDELETED=0 AND SCORE=TTT.SCORES) SCALE
					FROM
					(
						SELECT TESTID,GROUPNO,SUM(SCORE) SCORES,ATTEMPT,
						(SELECT (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=TSG.TSECID) +' | '  FROM TEST_SECTION_GROUPS TSG WHERE TESTID=TT.TESTID AND GROUPNO=TT.GROUPNO AND ISDELETED=0 FOR XML PATH('')) SECTIONS
						FROM
						(
							SELECT TESTID,TSECID,ATTEMPT,-- (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID) SECTION,
							--SCORE,
							CASE WHEN SCORE = 0
								THEN (SELECT COUNT(RESULT) FROM STUDENT_TEST_RESULTS_FREE WHERE STID=ST.STID AND RESULT>0 AND ISDELETED=0)
								ELSE SCORE
							END SCORE,
							ISNULL((SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0),0) GROUPNO
							FROM STUDENT_TESTS_FREE ST
							WHERE REGID=$userid AND TGID=$TGID AND ISDELETED=0
						) TT
						GROUP BY TESTID,ATTEMPT,GROUPNO
					)TTT
					GROUP BY TESTID,GROUPNO,SECTIONS,SCORES
				) TTTT
				GROUP BY GROUPNO,SECTIONS
				HAVING GROUPNO>0
				ORDER BY GROUPNO";
				
				$data['$getMaxScore'][] = $getMaxScore;
				$CountMaxScore = unique($getMaxScore);
				$resultMaxScore = sqlsrv_query($mysqli, $getMaxScore);
				$mxscore = array();
				if($CountMaxScore > 0){
					while($rowMaxScore = sqlsrv_fetch_array($resultMaxScore, SQLSRV_FETCH_ASSOC)){
						$rowMaxScore['SECTIONS'] = rtrim($rowMaxScore['SECTIONS'],' | ');
						$mxscore[] = $rowMaxScore;
					}	
				}
				// $data['rowMaxScore'][] = $mxscore;
				$data['data'][$TT_INDEX]['TEST']['SUPER_SCORE'] = $mxscore;
	
	
	
				// GET TESTSECTION BY TESTID
				$query="SELECT TM.TESTID,TESTDESC,TESTYEAR,TESTOPEN,REMARKS,S.TESTSECTION,S.TSECID,S.SEQNO,
				ISNULL((SELECT G.GROUPNO FROM TEST_SECTION_GROUPS G WHERE G.TESTID=TM.TESTID AND G.TSECID=S.TSECID AND G.ISDELETED=0),0)GROUPNO,
				ISNULL((SELECT MAX(ATTEMPT) FROM STUDENT_TESTS_FREE WHERE ISDELETED=0 AND REGID=$userid AND TESTID=S.TESTID AND TSECID=S.TSECID),0)ATTEMPT
				FROM TEST_MASTER TM,TEST_SECTIONS S
				WHERE TM.TESTID=S.TESTID AND TM.ISDELETED=0 AND S.ISDELETED=0
				AND TM.TESTID = $testid 
				ORDER BY S.SEQNO";
	
				// $data['$query'][]=$query;
				// echo json_encode($data);
	
				$RCOUNT = unique($query);
				$result = sqlsrv_query($mysqli, $query);
				
				if($RCOUNT > 0){
					$TS_INDEX=0;
					while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
						$row['TESTSECTION'] = ucfirst($row['TESTSECTION']);
						$ATTEMPT = $row['ATTEMPT'];
						$TESTID = $row['TESTID'];
						$TSECID = $row['TSECID'];
						$GROUPNO = $row['GROUPNO'];
	
	
	
						// GET ESSAY BY TEST/TSEC ID
						$GetEssay = "SELECT ESSID,TESTID,
						(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=E.TESTID)TESTDESC,TSECID,
						(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=E.TSECID)TESTSECTION,ESSTOPIC,LIMITON,[LIMIT],TIMEALLOWED,
						--ISNULL((SELECT MAX(ATTEMPT) FROM STUDENT_TESTS WHERE ISDELETED=0 AND REGID=$userid AND TESTID=E.TESTID AND TSECID=E.TSECID),0)ATTEMPT,
						ISNULL((SELECT CONVERT(VARCHAR,STARTDATETIME,106) FROM STUDENT_ESSAYS_FREE WHERE TESTID=E.TESTID AND TSECID=E.TSECID AND ISDELETED=0 AND INSERTID=$userid AND ESSID=E.ESSID),'')TESTDATE,
						CASE WHEN ISNULL((SELECT COUNT(*) FROM STUDENT_ESSAYS_FREE WHERE TESTID=E.TESTID AND TSECID=E.TSECID AND ISDELETED=0 AND INSERTID=$userid AND ESSID=E.ESSID),0) > 0 
							THEN 1
							ELSE 0
						END ATTEMPT
						FROM ESSAYS E
						WHERE ISDELETED=0 AND TESTID=$TESTID AND TSECID=$TSECID";
						$data['$GetEssay'][] = $GetEssay;
						$stmt_Essay = sqlsrv_query($mysqli, $GetEssay);
						$count_Essay = unique($GetEssay);
						if($count_Essay > 0){
							while($row_Essay = sqlsrv_fetch_array($stmt_Essay, SQLSRV_FETCH_ASSOC)){
								$data['data'][$TT_INDEX]['ESSAY'][] = $row_Essay;
							}
						}
	
						// GET ESSAY SCORE
						$RS = array();
						$SS = array();
						for($a=0; $a<$ATTEMPT; $a++){
							$ATT = $a+1;
	
							// GET STID & TSECID BY GROUPNO
							$GetIDs = "SELECT STID,TSECID FROM STUDENT_TESTS_FREE WHERE ISDELETED=0 AND ATTEMPT=$ATT AND REGID=$userid AND TESTID=$TESTID AND 
							TSECID IN (SELECT TSECID FROM TEST_SECTION_GROUPS WHERE TESTID=$TESTID AND ISDELETED=0 AND GROUPNO=$GROUPNO)";
							// $data['GetIDs'][]=$GetIDs;
							$stmt_IDs = sqlsrv_query($mysqli, $GetIDs);
							$MY_STID = array();
							$MY_TSECID = array();
							while($row_IDs = sqlsrv_fetch_array($stmt_IDs, SQLSRV_FETCH_ASSOC)){
								$MY_STID[] = (string) $row_IDs['STID'];
								$MY_TSECID[] = (string) $row_IDs['TSECID'];
							}
							$MY_STID = implode(", ",$MY_STID);
							$MY_TSECID = implode(", ",$MY_TSECID);
							$data['MY_STID'] = $MY_STID;
							$data['MY_TSECID'] = $MY_TSECID;
	
	
	
							// GET RESULT (working)
							// $qry_result="SELECT SUM(TOTAL_Q) TOTAL_Q,SUM(RESULT) RESULT,TESTDATE,
							// ISNULL((SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=$TESTID AND TSG_GROUPNO=TT.GROUPNO AND ISDELETED=0 AND SCORE=SUM(RESULT)),0) SCALE
							// FROM
							// (
							// SELECT (QUESTIONS) AS TOTAL_Q,(SCORE) AS RESULT,
							// 		isnull((SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0),0) GROUPNO,
							// 		CONVERT(VARCHAR,TESTDATE,106)TESTDATE,SCALE
							// 		FROM STUDENT_TESTS ST WHERE ISDELETED=0 AND ATTEMPT=$ATT AND REGID=$userid AND TESTID=$TESTID AND TSECID IN ($MY_TSECID)
							// ) TT
							// GROUP BY GROUPNO,TESTDATE";
							$qry_result="SELECT SUM(TOTAL_Q) TOTAL_Q,SUM(RESULT) RESULT,TESTDATE,
							ISNULL((SELECT SCALE FROM SCALE_SCORE_MASTER WHERE TESTID=$TESTID AND TSG_GROUPNO=TT.GROUPNO AND ISDELETED=0 AND SCORE=SUM(RESULT)),0) SCALE
							FROM
							(
							SELECT (QUESTIONS) AS TOTAL_Q,
									CASE WHEN SCORE = 0
										THEN (SELECT COUNT(RESULT) FROM STUDENT_TEST_RESULTS_FREE WHERE STID=ST.STID AND RESULT>0 AND ISDELETED=0)
										ELSE SCORE
									END RESULT,
									isnull((SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID AND ISDELETED=0),0) GROUPNO,
									CONVERT(VARCHAR,TESTDATE,106)TESTDATE,SCALE
									FROM STUDENT_TESTS_FREE ST WHERE ISDELETED=0 AND ATTEMPT=$ATT AND REGID=$userid AND TESTID=$TESTID AND TSECID IN ($MY_TSECID)
							) TT
							GROUP BY GROUPNO,TESTDATE";
							$data['qry_result'][] = $qry_result;
							// echo json_encode($data);exit;
							$stmt_result = sqlsrv_query($mysqli, $qry_result);
							$RESULT = 0;
							while($row_result = sqlsrv_fetch_array($stmt_result, SQLSRV_FETCH_ASSOC)){
								$TOTAL_RESULT = (int) $row_result['TOTAL_Q'];
								$RESULT += (int) $row_result['RESULT'];
								$TESTDATE = $row_result['TESTDATE'];
								$SCALE = $row_result['SCALE'];
							}
							$RS[$a]['RESULT'] = $RESULT.' / '.$TOTAL_RESULT;
							$RS[$a]['TESTDATE'] = $TESTDATE;
							$RS[$a]['SCALE'] = $SCALE;
								
							// GET SCALE (working)
						}
						$row['RESULT'] = $RS;
						// $row['SCALE'] = $SS;
	
						// $row['SCALE_SCORE'] = $SS;
						// $data['data'][] = $row;
						$data['data'][$TT_INDEX]['INFO'][] = $row;
						$data['AllTSECID'][] =(string)$row['TSECID'];
					
					$TS_INDEX++;
					}
					$data['success'] = true;
				}else{
					
					$data['success'] = false;
				}
				
				
			$TT_INDEX++;
			}
		}
		else{
			$data['message'] = 'Assessment Not Found.';
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
/*============ Get Test By Student Products =============*/ 






/*============ Get Test Sections =============*/
function getTestSections($mysqli){
	try
	{
		global $userid;
		$testid_array = array();
		$tsecid_array = array();
		$testidNew_array = array();
		$allid = array();
		$data = array();
		$chkSectionForTest = (!empty($_POST['chkSectionForTest'])) ? $_POST['chkSectionForTest'] : 0;
		if($chkSectionForTest == 0){throw new Exception ('Tsecid Missing.');}
		$chkSectionForTest = array_diff($chkSectionForTest, [0]);
		$chkSectionForTest = implode(",",$chkSectionForTest);
		// $data['chkSectionForTest']=$chkSectionForTest;
		// echo json_encode($data);exit;

		$query = "SELECT TSECID,TESTID,TESTSECTION,DISPLAYALL,ISNULL((SELECT QUEPERPAGE),0)QUEPERPAGE,MAXQUESTIONS,MAXSCORE,MAXSCALE,DURATION,SEQNO
		FROM TEST_SECTIONS WHERE ISDELETED=0 AND TSECID IN ($chkSectionForTest) ORDER BY SEQNO";

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
				$tsecid_array[] = $row['TSECID'];
				$testid_array[] = $row['TESTID'];
			}
			
			// $tsecid_array = array_diff($tsecid_array, [0]);

			$data['success'] = true;	
			$data['query']=$query;
			// echo json_encode($data);exit;
			$data['tsecid_array'] = $tsecid_array;
			foreach($testid_array as $v){
				isset($k[$v]) || ($k[$v]=1) && $testidNew_array[] = $v;
			}
			$data['testid_array'] = $testidNew_array;
			
			
			for($i=0; $i<count($testidNew_array); $i++){
				$ss = $testidNew_array[$i];
				$allid[$i]['TID']=$ss;
				$querys = "SELECT TSECID,TESTID,TESTSECTION,DISPLAYALL,QUEPERPAGE,MAXQUESTIONS,MAXSCORE,MAXSCALE,DURATION 
				FROM TEST_SECTIONS WHERE ISDELETED=0 AND TSECID IN ($chkSectionForTest) AND TESTID=$ss";
				$results = sqlsrv_query($mysqli, $querys);
				$c = 0;
				while ($rows = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
					$allid[$i]['TSID'][] = $rows['TSECID'];
				$c++;
				}
			}
			$data['TEST_TSEC_ID'] = $allid;

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
/*============ Get Test Section =============*/ 






/*============ SaveTest Data =============*/ 
function SaveTest($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
	//    $Testid=($_POST['Testid'] == 'undefined' || $_POST['Testid'] == '') ? 0 : $_POST['Testid'];
	   $Attempt=($_POST['Attempt'] == 'undefined' || $_POST['Attempt'] == '') ? 0 : $_POST['Attempt'];
	   $tsecid_array = (!empty($_POST['tsecid_array'])) ? $_POST['tsecid_array'] : 0 ;
	   if($tsecid_array == 0)
	   {throw new Exception("TsecId Array Error.");}
	   $tsecid_array = explode(',',$tsecid_array);
	//    $data['$tsecid_array'] = $tsecid_array;
	   
	   $testid_array = (!empty($_POST['testid_array'])) ? $_POST['testid_array'] : 0 ;
	   if($testid_array == 0)
	   {throw new Exception("TestId Array Error.");}
	   $testid_array = explode(',',$testid_array);
	//    $data['$testid_array'] = $testid_array;
	   
	 
	   $testid_tsecid_array = $_POST['testid_tsecid_array'];
	   $testid_tsecid_array = json_decode($testid_tsecid_array, true);
	//    $data['testid_tsecid_array'] = $testid_tsecid_array;
	
	for($t=0; $t<COUNT($testid_tsecid_array); $t++){
			$test = $testid_tsecid_array[$t]['TID'];
			$data['test'][] = $test;

			for($i=0;$i<count($testid_tsecid_array[$t]['TSID']);$i++){
				$tsec = $testid_tsecid_array[$t]['TSID'][$i];
				$data['tsec'][] = $tsec;
				
				// GET ATTEMPTS
				$qurGetAttempt = "SELECT ISNULL(MAX(ATTEMPT),0) ATTEMPT FROM STUDENT_TESTS_FREE 
				WHERE ISDELETED=0 AND REGID=$userid AND TESTID=$test AND TSECID=$tsec";
				$qurGetAttempt_stmt=sqlsrv_query($mysqli, $qurGetAttempt);
				$qurGetAttempt_row = sqlsrv_fetch_array($qurGetAttempt_stmt);
				$ATTEMPT =  $qurGetAttempt_row['ATTEMPT']+1;
				$data['CURRENT_ATTEMPT'] = $ATTEMPT;
				
				
				// GET QUESTION
				$que_query = "SELECT QUEID,(SELECT CORRECTANSWER FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID)CORRECTANSWER
				FROM TEST_SECTION_QUESTIONS TSQ WHERE ISDELETED=0 AND TESTID=$test AND TSECID=$tsec AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)";
				$data['$que_query'][] = $que_query;
				$que_count = unique($que_query);
				$que_stmt=sqlsrv_query($mysqli, $que_query);


				// GET TGID
				$TGID_query = "SELECT TGID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TESTID=$test";
				$TGID_stmt=sqlsrv_query($mysqli, $TGID_query);
				$TGID_row = sqlsrv_fetch_array($TGID_stmt,SQLSRV_FETCH_ASSOC);
				$TGID = (!isset($TGID_row['TGID']))?0:(int)$TGID_row['TGID'];
				
				// $data['$que_count'] = $que_count;
				// echo json_encode($data);exit;

				if($que_count > 0){

					$query="EXEC [STUDENT_TESTS_FREE_SP] 1,0,$userid,$test,$tsec,$ATTEMPT,$TGID,$que_count";
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
						$row = sqlsrv_fetch_array($stmt);
						$data['GET_STID'][] = (int) $row['STID'];
						// $data['GET_STID'][] = $qurGetSTID_row['STID'];
						$GET_STID = (int) $row['STID'];
			
						while($que_row = sqlsrv_fetch_array($que_stmt)){
							$queid = $que_row['QUEID'];
							$correctans = $que_row['CORRECTANSWER'];
							// insert Answer
							$save_ans = "EXEC [STUDENT_TEST_RESULTS_FREE_SP] 1,0,$userid,$GET_STID,$tsec,$queid,'$correctans','',0,''";
							$ans_stmt = sqlsrv_query($mysqli, $save_ans);
						}
						if($ans_stmt === false)
						{
							// die( print_r( sqlsrv_errors(), true));
							// 		throw new Exception( $mysqli->sqlstate.' - '. $query );

							// UPDATE STUDENT TEST


							$data['success'] = false;
							$data['save_ans'] = $save_ans;
							echo json_encode($data);exit;
						}
						else{
							$data['success'] = true;
							$data['save_ans'][] = $save_ans;
						}
						
						
						$data['query'] = $query;
						$data['success'] = true;
						$data['message'] = 'Record successfully inserted.';
					}
				}
				else{
					
					$data['success'] = false;
					$data['message'] = 'Questions Not Found.';
					echo json_encode($data);exit;
				}
			}
		}
		echo json_encode($data);exit;
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Test already attempt';
		// 	echo json_encode($data);exit;
		// }

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
/*============ SaveTest Data =============*/






/*============ Save Essay Data =============*/ 
function saveEssay($mysqli){
	try
	{
		$data = array();
		global $userid;

		$userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
		$EssEssid = ($_POST['EssEssid'] == 'undefined' || $_POST['EssEssid'] == '') ? 0 : $_POST['EssEssid'];
		$EssTestid = ($_POST['EssTestid'] == 'undefined' || $_POST['EssTestid'] == '') ? 0 : $_POST['EssTestid'];
		$EssTsecid = ($_POST['EssTsecid'] == 'undefined' || $_POST['EssTsecid'] == '') ? 0 : $_POST['EssTsecid'];
		if($EssEssid == 0)throw new Exception('Essid not found.');
		if($EssTestid == 0)throw new Exception('Testid not found.');
		if($EssTsecid == 0)throw new Exception('Tsecid not found.');

		

		$GET_STESSID = 0;
		// GET LATEST STESSID ID
		$getSTESSID = "SELECT TOP 1 STESSID FROM STUDENT_ESSAYS_FREE WHERE ISDELETED=0 AND TESTID=$EssTestid AND TSECID=$EssTsecid AND INSERTID=$userid ORDER BY STESSID DESC";
		$stmtSTESSID = sqlsrv_query($mysqli, $getSTESSID);
		$countSTESSID = unique($getSTESSID);
		if($countSTESSID > 0){
			$rowSTESSID = sqlsrv_fetch_array($stmtSTESSID, SQLSRV_FETCH_ASSOC);
			$GET_STESSID =  $rowSTESSID['STESSID'];
		}else{
			$GET_STESSID =  0;
		}
		
		// DELETE OLD ESSAY
		// $updQuery="UPDATE STUDENT_ESSAYS_FREE 
		// SET ISDELETED=1
		// WHERE STESSID IN (SELECT TOP 1 STESSID FROM STUDENT_ESSAYS_FREE WHERE ISDELETED=0 AND TESTID=$EssTestid AND TSECID=$EssTsecid AND INSERTID=$userid ORDER BY STESSID DESC)";
		// sqlsrv_query($mysqli, $updQuery);
		// $data['$updQuery']=$updQuery;
		// if($GET_STESSID>0){

			// $updQuery="UPDATE STUDENT_ESSAYS_FREE 
			// SET ISDELETED=1 WHERE STESSID=$GET_STESSID; UPDATE ESSAY_GRADING SET ISDELETED=1 WHERE STESSID=$GET_STESSID";
			// sqlsrv_query($mysqli, $updQuery);
			// $data['$updQuery']=$updQuery;
	
			$query="EXEC [STUDENT_ESSAYS_FREE_SP] 1,0,$EssEssid,$EssTestid,$EssTsecid,'',0,0,$userid";
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
				$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
				$data['STESSID'] = (int)$row['STESSID'];

				// GET RUBRICS
				$qryRub = "SELECT RMID,CRITERIA,ALLOTEDMARKS FROM RUBERIC_MASTER RM WHERE ISDELETED=0 AND TESTID=$EssTestid ORDER BY CRITERIA";
				$data['qryRub'] = $qryRub;
				$countRub = unique($qryRub);
				if($countRub > 0){
					$stmtRub=sqlsrv_query($mysqli, $qryRub);
					while($rowRub = sqlsrv_fetch_array($stmtRub, SQLSRV_FETCH_ASSOC)){
						$data['RUBRICS'][] = $rowRub;
					}

				}else{
					$data['RUBRICS'] = '';
				}


				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
		// }
		// else{
		// 	$data['success'] = false;
		// 	$data['message'] = 'STESSID Error.';
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
/*============ Save Essay Data =============*/







/*============ UPDATE ESSAY =============*/
function UpdateEssay($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
	   $STESSID=($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
	   $txtEssay=($_POST['txtEssay'] == 'undefined' || $_POST['txtEssay'] == '') ? '' : $_POST['txtEssay'];
	   $total_words=($_POST['total_words'] == 'undefined' || $_POST['total_words'] == '') ? 0 : $_POST['total_words'];
	   $total_chars=($_POST['total_chars'] == 'undefined' || $_POST['total_chars'] == '') ? 0 : $_POST['total_chars'];

	   if($STESSID == 0)
	   {throw new Exception("STESSID Error.");}
	   
	   $txtEssay=str_replace("'", "''", $txtEssay);

		$query="EXEC [STUDENT_ESSAYS_FREE_SP] 2,$STESSID,0,0,0,'$txtEssay',$total_words,$total_chars,$userid";
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
/*============ UPDATE ESSAY =============*/







/*============ UPDATE ANSWER =============*/
function UpdateAnswer($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
	   $Testid=($_POST['Testid'] == 'undefined' || $_POST['Testid'] == '') ? 0 : $_POST['Testid'];
	   $STID=($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];
	   $Tsecid=($_POST['Tsecid'] == 'undefined' || $_POST['Tsecid'] == '') ? 0 : $_POST['Tsecid'];
	   $queid=($_POST['queid'] == 'undefined' || $_POST['queid'] == '') ? 0 : $_POST['queid'];
	   $ans=($_POST['ans'] == 'undefined' || $_POST['ans'] == '') ? '' : $_POST['ans'];
	   $ans=str_replace("'","''",$ans);
	   $correct_ans=($_POST['correct_ans'] == 'undefined' || $_POST['correct_ans'] == '') ? '' : $_POST['correct_ans'];
	   $CanvasImg=($_POST['CanvasImg'] == 'undefined' || $_POST['CanvasImg'] == '') ? '' : $_POST['CanvasImg'];


	//    if($Testid == 0)
	//    {throw new Exception("TestId Error.");}
	   if($STID == 0)
	   {throw new Exception("STID Error.");}
	   if($Tsecid == 0)
	   {throw new Exception("Tsecid Error.");}
	   if($queid == 0)
	   {throw new Exception("Queid Error.");}
	   if($ans == '')
	   {throw new Exception("Please Enter Answer");}

	   $result = strtolower($ans) == strtolower($correct_ans) ? 1 : 0;

	   	if(strpos($correct_ans, ';') !== false ) {
			$CA = explode(";",$correct_ans);

			for($i=0; $i<count($CA); $i++){
				$array_text=$CA[$i];

				if(strtolower($ans) == strtolower($array_text)){
					$result = 1;
					break;
				}
			}
		}
	   

		$query="EXEC [STUDENT_TEST_RESULTS_FREE_SP] 2,0,$userid,$STID,$Tsecid,$queid,'$correct_ans','$ans',$result,'$CanvasImg'";
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
			$data['message'] = 'Question successfully updated.';
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
/*============ UPDATE ANSWER =============*/









/*============ Get Section Questions =============*/ 
function getSectionQuestions($mysqli){
	try
	{
		global $userid;
		$data = array();

		$userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
		$testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
		$tsecid = ($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
		$DisplayAll = ($_POST['DisplayAll'] == 'undefined' || $_POST['DisplayAll'] == '') ? '' : $_POST['DisplayAll'];
		$PerPageQue = ($_POST['PerPageQue'] == 'undefined' || $_POST['PerPageQue'] == '') ? '' : $_POST['PerPageQue'];
		if($testid == 0){throw new Exception ('Testid Not Found.');}
		if($tsecid == 0){throw new Exception ('Tsectid Not Found.');}
		if($DisplayAll == ''){throw new Exception ('Display All Not Found.');}
		if($PerPageQue == ''){throw new Exception ('Per Page Number Not Found.');}

		// $query = "SELECT TSQID,QUEID,
		// (SELECT QUEIMAGE FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUEIMAGE,
		// (SELECT QUESTION FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUESTION,
		// (SELECT QUETYPE FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUETYPE,
		// (SELECT QUEOPTIONS FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUEOPTIONS,
		// (SELECT CORRECTANSWER FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)CORRECTANSWER,
		// (SELECT ALLOWEDCALC FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)ALLOWEDCALC,
		// (SELECT TOP 1 STID FROM STUDENT_TESTS_FREE WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND REGID=$userid ORDER BY ATTEMPT DESC)STID
		// FROM TEST_SECTION_QUESTIONS TSQ
		// WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)
		// ORDER BY (SELECT LEN(QUESTION) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID),QUESTION";
		$query = "SELECT TSQID,QUEID,
		(SELECT QUEIMAGE FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUEIMAGE,
		(SELECT QUESTION FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUESTION,
		(SELECT QUETYPE FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUETYPE,
		(SELECT QUEOPTIONS FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)QUEOPTIONS,
		(SELECT CORRECTANSWER FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)CORRECTANSWER,
		(SELECT ALLOWEDCALC FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)ALLOWEDCALC,
		(SELECT TOP 1 STID FROM STUDENT_TESTS_FREE WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND REGID=$userid ORDER BY ATTEMPT DESC)STID
		FROM TEST_SECTION_QUESTIONS TSQ
		WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)
		ORDER BY 
		CASE WHEN (SELECT LEFT(QUESTION,1) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)='Q' AND
				ISNUMERIC((SELECT SUBSTRING(QUESTION, 2, 1) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID))=1
			THEN (SELECT LEN(SUBSTRING(QUESTION, 2, 1)) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)
			ELSE (SELECT LEN(QUESTION) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)
		END
		--,CASE WHEN (SELECT LEFT(QUESTION,1) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)='Q' AND
		--		ISNUMERIC((SELECT SUBSTRING(QUESTION, 2, 1) FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID))=1
		--	THEN ''
		--	ELSE (SELECT QUESTION FROM QUESTION_BANK WHERE ISDELETED=0 AND QUEID=TSQ.QUEID)
		--END
		";
		$data['query'] = $query;

		$RCOUNT = unique($query);
		
		$QUESTIONS = array();
		$ChunkQuestion = array();
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				if($row['QUETYPE'] == 'MCQ'){
					$row['QUEOPTIONS'] = rtrim($row['QUEOPTIONS'], ';#;');
					$QUESTIONS = explode(';#;,', $row['QUEOPTIONS']);
				}
				else{
					$QUESTIONS = [];
				}
				$row['QUEOPTIONS_LIST'] = $QUESTIONS;
				$data['data'][] = $row;
			}

			if($DisplayAll == 0){
				$ChunkQuestion = array_chunk($data['data'],$PerPageQue);
			}
			else{
				$ChunkQuestion = [];
			}

			$data['ChunkQuestion'][] = $ChunkQuestion;
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
/*============ Get Section Questions =============*/ 






/*============ GET RESULT ESSAY =============*/ 
function getEssayResult($mysqli){
	try
	{
		global $userid;
		$data = array();


	  	$STESSID = ($_POST['STESSID'] == 'undefined' || $_POST['STESSID'] == '') ? 0 : $_POST['STESSID'];
		if($STESSID == 0){throw new Exception('STESSID not found.');}

		$query = "SELECT ESSID,(SELECT ESSTOPIC FROM ESSAYS WHERE ISDELETED=0 AND ESSID=SE.ESSID)TOPIC,
		TESTID,(SELECT TESTDESC FROM TEST_MASTER  WHERE TESTID=SE.TESTID)TEST,TSECID,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=SE.TSECID)SECTION,ESSAY,
		TOTAL_WORD,TOTAL_CHAR,CONVERT(VARCHAR,INSERTDATE,100)TESTDATE,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME
		FROM STUDENT_ESSAYS_FREE SE
		WHERE ISDELETED=0 AND STESSID=$STESSID";

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Essay not found.';
			$data['success'] = false;
		}
		$data['query']=$query;
		echo json_encode($data);exit;
		
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET RESULT ESSAY  =============*/ 






/*============ GET RESULT BY SECTION  =============*/ 
function getResultBySections($mysqli){
	try
	{
		global $userid;
		$data = array();

		// $testid_array = $_POST['testid_array'] == 'undefined' ? '' : $_POST['testid_array'];
		// $tsecid_array = $_POST['tsecid_array'] == 'undefined' ? '' : $_POST['tsecid_array'];
	   	$userid = ($_POST['REGID_INDEX'] == 'undefined' || $_POST['REGID_INDEX'] == '') ? 0 : $_POST['REGID_INDEX'];
		// echo json_encode($data);exit;
		$testid_array = (!empty($_POST['testid_array'])) ? $_POST['testid_array'] : 0 ;
		if($testid_array == 0)
		{throw new Exception("TestId Array Error.");}
		$testid_array = implode(",",$testid_array);
		// $testid_array = explode(',',$testid_array);
		$data['$testid_array']=$testid_array;
		
		
		$tsecid_array = (!empty($_POST['tsecid_array'])) ? $_POST['tsecid_array'] : 0 ;
		if($tsecid_array == 0)
		{throw new Exception("TsecId Array Error.");}
		$tsecid_array = implode(",",$tsecid_array);
		$data['$tsecid_array']=$tsecid_array;
		// $tsecid_array = explode(',',$tsecid_array);


		$CURRENT_ATTEMPT = $_POST['CURRENT_ATTEMPT'] == 'undefined' ? 0 : $_POST['CURRENT_ATTEMPT'];
		if($CURRENT_ATTEMPT == 0){throw new Exception('Attempt Not Found.');}


	  	$GET_STID = (!empty($_POST['GET_STID'])) ? $_POST['GET_STID'] : 0 ;
	   	$data['GET_STID'] = $GET_STID;
		$GET_STID = implode(",",$GET_STID);
		// echo json_encode($data);exit;


		$SCORE = 0;
		$SCALE = 0;
		// ========== GET SCALE/SCORE ==========
		$STID_ARR = explode(",",$GET_STID);
		$data['$STID_ARR'] = $STID_ARR;

		$TSECID_ARR = explode(",",$tsecid_array);
		$data['$TSECID_ARR'] = $TSECID_ARR;

		$GET_TESTID = 0;
		$GET_TSECID = 0;
		$GET_SCORE = 0;
		for($i=0;$i<count($STID_ARR);$i++){
			$ST = $STID_ARR[$i];

			// GET TSECID,TESTID
			$getIDS="SELECT TESTID,TSECID FROM STUDENT_TESTS_FREE WHERE STID=$ST AND ISDELETED=0";
			$stmtIDS = sqlsrv_query($mysqli, $getIDS);
			if($stmtIDS){
				$rowIDS = sqlsrv_fetch_array($stmtIDS,SQLSRV_FETCH_ASSOC);
				$GET_TESTID = (int) $rowIDS['TESTID'];
				$GET_TSECID = (int) $rowIDS['TSECID'];


				// GET SCORE
				$getScore="SELECT SUM(CAST(RESULT AS INT)) SCORE FROM STUDENT_TEST_RESULTS_FREE 
						WHERE (SELECT TESTID FROM STUDENT_TESTS_FREE WHERE STID=$ST)=$GET_TESTID AND TSECID=$GET_TSECID
						AND STID=$ST";
				$data['$getScore'][] =$getScore;
				$stmtScore = sqlsrv_query($mysqli, $getScore);
				if($stmtScore){
					$rowScore = sqlsrv_fetch_array($stmtScore,SQLSRV_FETCH_ASSOC);
					$GET_SCORE = (int) $rowScore['SCORE'];
				}
				// GET SCALE
				$getScale="SELECT SCALE FROM SCALE_SCORE_MASTER WHERE  SCORE=
				(SELECT SUM(CAST(RESULT AS INT)) FROM STUDENT_TEST_RESULTS_FREE 
				WHERE (SELECT TESTID FROM STUDENT_TESTS_FREE WHERE STID=$ST)=$GET_TESTID AND TSECID=$GET_TSECID
				AND STID=$ST) AND TESTID=(SELECT TESTID FROM STUDENT_TESTS_FREE WHERE STID=$ST)
				AND TSG_GROUPNO=(SELECT GROUPNO FROM TEST_SECTION_GROUPS WHERE TSECID=$GET_TSECID AND TESTID=(SELECT TESTID FROM STUDENT_TESTS_FREE WHERE STID=$ST))";
				$data['$getScale'][] =$getScale;
				$stmtScale = sqlsrv_query($mysqli, $getScale);
				if($stmtScale){
					$rowScale = sqlsrv_fetch_array($stmtScale,SQLSRV_FETCH_ASSOC);
					$GET_SCALE = (int) $rowScale['SCALE'];
				}
				$GET_SCORE  = (!$GET_SCORE) ? 0 : $GET_SCORE;
				$GET_SCALE = (!$GET_SCALE) ? 0 : $GET_SCALE;
				$data['$GET_SCALE']=$GET_SCALE;
				// ========= UPDATE END TIME =========
					$query_EndTime = "UPDATE STUDENT_TESTS_FREE SET ENDDATETIME=GETDATE(),SCORE=$GET_SCORE,SCALE=$GET_SCALE WHERE REGID=$userid AND 
					STID =$ST";
					$data['$query_EndTime']=$query_EndTime;

					$stmt_EndTime = sqlsrv_query($mysqli, $query_EndTime);
					if($stmt_EndTime === false){
						// die( print_r( sqlsrv_errors(), true));
						// throw new Exception( $mysqli->sqlstate.' - '. $query );
						$data['success'] = false;
						$data['query_EndTime'] = $query_EndTime;
						// echo json_encode($data);exit;
					}
				// ========= UPDATE END TIME =========


			}else{
				throw new Exception('Something went wrong.');
			}
		}


		$query = "SELECT STID,
		(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=ST.TESTID)TEST,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID)SECTION,
		CONVERT(VARCHAR,TESTDATE,100)TESTDATE,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100)STARTDATETIME,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100)ENDDATETIME,
		(SELECT COUNT(QUEID) FROM STUDENT_TEST_RESULTS_FREE 
		WHERE ISDELETED=0 AND STID=ST.STID AND TESTID=ST.TESTID AND TSECID=ST.TSECID)TOTAL_QUE,
		(SELECT COUNT(*) FROM STUDENT_TEST_RESULTS_FREE 
		WHERE ISDELETED=0 AND STID=ST.STID AND TESTID=ST.TESTID AND TSECID=ST.TSECID AND RESULT=1)CURRECT_ANS,
		(SELECT COUNT(*) FROM STUDENT_TEST_RESULTS_FREE 
		WHERE ISDELETED=0 AND STID=ST.STID AND TESTID=ST.TESTID AND TSECID=ST.TSECID AND RESULT=0)WRONG_ANS
		FROM STUDENT_TESTS_FREE ST WHERE ISDELETED=0 AND REGID=$userid AND TSECID IN ($tsecid_array) AND TESTID IN ($testid_array)
		AND STID IN ($GET_STID) ORDER BY (SELECT SEQNO FROM TEST_SECTIONS WHERE TSECID=ST.TSECID)";

		// $data['$query']=$query;
		// echo json_encode($data);exit;
		

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['success'] = false;
		}
		$data['$query']=$query;
		echo json_encode($data);exit;
		
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET RESULT BY SECTION  =============*/ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







