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
		case "getHTMLMaster":getHTMLMaster($conn);break;
        case "getObjectType":getObjectType($conn);break;
		case "getFolder":getFolder($conn);break;
		case "getFunction":getFunction($conn);break;
		case "getTechPlatform":getTechPlatform($conn);break;
        case "delete":delete($conn);break;
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
        // $txtObject  = $_POST['txtObject'] == 'undefined' ? '' : $_POST['txtObject'];
		$txtOBJMASTER_DESC  = $_POST['txtOBJMASTER_DESC'] == 'undefined' ? '' : $_POST['txtOBJMASTER_DESC'];
		$txtddlObjTypeId  = $_POST['txtddlObjTypeId'] == 'undefined' ? '' : $_POST['txtddlObjTypeId'];
		$txtddlfolderid  = $_POST['txtddlfolderid'] == 'undefined' ? '' : $_POST['txtddlfolderid'];
		$txtddlfunctionid = $_POST['txtddlfunctionid'] == 'undefined' ? '' : $_POST['txtddlfunctionid'];
		$txTechPlatformId  = $_POST['txTechPlatformId'] == 'undefined' ? '' : $_POST['txTechPlatformId'];
		$chkEmail  = ($_POST['chkEmail'] == 'undefined' || $_POST['chkEmail']=='0') ? 0 : 1;
	    $chkStudentParent  = ($_POST['chkStudentParent'] == 'undefined' || $_POST['chkStudentParent']=='0') ? 0 : 1;
		$archiveflag  = ($_POST['archiveflag']=='undefined' || $_POST['archiveflag']=='0')?0:1;
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$txtDevelopmentStatus  = $_POST['txtDevelopmentStatus'] == 'undefined' ? '' : $_POST['txtDevelopmentStatus'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		if($txtOBJMASTER_DESC == '')
		{throw new Exception("Please Enter Object Name ");}

		$sql = "SELECT * FROM MEP_OBJECT_MASTER 
			WHERE OBJMASTER_DESC='$txtOBJMASTER_DESC' 
			AND   OBJMASTER_ID!=$pmid 
			AND   OBJTYPEID  = $txtddlObjTypeId
			and   FUNCTIONID = $txtddlfunctionid
			and   FOLDERID   = $txtddlfolderid
			AND   DEVELOPMENT_STATUS_CD = $txtDevelopmentStatus
			AND   ISDELETED   = 0
			AND   TECHPLATFORMID = $txTechPlatformId ";
						
		$data['sql'] = $sql;
	    $row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [IT_OBJECTMASTER_SP] 
			$actionid,
			$pmid,
			'$txtOBJMASTER_DESC',
			$txtddlObjTypeId,
			$txtddlfolderid,
			$txtddlfunctionid,
			$userid,
			$txTechPlatformId,
			'$txtremarks',
			$txtDevelopmentStatus,$chkEmail,$chkStudentParent,$archiveflag ";
			
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		
       	 
       $query = "SELECT 
	   			OBJMASTER_ID,
				OBJMASTER_DESC,
				OBJTYPEID,
				OBJECT_TYPE_DESC,
				FUNCTIONID,
				FUNCTION_DESC,
				FOLDERID,
				FOLDER_DESC,
				OBJMASTER_NEW_ID,
				TECHPLATFORMID,
				TECHPLATFORM, 
				DEVELOPMENT_STATUS_CD,
                DEVELOPMENT_STATUS_DESC,
				REMARKS,
				E_MAIL_FLAG,
				STUDENT_TEACHER_FLAG,
				ARCHIVE_FLAG
				FROM MEP_OBJECT_MASTER WHERE ISDELETED=0  ORDER BY OBJMASTER_ID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['OBJMASTER_ID'] = (int) $row['OBJMASTER_ID'];
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


 function getObjectType($mysqli){
	try
	{
		
		$query = "SELECT OBJTYPEID,OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER WHERE ISDELETED=0  ORDER BY OBJTYPEID ";

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


function getFolder($mysqli){
	try
	{
		// $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "select FOLDERID, FOLDER_DESC from MEP_OBJECT_FOLDER_PATHS where ISDELETED=0  ORDER BY folderid";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['FOLDERID'] = (int) $row['FOLDERID'];
				
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
		
		$query = "select FUNCTIONID, FUNCTION_DESC from MEP_OBJECT_FUNCTION_MASTER where ISDELETED=0  ORDER BY FUNCTIONID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				
				$row['FUNCTIONID'] = (int) $row['FUNCTIONID'];
				
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
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=35 and isdeleted=0";

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

			$stmt=sqlsrv_query($mysqli, "EXEC [IT_OBJECTMASTER_SP] 3,$pmid,'',0,0,0,$userid,0,'',0,0,0,0 ");
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







