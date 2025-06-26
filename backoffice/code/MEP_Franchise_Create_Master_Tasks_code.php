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
        case "getQuery":getQuery($conn);break;
		case "getLocations":getLocations($conn);break;
		case "getFranchisename":getFranchisename($conn);break;
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


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		      
        $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
   		
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlfranchiseID  = $_POST['ddlfranchiseID'] == 'undefined' ? '' : $_POST['ddlfranchiseID'];
    	$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];

		$actionid = $pmid == 0 ? 1 : 2;


		$sql = "SELECT * FROM MEP_FRANCHISE_CREATE_MASTER_TASKS 
		WHERE LOC_ID=$ddlLocation 
		AND FRANCHISE_ID= $ddlfranchiseID 
		AND TASK_MASTER_ID!=$pmid 
		AND ISDELETED=0";	

		// throw new Exception($sql);

		if ($ddlLocation=='')
		{
			throw new Exception ("Please Select Location.");
		}

		if ($ddlfranchiseID=='')
		{
			throw new Exception ("Please Select Franchise Name.");
		}
			
		$data['$sql'] = $sql;
		$row_count = unique($sql);
		 
		if($row_count == 0)
		{
		$query="EXEC [FRANCHISE_CREATE_MASTER_TASKS_SP] $actionid,$pmid,$ddlLocation,'$ddlfranchiseID',$userid,'$txtremarks'";
			
			$data['query'] = $query;
			//echo json_encode($data);exit();
			
			$stmt=sqlsrv_query($mysqli, $query);
 
			

			if($stmt === false)
			{
				
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($pmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				//echo json_encode($data);exit;
			}
			echo json_encode($data);exit;
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
			echo json_encode($data);
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



 function getQuery($mysqli){
	try
	{
		
        $query = "SELECT 
		TASK_MASTER_ID
		,FRANCHISE_ID
		,FRANCHISE_NAME
		,LOC_ID
		,LOCATION
		,REMARKS 
		,CONVERT(VARCHAR(11), INSERTDATE, 106) INSERTDATE_FORMATTED
		FROM MEP_FRANCHISE_CREATE_MASTER_TASKS WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TASK_MASTER_ID'] = (int) $row['TASK_MASTER_ID'];
			$data['data'][] = $row;
		}
		$data['success'] = true;
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
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('TASK_MASTER_ID ERROR.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [FRANCHISE_CREATE_MASTER_TASKS_SP] 3,$pmid,'','',$userid ,''");
			
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







