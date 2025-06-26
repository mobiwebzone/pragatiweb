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
		case "getTrackingData":getTrackingData($conn);break;
		case "getTechPlatform":getTechPlatform($conn);break;
        case "getObjectType":getObjectType($conn);break;
		case "getObject":getObject($conn);break;
        case "getRelatedObjectType":getRelatedObjectType($conn);break;
		case "getRelatedObject":getRelatedObject($conn);break;
        case "getDevelpedBY":getDevelpedBY($conn);break;
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

 // GET TECH PLATFORM 
 function getTechPlatform($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT TECHPLATFORMID,TECHPLATFORM FROM MEP_TECH_PLATFORM WHERE ISDELETED=0";
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




// GET OBJECT TYPE
function getObjectType($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT OBJTYPEID,OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER where isdeleted=0";
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

// GET OBJECT
function getObject($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT OBJMASTER_ID,OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE ISDELETED=0";
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




// GET RELATED OBJECT TYPE
function getRelatedObjectType($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT OBJTYPEID,OBJECT_TYPE_DESC FROM MEP_OBJECT_TYPE_MASTER where isdeleted=0";
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

// GET REALTED OBJECT
function getRelatedObject($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT OBJMASTER_ID,OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE ISDELETED=0";
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



// Get getUser
function getDevelpedBY($mysqli){
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


                

 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		$OBJECT_CHANGE_ID  = ($_POST['OBJECT_CHANGE_ID'] == 'undefined' || $_POST['OBJECT_CHANGE_ID'] == '') ? 0 : $_POST['OBJECT_CHANGE_ID'];
        $ddlTechPlatform  = ($_POST['ddlTechPlatform'] == 'undefined' || $_POST['ddlTechPlatform'] == '') ? 0 : $_POST['ddlTechPlatform'];
        $ddlObjectType  = ($_POST['ddlObjectType'] == 'undefined' || $_POST['ddlObjectType'] == '') ? 0 : $_POST['ddlObjectType'];
		$ddlObject  = ($_POST['ddlObject'] == 'undefined' || $_POST['ddlObject'] == '') ? 0 : $_POST['ddlObject'];
        $ddlRelatedObjectType  = ($_POST['ddlRelatedObjectType'] == 'undefined' || $_POST['ddlRelatedObjectType'] == '') ? 0 : $_POST['ddlRelatedObjectType'];
		$ddlRelatedObject  = ($_POST['ddlRelatedObject'] == 'undefined' || $_POST['ddlRelatedObject'] == '') ? 0 : $_POST['ddlRelatedObject'];
		$TxtModificaton  = ($_POST['TxtModificaton'] == 'undefined' || $_POST['TxtModificaton'] == '') ? '' : $_POST['TxtModificaton'];
        $ddlDevelopedBY  = ($_POST['ddlDevelopedBY'] == 'undefined' || $_POST['ddlDevelopedBY'] == '') ? 0 : $_POST['ddlDevelopedBY'];
        $Deploydate  = ($_POST['Deploydate'] == 'undefined' || $_POST['Deploydate'] == '') ? 0 : $_POST['Deploydate'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $OBJECT_CHANGE_ID == 0 ? 1 : 2;
		
		
		if($ddlTechPlatform == 0)
		{throw new Exception("Please Select TECH PLATFORM.");}
	
		// $querycount = "SELECT * FROM IT_OBJECT_CHANGES_TRACKER WHERE PUBLICATION_ID=$ddlPublicationname AND PUBLICATION_SITE_CD=$ddlPublicationSITE AND OBJECT_CHANGE_ID!=$OBJECT_CHANGE_ID AND ISDELETED=0";
		// $row_count = unique($querycount);	


		// if($row_count == 0)
		// {
			$query="EXEC [IT_OBJECT_CHANGES_TRACKER_SP] $actionid,$OBJECT_CHANGE_ID,$ddlObjectType,$ddlObject,
			$ddlRelatedObjectType,$ddlRelatedObject,'$TxtModificaton',$ddlDevelopedBY,'$Deploydate',
			$userid,$ddlTechPlatform,'$txtRemark'";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($OBJECT_CHANGE_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
		//  }
		//  else
		//  {
			
		//  	$data['success'] = false;
		//  	$data['message'] = 'Record already exists.';
		//  	echo json_encode($data);exit;
		//  }

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

/*============ GET SALES DATA =============*/ 
function getTrackingData($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT OBJECT_CHANGE_ID,TECHPLATFORMID,TECHPLATFORM,OBJTYPEID,OBJECT_TYPE_DESC,OBJMASTER_ID,OBJMASTER_DESC,OTHER_OBJTYPEID
        ,OTHER_OBJECT_TYPE_DESC,OTHER_OBJMASTER_ID,OTHER_OBJMASTER_DESC,MODIFICATION_DESC,DEVELOPER_ID,DEVELOPER_NAME,
		CONVERT(VARCHAR,DEPLOYMENT_DATE,106)DEPLOYMENT_DATE,INSERTID,REMARKS 
		FROM IT_OBJECT_CHANGES_TRACKER 
		WHERE ISDELETED=0 
		ORDER BY OBJECT_CHANGE_ID";

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
            $OBJECT_CHANGE_ID = ($_POST['OBJECT_CHANGE_ID'] == 'undefined' || $_POST['OBJECT_CHANGE_ID'] == '') ? 0 : $_POST['OBJECT_CHANGE_ID'];
			if($OBJECT_CHANGE_ID == 0) throw new Exception('Invalid PUBLICATION-ID.');
			$query = "EXEC [IT_OBJECT_CHANGES_TRACKER_SP] 3,$OBJECT_CHANGE_ID,0,0,0,0,'',0,'',$userid,0,''";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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






