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
        case "getSlideHeads":getSlideHeads($conn);break;
        case "saveSlideHeading":saveSlideHeading($conn);break;
        case "deleteSlideHeading":deleteSlideHeading($conn);break;
		
        case "getSlides":getSlides($conn);break;
        case "saveSlide":saveSlide($conn);break;
        case "deleteSlide":deleteSlide($conn);break;

        case "updateFinalConfigDate":updateFinalConfigDate($conn);break;
        case "openCloseIssue":openCloseIssue($conn);break;
        case "updateIssue":updateIssue($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

//###################################################
//                HEADING START
//###################################################

/*============ GET SLIDE HEADS =============*/ 
function getSlideHeads($mysqli){
	try
	{
		$data = array();
		$TOPICID = ($_POST['TOPICID'] =='undefined' || $_POST['TOPICID'] =='') ? 0 : $_POST['TOPICID'];
		$query = "SELECT SLIDEID,SLIDEHEADING,OPEN_ISSUE,ISSUE_REMARKS,OPEN_ISSUE_STUDENT,ISSUE_REMARKS_STUDENT,
		OPEN_ISSUE_TEACHER,ISSUE_REMARKS_TEACHER,
		CASE WHEN FINALIZED_DT IS NULL OR FINALIZED_DT='1900-01-01 00:00:00.000'
			THEN '' ELSE CONVERT(VARCHAR,FINALIZED_DT,106)
		END FINALIZED_DT,
		CASE WHEN CONFIGURED_DT IS NULL OR CONFIGURED_DT='1900-01-01 00:00:00.000'
			THEN '' ELSE CONVERT(VARCHAR,CONFIGURED_DT,106)
		END CONFIGURED_DT,
		CASE WHEN SLIDEREADY_DT IS NULL OR SLIDEREADY_DT='1900-01-01 00:00:00.000'
			THEN '' ELSE CONVERT(VARCHAR,SLIDEREADY_DT,106)
		END SLIDEREADY_DT,
		CASE WHEN CONFIGREADY_DT IS NULL OR CONFIGREADY_DT='1900-01-01 00:00:00.000'
			THEN '' ELSE CONVERT(VARCHAR,CONFIGREADY_DT,106)
		END CONFIGREADY_DT
		FROM LA_TOPIC_SLIDE_MASTER WHERE ISDELETED=0 AND TOPICID=$TOPICID ORDER BY SLIDEHEADING";
		$data['$query']=$query;
		// echo json_encode($data);exit;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SLIDEID'] = (int) $row['SLIDEID'];
				$row['OPEN_ISSUE'] = (string) $row['OPEN_ISSUE'];
				$row['OPEN_ISSUE_STUDENT'] = (string) $row['OPEN_ISSUE_STUDENT'];
				$row['OPEN_ISSUE_TEACHER'] = (string) $row['OPEN_ISSUE_TEACHER'];
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

/*============ SAVE SLIDE HEADS =============*/ 
function saveSlideHeading($mysqli){
	try
	{
	global $userid;
	$data = array();

	$slideid = ($_POST['slideid'] == 'undefined' || $_POST['slideid'] == '') ? 0 : $_POST['slideid'];
	$topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
	$txtHeadingName = ($_POST['txtHeadingName'] == 'undefined' || $_POST['txtHeadingName'] == '') ? '' : $_POST['txtHeadingName'];
	
	$actionid = $slideid == 0 ? 1 : 2;
	
	
	if($topicid == 0)throw new Exception("Error: Topicid Not Found.");
	if($txtHeadingName == '')throw new Exception("Please Enter Slide Head Name.");
	
	$sql = "SELECT * FROM LA_TOPIC_SLIDE_MASTER WHERE TOPICID=$topicid AND SLIDEHEADING='$txtHeadingName' AND SLIDEID!=$slideid AND ISDELETED=0";
	$row_count = unique($sql);
	
	
	if($row_count == 0)
	{
		$query="EXEC [LA_TOPIC_SLIDE_MASTER_SP] $actionid,$slideid,$topicid,'$txtHeadingName',$userid";
		$data['$query'] = $query;
		// echo json_encode($data);exit;
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
			$data['query'] = $query;
			$data['success'] = true;
			if(!empty($topicid))$data['message'] = 'Record successfully updated';
			else $data['message'] = 'Record successfully inserted.';
			echo json_encode($data);exit;
		}	
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Slide head name already exists.';
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
 
/* =========== DELETE =========== */ 
function deleteSlideHeading($mysqli){
	try{   
		global $userid;
		$data = array();     
		$SLIDEID = ($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];  
		$query = "EXEC [LA_TOPIC_SLIDE_MASTER_SP] 3,$SLIDEID,0,'',$userid";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

//###################################################
//                HEADING END
//###################################################








//###################################################
//                SLIDE  START
//###################################################

/*============ GET SLIDES =============*/ 
 function getSlides($mysqli){
	try
	{
		$data = array();
		$slideid = ($_POST['slideid'] =='undefined' || $_POST['slideid'] =='') ? 0 : $_POST['slideid'];
		if($slideid == 0)throw new Exception("Error : slideid not found.");
		$query = "SELECT SLIDEDETID,SLIDEID,CONTENT_TYPE,CONTENT,CONTENTFILE,SEQNO FROM LA_TOPIC_SLIDES WHERE ISDELETED=0 AND SLIDEID=$slideid
				ORDER BY LEN(SEQNO),SEQNO";
		$data['$query']=$query;
		// echo json_encode($data);exit;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SLIDEDETID'] = (int) $row['SLIDEDETID'];
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

/*============ SAVE SLIDE HEADS =============*/ 
function saveSlide($mysqli){
	try
	{
	   global $userid;
	   $data = array();

	   $slidedetid = ($_POST['slidedetid'] == 'undefined' || $_POST['slidedetid'] == '') ? 0 : $_POST['slidedetid'];
	   $slideid = ($_POST['slideid'] == 'undefined' || $_POST['slideid'] == '') ? 0 : $_POST['slideid'];
	   $txtSeqNo = ($_POST['txtSeqNo'] == 'undefined' || $_POST['txtSeqNo'] == '') ? 0 : $_POST['txtSeqNo'];
	   $ddlContentType = ($_POST['ddlContentType'] == 'undefined' || $_POST['ddlContentType'] == '') ? '' : $_POST['ddlContentType'];
	   $txtSlidTextContent = ($_POST['txtSlidTextContent'] == 'undefined' || $_POST['txtSlidTextContent'] == '') ? '' : str_replace("'","''",$_POST['txtSlidTextContent']);
	//    $txtContentImage = ($_POST['txtContentImage'] == 'undefined' || $_POST['txtContentImage'] == '') ? '' : $_POST['txtContentImage'];
	   $existingContentImage = ($_POST['existingContentImage'] == 'undefined' || $_POST['existingContentImage'] == '') ? '' : $_POST['existingContentImage'];
	   $IMG_SIZE = 1048576; //1 MB
	   $PDF_SIZE = 2097152; //2 MB
	   $VIDEO_SIZE = 10485760; //10 MB
	   $FILE_SIZE = [
						"IMAGE"=>["TYPE"=>'Image',"SIZE"=>'1MB',"BIT"=>$IMG_SIZE],
						"PDF"=>["TYPE"=>'PDF',"SIZE"=>'2MB',"BIT"=>$PDF_SIZE],
						"VIDEO"=>["TYPE"=>'Video',"SIZE"=>'10MB',"BIT"=>$VIDEO_SIZE],
					];
	   $fileSize = $ddlContentType=='IMAGE' ? $IMG_SIZE : ($ddlContentType=='PDF' ? $PDF_SIZE : $VIDEO_SIZE);
	   if(isset($_FILES['txtContentImage']['name']) && $_FILES['txtContentImage']['size'] > 0 && $_FILES['txtContentImage']['size'] > $fileSize){
			$msg = 'File size too large. Max '.$FILE_SIZE[$ddlContentType]['TYPE'].' Size '.$FILE_SIZE[$ddlContentType]['SIZE'].'.';
			throw new Exception($msg);
		}
	   
	   $actionid = $slidedetid == 0 ? 1 : 2;
	   
	   
	   if($slideid == 0)throw new Exception("Error: Slideid Not Found.");
	   if($txtSeqNo == 0)throw new Exception("Please Enter SEQ No.");
	   if($ddlContentType == '')throw new Exception("Please Enter Content Type.");
	   if($ddlContentType=='TEXT' && $txtSlidTextContent == '')throw new Exception("Please Enter Content.");
	   if($ddlContentType=='IMAGE') $txtSlidTextContent == '';
	   
	   $sql = "SELECT * FROM LA_TOPIC_SLIDES WHERE SEQNO=$txtSeqNo AND SLIDEID=$slideid AND SLIDEDETID!=$slidedetid AND ISDELETED=0";
	   $row_count = unique($sql);
	
	   if($row_count == 0)
	   {
		   $query="EXEC [LA_TOPIC_SLIDES_SP] $actionid,$slidedetid,$slideid,'$ddlContentType','$txtSlidTextContent','',$txtSeqNo,$userid";
		   $data['$query'] = $query;
		   // echo json_encode($data);exit;
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
				$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_SLIDEDETID = (int)$row['SLIDEDETID'];

				//==== IMAGE
				$txtContentImage = '';
				if(isset($_FILES['txtContentImage']['name']) && $_FILES['txtContentImage']['size'] > 0){
					$ext = pathinfo($_FILES['txtContentImage']['name'],PATHINFO_EXTENSION);
					$txtContentImage .= strtolower($ddlContentType.'_'.$GET_SLIDEDETID.'_slide_'.time().'.'.$ext);
				}
				if($txtContentImage != ''){

					if($GET_SLIDEDETID > 0)
					{
						$insertimage = "UPDATE LA_TOPIC_SLIDES SET CONTENTFILE='$txtContentImage' WHERE SLIDEDETID=$GET_SLIDEDETID";
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['txtContentImage']['name']) && $_FILES['txtContentImage']['size'] > 0)
					{
						move_uploaded_file($_FILES["txtContentImage"]["tmp_name"], '../images/slides/'.$txtContentImage);
					}

					
					if(isset($_FILES['txtContentImage']['name']) && $existingContentImage != '')
					{
						if (file_exists('../images/slides/'.$existingContentImage))
						{
							unlink('../images/slides/'.$existingContentImage);
						}
					}
				

				}

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($slidedetid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }	
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'SEQ No.already exists.';
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

/* =========== DELETE =========== */ 
function deleteSlide($mysqli){
	try{   
		global $userid;
		$data = array();     
		$SLIDEDETID = ($_POST['SLIDEDETID'] == 'undefined' || $_POST['SLIDEDETID'] == '') ? 0 : $_POST['SLIDEDETID'];  
		$query = "EXEC [LA_TOPIC_SLIDES_SP] 3,$SLIDEDETID,0,'','','',0,$userid";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

/*============ OPEN / CLOSE ISSUE =============*/ 
function openCloseIssue($mysqli){
	try
	{
	   global $userid;
	   $data = array();

	   $SLIDEID = ($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];	   	   
	//    throw new Exception($SLIDEID);
	   $FOR = ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];	   	   
	   $BO_ST = ($_POST['BO_ST'] == 'undefined' || $_POST['BO_ST'] == '') ? '' : $_POST['BO_ST'];	   	   
	   if($SLIDEID == 0)throw new Exception("Error: SLIDEID Not Found.");   
	   if($FOR == '')throw new Exception("Error: Issue For Not Found.");   
	   if($BO_ST == '')throw new Exception("Error: Issue For BO/ST Not Found.");   

	   if($FOR=='OPEN'){
			if($BO_ST=='BO') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE=1 WHERE SLIDEID=$SLIDEID";
			if($BO_ST=='ST') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE_STUDENT=1 WHERE SLIDEID=$SLIDEID";
			if($BO_ST=='TH') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE_TEACHER=1 WHERE SLIDEID=$SLIDEID";
		}else{
			if($BO_ST=='BO') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE=0 WHERE SLIDEID=$SLIDEID";
			if($BO_ST=='ST') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE_STUDENT=0 WHERE SLIDEID=$SLIDEID";
			if($BO_ST=='TH') $query="UPDATE LA_TOPIC_SLIDE_MASTER SET OPEN_ISSUE_TEACHER=0 WHERE SLIDEID=$SLIDEID";
	   }
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
			$data['message'] = 'Failed.';
		}
		else
		{
			$data['success'] = true;
			if($FOR=='OPEN'){
				$data['message'] = 'Issue successfully open.';
			}else{
				$data['message'] = 'Issue successfully close.';
			}
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

/*============ UPDATE FNALIZED OR CONFIGURED DATE =============*/ 
function updateFinalConfigDate($mysqli){
	try
	{
	   global $userid;
	   $data = array();

	   $SLIDEID = ($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];
	   $DATE = ($_POST['DATE'] == 'undefined' || $_POST['DATE'] == '') ? '' : $_POST['DATE'];	   	   
	   $DATE_TYPE = ($_POST['DATE_TYPE'] == 'undefined' || $_POST['DATE_TYPE'] == '') ? '' : $_POST['DATE_TYPE'];	   	   
	   if($SLIDEID == 0)throw new Exception("Error: SLIDEID Not Found.");   
	//    if($DATE == '')throw new Exception("Error: DATE Not Found.");   
	   if($DATE_TYPE == '')throw new Exception("Error: DATE TYPE Not Found.");   

	   	if($DATE_TYPE=='FINAL'){
			if($DATE==''){
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET FINALIZED_DT=NULL WHERE SLIDEID=$SLIDEID";
			}else{
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET FINALIZED_DT='$DATE' WHERE SLIDEID=$SLIDEID";
			}
		}else if($DATE_TYPE=='CONFIG'){
			if($DATE==''){
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET CONFIGURED_DT=NULL WHERE SLIDEID=$SLIDEID";
			}else{
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET CONFIGURED_DT='$DATE' WHERE SLIDEID=$SLIDEID";
			}
		}else if($DATE_TYPE=='SLIDE_READY'){
			if($DATE==''){
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET SLIDEREADY_DT=NULL WHERE SLIDEID=$SLIDEID";
			}else{
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET SLIDEREADY_DT='$DATE' WHERE SLIDEID=$SLIDEID";
			}
	   	}else if($DATE_TYPE=='CONFIG_READY'){
			if($DATE==''){
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET CONFIGREADY_DT=NULL WHERE SLIDEID=$SLIDEID";
			}else{
				$query="UPDATE LA_TOPIC_SLIDE_MASTER SET CONFIGREADY_DT='$DATE' WHERE SLIDEID=$SLIDEID";
			}
	   	}
		else{
			throw new Exception('Error : Invalid Date Type.');
		}
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
			$data['message'] = 'Failed.';
		}
		else
		{
			$data['success'] = true;
			if($DATE_TYPE=='FINAL'){
				$data['message'] = 'Finalized Date Successfully Updated.';
			}else{
				$data['message'] = 'Configured Date Successfully Updated.';
			}
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

/*============ UPDATE ISSUE =============*/ 
function updateIssue($mysqli){
	try
	{
		global $userid;
		$data = array();

		$SLIDEID = ($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];	   	   
		$issueText = ($_POST['issueText'] == 'undefined' || $_POST['issueText'] == '') ? '' : str_replace("'","''",$_POST['issueText']);
		$IssueFor = ($_POST['IssueFor'] == 'undefined' || $_POST['IssueFor'] == '') ? '' : $_POST['IssueFor'];	   	   
		if($SLIDEID == 0)throw new Exception("Error: SLIDEID Not Found.");   
		if($IssueFor == '')throw new Exception("Error: Invalid For BO/ST.");   

	   
	   	if($IssueFor=='BO'){
		   $query="UPDATE LA_TOPIC_SLIDE_MASTER SET ISSUE_REMARKS='$issueText' WHERE SLIDEID=$SLIDEID";
		}
		else if($IssueFor=='ST')
		{
			$query="UPDATE LA_TOPIC_SLIDE_MASTER SET ISSUE_REMARKS_STUDENT='$issueText' WHERE SLIDEID=$SLIDEID";
		}
		else if($IssueFor=='TH')
		{
			$query="UPDATE LA_TOPIC_SLIDE_MASTER SET ISSUE_REMARKS_TEACHER='$issueText' WHERE SLIDEID=$SLIDEID";
		}
		else
		{			
			if($IssueFor == '')throw new Exception("Error: Invalid For BO/ST.");   
	   	}
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
			$data['message'] = 'Failed.';
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Issue successfully updated.';
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

//###################################################
//                SLIDE  END
//###################################################








function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







