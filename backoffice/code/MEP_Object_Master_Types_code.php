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
        case "getObjectType":getObjectType($conn);break;
		case "getTechPlatform":getTechPlatform($conn);break;
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
		   
        $objid  = ($_POST['objid'] == 'undefined' || $_POST['objid'] == '') ? 0 : $_POST['objid'];
        $txtObjType  = $_POST['txtObjType'] == 'undefined' ? '' : $_POST['txtObjType'];
		$txTechPlatformId  = $_POST['txTechPlatformId'] == 'undefined' ? '' : $_POST['txTechPlatformId'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		$actionid = $objid == 0 ? 1 : 2;

		if($txtObjType == '')
		{throw new Exception("Please Enter Object Type.");}

	$sql = "SELECT * FROM MEP_OBJECT_TYPE_MASTER 
	        WHERE OBJECT_TYPE_DESC='$txtObjType' AND OBJTYPEID !=$objid AND ISDELETED=0 AND TECHPLATFORMID = $txTechPlatformId";
		
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [IT_OBJECTTYPES_SP] $actionid,$objid,'$txtObjType',$userid,$txTechPlatformId,'$txtremarks'";
			
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
				if(!empty($objid))$data['message'] = 'Record successfully updated';
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


 function getTechPlatform($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "select TECHPLATFORMID,  TECHPLATFORM FROM MEP_TECH_PLATFORM WHERE ISDELETED=0 ORDER BY TECHPLATFORMID ";

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



/*============ Get PAYMENT MODES =============*/ 
 function getObjectType($mysqli){
	try
	{
		
        $query = "select OBJTYPEID, TECHPLATFORM,OBJECT_TYPE_DESC,REMARKS from MEP_OBJECT_TYPE_MASTER WHERE ISDELETED=0";
 
		$result = sqlsrv_query($mysqli, $query); 
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['OBJTYPEID'] = (int) $row['OBJTYPEID'];
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
            $objid = ($_POST['objid'] == 'undefined' || $_POST['objid'] == '') ? 0 : $_POST['objid'];  
			if($objid == 0){
				throw new Exception('OJBID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_OBJECTTYPES_SP] 3,$objid,'',$userid,'','' ");
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







