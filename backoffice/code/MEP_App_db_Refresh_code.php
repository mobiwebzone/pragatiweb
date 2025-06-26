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
		case "getAppDBType": getAppDBType($conn);break;
		case "getMainServerLocation": getMainServerLocation($conn);break;
		case "getBackupLocation": getBackupLocation($conn);break;
		case "delete":delete($conn);break;
		// case "getUserByLoc":getUserByLoc($conn);break;
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
   		
		$txtTechPlatformId  = $_POST['txtTechPlatformId'] == 'undefined' ? '' : $_POST['txtTechPlatformId'];
        $txtAppDbBackupDate  = $_POST['txtAppDbBackupDate'] == 'undefined' ? '' : $_POST['txtAppDbBackupDate'];
		$txtAppDBTypeId  = $_POST['txtAppDBTypeId'] == 'undefined' ? '' : $_POST['txtAppDBTypeId'];
		$txtMainServerLocationId  = $_POST['txtMainServerLocationId'] == 'undefined' ? '' : $_POST['txtMainServerLocationId'];
		$txtBackupServerLocationId  = $_POST['txtBackupServerLocationId'] == 'undefined' ? '' : $_POST['txtBackupServerLocationId'];
	    $txtMainServerObjCount   = $_POST['txtMainServerObjCount'] == 'undefined' ? '' : $_POST['txtMainServerObjCount'];
		$txtBackServerObjCount  = $_POST['txtBackServerObjCount'] == 'undefined' ? '' : $_POST['txtBackServerObjCount'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $pmid == 0 ? 1 : 2;

		$sql = "SELECT * FROM MEP_APP_DB_REFRESH 
		        WHERE  APPDBREFRESH_ID!=$pmid
				AND    APPDB_BACKUP_DATE = '$txtAppDbBackupDate' 
				AND    APP_DB_TYPE_ID =     $txtAppDBTypeId
				AND    BACKUP_SERVER_LOCATION_ID = $txtBackupServerLocationId
				and    MAIN_SERVER_LOCATION_ID   = $txtMainServerLocationId
				AND    ISDELETED = 0";	
       
	   
	   $row_count = unique($sql);
	   
	   $data = array();
	
	   if($row_count == 0)
	   {
		   $query="EXEC [IT_APP_DB_REFRESH_SP]	$actionid,$pmid,'$txtAppDbBackupDate',$txtAppDBTypeId,$txtMainServerLocationId,$txtBackupServerLocationId,$txtMainServerObjCount,$txtBackServerObjCount,$userid,$txtTechPlatformId,'$txtremarks'";
		   $data['$sql'] = $query;
		
		   
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
		
       		
       $query =     "SELECT APPDBREFRESH_ID,
				    CONVERT(VARCHAR,APPDB_BACKUP_DATE,106)APPDB_BACKUP_DATE,
					APP_DB_TYPE_ID,
					APP_DB_TYPE_DESC,
					MAIN_SERVER_LOCATION_ID,
					MAIN_SERVER_LOCATION_DESC,
					MAIN_SERVER_OBJECTS_COUNT,
					BACKUP_SERVER_LOCATION_ID,
					BACKUP_SERVER_LOCATION_DESC,
					BACKUP_SERVER_OBJECTS_COUNT,
					TECHPLATFORMID,
					TECHPLATFORM,
					REMARKS
	   	            FROM MEP_APP_DB_REFRESH  WHERE ISDELETED=0  ORDER BY APPDBREFRESH_ID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['APPDBREFRESH_ID'] = (int) $row['APPDBREFRESH_ID'];
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


//Get Backup Type
function getAppDBType($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=3 and isdeleted=0";

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



//Get Main server Location
function getMainServerLocation($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=2 and isdeleted=0";

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



//Get Backup server Location
function getBackupLocation($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=1 and isdeleted=0";

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




/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('APPDBREFRESH_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_APP_DB_REFRESH_SP] 3,$pmid,'','','','','','',$userid ,'',''");
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







