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
		
		$LOCID  = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$UID  = ($_POST['UID'] == 'undefined' || $_POST['UID'] == '') ? 0 : $_POST['UID'];
		$MENUID  = ($_POST['MENUID'] == 'undefined' || $_POST['MENUID'] == '') ? 0 : $_POST['MENUID'];
		$val = ($_POST['val'] == 'undefined' || $_POST['val'] == '' || $_POST['val']==0) ? 0 : 1 ;
	
		if($LOCID == 0) throw new Exception("Invalid LOCID.");
		if($UID == 0) throw new Exception("Invalid UID.");
		if($MENUID == 0) throw new Exception("Invalid MENUID.");

		$query="EXEC [BO_LOCATION_USER_MENU_PERMISSIONS_UPDATE] $LOCID,$UID,$MENUID,$val,$userid";
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
		$UID  = ($_POST['USERID'] == 'undefined' || $_POST['USERID'] == '') ? 0 : $_POST['USERID'];
		$USERID_COPY  = ($_POST['USERID_COPY'] == 'undefined' || $_POST['USERID_COPY'] == '' || !isset($_POST['USERID_COPY'])) ? '' : $_POST['USERID_COPY'];
	
		if($LOCID == 0) throw new Exception("Invalid Location.");
		if($UID == 0) throw new Exception("Invalid User.");
		if($USERID_COPY == '') throw new Exception("Invalid Copy to User.");
	
		$query="EXEC [BO_LOCATION_USER_MENU_PERMISSIONS_COPY] $LOCID,$UID,'$USERID_COPY',$userid";
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
		$data = array();
		$USERID = ($_POST['USERID'] == 'undefined' || $_POST['USERID'] == '') ? 0 : $_POST['USERID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($USERID==0) throw new Exception('Invalid USERID.');
		if($LOCID==0) throw new Exception('Invalid LOCID.');
		
		// $CheckLocQuery = "INSERT INTO BO_LOCATION_USER_MENU_PERMISSIONS (LOCID,USERID,MENUID) 
		// SELECT $LOCID,$USERID,MENUID FROM BO_MENU WHERE ISDELETED=0 AND HAS_LINK=1 AND MENUID NOT IN (SELECT MENUID FROM BO_LOCATION_USER_MENU_PERMISSIONS WHERE ISDELETED=0 AND LOCID=$LOCID AND USERID=$USERID)";
		// sqlsrv_query($mysqli, $CheckLocQuery);

		// $query = "SELECT PERMID,BL.MENUID,
		// ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M1 WHERE MENUID=M.UNDER_MENUID),'')UNDER_MENU,
		// M.MENU,M.PAGE_LINK,M.PAGE_DESC,BL.ACTIVE,
		// (SELECT FIRSTNAME+'' +LASTNAME FROM USERS WHERE UID=BL.USERID)USERNAME
		// FROM BO_LOCATION_USER_MENU_PERMISSIONS BL,BO_MENU M WHERE BL.MENUID=M.MENUID AND BL.ISDELETED=0 AND M.ISDELETED=0
		// AND BL.LOCID=$LOCID AND BL.USERID=$USERID  AND M.MENUID IN (SELECT MENUID FROM BO_LOCATION_MENU_OPTIONS LM WHERE LOCID=1 AND ACTIVE=1 AND ISDELETED=0)";
		
		$query = "SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M1 WHERE MENUID=B.UNDER_MENUID),'')UNDER_MENU,
		LM.MENUID,B.MENU,B.PAGE_LINK,B.PAGE_DESC,
		CASE WHEN (SELECT COUNT(*) FROM BO_LOCATION_USER_MENU_PERMISSIONS WHERE ACTIVE=1 AND ISDELETED=0 AND MENUID=LM.MENUID AND USERID=$USERID AND LOCID=LM.LOCID)>0 THEN 1 ELSE 0 END ACTIVE,B.UNDER_MENUID
		FROM BO_LOCATION_MENU_OPTIONS LM,BO_MENU B WHERE ACTIVE=1 AND LM.ISDELETED=0 AND LOCID=$LOCID AND LM.MENUID=B.MENUID";
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







