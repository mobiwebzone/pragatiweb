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
	    case "getObjectMaster": getObjectMaster($conn);break;
		case "getTechPlatform":getTechPlatform($conn);break;
		case "getUserByLoc": getUserByLoc($conn);break;
		case "delete":delete($conn);break;
		case "deploy":deploy($conn);break;
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
	    $txtUser1  = $_POST['txtUser1'] == 'undefined' ? '' : $_POST['txtUser1'];
		$txtdevelopmentDate  = $_POST['txtdevelopmentDate'] == 'undefined' ? '' : $_POST['txtdevelopmentDate'];
		$txtremarks= $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
        $txtObjMasterId= $_POST['txtObjMasterId'] == 'undefined' ? '' : $_POST['txtObjMasterId'];

		$actionid = $pmid == 0 ? 1 : 2;

		if($txtObjMasterId == '')
		{throw new Exception("Please Enter Object Name ");}

		$sql = "SELECT * FROM MEP_IT_NEW_DEVELOPMENT 
			WHERE OBJMASTER_NEW_ID!=$pmid 
			and  OBJMASTER_ID = $txtObjMasterId
			AND  ISDELETED   = 0
			AND  TECHPLATFORMID = $txTechPlatformId";	
		
		$data['sql'] = $sql;
	    $row_count = unique($sql);

		$data = array();
		if($row_count == 0)
   		
 	       {$query="EXEC [IT_NEWDEVELOPMENT_SP] 
			$actionid,
			$pmid, 
			'',
			'',   
			$txtObjMasterId,
			$txtUser1,
			'$txtdevelopmentDate',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			$userid,
			$txTechPlatformId,
			'$txtremarks'
			";
		
		
			$data['query'] = $query;
			//    echo json_encode($data);exit();
			
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		
       		
       $query = "SELECT  OBJMASTER_NEW_ID
						,OBJMASTER_ID
						,OBJMASTER_DESC
						,DEVELOPER_USER_ID
						,DEVELOPER_USER_NAME
						,OBJECT_TYPE_DESC
						,CONVERT(VARCHAR,DEVELOPMENT_DATE,106)DEVELOPMENT_DATE
						,STATUS_CODE
						,STATUS_CODE_DESC
						,TECHPLATFORMID
						,TECHPLATFORM
						,REMARKS
	             		FROM MEP_IT_NEW_DEVELOPMENT WHERE ISDELETED=0 
						AND  STATUS_CODE = 112
						  ORDER BY OBJMASTER_NEW_ID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['OBJMASTER_NEW_ID'] = (int) $row['OBJMASTER_NEW_ID'];
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




//Get Object Master
function getObjectMaster($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT * FROM MEP_OBJECT_MASTER 
		WHERE ISDELETED=0 and DEVELOPMENT_STATUS_CD=112 and OBJMASTER_ID not in (select OBJMASTER_ID from MEP_IT_NEW_DEVELOPMENT
		                                                                        where ISDELETED=0)
		ORDER BY OBJMASTER_ID";
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('FUNCTIONID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_NEWDEVELOPMENT_SP] 3,$pmid,'','','','','','','','','','','','',$userid ,'',''");
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
function deploy($mysqli){
	try{   
			global $userid;
			$pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
			$txtObjMasterId= $_POST['txtObjMasterId'] == 'undefined' ? '' : $_POST['txtObjMasterId'];  
			
			// $sql = "SELECT * from MEP_IT_NEW_DEVELOPMENT WHERE OBJMASTER_NEW_ID=$pmid and ISDELETED=0 and STATUS_CODE=14";
            // $data['sql'] = $sql;
	        // $row_count = unique($sql);
    		// $data = array();
			
			// if($row_count == 1)
			//  {
			// 	$data['success'] = false;
			//     $data['message'] = 'Please change the status to closed first';
			// 	echo json_encode($data);exit;
			//  }
			
			$sql = "SELECT * from  MEP_IT_NEW_DEVELOPMENT WHERE OBJMASTER_NEW_ID=$pmid and ISDELETED=0 and STATUS_CODE =33";

			$data['sql'] = $sql;
	        $row_count = unique($sql);
    		$data = array();
	
		if($row_count == 0)
		{
					
					if($pmid == 0){
						throw new Exception('FUNCTIONID Error.');
					}

					$stmt=sqlsrv_query($mysqli, "EXEC [IT_NEWDEVELOPMENT_SP] 4,$pmid,'','',$txtObjMasterId,'','','','','','','','','',$userid ,'',''");
					if( $stmt === false ) 
					{
						die( print_r( sqlsrv_errors(), true));
						throw new Exception( $mysqli->sqlstate );
					}
					else
					{
						$data['success'] = true;
						$data['message'] = 'Record successfully deployed';
					}
				echo json_encode($data);exit;
		}
		 else
		{
			$data['success'] = false;
			$data['message'] = 'Object  already deployed.';
			echo json_encode($data);exit;
		}
		
	
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






 
