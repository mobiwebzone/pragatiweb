<?php
session_start();
require_once '../../code/connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


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
		case "getMastercode":getMastercode($conn);break;
		case "getCodeId":getCodeId($conn);break;
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

// GET MASTER CODE
function getMastercode($mysqli){
	try
	{
		
        $query = "SELECT CODE_ID, CODE_DESC ,REMARKS FROM MEP_CODE_MASTER WHERE ISDELETED=0 ORDER BY CODE_DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODE_ID'] = (int) $row['CODE_ID'];
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



 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		   
        $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        $txtObject  = ($_POST['txtObject'] == 'undefined' || $_POST['txtObject'] == '') ? 0 : $_POST['txtObject'];
		$txtCodeId  = $_POST['txtCodeId'] == 'undefined' ? '' : $_POST['txtCodeId'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		if($txtObject == '')
		{throw new Exception("Please Enter Code Details ");}

	$sql = "SELECT * FROM  MEP_CODE_DETAILS 
	WHERE CODE_DETAIL_DESC='$txtObject' 
	AND CODE_DETAIL_ID!=$pmid 
	AND ISDELETED=0 
	AND CODE_ID = $txtCodeId order by CODE_DESC ";

	$data['sql'] = $sql;
	

	$row_count = unique($sql);

		
		if($row_count == 0)
		{
			$query="EXEC [IT_CODEDETAILS_SP] $actionid,$pmid,$txtCodeId,'$txtObject',$userid,'$txtremarks'";
			$data['query'] =$query;
			
			
			
			$stmt=sqlsrv_query($mysqli, $query);
			
			
			
			if($stmt === false)
			{
				
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
								
				if(!empty($pmid))
				{
					$data['message'] = 'Record successfully updated';
                }
				else 
				{
					$data['message'] = 'Record successfully inserted.';
				}
				
			}
			echo json_encode($data);exit;
			
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		$ddlMCode = ($_POST['ddlMCode'] == 'undefined' || $_POST['ddlMCode'] == '') ? 0 : $_POST['ddlMCode'];
        
		$query = "SELECT CODE_ID, CODE_DESC,CODE_DETAIL_ID, CODE_DETAIL_DESC ,REMARKS 
		FROM MEP_CODE_DETAILS WHERE ISDELETED=0";

       if($ddlMCode != ''){
	   $query .= " AND CODE_ID = $ddlMCode ";
         }

		 $query .=  " ORDER BY code_desc";
		
	    $data['query'] = $query;
   
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
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
function getCodeId($mysqli){
	try
	{
		
		$query = "select CODE_ID,  CODE_DESC FROM MEP_CODE_MASTER WHERE ISDELETED=0 ORDER BY CODE_DESC ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_ID'] = (int) $row['CODE_ID'];
				
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
				throw new Exception('CODE_DETAIL_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_CODEDETAILS_SP] 3,$pmid,'',$userid ,'',''");
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







