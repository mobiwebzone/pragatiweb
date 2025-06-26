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
		//######## MASTER
        case "saveData":saveData($conn);break;
        case "getStepMasters":getStepMasters($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getStepsAttachments":getStepsAttachments($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;	

		$stepid = ($_POST['stepid'] == 'undefined' || $_POST['stepid'] == '') ? 0 : $_POST['stepid'];
		$txtStepNumber = ($_POST['txtStepNumber'] == 'undefined' || $_POST['txtStepNumber'] == '') ? 0 : $_POST['txtStepNumber'];
		$txtDesc = $_POST['txtDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDesc']);
		$txtLink = $_POST['txtLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLink']);
		$txtComment = $_POST['txtComment'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComment']);


		$actionid = $stepid == 0 ? 1 : 2;

		if($txtStepNumber == 0) throw new Exception("Enter Step Number.");


		$sql = "SELECT * FROM STEPS_MASTER WHERE STEPNO=$txtStepNumber AND STEPID!=$stepid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [STEPS_MASTER_SP] $actionid,$stepid,$txtStepNumber,'$txtDesc','$txtLink','$txtComment',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET STEPID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_STEPID'] = $row['STEPID'];
				$GET_STEPID = $row['STEPID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($stepid))$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET STEPS MASTER =========== */
 function getStepMasters($mysqli){
	try
	{
		$data = array();
		$query = "SELECT STEPID,STEPNO,STEPDESC,STEPLINK,COMMENTS,
		(SELECT COUNT(*) FROM STEPS_ATTACHEMENTS WHERE STEPID=SM.STEPID AND ISDELETED=0)TOTAL_IMG
		FROM STEPS_MASTER SM
		WHERE ISDELETED=0
		ORDER BY STEPNO";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['STEPID'] = (int) $row['STEPID'];
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
/* ========== GET STEPS MASTER =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STEPID = ($_POST['STEPID'] == 'undefined' || $_POST['STEPID'] == '') ? 0 : $_POST['STEPID'];  
			if($STEPID == 0)throw new Exception('STEPID Not Found.');
			$delQuery = "EXEC [STEPS_MASTER_SP] 3,$STEPID,0,'','','',$userid";
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
		$data['success'] = false . $query;
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

	   $attachid = ($_POST['attachid'] == 'undefined' || $_POST['attachid'] == '') ? 0 : $_POST['attachid'];
	   $stepid = ($_POST['stepid'] == 'undefined' || $_POST['stepid'] == '') ? 0 : $_POST['stepid'];
	   $txtCommentImg = $_POST['txtCommentImg'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCommentImg']);

	   $actionid = $attachid == 0 ? 1 : 2;

	   if($actionid == 1 && $txtImage="") throw new Exception("Select Your Image.");
	   if($stepid == 0) throw new Exception("STEPID Error.");


	//    $sql = "SELECT * FROM COLLEGE_SPECIFIC_ESSAYS_DETAILS WHERE CSEID=$cseid AND ESSAYTYPE='$ddlEssayType' AND ESSAYTYPE_OTHER='$txtEssayTypeOther'
	//     AND LIMITON='$ddlLimitOn' AND MINLIMIT=$txtMinLimit AND MAXLIMIT=$txtMaxLimit AND CSEDETID!=$csedetid AND ISDELETED=0";
	//    $row_count = unique($sql);
	   
	//    if($row_count == 0)
	//    {

		   $GET_ATTACHID = 0;
		   $query="EXEC [STEPS_ATTACHEMENTS_SP] $actionid,$attachid,$stepid,'','$txtCommentImg',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
				$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_ATTACHID = (int)$row['ATTACHID'];


				//==== IMAGE
				$existingCatImage  = $_POST['existingCatImage'] == 'undefined' ? '' : $_POST['existingCatImage'];
				$txtImage = '';
				if(isset($_FILES['txtImage']['name']) && $_FILES['txtImage']['size'] > 0){
					$ext = pathinfo($_FILES['txtImage']['name'],PATHINFO_EXTENSION);
					$txtImage .= strtolower(time().'.'.$ext);
				}
				else
				{
					$txtImage="";
				}
				//==== IMAGE

				$data['$GET_ATTACHID']=$GET_ATTACHID;
				$data['$txtImage2']=$txtImage;
				$data['$txtImage3']=$_FILES['txtImage']['name'];
				if($txtImage != ''){

					if($GET_ATTACHID > 0)
					{
						$insertimage = "UPDATE STEPS_ATTACHEMENTS SET ATTACHMENT='$txtImage' WHERE ATTACHID=$GET_ATTACHID";
						$data['$insertimage'] = $insertimage;
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['txtImage']['name']) && $_FILES['txtImage']['size'] > 0)
					{
						move_uploaded_file($_FILES["txtImage"]["tmp_name"], '../steps_master_images/'.$txtImage);
					}

					
					if(isset($_FILES['txtImage']['name']) && $existingCatImage != '')
					{
						if (file_exists('../steps_master_images/'.$existingCatImage))
						{
							unlink('../steps_master_images/'.$existingCatImage);
						}
					}
				

				}



			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($attachid))$data['message'] = 'Record successfully updated.';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	//    	$data['success'] = false;
	//    	$data['message'] = 'Record already exists';
	//    	echo json_encode($data);exit;
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
/* ============ SAVE DATA ============= */ 





/* ========== GET STEPS ATTACHMENTS =========== */
function getStepsAttachments($mysqli){
	try
	{
		$data = array();
		$stepid = ($_POST['stepid'] == 'undefined' || $_POST['stepid'] == '') ? 0 : $_POST['stepid'];
		if($stepid == 0)throw new Exception("STEPID Error.");

		$query = "SELECT ATTACHID,ATTACHMENT,COMMENTS 
		FROM STEPS_ATTACHEMENTS
		WHERE ISDELETED=0 AND STEPID=$stepid
		ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ATTACHID'] = (int) $row['ATTACHID'];
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
/* ========== GET STEPS ATTACHMENTS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ATTACHID = ($_POST['ATTACHID'] == 'undefined' || $_POST['ATTACHID'] == '') ? 0 : $_POST['ATTACHID'];  
			if($ATTACHID == 0)throw new Exception('ATTACHID Not Found.');
			$delQuery = "EXEC [STEPS_ATTACHEMENTS_SP] 3,$ATTACHID,0,'','',$userid";
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
/*============ GET STUDENT BY LOCATION =============*/ 

/*============ GET STUDENT BY LOCATION =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







