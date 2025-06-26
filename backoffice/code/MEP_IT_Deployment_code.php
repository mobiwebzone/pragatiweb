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
		case "getObjectType": getObjectType($conn);break;
		case "getObjectMaster": getObjectMaster($conn);break;
		case "delete":delete($conn);break;
		case "getUserByLoc":getUserByLoc($conn);break;
		case "getDeployedcd":getDeployedcd($conn);break;
		case "getSourceDatabase":getSourceDatabase($conn);break;
		
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
   		
		$txtObjTypeId  = $_POST['txtObjTypeId'] == 'undefined' ? '' : $_POST['txtObjTypeId'];
	    $txtObjMasterId   = $_POST['txtObjMasterId'] == 'undefined' ? '' : $_POST['txtObjMasterId'];
        
		$txtModificationDesc  = $_POST['txtModificationDesc'] == 'undefined' ? '' : $_POST['txtModificationDesc'];
		
		$txtUser1  = ($_POST['txtUser1'] == 'undefined' || $_POST['txtUser1'] == '') ? 0 : $_POST['txtUser1'];
      
		$txtModificationDate  = $_POST['txtModificationDate'] == 'undefined' ? '' : $_POST['txtModificationDate'];

		$txtDeploymentDate  = $_POST['txtDeploymentDate'] == 'undefined' ? '' : $_POST['txtDeploymentDate'];
                
		$txtDeployedcd  = $_POST['txtDeployedcd'] == 'undefined' ? '' : $_POST['txtDeployedcd'];
		$source_database_cd  = $_POST['source_database_cd'] == 'undefined' ? '' : $_POST['source_database_cd'];
        
		$txtTechPlatformId  = $_POST['txtTechPlatformId'] == 'undefined' ? '' : $_POST['txtTechPlatformId'];
        
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $pmid == 0 ? 1 : 2;

		if($txtModificationDesc  == '')
		{throw new Exception("Please Enter the Modifications.");}
		

	$sql = "SELECT * FROM MEP_IT_DEPLOYMENT 
			WHERE DEPLOYMENTID!=$pmid 
			AND  OBJTYPEID  = $txtObjTypeId 
			AND  OBJMASTER_ID = $txtObjMasterId
			AND  ISDELETED   = 0
			AND  DEPLOYED_CD = $txtDeployedcd
			and  MODIFICATION_DATE = $txtModificationDate
			-- / adding deployment date column 
			-- and  DEPLOYMENT_DATE = $txtDeploymentDate
			AND  TECHPLATFORMID = $txtTechPlatformId ";	
		
		// $data['$sql'] = $sql;
		$row_count = unique($sql);
		if($row_count == 0)
		{
			$query="EXEC [IT_DEPLOYMENT_SP]	$actionid,$pmid,$txtObjTypeId,$txtObjMasterId,'$txtModificationDesc',$txtUser1,'$txtModificationDate','','','','$txtDeploymentDate',$userid,$txtTechPlatformId,'$txtremarks',$txtDeployedcd,$source_database_cd";
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


/*============ GET USER BY LOCATION =============*/ 
 function getUserByLoc($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT [UID],FIRSTNAME,LASTNAME,USERROLE FROM USERS WHERE ISDELETED=0 AND LOCID=12 ORDER BY USERROLE, LASTNAME";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Users not found.';
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
/*============ GET USER BY LOCATION =============*/ 





function getQuery($mysqli){
	try
	{
		
       		
       $query = "SELECT DEPLOYMENTID,
	   (SELECT OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER WHERE OBJTYPEID=M.OBJTYPEID)OBJECT_TYPE_DESC,
	   OBJTYPEID,
	   (SELECT OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE OBJMASTER_ID=M.OBJMASTER_ID)OBJMASTER_DESC,
	   OBJMASTER_ID,MODIFICATION_DESC,MODIFICATION_USER_ID,
	   MODIFICATION_USER_NAME,CONVERT(VARCHAR,MODIFICATION_DATE,106)MODIFICATION_DATE,
	   TESTING_USER_ID,TESTING_USER_NAME,CONVERT(VARCHAR,TESTING_DATE,106)TESTING_DATE,DEPLOYMENT_USER_ID,DEPLOYMENT_USER_NAME,
	   CONVERT(VARCHAR,DEPLOYMENT_DATE,106)DEPLOYMENT_DATE,TECHPLATFORMID,TECHPLATFORM,REMARKS,DEPLOYED_CD,DEPLOYED_DESC,FUNCTION_DESC,
	   SOURCE_DATABASE_CD,SOURCE_DATABASE
	   FROM MEP_IT_DEPLOYMENT M WHERE ISDELETED=0   and DEPLOYED_CD not in (115) ORDER BY DEPLOYMENTID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['DEPLOYMENTID'] = (int) $row['DEPLOYMENTID'];
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

//Get Object Type
function getObjectType($mysqli){
	try
	{
		
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

//Get Object Master
function getObjectMaster($mysqli){
	try
	{
		$data = array();
		$txtObjTypeId= $_POST['txtObjTypeId'] == 'undefined' ? 0 : $_POST['txtObjTypeId'];

		$query = "SELECT * FROM MEP_OBJECT_MASTER WHERE ISDELETED=0  and OBJTYPEID = $txtObjTypeId and DEVELOPMENT_STATUS_CD=112
		          ORDER BY OBJMASTER_DESC";
		$data['query'] = $query;

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


//Get Tech Platform
function getDeployedcd($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=35 and isdeleted=0 order by code_detail_id";
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

//Get Source Database
function getSourceDatabase($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=32 and isdeleted=0 order by code_detail_id desc";
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('DEPLOYMENTID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_DEPLOYMENT_SP] 3,$pmid,'','','','','','','','','',$userid ,'','','',''");
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







