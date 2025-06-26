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
		case "getTechPlatform":getTechPlatform($conn);break;
		case "getObjectTypeSource": getObjectTypeSource($conn);break;
		case "getObjectTypeTarget": getObjectTypeTarget($conn);break;
		case "getObjectMasterSource": getObjectMasterSource($conn);break;
		case "getObjectMasterTarget": getObjectMasterTarget($conn);break;
		case "delete":delete($conn);break;
		case "getUserByLoc":getUserByLoc($conn);break;
        case "getDevelopmentStatus":getDevelopmentStatus($conn);break;

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
   		
		$txtObjTypeIdSource  = $_POST['txtObjTypeIdSource'] == 'undefined' ? '' : $_POST['txtObjTypeIdSource'];
	    $txtObjMasterIdSource   = $_POST['txtObjMasterIdSource'] == 'undefined' ? '' : $_POST['txtObjMasterIdSource'];
    	$txtObjTypeIdTarget  = $_POST['txtObjTypeIdTarget'] == 'undefined' ? '' : $_POST['txtObjTypeIdTarget'];
      	$txtObjMasterIdTarget  = $_POST['txtObjMasterIdTarget'] == 'undefined' ? '' : $_POST['txtObjMasterIdTarget'];
       	$txtTechPlatformId  = $_POST['txtTechPlatformId'] == 'undefined' ? '' : $_POST['txtTechPlatformId'];
        $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$txtDevelopmentStatus  = $_POST['txtDevelopmentStatus'] == 'undefined' ? '' : $_POST['txtDevelopmentStatus'];
		
		$actionid = $pmid == 0 ? 1 : 2;

		
		

	$sql = "SELECT OBJTYPEID_SOURCE,OBJECT_TYPE_DESC_SOURCE,OBJMASTER_ID_SOURCE,OBJMASTER_DESC_SOURCE,OBJTYPEID_TARGET,
			OBJECT_TYPE_DESC_TARGET,OBJMASTER_ID_TARGET,OBJMASTER_DESC_TARGET,TECHPLATFORMID,TECHPLATFORM,REMARKS 
			FROM MEP_OBJECT_MAPPING 
			WHERE OBJMAPPINGID!=$pmid 
			AND  OBJTYPEID_SOURCE  = $txtObjTypeIdSource 
			and  OBJMASTER_ID_SOURCE = $txtObjMasterIdSource
			and  OBJTYPEID_TARGET   = $txtObjTypeIdTarget
			AND  OBJMASTER_ID_TARGET = $txtObjMasterIdTarget
			AND  ISDELETED   = 0
			AND  DEVELOPMENT_STATUS_CD = $txtDevelopmentStatus
			AND  TECHPLATFORMID = $txtTechPlatformId";	
		
		
		// $data['$sql'] = $sql;
		$row_count = unique($sql);
		
		if($row_count == 0)
		{
			$query="EXEC [IT_OBJECTMAPPING_SP]$actionid,$pmid,$txtObjTypeIdSource,$txtObjMasterIdSource,$txtObjTypeIdTarget,$txtObjMasterIdTarget,$userid,$txtTechPlatformId,'$txtremarks',$txtDevelopmentStatus";
			
			$data['query'] = $query;
			// echo json_encode($data);exit();
			
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
		
       		
    //    $query = "SELECT DEPLOYMENTID,
	//    (SELECT OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER WHERE OBJTYPEID=M.OBJTYPEID)OBJECT_TYPE_DESC,
	//    OBJTYPEID,
	//    (SELECT OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE OBJMASTER_ID=M.OBJMASTER_ID)OBJMASTER_DESC,
	//    OBJMASTER_ID,MODIFICATION_DESC,MODIFICATION_USER_ID,
	//    MODIFICATION_USER_NAME,CONVERT(VARCHAR,MODIFICATION_DATE,106)MODIFICATION_DATE,
	//    TESTING_USER_ID,TESTING_USER_NAME,CONVERT(VARCHAR,TESTING_DATE,106)TESTING_DATE,DEPLOYMENT_USER_ID,DEPLOYMENT_USER_NAME,
	//    CONVERT(VARCHAR,DEPLOYMENT_DATE,106)DEPLOYMENT_DATE,TECHPLATFORMID,TECHPLATFORM,REMARKS
	//    FROM MEP_IT_DEPLOYMENT M WHERE ISDELETED=0  ORDER BY DEPLOYMENTID DESC";


	$query = "SELECT 
			    OBJMAPPINGID,
				OBJTYPEID_SOURCE,
				OBJECT_TYPE_DESC_SOURCE,
				OBJMASTER_ID_SOURCE,
				OBJMASTER_DESC_SOURCE,
				OBJTYPEID_TARGET,
				OBJECT_TYPE_DESC_TARGET,
				OBJMASTER_ID_TARGET,
				OBJMASTER_DESC_TARGET,
				TECHPLATFORMID,
				TECHPLATFORM,
				DEVELOPMENT_STATUS_CD,
				DEVELOPMENT_STATUS_DESC,
				REMARKS
	 			FROM MEP_OBJECT_MAPPING  WHERE ISDELETED=0  ORDER BY OBJMAPPINGID";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['OBJMAPPINGID'] = (int) $row['OBJMAPPINGID'];
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

//Get Object Type -Source
function getObjectTypeSource($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT OBJTYPEID,OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER WHERE ISDELETED=0  ORDER BY OBJTYPEID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['OBJTYPEID'] = (int) $row['OBJTYPEID'];
				
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


function getObjectTypeTarget($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT OBJTYPEID,OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER WHERE ISDELETED=0  ORDER BY OBJTYPEID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['OBJTYPEID'] = (int) $row['OBJTYPEID'];
				
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



//Get Object Master - Source
function getObjectMasterTarget($mysqli){
	try
	{
		
		$query = "SELECT * FROM MEP_OBJECT_MASTER WHERE ISDELETED=0  ORDER BY OBJMASTER_ID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['OBJMASTER_ID'] = (int) $row['OBJMASTER_ID'];
				
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


//Get Object Master - Source
function getObjectMasterSource($mysqli){
	try
	{
		
		$query = "SELECT * FROM MEP_OBJECT_MASTER WHERE ISDELETED=0  ORDER BY OBJMASTER_ID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['OBJMASTER_ID'] = (int) $row['OBJMASTER_ID'];
				
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



//Get Tech Platform
function getTechPlatform($mysqli){
	try
	{
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


//Get Development Status
function getDevelopmentStatus($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=14 and isdeleted=0";

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
				throw new Exception('OBJMAPPINGID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_OBJECTMAPPING_SP] 3,$pmid,'','','','',$userid ,'','',''");
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







