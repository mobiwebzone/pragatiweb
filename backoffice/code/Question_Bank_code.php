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
        case "saveData":saveData($conn);break;
        case "getQuestions":getQuestions($conn);break;
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



// =============== SAVE EXCEL DATA ==================
	function saveExcelFile($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$ddlTopic  = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
			$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];

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
						$answes ='';
						if(strlen($emapData[3])>0){
							$answes = preg_replace('/[ ,]+/', ';#;,', trim($emapData[3]));
							$answes =  $answes.';#;';
						}
						$data['answer'][]=$answes;
						$data['$emapData'][]=$emapData;

						$CalcAllow = $emapData[4] == 'N' ? 0 : 1;
						$GridIn = $emapData[5] == 'N' ? 0 : 1;
						$WordProb = $emapData[6] == 'N' ? 0 : 1;
						$query="EXEC [QUESTION_BANK_SP] 1,0,$ddlTopic,N'$emapData[0]','$emapData[1]',N'$answes','$emapData[2]',$CalcAllow,$GridIn,$WordProb,'',$userid";
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











 
// =============== SAVE DATA ==================
	function saveData($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$queid  = ($_POST['queid'] == 'undefined' || $_POST['queid'] == '') ? 0 : $_POST['queid'];
			$ddlTopic  = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
			$txtQuestion  = $_POST['txtQuestion'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtQuestion']);
			$ddlQueType  = $_POST['ddlQueType'] == 'undefined' ? '' : $_POST['ddlQueType'];
			$rdCalcAllow  = ($_POST['rdCalcAllow'] == '0' || $_POST['rdCalcAllow'] == 'undefined') ? 0 : 1;
			$QuestionOptions  = $_POST['QuestionOptions'] == 'undefined' ? '' : $_POST['QuestionOptions'];
			$txtAnswer  = $_POST['txtAnswer'] == 'undefined' ? '' : $_POST['txtAnswer'];
			$rdGridIn  = ($_POST['rdGridIn'] == '0' || $_POST['rdGridIn'] == 'undefined') ? 0 : 1;
			$rdWordProblem  = ($_POST['rdWordProblem'] == '0' || $_POST['rdWordProblem'] == 'undefined') ? 0 : 1;
			$txtAnswerExplanation =$_POST['txtAnswerExplanation'] == 'undefined' ? '' : $_POST['txtAnswerExplanation'];

			// #### QUE IMAGE
			$existingQueImage  = $_POST['existingQueImage'] == 'undefined' ? '' : $_POST['existingQueImage'];
			$txtQueImage = '';
			if(isset($_FILES['txtQueImage']['name']) && $_FILES['txtQueImage']['size'] > 0){
				$ext = pathinfo($_FILES['txtQueImage']['name'],PATHINFO_EXTENSION);
				$txtQueImage .= strtolower(time().'.'.$ext);
			}else{$txtQueImage="";}

			// #### EXP IMAGE
			$existingExpImage = $_POST['existingExpImage'] == 'undefined' ? '' : $_POST['existingExpImage'];
			$txtExpImage = '';
			if(isset($_FILES['txtExpImage']['name']) && $_FILES['txtExpImage']['size'] > 0){
				$ext = pathinfo($_FILES['txtExpImage']['name'],PATHINFO_EXTENSION);
				$txtExpImage .= strtolower(time().'.'.$ext);
			}else{$txtExpImage="";}

			
			$actionid = $queid == 0 ? 1 : 2;

			if($ddlTopic == 0){throw new Exception("Please Select Topic Name.");}
			if($txtQuestion == ''){throw new Exception("Please Enter Question.");}
			if($ddlQueType == ''){throw new Exception("Please Enter Question Type.");}
			if($ddlQueType == 'MCQ'){
				if($QuestionOptions == ''){throw new Exception("Please Add Question Options.");}
			}
			if($txtAnswer == ''){throw new Exception("Please Enter Answer.");}


			$sql = "SELECT * FROM QUESTION_BANK WHERE TOPICID=$ddlTopic AND QUESTION='$txtQuestion' AND QUETYPE='$ddlQueType' AND ALLOWEDCALC=$rdCalcAllow AND 
					QUEID!=$queid AND ISDELETED=0";

			// $data['sql'] = $sql;
			// $data['success'] = false;
			// echo json_encode($data);exit;
			$row_count = unique($sql);

			$data = array();
			if($row_count == 0)
			{
				$query="EXEC [QUESTION_BANK_SP] $actionid,$queid,$ddlTopic,N'$txtQuestion','$ddlQueType',N'$QuestionOptions','$txtAnswer',$rdCalcAllow,$rdGridIn,$rdWordProblem,N'$txtAnswerExplanation',$userid";
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
					$row = sqlsrv_fetch_array($stmt);
					$queid = $row['QUEID'];

					// #### QUE IMAGE
					if($txtQueImage != ''){
						if($queid > 0)
						{
							$insertimage = "UPDATE QUESTION_BANK SET QUEIMAGE='$txtQueImage' WHERE QUEID=$queid";
							sqlsrv_query($mysqli,$insertimage);
						}		
						if(isset($_FILES['txtQueImage']['name']) && $_FILES['txtQueImage']['size'] > 0)
						{
							move_uploaded_file($_FILES["txtQueImage"]["tmp_name"], '../question_images/'.$txtQueImage);
						}
						if(isset($_FILES['txtQueImage']['name']) && $existingQueImage != '')
						{
							if (file_exists('../question_images/'.$existingQueImage))
							{
								unlink('../question_images/'.$existingQueImage);
							}
						}
					}else{
						if($queid > 0)
						{
							$insertimage = "UPDATE QUESTION_BANK SET QUEIMAGE='' WHERE QUEID=$queid";
							sqlsrv_query($mysqli,$insertimage);
						}		

						if (file_exists('../question_images/'.$existingQueImage))
						{
							unlink('../question_images/'.$existingQueImage);
						}
						
					}
					// #### EXP IMAGE
					if($txtExpImage != ''){
						if($queid > 0)
						{
							$insertimage = "UPDATE QUESTION_BANK SET ANS_EXPIMAGE='$txtExpImage' WHERE QUEID=$queid";
							sqlsrv_query($mysqli,$insertimage);
						}		
						if(isset($_FILES['txtExpImage']['name']) && $_FILES['txtExpImage']['size'] > 0)
						{
							move_uploaded_file($_FILES["txtExpImage"]["tmp_name"], '../question_images/explanation_images/'.$txtExpImage);
						}
						if(isset($_FILES['txtExpImage']['name']) && $existingExpImage != '')
						{
							if (file_exists('../question_images/explanation_images/'.$existingExpImage))
							{
								unlink('../question_images/explanation_images/'.$existingExpImage);
							}
						}
					}else{
						if($queid > 0)
						{
							$insertimage = "UPDATE QUESTION_BANK SET ANS_EXPIMAGE='' WHERE QUEID=$queid";
							sqlsrv_query($mysqli,$insertimage);
						}		

						if (file_exists('../question_images/explanation_images/'.$existingExpImage))
						{
							unlink('../question_images/explanation_images/'.$existingExpImage);
						}
					}


					$data['existingQueImage'] = $existingQueImage;
					$data['query'] = $query;
					$data['success'] = true;
					if(!empty($queid))$data['message'] = 'Record successfully updated';
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
 
 
 


 
 
 // =============== GET QUESTIONS DATA ==============
	function getQuestions($mysqli){
		try
		{
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
					QUEIMAGE,QUESTION,QUETYPE,QUEOPTIONS,CORRECTANSWER,ALLOWEDCALC,GRIDIN,WORDPROBLEM,
					ANS_EXPLANATION,ANS_EXPIMAGE
					FROM QUESTION_BANK QB WHERE ISDELETED=0";
			
			if($ddlSection > 0){
				$query .= " AND (SELECT SECID FROM SECTION_MASTER WHERE 
				SECID=(SELECT SECID FROM SECTION_CATEGORIES WHERE 
				CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))))=$ddlSection"; 
			}

			if($ddlCategory > 0){
				$query .= " AND (SELECT CATID FROM SECTION_CATEGORIES WHERE 
				CATID=(SELECT CATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID))))=$ddlCategory"; 
			}


			if($ddlSubCategory > 0){
				$query .= " AND (SELECT SUBCATID FROM SECTION_SUB_CATEGORIES WHERE 
				SUBCATID=(SELECT SUBCATID FROM SECTION_TOPICS WHERE 
				TOPICID=(SELECT TOPICID FROM QUESTION_BANK WHERE QUEID=QB.QUEID)))=$ddlSubCategory"; 
			}


			if($ddlTopic > 0){
				$query .= " AND TOPICID=$ddlTopic"; 
			}
			$query .= " ORDER BY QUEID DESC";

			$result = sqlsrv_query($mysqli, $query);
			$data = array();
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








/* =========== Delete =========== */ 
	function delete($mysqli){
		try{   
				global $userid;
				$data = array();     
				$queid = ($_POST['queid'] == 'undefined' || $_POST['queid'] == '') ? 0 : $_POST['queid'];
				if($queid == 0){throw new Exception('QUEID Error.');}

				$stmt=sqlsrv_query($mysqli, "EXEC [QUESTION_BANK_SP] 3,$queid,0,'','','','',0,0,0,'',$userid");
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






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







