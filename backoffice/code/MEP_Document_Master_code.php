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
		case "getSystemdocu":getSystemdocu($conn);break;
		case "getBusinessProc":getBusinessProc($conn);break;
		case "getRolesdata":getRolesdata($conn);break;
        case "getINOUT":getINOUT($conn);break;
		case "getfLocationenabled":getfLocationenabled($conn);break;
		case "getfDisplaywebsite":getfDisplaywebsite($conn);break;
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


// Get getBusinessProc
function getBusinessProc($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT BUSINESS_PROCEDURE_ID,BUSINESS_PROCEDURE_NAME FROM MEP_BUSINESS_PROCEDURE_MASTER WHERE ISDELETED=0";
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

// Get getRolesdata
function getRolesdata($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT ROLE_ID,ROLE_DESC FROM MEP_ROLES WHERE ISDELETED=0";
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



// Get getINOUT
function getINOUT($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=21 and isdeleted=0";
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


// Get getfLocationenabled
function getfLocationenabled($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";
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


// Get getfDisplaywebsite
function getfDisplaywebsite($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";
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

// SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$DESIGN_DOC_ID  = ($_POST['DESIGN_DOC_ID'] == 'undefined' || $_POST['DESIGN_DOC_ID'] == '') ? 0 : $_POST['DESIGN_DOC_ID'];
		$ddlbusinessProc  = ($_POST['ddlbusinessProc'] == 'undefined' || $_POST['ddlbusinessProc'] == '') ? 0 : $_POST['ddlbusinessProc'];
		$txtBPgroupname  = ($_POST['txtBPgroupname'] == 'undefined' || $_POST['txtBPgroupname'] == '') ? '' : $_POST['txtBPgroupname'];
		$txtfilename  = ($_POST['txtfilename'] == 'undefined' || $_POST['txtfilename'] == '') ? '' : $_POST['txtfilename'];
        $txtMenuNav  = ($_POST['txtMenuNav'] == 'undefined' || $_POST['txtMenuNav'] == '') ? '' : $_POST['txtMenuNav'];
		$txtHTMLcontent  = ($_POST['txtHTMLcontent'] == 'undefined' || $_POST['txtHTMLcontent'] == '') ? '' : str_replace("'","''",$_POST['txtHTMLcontent']);
        $ddlRoles  = ($_POST['ddlRoles'] == 'undefined' || $_POST['ddlRoles'] == '') ? 0 : $_POST['ddlRoles'];
        $ddlInOut  = ($_POST['ddlInOut'] == 'undefined' || $_POST['ddlInOut'] == '') ? 0 : $_POST['ddlInOut'];
		$ddlLocationEnable  = ($_POST['ddlLocationEnable'] == 'undefined' || $_POST['ddlLocationEnable'] == '') ? 0 : $_POST['ddlLocationEnable'];
		$ddlDisplaywebsite  = ($_POST['ddlDisplaywebsite'] == 'undefined' || $_POST['ddlDisplaywebsite'] == '') ? 0 : $_POST['ddlDisplaywebsite'];
		$txtGoogledrive1  = ($_POST['txtGoogledrive1'] == 'undefined' || $_POST['txtGoogledrive1'] == '') ? '' : $_POST['txtGoogledrive1'];
		$txtGoogledrive2  = ($_POST['txtGoogledrive2'] == 'undefined' || $_POST['txtGoogledrive2'] == '') ? '' : $_POST['txtGoogledrive2'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $DESIGN_DOC_ID == 0 ? 1 : 2;
		
		
		if($ddlbusinessProc == 0) throw new Exception("Please Select BUSINESS PROCEDURE.");

		
		// $sql = "SELECT * FROM MEP_FRANCHISE_BUYING_lIST WHERE FRANCHISE_NAME=$ddlfranchiseID AND TASK_DESC='$txtTask' AND TASK_ID!=$TASK_ID AND ISDELETED=0";
		// $data['$query'] = $sql;
		// echo json_encode($data);exit;
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [MEP_SYSTEM_DESIGN_MASTER_SP] $actionid,$DESIGN_DOC_ID,$ddlbusinessProc,'','$txtBPgroupname','$txtfilename','$txtMenuNav','$txtHTMLcontent',$ddlRoles,
			$ddlInOut,$ddlLocationEnable,$ddlDisplaywebsite,'$txtGoogledrive1','$txtGoogledrive2',$userid,'$txtRemark'";
			//  throw new Exception($query);
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
				if(!empty($DESIGN_DOC_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists.';
		// 	echo json_encode($data);exit;
		// }

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

 
		
/*============ GET ITEM DATA =============*/ 
function getSystemdocu($mysqli){
	try
	{
		$data = array();

		$query = "SELECT DESIGN_DOC_ID,BUSINESS_PROCEDURE_ID,BUSINESS_PROCEDURE_NAME,BUSINESS_PROCEDURE_GROUP_NAME,HTML_FILE_NAME,MENU_NAVIGATION,
		 HTML_CONTENT,ROLE_ID,ROLE_DESC,SCOPE_ID,SCOPE,LOCATION_ENABLED_CODE,LOCATION_ENABLED_CODE_DESC,DISPLAY_ON_WEBSITE_CD,DISPLAY_ON_WEBSITE_DESC,
		 GOOGLE_DRIVE_LINK_VIDEO,GOOGLE_DRIVE_LINK_PDF,REMARKS
		from MEP_SYSTEM_DESIGN_MASTER
		WHERE ISDELETED=0
		ORDER BY DESIGN_DOC_ID";

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
            $DESIGN_DOC_ID = ($_POST['DESIGN_DOC_ID'] == 'undefined' || $_POST['DESIGN_DOC_ID'] == '') ? 0 : $_POST['DESIGN_DOC_ID'];
			if($DESIGN_DOC_ID == 0) throw new Exception('Invalid Item ID.');
			$query="EXEC [MEP_SYSTEM_DESIGN_MASTER_SP] 3,$DESIGN_DOC_ID,0,'','','','','',0,0,0,0,'','',$userid,''";
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








