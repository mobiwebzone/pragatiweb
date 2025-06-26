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
		case "getMediaType":getMediaType($conn);break;
		case "getFileExt":getFileExt($conn);break;
		case "gethtml":gethtml($conn);break;
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
        $txtObjMaster_Id  = $_POST['txtObjMaster_Id'] == 'undefined' ? '' : $_POST['txtObjMaster_Id'];
        $txtWebSiteLocation  = $_POST['txtWebSiteLocation'] == 'undefined' ? '' : $_POST['txtWebSiteLocation'];
		$txtMenuNavigation = $_POST['txtMenuNavigation'] == 'undefined' ? '' : $_POST['txtMenuNavigation'];
		$txtMediaTypeId = $_POST['txtMediaTypeId'] == 'undefined' ? '' : $_POST['txtMediaTypeId'];
		$txtFileExtId = $_POST['txtFileExtId'] == 'undefined' ? '' : $_POST['txtFileExtId'];
		$txtMaxFileSize = $_POST['txtMaxFileSize'] == 'undefined' ? '' : $_POST['txtMaxFileSize'];
		$txtFileLength  = $_POST['txtFileLength'] == 'undefined' ? '' : $_POST['txtFileLength'];
		$txtFileWidth  = $_POST['txtFileWidth'] == 'undefined' ? '' : $_POST['txtFileWidth'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];

		$actionid = $pmid == 0 ? 1 : 2;
		
		if($txtWebSiteLocation == '')
		{throw new Exception("Please Enter Website Location ");}

		$sql = "SELECT * FROM MEP_IMAGE_MASTER 
			    WHERE MEP_IMAGE_MASTER_ID!=$pmid 
			    AND  OBJMASTER_ID  = $txtObjMaster_Id
			    AND  ISDELETED = 0";	


		$data['sql'] = $sql;
		$row_count = unique($sql);
		if($row_count == 0)
		{
			
			 $query="EXEC [IT_IMAGE_MASTER_SP] 
			 $actionid,
			 $pmid,
			 $txtObjMaster_Id,
			'$txtWebSiteLocation',
			'$txtMenuNavigation',
			 $txtMediaTypeId,
			 $txtFileExtId,
			 $txtMaxFileSize,
			 $txtFileLength,
			 $txtFileWidth,
			 $userid,'$txtremarks'";

			$data['query'] = $query;
			// throw new Exception($query);
			
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				
				$data['success'] = false;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($pmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				// echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
		}
		echo json_encode($data);exit;

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
		
       	 
       $query = "SELECT 
	   			 MEP_IMAGE_MASTER_ID
				,OBJMASTER_ID
				,OBJMASTER_DESC
				,WEBSITE_LOCATION
				,MENU_NAVIGATION
				,MEDIA_TYPE_ID
				,MEDIA_TYPE_DESC
				,FILE_EXTENSION_ID
				,FILE_EXTENSION_DESC
				,FILE_SIZE
				,FILE_LENGTH
				,FILE_WIDTH
				,REMARKS FROM 
				MEP_IMAGE_MASTER WHERE ISDELETED=0  ORDER BY MEP_IMAGE_MASTER_ID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['MEP_IMAGE_MASTER_ID'] = (int) $row['MEP_IMAGE_MASTER_ID'];
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

//Get HTML Objects
function gethtml($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT OBJMASTER_ID,OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE OBJTYPEID=1 AND ISDELETED=0 order by OBJMASTER_DESC";
        
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


 function getMediaType($mysqli){
	try
	{
		
		$query = "SELECT CODE_DETAIL_ID, CODE_DETAIL_DESC  FROM MEP_CODE_DETAILS WHERE CODE_ID=19 AND ISDELETED=0 ";

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




function getFileExt($mysqli){
	try
	{
		
		$query = "SELECT CODE_DETAIL_ID, CODE_DETAIL_DESC  FROM MEP_CODE_DETAILS WHERE CODE_ID=22 AND ISDELETED=0";

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
				throw new Exception('FUNCTIONID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_IMAGE_MASTER_SP] 3,$pmid,'','','','','','','','',$userid ,'' ");
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







