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
        case "updatePermission":updatePermission($conn);break;
        case "updatePermissionAll":updatePermissionAll($conn);break;
        case "copyMenu":copyMenu($conn);break;
        case "getMenuForLocation":getMenuForLocation($conn);break;
        case "getMenusData":getMenusData($conn);break;
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



// ##############################################
//                      MENU
// ##############################################
function updatePermission($mysqli){
	try{
		$data = array();
		global $userid;
		
		$data = array();
		$LOCID  = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$MENUID  = ($_POST['MENUID'] == 'undefined' || $_POST['MENUID'] == '') ? 0 : $_POST['MENUID'];
		$val = ($_POST['val'] == 'undefined' || $_POST['val'] == '' || $_POST['val']==0) ? 0 : 1 ;
		
	
	
		if($LOCID == 0) throw new Exception("Invalid Location.");
		if($MENUID == 0) throw new Exception("Invalid MENUID.");
	

		// $query="UPDATE BO_LOCATION_MENU_OPTIONS SET ACTIVE=$val WHERE LOCMENUID=$LOCMENUID";
		$query="EXEC [BO_LOCATION_MENU_OPTIONS_UPDATE] $LOCID,$MENUID,$val,$userid";
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		// throw new Exception($query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{

			$data['success'] = true;
			$data['message'] = 'Record successfully updated.';
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


function updatePermissionAll($mysqli){
	try{
		$data = array();
		global $userid;
		
		$data = array();
		$LOCID  = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$val = ($_POST['val'] == 'undefined' || $_POST['val'] == '' || $_POST['val']==0) ? 0 : 1 ;
	
	
		if($LOCID == 0) throw new Exception("Invalid Location.");
	

		// $query="UPDATE BO_LOCATION_MENU_OPTIONS SET ACTIVE=$val WHERE LOCMENUID=$LOCMENUID";
		$query="EXEC [BO_LOCATION_MENU_OPTIONS_UPDATE_ALL] $LOCID,$val,$userid";
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		// throw new Exception($query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{

			$data['success'] = true;
			$data['message'] = 'Record successfully updated.';
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


/*============ COPY MENU LOCATION =============*/ 
function copyMenu($mysqli){
	try{
		$data = array();
		global $userid;
		
		$data = array();
		$LOCID  = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$LOCID_COPY  = ($_POST['LOCID_COPY'] == 'undefined' || $_POST['LOCID_COPY'] == '' || !isset($_POST['LOCID_COPY'])) ? '' : $_POST['LOCID_COPY'];
	
		if($LOCID == 0) throw new Exception("Invalid Location.");
		if($LOCID_COPY == '') throw new Exception("Invalid Copy to Location.");
	
		$query="EXEC [BO_LOCATION_MENU_OPTIONS_COPY] $LOCID,'$LOCID_COPY',$userid";
		$data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{

			$data['success'] = true;
			$data['message'] = 'Record successfully copy.';
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



/*============ GET MENU FOR LOCATION =============*/ 
 function getMenuForLocation($mysqli){
	try{
		$data = array();
		$query = "SELECT MENUID,MENU FROM BO_MENU WHERE ISDELETED=0 AND HAS_LINK=1 ORDER BY MENU";
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


/*============ GET MENU DATA =============*/ 
 function getMenusData($mysqli){
	try{
		global $userid;
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID==0) throw new Exception('Invalid LOCID.');
		
		// $CheckLocQuery = "INSERT INTO BO_LOCATION_MENU_OPTIONS (LOCID,MENUID,INSERTID) 
		// SELECT $LOCID,MENUID,$userid FROM BO_MENU WHERE ISDELETED=0 AND HAS_LINK=1 AND MENUID NOT IN (SELECT MENUID FROM BO_LOCATION_MENU_OPTIONS WHERE ISDELETED=0 AND LOCID=$LOCID)";
		// sqlsrv_query($mysqli, $CheckLocQuery);

		// $query = "SELECT LOCMENUID,
		// ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M1 WHERE MENUID=M.UNDER_MENUID),'')UNDER_MENU,
		// BL.MENUID,M.MENU,M.PAGE_LINK,M.PAGE_DESC,ACTIVE,M.UNDER_MENUID
		// FROM BO_LOCATION_MENU_OPTIONS BL,BO_MENU M WHERE BL.MENUID=M.MENUID AND BL.ISDELETED=0 AND M.ISDELETED=0 AND BL.LOCID=$LOCID";
		
		$query = "SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M1 WHERE MENUID=M.UNDER_MENUID),'')UNDER_MENU,
		MENUID,MENU,PAGE_LINK,PAGE_DESC,
		--CASE WHEN (SELECT COUNT(*) FROM BO_LOCATION_MENU_OPTIONS WHERE ISDELETED=0 AND MENUID=M.MENUID AND LOCID=$LOCID)>0 THEN 1 ELSE 0 END ACTIVE,
		(SELECT ACTIVE FROM BO_LOCATION_MENU_OPTIONS WHERE ISDELETED=0 AND MENUID=M.MENUID AND LOCID=$LOCID)ACTIVE,
		M.UNDER_MENUID
		FROM BO_MENU M WHERE ISDELETED=0 AND HAS_LINK=1";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ACTIVE'] = (string) $row['ACTIVE'];
				$row['UNDER_MENU'] = str_replace(","," <span class='font-weight-bold font-18'> &#10230; </span> ",$row['UNDER_MENU']);
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







