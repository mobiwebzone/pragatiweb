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
        case "getAppEssays":getAppEssays($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getAppEssays_DET":getAppEssays_DET($conn);break;
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

		$appessayid = ($_POST['appessayid'] == 'undefined' || $_POST['appessayid'] == '') ? 0 : $_POST['appessayid'];
		$ddlApp = ($_POST['ddlApp'] == 'undefined' || $_POST['ddlApp'] == '') ? 0 : $_POST['ddlApp'];
		$ddlAdmType = $_POST['ddlAdmType'] == 'undefined' ? '' : $_POST['ddlAdmType'];
		$txtAdmTypeOther = $_POST['txtAdmTypeOther'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtAdmTypeOther']);
		$ddlAdmYear = ($_POST['ddlAdmYear'] == 'undefined' || $_POST['ddlAdmYear'] == '') ? 0 : $_POST['ddlAdmYear'];
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);
		$txtNoOfEssays = ($_POST['txtNoOfEssays'] == 'undefined' || $_POST['txtNoOfEssays'] == '') ? 0 : $_POST['txtNoOfEssays'];

		$actionid = $appessayid == 0 ? 1 : 2;

		if($ddlApp == 0) throw new Exception("Select App Name.");
		if($ddlAdmType == '') throw new Exception("Select Admission Type.");


		// $sql = "SELECT * FROM SCHOLARSHIP_MASTER WHERE SCHOLARSHIP='$txtScholarship' AND SCHMID!=$schmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$query="EXEC [APP_ESSAYS_MASTER_SP] $actionid,$appessayid,$ddlApp,'$ddlAdmType','$txtAdmTypeOther',$ddlAdmYear,
			'$txtComments',$txtNoOfEssays,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET APPESSAYID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_APPESSAYID'] = $row['APPESSAYID'];
				$GET_APPESSAYID = $row['APPESSAYID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($appessayid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET APP ESSAYS =========== */
 function getAppEssays($mysqli){
	try
	{
		$data = array();
		$query = "SELECT APPESSAYID,APPID,
		(SELECT APPNAME FROM APP_MASTER WHERE APPID=AEM.APPID)APPNAME,ADMTYPE,ADMTYPE_OTHER,ADMYEARID,
		ISNULL((SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=AEM.ADMYEARID),'')ADMYEAR,COMMENTS,NO_OF_ESSAYS 
		FROM APP_ESSAYS_MASTER AEM
		WHERE ISDELETED=0
		ORDER BY APPNAME";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPESSAYID'] = (int) $row['APPESSAYID'];
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
/* ========== GET APP ESSAYS =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPESSAYID = ($_POST['APPESSAYID'] == 'undefined' || $_POST['APPESSAYID'] == '') ? 0 : $_POST['APPESSAYID'];  
			if($APPESSAYID == 0)throw new Exception('APPESSAYID Not Found.');
			$delQuery = "EXEC [APP_ESSAYS_MASTER_SP] 3,$APPESSAYID,0,'','',0,'',0,$userid";
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

	   $appessaydetid = ($_POST['appessaydetid'] == 'undefined' || $_POST['appessaydetid'] == '') ? 0 : $_POST['appessaydetid'];
	   $appessayid = ($_POST['appessayid'] == 'undefined' || $_POST['appessayid'] == '') ? 0 : $_POST['appessayid'];
	   $txtEssayTitle = $_POST['txtEssayTitle'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEssayTitle']);
	   $ddlLimitOn = $_POST['ddlLimitOn'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlLimitOn']);
	   $txtMinLimit = ($_POST['txtMinLimit'] == 'undefined' || $_POST['txtMinLimit'] == '') ? 0 : $_POST['txtMinLimit'];
	   $txtMaxLimit = ($_POST['txtMaxLimit'] == 'undefined' || $_POST['txtMaxLimit'] == '') ? 0 : $_POST['txtMaxLimit'];

	   $actionid = $appessaydetid == 0 ? 1 : 2;

	   if($appessayid == 0) throw new Exception("APPESSAYID Error.");
	   if($txtEssayTitle == '') throw new Exception("Enter Essay Title Name.");


	   $sql = "SELECT * FROM APP_ESSAYS_DETAILS WHERE APPESSAYID=$appessayid AND ESSAY_TITLE='$txtEssayTitle' AND LIMIT_ON='$ddlLimitOn' 
	   	AND MINLIMIT=$txtMinLimit AND MAXLIMIT=$txtMaxLimit AND APPESSAYDETID!=$appessaydetid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [APP_ESSAYS_DETAILS_SP] $actionid,$appessaydetid,$appessayid,'$txtEssayTitle','$ddlLimitOn',$txtMinLimit,$txtMaxLimit,$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($appessaydetid))$data['message'] = 'Record successfully updated.';
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





/* ========== GET APP ESSAYS DETAILS =========== */
function getAppEssays_DET($mysqli){
	try
	{
		$data = array();
		$appessayid = ($_POST['appessayid'] == 'undefined' || $_POST['appessayid'] == '') ? 0 : $_POST['appessayid'];
		if($appessayid == 0)throw new Exception("AppEssayId Error.");

		$query = "SELECT APPESSAYDETID,ESSAY_TITLE,LIMIT_ON,MINLIMIT,MAXLIMIT 
		FROM APP_ESSAYS_DETAILS
		WHERE ISDELETED=0 AND APPESSAYID=$appessayid
		ORDER BY ESSAY_TITLE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPESSAYDETID'] = (int) $row['APPESSAYDETID'];
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
/* ========== GET APP ESSAYS DETAILS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPESSAYDETID = ($_POST['APPESSAYDETID'] == 'undefined' || $_POST['APPESSAYDETID'] == '') ? 0 : $_POST['APPESSAYDETID'];  
			if($APPESSAYDETID == 0)throw new Exception('APPESSAYDETID Not Found.');
			$delQuery = "EXEC [APP_ESSAYS_DETAILS_SP] 3,$APPESSAYDETID,0,'','',0,0,$userid";
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







