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
        case "saveData":saveData($conn);break;
        case "getTestByRegid":getTestByRegid($conn);break;
        case "getTestSection":getTestSection($conn);break;
        case "getStudentTestResult":getStudentTestResult($conn);break;
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



// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$stid = ($_POST['stid'] == 'undefined' || $_POST['stid'] == '') ? 0 : $_POST['stid'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$ddlTest = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
		$ddlTestSection = ($_POST['ddlTestSection'] == 'undefined' || $_POST['ddlTestSection'] == '') ? 0 : $_POST['ddlTestSection'];
		$txtTestDate = $_POST['txtTestDate'] == 'undefined' ? '' : $_POST['txtTestDate'];
		$txtstartDate = $_POST['txtstartDate'] == 'undefined' ? '' : $_POST['txtstartDate'];
		$txtEndDate = $_POST['txtEndDate'] == 'undefined' ? '' : $_POST['txtEndDate'];
		$txtScore = ($_POST['txtScore'] == 'undefined' || $_POST['txtScore'] == '') ? 0 : $_POST['txtScore'];
		$txtScale = ($_POST['txtScale'] == 'undefined' || $_POST['txtScale'] == '') ? 0 : $_POST['txtScale'];
		$txtReviewed = ($_POST['txtReviewed'] == 'undefined' || $_POST['txtReviewed'] == '' || $_POST['txtReviewed'] == '0') ? 0 : 1;
		// $GROUPNO = ($_POST['GROUPNO'] == 'undefined' || $_POST['GROUPNO'] == '') ? 0 : $_POST['GROUPNO'];

		// === IMAGE
		$existingDocsUpload  = $_POST['existingDocsUpload'] == 'undefined' ? '' : $_POST['existingDocsUpload'];
		$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
		if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0 && $_FILES['DocsUpload']['size'] > 1048576) throw new Exception('File size too large.');
		// === IMAGE
    
		$actionid = $stid == 0 ? 1 : 2;

		if($ddlStudent == 0){throw new Exception("Please Select 'Student Name'.");}
		if($ddlTest == 0){throw new Exception("Please Select 'Test Name'.");}
		if($ddlTestSection == 0){throw new Exception("Please Select 'Test Section Name'.");}
		if($txtTestDate == ''){throw new Exception("Please Select 'Test Date'.");}
		if($txtScore == ''){throw new Exception("Please Enter 'Test Score'.");}
		if($txtScale == ''){throw new Exception("Please Enter 'Test Scale'.");}


		// GET QUESTION
		$que_query = "SELECT QUEID,(SELECT CORRECTANSWER FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID)CORRECTANSWER
		FROM TEST_SECTION_QUESTIONS TSQ WHERE ISDELETED=0 AND TESTID=$ddlTest AND TSECID=$ddlTestSection 
		AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)";
		$data['$que_query'][] = $que_query;
		$que_count = unique($que_query);
		$que_stmt=sqlsrv_query($mysqli, $que_query);

		// GET ATTEMPTS
		$qurGetAttempt = "SELECT ISNULL(MAX(ATTEMPT),0) ATTEMPT FROM STUDENT_TESTS 
		WHERE ISDELETED=0 AND REGID=$ddlStudent AND TESTID=$ddlTest AND TSECID=$ddlTestSection";
		$qurGetAttempt_stmt=sqlsrv_query($mysqli, $qurGetAttempt);
		$qurGetAttempt_row = sqlsrv_fetch_array($qurGetAttempt_stmt);
		$ATTEMPT =  $qurGetAttempt_row['ATTEMPT']+1;
		$data['CURRENT_ATTEMPT'] = $ATTEMPT;

		// GET TGID
		$TGID =0;
		$qryTGID = "SELECT TGID FROM TEST_GROUPS_DETAILS 
		WHERE ISDELETED=0 AND TESTID=$ddlTest";
		$countTG = unique($qryTGID);
		if($countTG > 0){
			$qurTG_stmt=sqlsrv_query($mysqli, $qryTGID);
			$qurTG_row = sqlsrv_fetch_array($qurTG_stmt);
			$TGID = (int)$qurTG_row['TGID'];
		}
		
		
		// GET GROUPNO
		// $GROUPNO =0;
		// $qryGroupNo = "SELECT GROUPNO FROM TEST_SECTION_GROUPS
		// WHERE ISDELETED=0 AND TESTID=$ddlTest AND TSECID=$ddlTestSection";
		// $countGN = unique($qryGroupNo);
		// if($countGN > 0){
		// 	$qurGN_stmt=sqlsrv_query($mysqli, $qryGroupNo);
		// 	$qurGN_row = sqlsrv_fetch_array($qurGN_stmt);
		// 	$GROUPNO = (int)$qurGN_row['GROUPNO'];
		// }

		// GET SCALE BY SCORE
		// $countScale = 0;
		// if($GROUPNO > 0){
		// 	$gryScale = "SELECT SCALE FROM SCALE_SCORE_MASTER 
		// 	WHERE ISDELETED=0 AND TESTID=$ddlTest AND TSG_GROUPNO=$GROUPNO AND SCORE=$txtScore";
		// 	$countScale = unique($gryScale);
		// }
		
		// if($countScale <= 0){
		// 	throw new Exception('Scale Not Found.');
		// }
		

		$sql = "SELECT * FROM STUDENT_TESTS WHERE REGID=$ddlStudent AND TESTID=$ddlTest AND TSECID=$ddlTestSection AND ATTEMPT=$ATTEMPT AND 
				CONVERT(DATE,TESTDATE,105)='$txtTestDate' AND TGID=$TGID AND SCORE=$txtScore AND STID!=$stid AND ISDELETED=0";
		// $data['$sql']=$sql;
		// echo json_encode($data);exit;
		$row_count = unique($sql);

		$data = array();
		
		if($row_count == 0)
		{
			if($actionid == 1){
				$query="EXEC [STUDENT_TESTS_SP] $actionid,$stid,$ddlStudent,$ddlTest,$ddlTestSection,$ATTEMPT,$TGID,$que_count";
				$stmt=sqlsrv_query($mysqli, $query);

			// ################
			// ######### UPDATE
			// ################
			}else if($actionid == 2){
				$updScaleScore = "UPDATE STUDENT_TESTS SET TESTID=$ddlTest,TSECID=$ddlTestSection,TESTDATE='$txtTestDate', 
								STARTDATETIME='$txtstartDate',ENDDATETIME='$txtEndDate', SCORE=$txtScore,SCALE=$txtScale,REVIEWED=$txtReviewed 
								 WHERE STID=$stid";
				$data['$updScaleScore']=$updScaleScore;
				sqlsrv_query($mysqli, $updScaleScore);

				//========== IMAGE
				$DocsUpload = '';
				if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0){
					$ext = pathinfo($_FILES['DocsUpload']['name'],PATHINFO_EXTENSION);
					$DocsUpload .= strtolower($stid.'_upload_test_'.time().'.'.$ext);
				}
				
				if($DocsUpload != ''){
					$insertimage = "UPDATE STUDENT_TESTS SET DOCS='$DocsUpload' WHERE STID=$stid";
					sqlsrv_query($mysqli,$insertimage);

					if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["DocsUpload"]["tmp_name"], '../images/upload_test_result/'.$DocsUpload);
					}
	
					if(isset($_FILES['DocsUpload']['name']) && $existingDocsUpload != '')
					{
						if (file_exists('../images/upload_test_result/'.$existingDocsUpload))
						{
							unlink('../images/upload_test_result/'.$existingDocsUpload);
						}
					}
				}
	
				if($chkRemoveImgOnUpdate>0 && $existingDocsUpload!='' && $DocsUpload==''){
					$insertimage = "UPDATE STUDENT_TESTS SET DOCS='' WHERE STID=$stid";
					sqlsrv_query($mysqli,$insertimage);
					
					if (file_exists('../images/upload_test_result/'.$existingDocsUpload))
					{
						unlink('../images/upload_test_result/'.$existingDocsUpload);
					}
				}
				//========== IMAGE
				
				$data['updScaleScore'] = $updScaleScore;
				$data['success'] = true;
				$data['message'] = 'Record successfully updated.';
				echo json_encode($data);exit;
			}
			else{
				throw new Exception('Error.');
			}
			
			// ##############
			// ######### SAVE
			// ##############
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$rowStid = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_STID = (int)$rowStid['STID'];

				
				//========== IMAGE
				$DocsUpload = '';
				if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0){
					$ext = pathinfo($_FILES['DocsUpload']['name'],PATHINFO_EXTENSION);
					$DocsUpload .= strtolower($GET_STID.'_upload_test_'.time().'.'.$ext);
				}
				else
				{
					$DocsUpload="";
				}
				
				if($DocsUpload != ''){
					if($GET_STID > 0)
					{
						$insertimage = "UPDATE STUDENT_TESTS SET DOCS='$DocsUpload' WHERE STID=$GET_STID";
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["DocsUpload"]["tmp_name"], '../images/upload_test_result/'.$DocsUpload);
					}
	
					
					if(isset($_FILES['DocsUpload']['name']) && $existingDocsUpload != '')
					{
						if (file_exists('../images/upload_test_result/'.$existingDocsUpload))
						{
							unlink('../images/upload_test_result/'.$existingDocsUpload);
						}
					}
				}

				//========== IMAGE

				$updScaleScore = "UPDATE STUDENT_TESTS SET TESTDATE='$txtTestDate',STARTDATETIME='$txtstartDate',ENDDATETIME='$txtEndDate', 
								SCORE=$txtScore,SCALE=$txtScale,REVIEWED=$txtReviewed WHERE STID=$GET_STID";
				sqlsrv_query($mysqli, $updScaleScore);

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($essid))$data['message'] = 'Record successfully updated.';
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
 // =============== SAVE DATA ==============





/* ========== GET STUDENT TEST RESULT =========== */
 function getStudentTestResult($mysqli){
	try
	{
		$data = array();
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];

		$query = "SELECT STID,REGID,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=ST.REGID)LOCID,TESTID,
		(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=ST.TESTID)TEST,TSECID,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID)TESTSECTION,ATTEMPT,
		CONVERT(VARCHAR,TESTDATE,106)TESTDATE,
		CASE WHEN (STARTDATETIME = '1900-01-01 00:00:00.000' OR STARTDATETIME is null) 
			 THEN ''
			 ELSE CONVERT(VARCHAR,STARTDATETIME,21)
		END STARTDATETIME,
		CASE WHEN (ENDDATETIME = '1900-01-01 00:00:00.000' OR ENDDATETIME is null) 
			 THEN ''
			 ELSE CONVERT(VARCHAR,ENDDATETIME,21)
		END ENDDATETIME,
		TGID,QUESTIONS,SCORE,SCALE,REVIEWED,DOCS
		FROM STUDENT_TESTS ST WHERE ISDELETED=0 AND REGID=$ddlStudent
		ORDER BY TEST,TESTSECTION,CONVERT(DATE,TESTDATE,105) DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['STARTTIME'] = '';
				$row['ENDTIME'] = '';
				if($row['STARTDATETIME']!=''){
					$STARTTIME = date_create($row['STARTDATETIME']);
					$row['STARTTIME'] = date_format($STARTTIME,"h:i:s A");
				}
				if($row['ENDDATETIME']!=''){
					$ENDTIME = date_create($row['ENDDATETIME']);
					$row['ENDTIME'] = date_format($ENDTIME,"h:i:s A");
				}
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Data not found.';
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
/* ========== GET STUDENT TEST RESULT =========== */





/*============ GET TEST BY REGID =============*/ 
 function getTestByRegid($mysqli){
	try
	{
		$data = array();
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		if($ddlStudent == 0) throw new Exception('Studentid not found.');

		$query = "SELECT DISTINCT TM.TESTID,TESTDESC,LEN(TESTDESC)ss,
		ISNULL((SELECT TGID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TESTID=S.TESTID),0)TGID
		FROM TEST_MASTER TM,TEST_SECTIONS S
		WHERE TM.TESTID=S.TESTID AND TM.ISDELETED=0 AND S.ISDELETED=0
		AND TM.TESTID IN (SELECT TESTID FROM TESTS_TO_PRODUCTS WHERE PRODUCTID IN
		(SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID IN 
		(SELECT PLANID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND REGID=$ddlStudent AND DISCONTINUE_APPROVED=0)))
		ORDER BY LEN(TESTDESC),TESTDESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Test not found.';
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
/*============ GET TEST BY REGID =============*/ 





/*============ GET TEST SECTION =============*/ 
 function getTestSection($mysqli){
	try
	{
		$data = array();
		$ddlTest = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
		if($ddlTest == 0) throw new Exception('Testid not found.');

		$query = "SELECT TSECID,TESTSECTION FROM TEST_SECTIONS 
		WHERE ISDELETED=0 AND TESTID=$ddlTest AND TESTSECTION NOT LIKE '%Break%'
		ORDER BY TESTSECTION";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Test Section not found.';
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
/*============ GET TEST SECTION =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STID = ($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];
			if($STID == 0){throw new Exception('STID Error.');}
			$delQuery = "EXEC [STUDENT_TESTS_SP] 3,$STID,0,0,0,0,0,0";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				// die( print_r( sqlsrv_errors(), true));
				// throw new Exception( $mysqli->sqlstate );
				$data['$delQuery'] = $delQuery;
				$data['success'] = false;
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







