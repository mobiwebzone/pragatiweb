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
        case "saveTestMaster":saveTestMaster($conn);break;
        case "getTestMaster":getTestMaster($conn);break;
        case "deleteTestMaster":deleteTestMaster($conn);break;
		
		case "addSectionInGroup":addSectionInGroup($conn);break;
		case "getSectionGroup":getSectionGroup($conn);break;
		case "removeSectionGroup":removeSectionGroup($conn);break;
        
		case "saveTestSection":saveTestSection($conn);break;
        case "getTestSection":getTestSection($conn);break;
        case "deleteTestSection":deleteTestSection($conn);break;
		
		case "saveQuestionSection":saveQuestionSection($conn);break;
        case "getQuestions":getQuestions($conn);break;
        case "getQuestionSection":getQuestionSection($conn);break;
        case "deleteQuestionSection":deleteQuestionSection($conn);break;
		
		case "saveScaledScore":saveScaledScore($conn);break;
        case "getScaledScore":getScaledScore($conn);break;
        case "deleteScaledScore":deleteScaledScore($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ################################### TEST MATER START ################################### */ 


// =============== SAVE TEST MASTER DATA ==================
 function saveTestMaster($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $testid  = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
        $txtTestDesc  = $_POST['txtTestDesc'] == 'undefined' ? '' : $_POST['txtTestDesc'];
        $txtTestYear  = ($_POST['txtTestYear'] == 'undefined' || $_POST['txtTestYear'] == '') ? 0 : $_POST['txtTestYear'];
        $rdTestOpen  = ($_POST['rdTestOpen'] == 'undefined' || $_POST['rdTestOpen'] == '') ? 'Yes' : $_POST['rdTestOpen'];
		$rdMultipleAttempts  = ($_POST['rdMultipleAttempts'] == 'undefined' || $_POST['rdMultipleAttempts'] == '') ? 0 : $_POST['rdMultipleAttempts'];
		$txtNumOfAttempts  = ($_POST['txtNumOfAttempts'] == 'undefined' || $_POST['txtNumOfAttempts'] == '' || $_POST['txtNumOfAttempts'] == 0) ? 1 : $_POST['txtNumOfAttempts'];
        $txtTestRemark  = $_POST['txtTestRemark'] == 'undefined' ? '' : $_POST['txtTestRemark'];
        $chkFreeTest  = ($_POST['chkFreeTest'] == 'undefined' || $_POST['chkFreeTest'] == '0' || $_POST['chkFreeTest'] == '') ? 0 : 1;
		
		$actionid = $testid == 0 ? 1 : 2;

		if($txtTestDesc == ''){throw new Exception("Please Enter Test Description.");}
		if($txtTestYear == 0){throw new Exception("Please Enter Test Year.");}

		$sql = "SELECT * FROM TEST_MASTER WHERE TESTDESC='$txtTestDesc' AND TESTYEAR=$txtTestYear AND TESTID!=$testid AND ISDELETED=0";
		$row_count = unique($sql);

		// throw new Exception($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TEST_MASTER_SP] $actionid,$testid,'$txtTestDesc',$txtTestYear,'$rdTestOpen','$txtTestRemark',$rdMultipleAttempts,$txtNumOfAttempts,$chkFreeTest,$userid";
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
				$data['GET_TESTID'] = $row['TESTID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($testid))$data['message'] = 'Record successfully updated';
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
 // =============== SAVE TEST MASTER DATA ==============





/*============ Get Test Master =============*/ 
 function getTestMaster($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TESTID,TESTDESC,LEN(TESTDESC)CNT,TESTYEAR,TESTOPEN,REMARKS,MULTIPLEATTEMPTS,NUMOFATTEMPTS,FREE_TEST FROM TEST_MASTER 
		WHERE ISDELETED=0 
		ORDER BY TESTDESC ASC";
		// ORDER BY CNT,TESTDESC ASC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Test Master =============*/ 






/* =========== Delete TEST MASTER =========== */ 
function deleteTestMaster($mysqli){
	try{   
			global $userid;
			$data = array();     
            $testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
			if($testid == 0){throw new Exception('Testid Error.');}

			$delQuery = "EXEC [TEST_MASTER_SP] 3,$testid,'',0,'','',0,0,0,$userid";
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
/* =========== Delete TEST MASTER =========== */ 


/* ################################### TEST MATER START ################################### */ 








/* ################################### SECTION GROUP START ################################### */ 

// =============== Add TEST SECTION IN GROUP ==================
function addSectionInGroup($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $testid  = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   if($testid == 0){throw new Exception("TestID Not Found.");}
	   
	   $chkTestSection  = ($_POST['chkTestSection'] == 'undefined' || $_POST['chkTestSection'] == '') ? '' : $_POST['chkTestSection'];
	   if(empty($chkTestSection)){throw new Exception("Select Section First.");}
	   $chkTestSection = explode(',',$chkTestSection);
	//    throw new Exception($chkTestSection);
	//    $data['chkTestSection']=$chkTestSection;
	
	
			//Get Groupno
			$GPNO = "SELECT TOP 1 GROUPNO FROM TEST_SECTION_GROUPS WHERE ISDELETED=0 AND TESTID=$testid ORDER BY TSGID DESC";
			$stmtGPNO = sqlsrv_query($mysqli, $GPNO);
			$GPNO_COUNT = unique($GPNO);
			if($GPNO_COUNT > 0){
				$rowGPNO = sqlsrv_fetch_array($stmtGPNO);
				$GROUPNO = (int)$rowGPNO['GROUPNO']+1;
			}else{
				$GROUPNO = 1;
			}

	//    $sql = "SELECT * FROM TEST_SECTION_GROUPS WHERE GROUPNO=$GROUPNO AND TESTID=$testid AND ISDELETED=0";
	//    $row_count = unique($sql);
	//    $data['$$GPNO']=$GPNO;
	//    echo json_encode($data);exit;


	   $data = array();
	//    if($row_count == 0)
	//    {
		   for($i=0; $i<count($chkTestSection); $i++){
				$TSECID = $chkTestSection[$i]; 

				if($TSECID > 0){
					$query="INSERT INTO TEST_SECTION_GROUPS (GROUPNO,TESTID,TSECID) VALUES($GROUPNO,$testid,$TSECID)";
					$stmt=sqlsrv_query($mysqli, $query);
				}
		   }
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
			   $data['message'] = 'Record successfully inserted.';

			   echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	// 	   $data['success'] = false;
	// 	   $data['message'] = 'Record already exists';
	// 	   echo json_encode($data);exit;
	//    }

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
// =============== Add TEST SECTION IN GROUP ==============



/*============ Get SECTION GROUP =============*/ 
function getSectionGroup($mysqli){
	try
	{
		$data = array();
 
		$TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
		if($TESTID == 0){throw new Exception('TestId Not Found.');}
 
		// $query = "SELECT TSGID,GROUPNO,TESTID,TSECID,(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=TSG.TSECID)TESTSECTION 
		// 	 FROM TEST_SECTION_GROUPS TSG
		// 	 WHERE ISDELETED=0 AND TESTID=$TESTID ORDER BY GROUPNO";

		$query = "SELECT COUNT(*),GROUPNO,TESTID,
		(SELECT (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=TSG2.TSECID AND ISDELETED=0) + ' | ' FROM TEST_SECTION_GROUPS TSG2 WHERE GROUPNO=TSG.GROUPNO AND TESTID=TSG.TESTID AND ISDELETED=0 FOR XML PATH(''))TESTSECTION
		FROM TEST_SECTION_GROUPS TSG
		WHERE ISDELETED=0 AND TESTID=$TESTID
		GROUP BY GROUPNO,TESTID";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TESTSECTION'] = rtrim($row['TESTSECTION'] , ' | ');
				$data['data'][] = $row;
			}
			$data['$query'] = $query;
			$data['success'] = true;
		 }
		 else{
			$data['$query'] = $query;
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
/*============ Get SECTION GROUP =============*/ 



/* =========== REMOVE TEST SECTION IN GROUP =========== */ 
function removeSectionGroup($mysqli){
	try{   
			global $userid;
			$data = array();     
			$testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
			$GROUPNO = ($_POST['GROUPNO'] == 'undefined' || $_POST['GROUPNO'] == '') ? '' : $_POST['GROUPNO'];
			if($testid == 0){throw new Exception('Testid Not Found.');}
			if($GROUPNO == ''){throw new Exception('Group No. Not Found.');}
 
			$delQuery = "UPDATE TEST_SECTION_GROUPS SET ISDELETED=1 WHERE GROUPNO=$GROUPNO AND TESTID=$testid; 
						UPDATE SCALE_SCORE_MASTER SET ISDELETED=1, DELETEID=$userid, DELETEDATE=GETDATE() WHERE TSG_GROUPNO=$GROUPNO AND TESTID=$testid";
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
 /* =========== REMOVE TEST SECTION IN GROUP =========== */ 


/* ################################### SECTION GROUP END ################################### */ 








/* ################################### TEST SECTION START ################################### */ 

// =============== SAVE TEST SECTION DATA ==================
function saveTestSection($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $tsecid  = ($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
	   $testid  = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   $txtTestSection  = $_POST['txtTestSection'] == 'undefined' ? '' : $_POST['txtTestSection'];
	   $rdDisplayAll  = ($_POST['rdDisplayAll'] == 'undefined' || $_POST['rdDisplayAll'] == '') ? 1 : $_POST['rdDisplayAll'];
	   $txtDisplayNOP  = ($_POST['txtDisplayNOP'] == 'undefined' || $_POST['txtDisplayNOP'] == '') ? 0 : $_POST['txtDisplayNOP'];
	   $txtSectionMaxQs  = ($_POST['txtSectionMaxQs'] == 'undefined' || $_POST['txtSectionMaxQs'] == '') ? 0 : $_POST['txtSectionMaxQs'];
	   $txtSectionMaxRowScore  = ($_POST['txtSectionMaxRowScore'] == 'undefined' || $_POST['txtSectionMaxRowScore'] == '') ? 0 : $_POST['txtSectionMaxRowScore'];
	   $txtSectionMaxScaledScore  = ($_POST['txtSectionMaxScaledScore'] == 'undefined' || $_POST['txtSectionMaxScaledScore'] == '') ? 0 : $_POST['txtSectionMaxScaledScore'];
	   $txtTestDurationMin  = ($_POST['txtTestDurationMin'] == 'undefined' || $_POST['txtTestDurationMin'] == '') ? 0 : $_POST['txtTestDurationMin'];
	   $txtSEQNo  = ($_POST['txtSEQNo'] == 'undefined' || $_POST['txtSEQNo'] == '') ? 0 : $_POST['txtSEQNo'];
	   $ddlSection_TS  = ($_POST['ddlSection_TS'] == 'undefined' || $_POST['ddlSection_TS'] == '') ? 0 : $_POST['ddlSection_TS'];
	   $rdShowCalc  = ($_POST['rdShowCalc'] == 'undefined' || $_POST['rdShowCalc'] == '' || $_POST['rdShowCalc'] == '0') ? 0 : 1;
	   
	   $actionid = $tsecid == 0 ? 1 : 2;

	   if($testid == 0){throw new Exception("TestId Not Found.");}
	   if($txtTestSection == ''){throw new Exception("Please Enter Test Section.");}
	   if($txtSectionMaxQs == 0){throw new Exception("Please Enter Max Qs.");}
	//    if($txtSectionMaxRowScore == 0){throw new Exception("Please Enter Max Row Score.");}
	//    if($txtSectionMaxScaledScore == 0){throw new Exception("Please Enter Max Scaled Score.");}
	   if($txtTestDurationMin == 0){throw new Exception("Please Enter Duration in Minutes.");}
	   if($txtSEQNo == 0){throw new Exception("Please Enter SEQ No.");}
	   if($ddlSection_TS == 0){throw new Exception("Please Select Main Category.");}

	//======================== PDF
	$existingContentPdf  = $_POST['existingContentPdf'] == 'undefined' ? '' : $_POST['existingContentPdf'];
	$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
	if(isset($_FILES['txtContentPdf']['name']) && $_FILES['txtContentPdf']['size'] > 0 && $_FILES['txtContentPdf']['size'] > 4194304) throw new Exception('File size too large.');
	$txtContentPdf = '';
	if(isset($_FILES['txtContentPdf']['name']) && $_FILES['txtContentPdf']['size'] > 0){
		$ext = pathinfo($_FILES['txtContentPdf']['name'],PATHINFO_EXTENSION);
		$txtContentPdf .= strtolower('Section_'.rand().'_'.time().'.'.$ext);
	}
	else
	{
		$txtContentPdf=$existingContentPdf;
	}
	//======================== PDF

	   $sql = "SELECT * FROM TEST_SECTIONS WHERE TESTID=$testid AND TESTSECTION='$txtTestSection' AND TSECID!=$tsecid AND ISDELETED=0";
	   $row_count = unique($sql);

	   // throw new Exception($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="EXEC [TEST_SECTIONS_SP] $actionid,$tsecid,$testid,'$txtTestSection',$rdDisplayAll,$txtDisplayNOP,$txtSectionMaxQs,$txtSectionMaxRowScore,
		   		$txtSectionMaxScaledScore,$txtTestDurationMin,$txtSEQNo,$ddlSection_TS,$rdShowCalc,'$txtContentPdf',$userid";
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
			   //========== IMAGE
				if($txtContentPdf != ''){
					// if($GET_SCCDID > 0)
					// {
					// 	$insertimage = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET DOC='$DocsUpload' WHERE SCCDID=$GET_SCCDID";
					// 	sqlsrv_query($mysqli,$insertimage);
					// }


					if(isset($_FILES['txtContentPdf']['name']) && $_FILES['txtContentPdf']['size'] > 0)
					{
						move_uploaded_file($_FILES["txtContentPdf"]["tmp_name"], '../images/question_master/'.$txtContentPdf);
					}

					
					if(isset($_FILES['txtContentPdf']['name']) && $existingContentPdf != '')
					{
						if (file_exists('../images/question_master/'.$existingContentPdf))
						{
							unlink('../images/question_master/'.$existingContentPdf);
						}
					}
				}
				// ###### REMOVE IMAGE ON UPDATE
				if($chkRemoveImgOnUpdate>0 && $existingContentPdf!=''){
					if($tsecid>0){
						$insertimage = "UPDATE TEST_SECTIONS SET [PDFFILE]='' WHERE TSECID=$tsecid";
							sqlsrv_query($mysqli,$insertimage);
					}
					if (file_exists('../images/question_master/'.$existingContentPdf))
					{
						unlink('../images/question_master/'.$existingContentPdf);
					}
				}
				//========== IMAGE


			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($tsecid))$data['message'] = 'Record successfully updated';
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
// =============== SAVE TEST SECTION DATA ==============





/*============ Get Test SECTION =============*/ 
function getTestSection($mysqli){
   try
   {
	   $data = array();

	   $TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
	   $testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   $tid = $TESTID > 0 ? $TESTID : $testid;

	   if($tid == 0){throw new Exception('TestId Not Found.');}

	   $query = "SELECT TSECID,TESTID,TESTSECTION,DISPLAYALL,QUEPERPAGE,MAXQUESTIONS,MAXSCORE,MAXSCALE,DURATION,SEQNO,SECID,
	   			(SELECT SECTION FROM SECTION_MASTER WHERE SECID=TS.SECID)SECTION,
				CASE WHEN (
					(SELECT TESTID FROM TEST_SECTION_GROUPS WHERE TSECID=TS.TSECID AND TESTID=TS.TESTID AND ISDELETED=0)=TESTID OR
					(SELECT TSECID FROM TEST_SECTION_GROUPS WHERE TSECID=TS.TSECID AND TESTID=TS.TESTID AND ISDELETED=0)=TSECID)
					THEN 'YES' ELSE 'NO' 
				END GROUPSTATUS,SHOWCALC,PDFFILE FROM TEST_SECTIONS TS
			WHERE ISDELETED=0 AND TESTID=$tid ORDER BY MAXSCORE";
	   $result = sqlsrv_query($mysqli, $query);
		
	//    $data['$query'] = $query;
	//    echo json_encode($data);exit;

	   $count = unique($query);
	   if($count > 0){
		   while ($row = sqlsrv_fetch_array($result)) {
			   if($TESTID > 0){
					if($row['GROUPSTATUS'] == 'NO'){
						$row['TSECID'] = (string) $row['TSECID'];
						$data['data'][] = $row;
					}
				}
				else{
				   $data['data'][] = $row;
			   }
		   }
		   $data['1$query'] = $query;
		   $data['success'] = true;
		}
		else{
		   $data['$query'] = $query;
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
/*============ Get Test SECTION =============*/ 





/* =========== Delete TEST SECTION =========== */ 
function deleteTestSection($mysqli){
   try{   
		   global $userid;
		   $data = array();     
		   $tsecid = ($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
		   if($tsecid == 0){throw new Exception('Tsecid Error.');}

		   $delQuery = "EXEC [TEST_SECTIONS_SP] 3,$tsecid,0,'',0,0,0,0,0,0,0,0,0,'',$userid";
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
/* =========== Delete TEST SECTION =========== */ 

/* ################################### TEST SECTION START ################################### */ 










/* ################################### QUESTION SECTION START ################################### */ 
 // =============== GET QUESTIONS DATA ==============
 function getQuestions($mysqli){
	try
	{
		$data = array();
		$ddlSection = ($_POST['ddlSection'] == 'undefined' || $_POST['ddlSection'] == '') ? 0 : $_POST['ddlSection'];
		$ddlCategory = ($_POST['ddlCategory'] == 'undefined' || $_POST['ddlCategory'] == '') ? 0 : $_POST['ddlCategory'];
		$ddlSubCategory = ($_POST['ddlSubCategory'] == 'undefined' || $_POST['ddlSubCategory'] == '') ? 0 : $_POST['ddlSubCategory'];
		$ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];

		$query = "SELECT QUEID,TOPICID,
				(SELECT SECID FROM SECTION_CATEGORIES WHERE 
				CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))SECID,

				(SELECT SECTION FROM SECTION_MASTER WHERE 
				SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE 
				CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))SECTION,

				(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))CATID,

				(SELECT CATEGORY FROM SECTION_CATEGORIES WHERE 
				CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))CATEGORIES,

				(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))SUBCATID,

				(SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))SUBCATEGORY,

				(SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=QB.TOPICID)TOPIC,
				QUEIMAGE,QUESTION,QUETYPE,QUEOPTIONS,CORRECTANSWER,ALLOWEDCALC,GRIDIN,WORDPROBLEM
				FROM QUESTION_BANK QB WHERE ISDELETED=0";

		if($ddlSection > 0){
			$query .= " AND QUEID NOT IN (SELECT QUEID FROM TEST_SECTION_QUESTIONS WHERE ISDELETED=0 AND
			(SELECT SECID FROM SECTION_MASTER WHERE ISDELETED=0 AND 
			SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE ISDELETED=0 AND 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE ISDELETED=0 AND 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE ISDELETED=0 AND 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection)"; 

			$query .= " AND (SELECT SECID FROM SECTION_MASTER WHERE ISDELETED=0 AND 
			SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE  ISDELETED=0 AND 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE ISDELETED=0 AND 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE ISDELETED=0 AND 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection"; 
		}

		if($ddlCategory > 0){
			$query .= " AND QUEID NOT IN (SELECT QUEID FROM TEST_SECTION_QUESTIONS WHERE ISDELETED=0 AND
			(SELECT SECID FROM SECTION_MASTER WHERE 
			SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection AND
			(SELECT CATID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))=$ddlCategory)"; 

			$query .= " AND (SELECT CATID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))=$ddlCategory"; 
		}


		if($ddlSubCategory > 0){
			$query .= " AND QUEID NOT IN (SELECT QUEID FROM TEST_SECTION_QUESTIONS WHERE ISDELETED=0 AND
			(SELECT SECID FROM SECTION_MASTER WHERE 
			SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection AND
			(SELECT CATID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))=$ddlCategory AND
			(SELECT SUBCATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))=$ddlSubCategory)"; 

			$query .= " AND (SELECT SUBCATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))=$ddlSubCategory"; 
		}


		if($ddlTopic > 0){
			$query .= " AND QUEID NOT IN (SELECT QUEID FROM TEST_SECTION_QUESTIONS WHERE ISDELETED=0 AND
			(SELECT SECID FROM SECTION_MASTER WHERE 
			SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection AND
			(SELECT CATID FROM SECTION_CATEGORIES WHERE 
			CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))=$ddlCategory AND
			(SELECT SUBCATID FROM SECTION_SUB_CATEGORIES WHERE 
			SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
			TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))=$ddlSubCategory AND
			TOPICID=$ddlTopic)"; 

			$query .= " AND TOPICID=$ddlTopic"; 
		}
		$query .= " ORDER BY QUEID DESC";

		$data['query'] = $query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['QUEOPTIONS'] = str_replace(';#;', ' ', $row['QUEOPTIONS']);
				// $row['QUEOPTIONS'] = rtrim($row['QUEOPTIONS'], ', ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
// =============== GET QUESTIONS DATA ==============



// =============== SAVE QUESTION SECTION DATA ==================
function saveQuestionSection($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $tsqid  = ($_POST['tsqid'] == 'undefined' || $_POST['tsqid'] == '') ? 0 : $_POST['tsqid'];
	   $testid  = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   $tsecid  = ($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
	   $ddlQuestion  = ($_POST['ddlQuestion'] == 'undefined' || $_POST['ddlQuestion'] == '') ? 0 : $_POST['ddlQuestion'];
	   
	   
	   $actionid = $tsqid == 0 ? 1 : 2;

	   if($testid == 0){throw new Exception("TestId Not Found.");}
	   if($tsecid == 0){throw new Exception("TsecId Not Found.");}
	   if($ddlQuestion == 0){throw new Exception("Please Select Question.");}

	   $sql = "SELECT * FROM TEST_SECTION_QUESTIONS WHERE TESTID=$testid AND TSECID=$tsecid AND QUEID=$ddlQuestion AND TSQID!=$tsqid AND ISDELETED=0";
	   $row_count = unique($sql);

	   // throw new Exception($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="EXEC [TEST_SECTION_QUESTIONS_SP] $actionid,$tsqid,$testid,$tsecid,$ddlQuestion,$userid";
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
			   
			//    $row = sqlsrv_fetch_array($stmt);
			//    $data['GET_TESTID'] = $row['TESTID'];

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($tsqid))$data['message'] = 'Record successfully updated';
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
// =============== SAVE QUESTION SECTION DATA ==============





/*============ Get QUESTION SECTION =============*/ 
function getQuestionSection($mysqli){
   try
   {
	   $data = array();

	   $testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   $tsecid = ($_POST['tsecid'] == 'undefined' || $_POST['tsecid'] == '') ? 0 : $_POST['tsecid'];
	   $ddlSection = ($_POST['ddlSection'] == 'undefined' || $_POST['ddlSection'] == '') ? 0 : $_POST['ddlSection'];
	   $ddlCategory = ($_POST['ddlCategory'] == 'undefined' || $_POST['ddlCategory'] == '') ? 0 : $_POST['ddlCategory'];
	   $ddlSubCategory = ($_POST['ddlSubCategory'] == 'undefined' || $_POST['ddlSubCategory'] == '') ? 0 : $_POST['ddlSubCategory'];
	   $ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
	   if($testid == 0){throw new Exception('TestId Not Found.');}
	   if($tsecid == 0){throw new Exception('TsecId Not Found.');}

	   $query = "SELECT TSQID,TESTID,TSECID,
	   (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID = TSQ.TSECID)TEST_SECTION,QUEID,
	   (SELECT QUESTION FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID)QUESTION,
	   (SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID)TOPICID
	   --(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0))SUBCATID,
	   --(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0)))CATID,
	   --(SELECT SECID FROM SECTION_CATEGORIES WHERE CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0))))SECID
	   FROM TEST_SECTION_QUESTIONS TSQ WHERE ISDELETED=0 AND TESTID=$testid AND TSECID=$tsecid AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)
	   --AND QUEID NOT IN (SELECT QUEID FROM QUESTION_BANK WHERE ISDELETED=1)
	   ";

		// if($ddlSection > 0){
		// 	$query .=" AND (SELECT SECID FROM SECTION_CATEGORIES WHERE CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0))))=$ddlSection";	
		// }
		// if($ddlCategory > 0){
		// 	$query .=" AND (SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0)))=$ddlCategory";	
		// }
		// if($ddlSubCategory > 0){
		// 	$query .=" AND (SELECT SUBCATID FROM SECTION_TOPICS WHERE TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0))= $ddlSubCategory";	
		// }
		// if($ddlTopic > 0){
		// 	$query .=" AND (SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=TSQ.QUEID AND ISDELETED=0) = $ddlTopic";	
		// }

	   $result = sqlsrv_query($mysqli, $query);
	   $count = unique($query);
	   if($count > 0){
		   while ($row = sqlsrv_fetch_array($result)) {
			   $data['data'][] = $row;
		   }
		   $data['$query'] = $query;
		   $data['success'] = true;
		}
		else{
		   $data['$query'] = $query;
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
/*============ Get QUESTION SECTION =============*/ 






/* =========== Delete QUESTION SECTION =========== */ 
function deleteQuestionSection($mysqli){
   try{   
		   global $userid;
		   $data = array();     
		   $tsqid = ($_POST['tsqid'] == 'undefined' || $_POST['tsqid'] == '') ? 0 : $_POST['tsqid'];
		   if($tsqid == 0){throw new Exception('Tsecid Error.');}

		   $delQuery = "EXEC [TEST_SECTION_QUESTIONS_SP] 3,$tsqid,0,0,0,$userid";
		   $stmt=sqlsrv_query($mysqli, $delQuery);
		   if( $stmt === false ) 
		   {
			//    die( print_r( sqlsrv_errors(), true));
			//    throw new Exception( $mysqli->sqlstate );
			$data[$delQuery] = $delQuery;
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
/* =========== Delete QUESTION SECTION =========== */ 

/* ################################### QUESTION SECTION START ################################### */ 









/* ################################### SCALED SCORE START ################################### */ 
function uploadExcel($mysqli){
	try{
		global $userid;   
		$data = array();
		
		$sadid = ($_POST['sadid'] == 'undefined' || $_POST['sadid'] == '') ? 0 : $_POST['sadid'];
		$said = ($_POST['said'] == 'undefined' || $_POST['said'] == '') ? 0 : $_POST['said'];
		$speid = ($_POST['speid'] == 'undefined' || $_POST['speid'] == '') ? 0 : $_POST['speid'];
		$itemType = $_POST['itemType'] == 'undefined' ? '' : $_POST['itemType'];
		$SPEIDS=explode(",",$speid);
		

		$checkData="SELECT * FROM SPECTRO_ANALYSIS_DATA WHERE SAID=$said";
		$count=unique($checkData);
		if($count > 0 ){throw new Exception("Data already exist!");}

		$filename=$_FILES["txtXlsFile"]["tmp_name"];
    	if($_FILES["txtXlsFile"]["size"] > 0)
    	{
        	$file = fopen($filename, "r");
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
			{   
				$col=3;
				for($i=0;$i<count($SPEIDS);$i++)
				{
						$query = "INSERT INTO SPECTRO_ANALYSIS_DATA (SAID, SNO, SAMPLENO,HEATNO,SPEID,READING,MAXVAL,MINVAL) 
						VALUES ($said, $emapData[0], '$emapData[1]','$emapData[2]',$SPEIDS[$i],'$emapData[$col]','$mxv','$mnv')";
						$result = sqlsrv_query($mysqli, $query);
						$col++;
				}
			}
			fclose($file);
			$data['success'] = true;
			$data['success'] = true;
			$data['message'] = "<i class='fa fa-check'> Data successfully uploaded . $itemType";
		}
		else {
			$data['success'] = false;
			$data['message'] = 'Upload error';
		}
		echo json_encode($data);
		exit;
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

// =============== SAVE SCALED SCORE DATA ==================
function saveScaledScore($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $ssmid  = ($_POST['ssmid'] == 'undefined' || $_POST['ssmid'] == '') ? 0 : $_POST['ssmid'];
	   $SS_GROUP  = ($_POST['SS_GROUP'] == 'undefined' || $_POST['SS_GROUP'] == '') ? 0 : $_POST['SS_GROUP'];
	   $testid  = ($_POST['SS_TESTID'] == 'undefined' || $_POST['SS_TESTID'] == '') ? 0 : $_POST['SS_TESTID'];
	   $txtScore  = ($_POST['txtScore'] == 'undefined' || $_POST['txtScore'] == '') ? 0 : $_POST['txtScore'];
	   $txtScale  = ($_POST['txtScale'] == 'undefined' || $_POST['txtScale'] == '') ? 0 : $_POST['txtScale'];
	//    $txtUploadExcel  = ($_POST['txtUploadExcel'] == 'undefined' || $_POST['txtUploadExcel'] == '') ? '' : $_POST['txtUploadExcel'];
		$txtUploadExcel ='';
	   
		if($txtScore <= 0 && $txtScale <= 0){

			$txtUploadExcel =$_FILES["txtUploadExcel"]["name"];
		}
		else{
			$txtUploadExcel ='';
		}
		
	   
	   $actionid = $ssmid == 0 ? 1 : 2;

	   if($SS_GROUP == 0){throw new Exception("Group No. Not Found.");}
	   if($testid == 0){throw new Exception("TestID Not Found.");}
	//    if($txtScore == 0){throw new Exception("Please Enter Score.");}
	//    if($txtScale == 0){throw new Exception("Please Enter Scale.");}

		if($txtScore >= 0 && $txtScale > 0){

			$sql = "SELECT * FROM SCALE_SCORE_MASTER WHERE TESTID=$testid AND TSG_GROUPNO=$SS_GROUP AND SCORE=$txtScore AND SCALE=$txtScale AND SSMID!=$ssmid AND ISDELETED=0";
			$row_count = unique($sql);
			if($row_count == 0)
			{
				$query="EXEC [SCALE_SCORE_MASTER_SP] $actionid,$ssmid,$testid,$SS_GROUP,$txtScore,$txtScale,$userid";
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
					
				//    $row = sqlsrv_fetch_array($stmt);
				//    $data['GET_TESTID'] = $row['TESTID'];
		
					$data['query'] = $query;
					$data['success'] = true;
					if(!empty($ssmid))$data['message'] = 'Record successfully updated';
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
		else if($txtScore <= 0 && $txtScale <= 0 && $txtUploadExcel != ''){
			//Excel File Data
			$filename=$_FILES["txtUploadExcel"]["tmp_name"];
			if($_FILES["txtUploadExcel"]["size"] > 0)
			{
				$file = fopen($filename, "r");
				$count = 0;
				while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
				{ 
					$count++;
					if($count>1){                                 
						// $query = "INSERT INTO EXCEL_DATA (ST_NAME,AGE) VALUES('$emapData[1]', $emapData[2])";
						$query = "EXEC [SCALE_SCORE_MASTER_SP] 1,$ssmid,$testid,$SS_GROUP,$emapData[0],$emapData[1],$userid";
						sqlsrv_query($mysqli, $query);
					}  
				}
				fclose($file);
				$data['message'] = "<i class='fa fa-check'> Data successfully uploaded.";
				$data['success'] = true;
				echo json_encode($data);exit;
			}
			else {
				$data['success'] = false;
				$data['message'] = 'Upload error';
			}
			$data['message'] = 'failed Outside';
			$data['tmp_name']=$_FILES["txtUploadExcel"]["tmp_name"];
			echo json_encode($data);exit;
		}
		else{
			$data['success'] = false;
			$data['message'] = 'Scale/Score Error.';
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
// =============== SAVE SCALED SCORE DATA ==============





/*============ Get SCALED SCORE =============*/ 
function getScaledScore($mysqli){
   try
   {
	   $data = array();

	   $SS_GROUP = ($_POST['SS_GROUP'] == 'undefined' || $_POST['SS_GROUP'] == '') ? 0 : $_POST['SS_GROUP'];
	   $SS_TESTID = ($_POST['SS_TESTID'] == 'undefined' || $_POST['SS_TESTID'] == '') ? 0 : $_POST['SS_TESTID'];
	   if($SS_GROUP == 0){throw new Exception('GroupNo Not Found.');}
	   if($SS_TESTID == 0){throw new Exception('TestID Not Found.');}

	   $query = "SELECT SSMID,TSG_GROUPNO,SCORE,SCALE
	   FROM SCALE_SCORE_MASTER WHERE ISDELETED=0 AND TESTID=$SS_TESTID AND TSG_GROUPNO=$SS_GROUP ORDER BY SCORE";
	   $result = sqlsrv_query($mysqli, $query);
	   $count = unique($query);
	   if($count > 0){
		   while ($row = sqlsrv_fetch_array($result)) {
			   $data['data'][] = $row;
		   }
		   $data['$query'] = $query;
		   $data['success'] = true;
		}
		else{
		   $data['$query'] = $query;
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
/*============ Get SCALED SCORE =============*/ 






/* =========== Delete SCALED SCORE =========== */ 
function deleteScaledScore($mysqli){
   try{   
		   global $userid;
		   $data = array();     
		   $ssmid = ($_POST['ssmid'] == 'undefined' || $_POST['ssmid'] == '') ? 0 : $_POST['ssmid'];
		   if($ssmid == 0){throw new Exception('SSMID Error.');}

		   $delQuery = "EXEC [SCALE_SCORE_MASTER_SP] 3,$ssmid,0,0,0,0,$userid";
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
/* =========== Delete SCALED SCORE =========== */ 

/* ################################### SCALED SCORE START ################################### */






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







