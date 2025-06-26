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
        case "getUnderMenu":getUnderMenu($conn);break;
        case "getMenuData":getMenuData($conn);break;
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



// ##############################################
//                      MENU
// ##############################################
function save($mysqli){
	try{
	$data = array();
	global $userid;
	
	$data = array();
	$menuid  = ($_POST['menuid'] == 'undefined' || $_POST['menuid'] == '') ? 0 : $_POST['menuid'];
	$txtMenu  = ($_POST['txtMenu'] == 'undefined' || $_POST['txtMenu'] == '') ? '' : str_replace("'","''",$_POST['txtMenu']);
	$txtShortName  = ($_POST['txtShortName'] == 'undefined' || $_POST['txtShortName'] == '') ? '' : str_replace("'","''",$_POST['txtShortName']);
	$ddlUnderMenu = ($_POST['ddlUnderMenu'] == 'undefined' || $_POST['ddlUnderMenu'] == '') ? 0 : $_POST['ddlUnderMenu'];
	$ddlfunctionid = ($_POST['ddlfunctionid'] == 'undefined' || $_POST['ddlfunctionid'] == '') ? 0 : $_POST['ddlfunctionid'];
	$hasLink = ($_POST['hasLink'] == 'undefined' || $_POST['hasLink'] == '' || $_POST['hasLink']=='0') ? 0 : 1;
	$txtPageLink = ($_POST['txtPageLink'] == 'undefined' || $_POST['txtPageLink'] == '') ? '' : $_POST['txtPageLink'];
	$txtPageDesc = ($_POST['txtPageDesc'] == 'undefined' || $_POST['txtPageDesc'] == '') ? '' : str_replace("'","''",$_POST['txtPageDesc']);
	$txtSEQNO = ($_POST['txtSEQNO'] == 'undefined' || $_POST['txtSEQNO'] == '') ? 0 : $_POST['txtSEQNO'];
	$isHeader  = ($_POST['isHeader'] == 'undefined' || $_POST['isHeader'] == '' || $_POST['isHeader'] == '0') ? 0 : 1;
    $txtHeader  = ($_POST['txtHeader'] == 'undefined' || $_POST['txtHeader'] == 'null') ? '' : str_replace("'","''",$_POST['txtHeader']);
	
	$actionid = $menuid == 0 ? 1 : 2;
	
	if($txtMenu == '') throw new Exception("Please Enter Menu Name.");
	// if($txtShortName == '') throw new Exception("Please Enter Menu Short Name.");
	
	$sql = "SELECT * FROM BO_MENU WHERE MENU='$txtMenu' AND UNDER_MENUID=$ddlUnderMenu AND SEQNO=$txtSEQNO AND MENUID!=$menuid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [BO_MENU_SP] $actionid,$menuid,'$txtMenu','$txtShortName',$ddlUnderMenu, $ddlfunctionid, $hasLink,'$txtPageLink','$txtPageDesc',$txtSEQNO,$isHeader,'$txtHeader',$userid";
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
			// GET MENUID
			// $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			// $data['MENUID'] = $row['MENUID'];

			$data['success'] = true;
			if(!empty($menuid))$data['message'] = 'Record successfully updated.';
			else $data['message'] = 'Record successfully inserted.';
			echo json_encode($data);exit;
		}
		
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Record already exists.';
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


/*============ GET UNDER MENU DATA =============*/ 
 function getUnderMenu($mysqli){
	try{
		$data = array();
		$query = "SELECT MENUID,MENU+''+
			ISNULL((SELECT CASE WHEN MENU IS NULL THEN '' ELSE ' (' + MENU + ')' END FROM BO_MENU WHERE MENUID=BM.UNDER_MENUID),'') MENU
		FROM BO_MENU BM WHERE ISDELETED=0 AND HAS_LINK=0 ORDER BY MENU";
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
 function getMenuData($mysqli){
	try{
		$data = array();
		$query = "SELECT MENUID,MENU,MENU_SHORTNAME,UNDER_MENUID,
				ISNULL((SELECT MENU+''+ISNULL((SELECT CASE WHEN MENU IS NULL THEN '' ELSE ' (' + MENU + ')' END FROM BO_MENU WHERE MENUID=BMM.UNDER_MENUID),'') FROM BO_MENU BMM WHERE MENUID=BM.UNDER_MENUID),'-')UNDER_MENU,
				HAS_LINK,PAGE_LINK,PAGE_DESC,SEQNO,ISHEADER,HEADER,
				ISNULL((SELECT (SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=LM.LOCID)+', ' FROM BO_LOCATION_MENU_OPTIONS LM WHERE ISDELETED=0 AND MENUID=BM.MENUID FOR XML PATH('')),'-')LOCATIONS,
				ISNULL((SELECT TOP 1 FUNCTIONID FROM MEP_OBJECT_MASTER WHERE OBJMASTER_DESC=BM.PAGE_LINK AND ISDELETED=0),0) FUNCTIONID
				FROM BO_MENU BM
				WHERE ISDELETED=0 
				ORDER BY MENUID DESC,UNDER_MENUID,SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['HAS_LINK'] = (string) $row['HAS_LINK'];
				$row['ISHEADER'] = (string) $row['ISHEADER'];
				$row['LOCATIONS'] = rtrim($row['LOCATIONS'],", ");
				$row['LOCATIONS'] = str_replace(", ",",\n",$row['LOCATIONS']);
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
		$MENUID = ($_POST['MENUID'] == 'undefined' || $_POST['MENUID'] == '') ? 0 : $_POST['MENUID']; 
		if($MENUID==0)throw new Exception('MENUID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [BO_MENU_SP] 3,$MENUID,'','',0,0,0,'','',0,0,'',$userid");
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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







