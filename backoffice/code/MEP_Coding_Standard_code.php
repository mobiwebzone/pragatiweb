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
		case "login":login($conn);break;
        case "save":save($conn);break;
        case "getQuery":getQuery($conn);break;
		case "getTechPlatform":getTechPlatform($conn);break;
		case "getFunction":getFunction($conn);break;
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
   		$txTechPlatformId  = $_POST['txTechPlatformId'] == 'undefined' ? '' : $_POST['txTechPlatformId'];
        $txtddlfunctionid  = $_POST['txtddlfunctionid'] == 'undefined' ? '' : $_POST['txtddlfunctionid'];
        $txtCodingStandardDesc  = $_POST['txtCodingStandardDesc'] == 'undefined' ? '' : $_POST['txtCodingStandardDesc'];
        $txtExampleDesc  = $_POST['txtExampleDesc'] == 'undefined' ? '' : $_POST['txtExampleDesc'];
		$txtSampleCode  = $_POST['txtSampleCode'] == 'undefined' ? '' : $_POST['txtSampleCode'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		if($txtCodingStandardDesc == '')
		{throw new Exception("Please Enter the Coding Standard.");}

	$sql = "SELECT * FROM MEP_CODING_STANDARD WHERE CODINGSTANDARD_DESC='$txtCodingStandardDesc' AND CODINGSTANDARDID!=$pmid AND ISDELETED=0 AND TECHPLATFORMID = $txTechPlatformId and FUNCTIONID =$txtddlfunctionid";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [IT_CODINGSTANDARD_SP] $actionid,$pmid,$txtddlfunctionid,'$txtCodingStandardDesc','$txtExampleDesc','$txtSampleCode',$userid ,$txTechPlatformId,'$txtremarks'";
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
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
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



function getQuery($mysqli){
	try
	{
		
       		
       $query = "SELECT * FROM MEP_CODING_STANDARD WHERE ISDELETED=0  ORDER BY CODINGSTANDARDID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODINGSTANDARDID'] = (int) $row['CODINGSTANDARDID'];
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

//Get Tech Platform
function getTechPlatform($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "select TECHPLATFORMID,TECHPLATFORM FROM MEP_TECH_PLATFORM WHERE ISDELETED=0 ORDER BY TECHPLATFORMID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TECHPLATFORMID'] = (int) $row['TECHPLATFORMID'];
				
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



function getFunction($mysqli){
	try
	{
		
        $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=4 and isdeleted=0";
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('CODINGSTANDARDID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_CODINGSTANDARD_SP] 3,$pmid,'','','','',$userid ,'',''");
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







