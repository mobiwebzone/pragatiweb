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
        case "save":save($conn);break;
        case "getLocations":getLocations($conn);break;
		case "getFranchisename":getFranchisename($conn);break;
		case "getpriority":getpriority($conn);break;
        case "getfranchisedataData":getfranchisedataData($conn);break;
		case "getTaskCategoryData":getTaskCategoryData($conn);break;
		case "getuserData":getuserData($conn);break;
		case "getTaskstatusData":getTaskstatusData($conn);break;
		case "getMastertask":getMastertask($conn);break;
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

// Get Module
function getModule($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=12 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

// Get getFranchisename
function getFranchisename($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=10 and isdeleted=0";
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

// Get getpriority
function getpriority($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=8 and isdeleted=0";
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

// Get getTaskCategoryData
function getTaskCategoryData($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=11 and isdeleted=0";
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


// Get getuserData
function getuserData($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=7 and isdeleted=0";
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

// Get getTaskstatusData
function getTaskstatusData($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=9 and isdeleted=0";
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

// Get getLocations
function getLocations($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=12 and isdeleted=0";
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

// Get getMastertask
function getMastertask($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";
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

/*SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		$TASK_ID  = ($_POST['TASK_ID'] == 'undefined' || $_POST['TASK_ID'] == '') ? 0 : $_POST['TASK_ID'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlfranchiseID  = ($_POST['ddlfranchiseID'] == 'undefined' || $_POST['ddlfranchiseID'] == '') ? 0 : $_POST['ddlfranchiseID'];
         $txtTask  = ($_POST['txtTask'] == 'undefined' || $_POST['txtTask'] == '') ? '' : $_POST['txtTask'];
        $ddlTaskctgy  = ($_POST['ddlTaskctgy'] == 'undefined' || $_POST['ddlTaskctgy'] == '') ? 0 : $_POST['ddlTaskctgy'];
		$txtTaskname  = ($_POST['txtTaskname'] == 'undefined' || $_POST['txtTaskname'] == '') ? '' : $_POST['txtTaskname'];
		$ddluser  = ($_POST['ddluser'] == 'undefined' || $_POST['ddluser'] == '') ? 0 : $_POST['ddluser'];
		$Sdate  = ($_POST['Sdate'] == 'undefined' || $_POST['Sdate'] == '') ? '' : $_POST['Sdate'];
		$Edate  = ($_POST['Edate'] == 'undefined' || $_POST['Edate'] == '') ? '' : $_POST['Edate'];
		$ddlPriority  = ($_POST['ddlPriority'] == 'undefined' || $_POST['ddlPriority'] == '') ? 0 : $_POST['ddlPriority'];
		$ddltaststatus  = ($_POST['ddltaststatus'] == 'undefined' || $_POST['ddltaststatus'] == '') ? 0 : $_POST['ddltaststatus'];
		$QUESTIONS_FRANCHISE  = ($_POST['QUESTIONS_FRANCHISE'] == 'undefined' || $_POST['QUESTIONS_FRANCHISE'] == '') ? '' : $_POST['QUESTIONS_FRANCHISE'];
		$QUESTIONS_HQ  = ($_POST['QUESTIONS_HQ'] == 'undefined' || $_POST['QUESTIONS_HQ'] == '') ? '' : $_POST['QUESTIONS_HQ'];
		$ddlMastertask  = ($_POST['ddlMastertask'] == 'undefined' || $_POST['ddlMastertask'] == '') ? 0 : $_POST['ddlMastertask'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $TASK_ID == 0 ? 1 : 2;
		
		
		if($ddlfranchiseID == 0)
		{throw new Exception("Please Select Franchise Name.");}
	
		
		// $sql = "SELECT * FROM MEP_FRANCHISE_LAUNCH_TASKS WHERE FRANCHISE_NAME=$ddlfranchiseID AND TASK_DESC='$txtTask' AND TASK_ID!=$TASK_ID AND ISDELETED=0";
		// $data['$query'] = $sql;
		// echo json_encode($data);exit;
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {

			$query="EXEC [FRANCHISE_LAUNCH_TASKS_SP] $actionid,$TASK_ID,$ddlLocation,$ddlfranchiseID,'$txtTask',$ddlTaskctgy,$ddluser,'$Sdate','$Edate',$ddlPriority,$ddltaststatus,'$QUESTIONS_FRANCHISE','$QUESTIONS_HQ',$userid,'$txtRemark', $ddlMastertask";
			//throw new Exception($query);
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
				if(!empty($TASK_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists.';
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
 */

/*============ GET SALES DATA =============*/ 
function getfranchisedataData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TASK_ID,LOC_ID,LOCATION,FRANCHISE_ID,FRANCHISE_NAME,TASK_DESC,TASK_CATG_ID,TASK_CATG_DESC,WHO_ID,WHO_NAME,
		CONVERT(VARCHAR,START_DATE,106)START_DATE,CONVERT(VARCHAR,END_DATE,106)END_DATE,PRIORTY_ID,
		PRIORTY_DESC,TASK_STATUS_ID,TASK_STATUS_DESC,QUESTIONS_FRANCHISE,QUESTIONS_HQ,MASTER_TASK_CD,MASTER_TASK_DESC,REMARKS 
		from MEP_FRANCHISE_LAUNCH_TASKS
		WHERE ISDELETED=0
		ORDER BY FRANCHISE_NAME,TASK_ID";

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




/* =========== Delete =========== 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TASK_ID = ($_POST['TASK_ID'] == 'undefined' || $_POST['TASK_ID'] == '') ? 0 : $_POST['TASK_ID'];
			if($TASK_ID == 0) throw new Exception('Invalid FRANCHISE ID.');
			$query = "EXEC [FRANCHISE_LAUNCH_TASKS_SP] 3,$TASK_ID,0,0,'',0,0,'','',0,0,'','',$userid,'',0";
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


*/ 

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}






