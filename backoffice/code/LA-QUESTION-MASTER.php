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
		case "saveExcelFile":saveExcelFile($conn);break;

		//######## MASTER
        case "saveData":saveData($conn);break;
        case "getMainQuestions":getMainQuestions($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getSubQuestions":getSubQuestions($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## OPTIONS
        case "saveDataOPT":saveDataOPT($conn);break;
        case "getOptions":getOptions($conn);break;
        case "deleteOPT":deleteOPT($conn);break;
		
		
		//######## PASSAGE IMAGE
        case "saveDataPs":saveDataPs($conn);break;
        case "getPassageImages":getPassageImages($conn);break;
        case "deletePs":deletePs($conn);break;
		
		
		//######## EXTRA DATA TABLE
		case "getTopics":getTopics($conn);break;

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

// =============== SAVE EXCEL DATA ==================
function saveExcelFile($mysqli){
	try
	{
		$data = array();
		global $userid;
		
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade  = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$ddlTopic  = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
		$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];

		if($ddlLocation == 0){throw new Exception("Please Select Location Name.");}
		if($ddlGrade == 0){throw new Exception("Please Select Grade Name.");}
		if($ddlSubject == 0){throw new Exception("Please Select Subject Name.");}
		if($ddlTopic == 0){throw new Exception("Please Select Topic Name.");}
		if($txtUploadExcel == ''){throw new Exception("Please Select Excel File.");}

		
		$filename=$_FILES["txtUploadExcelData"]["tmp_name"];
		if($_FILES["txtUploadExcelData"]["size"] > 0)
		{
			$file = fopen($filename, "r");
			$count = 0;
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
			{ 
				$count++;
				if($count>1){
					$options = array();
					if(strlen($emapData[6])>0){
						$options = explode("@",$emapData[6]);
					}
					$data['options'][]=$options;
					$data['$emapData'][]=$emapData;

					

					// echo json_encode($data);exit();

					$dayno = $emapData[0];
					$testcode = $emapData[1];
					$question = $emapData[2];
					$passage = $emapData[3];
					$que_type = ltrim(rtrim($emapData[4],' '),'');
					$subQuestion = $emapData[5];
					// $answer = $emapData[6];
					// $GridIn = $emapData[5] == 'N' ? 0 : 1;
					// $WordProb = $emapData[6] == 'N' ? 0 : 1;
					
					if($que_type=='MCQ SINGLE'){
						$query="EXEC [LA_QUESTIONS_MAIN_SP] 1,0,$ddlLocation,$ddlGrade,$ddlSubject,$ddlTopic,$dayno,'$testcode','$question','$passage',$userid";
						$stmt=sqlsrv_query($mysqli, $query);
						if($stmt === false)
						{
							$data['success'] = false;
							$data['query'] = $query;
							echo json_encode($data);exit;
						}
						else
						{
							// #### GET MQUEID
							$row = sqlsrv_fetch_array($stmt);
							$GET_MQUEID = $row['MQUEID'];
	
							//####### INSERT SUB QUESTION
							$querySub="EXEC [LA_QUESTIONS_SUB_SP] 1,0,$GET_MQUEID,'$subQuestion','$que_type','',0,0,$userid";
							$stmtSub=sqlsrv_query($mysqli, $querySub);
							
							if($stmtSub === false)
							{
								$data['success'] = false;
								$data['querySub'] = $querySub;
								// echo json_encode($data);exit;
							}
							else
							{
									// #### GET SQUEID
									$rowSub = sqlsrv_fetch_array($stmtSub);
									$GET_SQUEID = $rowSub['SQUEID'];


									//####### INSERT OPTIONS QUESTION
									if(count($options)>0){
										foreach($options as $item){
											$OPTION_ARR = explode("--",$item);
											$data['OP'][] = $OPTION_ARR;
											$OP = $OPTION_ARR[0];
											$ISCORRECT = $OPTION_ARR[1] == 'N' ? 0 : 1;
											
											$queryOp="EXEC [LA_QUESTIONS_OPTIONS_SP] 1,0,$GET_SQUEID,'$OP',$ISCORRECT,''";
											$stmtOp=sqlsrv_query($mysqli, $queryOp);
										}
									}
									
							}
						}
					}

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
		$data['tmp_name']=$_FILES["txtUploadExcelData"]["tmp_name"];
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
// =============== SAVE EXCEL DATA ==============


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;	

		$mqueid = ($_POST['mqueid'] == 'undefined' || $_POST['mqueid'] == '') ? 0 : $_POST['mqueid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
		$txtDayNo = ($_POST['txtDayNo'] == 'undefined' || $_POST['txtDayNo'] == '') ? 0 : $_POST['txtDayNo'];
		$txtTestCode = ($_POST['txtTestCode'] == 'undefined' || $_POST['txtTestCode'] == '') ? '' : str_replace("'","''",$_POST['txtTestCode']);
		$txtQuestion = ($_POST['txtQuestion'] == 'undefined' || $_POST['txtQuestion'] == '') ? '' : str_replace("'","''",$_POST['txtQuestion']);
		$txtPassage = ($_POST['txtPassage'] == 'undefined' || $_POST['txtPassage'] == '') ? '' : str_replace("'","''",$_POST['txtPassage']);

		$actionid = $mqueid == 0 ? 1 : 2;

		if($ddlLocation == 0) throw new Exception("Select Location First.");
		if($ddlGrade == 0) throw new Exception("Select Grade First.");
		if($ddlSubject == 0) throw new Exception("Select Subject First.");
		if($ddlTopic == 0) throw new Exception("Select Topic First.");
		if($txtDayNo == 0) throw new Exception("Select Day No. First.");
		if($txtTestCode == '') throw new Exception("Enter Topic Code First.");
		if($txtQuestion == '') throw new Exception("Enter Question First.");


		$sql = "SELECT * FROM LA_QUESTIONS_MAIN WHERE LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject AND TOPICID=$ddlTopic 
		AND DAYNO=$txtDayNo AND TESTCODE='$txtTestCode' AND QUESTION='$txtQuestion' AND MQUEID!=$mqueid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [LA_QUESTIONS_MAIN_SP] $actionid,$mqueid,$ddlLocation,$ddlGrade,$ddlSubject,$ddlTopic,$txtDayNo,'$txtTestCode','$txtQuestion','$txtPassage',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET MQUEID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_MQUEID'] = $row['MQUEID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($mqueid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Question already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET MAIN QUESTIONS =========== */
 function getMainQuestions($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];

		$query = "SELECT MQUEID,LOCID,
		(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=QM.GRADEID)GRADE,GRADEID,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=QM.SUBID)SUBJECTNAME,SUBID,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=QM.TOPICID)TOPIC,TOPICID,DAYNO,TESTCODE,QUESTION,PASSAGE
		FROM LA_QUESTIONS_MAIN QM WHERE ISDELETED=0";
		if($ddlLocation > 0)$query .= " AND LOCID=$ddlLocation";
		if($ddlGrade > 0)$query .= " AND GRADEID=$ddlGrade";
		if($ddlSubject > 0)$query .= " AND SUBID=$ddlSubject";
		if($ddlTopic > 0)$query .= " AND TOPICID=$ddlTopic";
		$query .= " ORDER BY QUESTION";

		$count = unique($query);

		if($count > 0){
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
/* ========== GET MAIN QUESTIONS =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
		global $userid;
		$data = array();     
		$MQUEID = ($_POST['MQUEID'] == 'undefined' || $_POST['MQUEID'] == '') ? 0 : $_POST['MQUEID'];  
		if($MQUEID == 0)throw new Exception('MQUEID Not Found.');
		$delQuery = "EXEC [LA_QUESTIONS_MAIN_SP] 3,$MQUEID,0,0,0,0,0,'','','',$userid";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataDET($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $squeid = ($_POST['squeid'] == 'undefined' || $_POST['squeid'] == '') ? 0 : $_POST['squeid'];
	   $mqueid = ($_POST['mqueid'] == 'undefined' || $_POST['mqueid'] == '') ? 0 : $_POST['mqueid'];
	   $txtQuestionSub = ($_POST['txtQuestionSub'] == 'undefined' || $_POST['txtQuestionSub'] == '') ? '' : $_POST['txtQuestionSub'];
	   $ddlQueType = $_POST['ddlQueType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlQueType']);
	   $txtAnswer = $_POST['txtAnswer'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtAnswer']);
	   $txtRows = ($_POST['txtRows'] == 'undefined' || $_POST['txtRows'] == '') ? 0 : $_POST['txtRows'];
	   $txtCols = ($_POST['txtCols'] == 'undefined' || $_POST['txtCols'] == '') ? 0 : $_POST['txtCols'];
	   $gridTextArr = (!isset($_POST['gridTextArr']) || $_POST['gridTextArr'] == 'undefined' || $_POST['gridTextArr'] == '') ? array() : json_decode($_POST['gridTextArr'],true);
	   $data['$gridTextArr']=$gridTextArr;

	   	if($ddlQueType === 'GRID'){
			$gridTextFinal = '';
			for($i=0; $i<count($gridTextArr); $i++){
				$dt[] = $gridTextArr[$i];
				for($s=0; $s<count($gridTextArr[$i]); $s++){
					$gridTextFinal .= $gridTextArr[$i][$s].'; ';
				}
			}
			$gridTextFinal = rtrim($gridTextFinal,'; ');
			$data['gridTextFinal']=$gridTextFinal;
		   	// $data['success'] = false;

			$txtQuestionSub = $gridTextFinal;
		}
		// echo json_encode($data);exit;
		
	   	$actionid = $squeid == 0 ? 1 : 2;

	   if($mqueid == 0) throw new Exception("Error : MQUEID Not Found.");
	   if($ddlQueType == '') throw new Exception("Select Question Type First.");
	   if($txtQuestionSub == '') throw new Exception("Enter Question First.");
	//    if($txtAnswer == '') throw new Exception("Enter Answer First.");


	   $sql = "SELECT * FROM LA_QUESTIONS_SUB WHERE MQUEID=$mqueid AND QUESTION='$txtQuestionSub' AND QUE_TYPE='$ddlQueType' AND ANSWER='$txtAnswer'
	   			AND SQUEID!=$squeid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [LA_QUESTIONS_SUB_SP] $actionid,$squeid,$mqueid,'$txtQuestionSub','$ddlQueType','$txtAnswer',$txtRows,$txtCols,$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
				// #### GET SQUEID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_SQUEID'] = $row['SQUEID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($squeid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
	   	$data['success'] = false;
	   	$data['message'] = 'Question already exists';
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
/* ============ SAVE DATA ============= */ 





/* ========== GET SUB QUESTIONS =========== */
function getSubQuestions($mysqli){
	try
	{
		$data = array();
		$mqueid = ($_POST['mqueid'] == 'undefined' || $_POST['mqueid'] == '') ? 0 : $_POST['mqueid'];
		if($mqueid == 0)throw new Exception("Error : MQUEID Not Found.");

		$query = "SELECT SQUEID,QUESTION,QUE_TYPE,ANSWER,[ROWS],[COLUMNS] FROM LA_QUESTIONS_SUB WHERE ISDELETED=0 AND MQUEID=$mqueid
		ORDER BY QUESTION";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$QUE_TYPE = $row['QUE_TYPE'];
				if($QUE_TYPE=='GRID'){
					$QUESTION_ARR = explode('; ',$row['QUESTION']);
					$ROWS = $row['ROWS'];
					$COLUMNS = $row['COLUMNS'];

					$associativeArray = array();
					$index = 0;

					for ($i = 0; $i < $ROWS; $i++) {
						for ($j = 0; $j < $COLUMNS; $j++) {
							if (isset($QUESTION_ARR[$index])) {
								$associativeArray[$i][$j] = $QUESTION_ARR[$index];
								$index++;
							} else {
								break;
							}
						}
					}
					$row['QUESTION_GRID'] = $associativeArray;
				}
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
/* ========== GET SUB QUESTIONS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SQUEID = ($_POST['SQUEID'] == 'undefined' || $_POST['SQUEID'] == '') ? 0 : $_POST['SQUEID'];  
			if($SQUEID == 0)throw new Exception('SQUEID Not Found.');
			$delQuery = "EXEC [LA_QUESTIONS_SUB_SP] 3,$SQUEID,0,'','','',0,0,$userid";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% OPTIONS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataOPT($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $optionid = ($_POST['optionid'] == 'undefined' || $_POST['optionid'] == '') ? 0 : $_POST['optionid'];
	   $squeid = ($_POST['squeid'] == 'undefined' || $_POST['squeid'] == '') ? 0 : $_POST['squeid'];
	   $txtOption = $_POST['txtOption'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtOption']);
	   $chkIsCorrect = ($_POST['chkIsCorrect'] == 'undefined' || $_POST['chkIsCorrect'] == '0') ? 0 : 1;

	   // === IMAGE
		$existingOptionImg  = $_POST['existingOptionImg'] == 'undefined' ? '' : $_POST['existingOptionImg'];
		$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
		if(isset($_FILES['optionImg']['name']) && $_FILES['optionImg']['size'] > 0 && $_FILES['optionImg']['size'] > 1048576) throw new Exception('File size too large.');
		$optionImg = '';
		if(isset($_FILES['optionImg']['name']) && $_FILES['optionImg']['size'] > 0){
			$ext = pathinfo($_FILES['optionImg']['name'],PATHINFO_EXTENSION);
			$optionImg .= strtolower('Option_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$optionImg=$existingOptionImg;
		}
		// === IMAGE

	   $actionid = $optionid == 0 ? 1 : 2;

	   if($squeid == 0) throw new Exception("Error : SQUEID Not Found.");
	   if($txtOption == '') throw new Exception("Enter Option First.");


	   $sql = "SELECT * FROM LA_QUESTIONS_OPTIONS WHERE SQUEID=$squeid AND OPTIONS='$txtOption' AND ISCORRECT=$chkIsCorrect
	   			AND OPTIONID!=$optionid";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [LA_QUESTIONS_OPTIONS_SP] $actionid,$optionid,$squeid,'$txtOption',$chkIsCorrect,'$optionImg'";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
				//========== IMAGE
				if($optionImg != ''){
					// if($GET_SCCDID > 0)
					// {
					// 	$insertimage = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET DOC='$DocsUpload' WHERE SCCDID=$GET_SCCDID";
					// 	sqlsrv_query($mysqli,$insertimage);
					// }


					if(isset($_FILES['optionImg']['name']) && $_FILES['optionImg']['size'] > 0)
					{
						move_uploaded_file($_FILES["optionImg"]["tmp_name"], '../images/la_question_master/'.$optionImg);
					}

					
					if(isset($_FILES['optionImg']['name']) && $existingOptionImg != '')
					{
						if (file_exists('../images/la_question_master/'.$existingOptionImg))
						{
							unlink('../images/la_question_master/'.$existingOptionImg);
						}
					}
				}
				// ###### REMOVE IMAGE ON UPDATE
				if($chkRemoveImgOnUpdate>0 && $existingOptionImg!=''){
					if($optionid>0){
						$insertimage = "UPDATE LA_QUESTIONS_OPTIONS SET [IMAGE]='' WHERE OPTIONID=$optionid";
							sqlsrv_query($mysqli,$insertimage);
					}
					if (file_exists('../images/la_question_master/'.$existingOptionImg))
					{
						unlink('../images/la_question_master/'.$existingOptionImg);
					}
				}
				//========== IMAGE

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($optionid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
	   	$data['success'] = false;
	   	$data['message'] = 'Option already exists';
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
/* ============ SAVE DATA ============= */ 





/* ========== GET OPTIONS =========== */
function getOptions($mysqli){
	try
	{
		$data = array();
		$squeid = ($_POST['squeid'] == 'undefined' || $_POST['squeid'] == '') ? 0 : $_POST['squeid'];
		if($squeid == 0)throw new Exception("Error : SQUEID Not Found.");

		$query = "SELECT OPTIONID,OPTIONS,ISCORRECT,[IMAGE] FROM LA_QUESTIONS_OPTIONS WHERE SQUEID=$squeid
		ORDER BY OPTIONS";
		$count = unique($query);

		if($count > 0){
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
/* ========== GET OPTIONS =========== */







/* =========== Delete =========== */ 
function deleteOPT($mysqli){
	try{   
			global $userid;
			$data = array();     
            $OPTIONID = ($_POST['OPTIONID'] == 'undefined' || $_POST['OPTIONID'] == '') ? 0 : $_POST['OPTIONID'];  
			if($OPTIONID == 0)throw new Exception('OPTIONID Not Found.');
			$delQuery = "EXEC [LA_QUESTIONS_OPTIONS_SP] 3,$OPTIONID,0,'',0,''";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% OPTIONS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% PASSAGE IMAGE START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataPs($mysqli){
	try
	{
	   	$data = array();
	   	global $userid;

	   	$piid = ($_POST['piid'] == 'undefined' || $_POST['piid'] == '') ? 0 : $_POST['piid'];
	   	$mqueid = ($_POST['mqueid'] == 'undefined' || $_POST['mqueid'] == '') ? 0 : $_POST['mqueid'];
	   	// $passageImg = 
	   	$existingPassageImg  = $_POST['existingPassageImg'] == 'undefined' ? '' : $_POST['existingPassageImg'];
		if(isset($_FILES['passageImg']['name']) && $_FILES['passageImg']['size'] > 0 && $_FILES['passageImg']['size'] > 1048576) throw new Exception('File size too large.');
		$passageImg = '';
		if(isset($_FILES['passageImg']['name']) && $_FILES['passageImg']['size'] > 0){
			$ext = pathinfo($_FILES['passageImg']['name'],PATHINFO_EXTENSION);
			$passageImg .= strtolower('Passage_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$passageImg=$existingPassageImg;
		}

		$actionid = $piid == 0 ? 1 : 2;
		if($mqueid == 0) throw new Exception("Error : MQUEID Not Found.");


		$query="EXEC [LA_PASSAGE_IMAGES_SP] $actionid,$piid,$mqueid,'$passageImg',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			$data['success'] = false;
			$data['query'] = $query;
			echo json_encode($data);exit;
		}
		else
		{
			//========== IMAGE
			if($passageImg != ''){
				// if($GET_SCCDID > 0)
				// {
				// 	$insertimage = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET DOC='$DocsUpload' WHERE SCCDID=$GET_SCCDID";
				// 	sqlsrv_query($mysqli,$insertimage);
				// }


				if(isset($_FILES['passageImg']['name']) && $_FILES['passageImg']['size'] > 0)
				{
					move_uploaded_file($_FILES["passageImg"]["tmp_name"], '../images/la_question_master/'.$passageImg);
				}

				
				if(isset($_FILES['passageImg']['name']) && $existingPassageImg != '')
				{
					if (file_exists('../images/la_question_master/'.$existingPassageImg))
					{
						unlink('../images/la_question_master/'.$existingPassageImg);
					}
				}
			}
			//========== IMAGE

			$data['query'] = $query;
			$data['success'] = true;
			if(!empty($piid))$data['message'] = 'Record successfully updated.';
			else $data['message'] = 'Record successfully inserted.';
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
/* ============ SAVE DATA ============= */ 





/* ========== GET PASSAGE IMAGES =========== */
function getPassageImages($mysqli){
	try
	{
		$data = array();
		$mqueid = ($_POST['mqueid'] == 'undefined' || $_POST['mqueid'] == '') ? 0 : $_POST['mqueid'];
		if($mqueid == 0)throw new Exception("Error : MQUEID Not Found.");

		$query = "SELECT PIID,PASSAGE_IMAGE FROM LA_PASSAGE_IMAGES WHERE MQUEID=$mqueid";
		$count = unique($query);

		if($count > 0){
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
/* ========== GET PASSAGE IMAGES =========== */







/* =========== Delete =========== */ 
function deletePs($mysqli){
	try{   
			global $userid;
			$data = array();     
            $PIID = ($_POST['PIID'] == 'undefined' || $_POST['PIID'] == '') ? 0 : $_POST['PIID'];  
			if($PIID == 0)throw new Exception('PIID Not Found.');
			$delQuery = "EXEC [LA_PASSAGE_IMAGES_SP] 3,$PIID,0,'',$userid";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% PASSAGE IMAGE END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 


/*============ GET TOPICS =============*/ 
function getTopics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');
		if($ddlGrade == 0) throw new Exception('Select Grade First.');
		if($ddlSubject == 0) throw new Exception('Select Subject First.');

		$query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID 
				FROM LA_TOPICS_MASTER TM 
				WHERE ISDELETED=0 AND LOCID IN ($ddlLocation,1) AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
				AND TOPICID NOT IN (SELECT TOPICID FROM LA_HIDE_TOPICS_LOCATIONWISE WHERE TOPICID=TM.TOPICID AND LOCID=$ddlLocation AND HIDE=1)
				ORDER BY LOCID DESC, UNDER,TOPIC";
		$data['query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Topic not found.';
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
/*============ GET TOPICS =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







