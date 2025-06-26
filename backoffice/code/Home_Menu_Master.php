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
        case "getMenuData":getMenuData($conn);break;
        case "delete":delete($conn);break;

        case "saveItem":saveItem($conn);break;
        case "getMenuItemData":getMenuItemData($conn);break;
        case "deleteItem":deleteItem($conn);break;
		
        case "saveRes":saveRes($conn);break;
        case "getMenuResourceData":getMenuResourceData($conn);break;
        case "deleteRes":deleteRes($conn);break;

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
	$txtMenu  = ($_POST['txtMenu'] == 'undefined' || $_POST['txtMenu'] == '') ? '' : $_POST['txtMenu'];
	$txtSeqno  = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
	$txtColor  = ($_POST['txtColor'] == 'undefined' || $_POST['txtColor'] == '') ? '#000000' : $_POST['txtColor'];
	
	$actionid = $menuid == 0 ? 1 : 2;
	
	if($txtMenu == '') throw new Exception("Please Enter Menu Name.");
	
	$sql = "SELECT * FROM MENU WHERE MENU='$txtMenu' AND MENUID!=$menuid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MENU_SP] $actionid,$menuid,'$txtMenu',$txtSeqno,'$txtColor',$userid";
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
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['MENUID'] = $row['MENUID'];

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


/*============ GET MENU DATA =============*/ 
 function getMenuData($mysqli){
	try{
		$data = array();
		$query = "SELECT MENUID,MENU,SEQNO,COLOR FROM MENU WHERE ISDELETED=0 ORDER BY SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MENUID'] = (int) $row['MENUID'];
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
		$stmt=sqlsrv_query($mysqli, "EXEC [MENU_SP] 3,$MENUID,'',0,'',$userid");
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




// ##############################################
//                   MENU ITEMS
// ##############################################
function saveItem($mysqli){
	try{
	$data = array();
	global $userid;
	
	$data = array();
	$mitemid  = ($_POST['mitemid'] == 'undefined' || $_POST['mitemid'] == '') ? 0 : $_POST['mitemid'];
	$menuid  = ($_POST['menuid'] == 'undefined' || $_POST['menuid'] == '') ? 0 : $_POST['menuid'];
	$ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
	$txtSeqno  = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
	
	$actionid = $mitemid == 0 ? 1 : 2;
	
	if($menuid == 0) throw new Exception("Error : Invalid MENUID.");
	if($ddlProduct == 0) throw new Exception("Please Select Product Name.");
	
	$sql = "SELECT * FROM MENU_ITEMS WHERE MENUID=$menuid AND PDMID=$ddlProduct AND MITEMID!=$mitemid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MENU_ITEMS_SP] $actionid,$mitemid,$menuid,$ddlProduct,$txtSeqno,$userid";
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
			if(!empty($mitemid))$data['message'] = 'Record successfully updated.';
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


/*============ GET MENU ITEM DATA =============*/ 
function getMenuItemData($mysqli){
	try{
		$data = array();
		$MENUID = ($_POST['MENUID'] == 'undefined' || $_POST['MENUID'] == '') ? 0 : $_POST['MENUID'] ;
		if($MENUID == 0) throw new Exception('Error : Invalid MENUID.');
		$query = "SELECT MITEMID,PDMID,
		(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0 AND PDMID=MI.PDMID)PRODUCT,
		SEQNO FROM MENU_ITEMS MI WHERE ISDELETED=0 AND MENUID=$MENUID ORDER BY SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MITEMID'] = (int) $row['MITEMID'];
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
function deleteItem($mysqli){
	try{   
		global $userid;
		$data = array();     
		$MITEMID = ($_POST['MITEMID'] == 'undefined' || $_POST['MITEMID'] == '') ? 0 : $_POST['MITEMID']; 
		if($MITEMID==0)throw new Exception('MITEMID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [MENU_ITEMS_SP] 3,$MITEMID,0,0,0,$userid");
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




// ##############################################
//              MENU ITEMS RESOURCES
// ##############################################
function saveRes($mysqli){
	try{
	$data = array();
	global $userid;
	
	$data = array();
	$mresid  = ($_POST['mresid'] == 'undefined' || $_POST['mresid'] == '') ? 0 : $_POST['mresid'];
	$mitemid  = ($_POST['mitemid'] == 'undefined' || $_POST['mitemid'] == '') ? 0 : $_POST['mitemid'];
	$ddlResource  = ($_POST['ddlResource'] == 'undefined' || $_POST['ddlResource'] == '') ? 0 : $_POST['ddlResource'];
	$txtSeqno  = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
	
	$actionid = $mresid == 0 ? 1 : 2;
	
	if($mitemid == 0) throw new Exception("Error : Invalid MITEMID.");
	if($ddlResource == 0) throw new Exception("Please Select Resource Name.");
	
	$sql = "SELECT * FROM MENU_ITEM_RESOURCES WHERE MITEMID=$mitemid AND RESOURCEID=$ddlResource AND MRESID!=$mresid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MENU_ITEM_RESOURCES_SP] $actionid,$mresid,$mitemid,$ddlResource,$txtSeqno,$userid";
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
			if(!empty($mresid))$data['message'] = 'Record successfully updated.';
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


/*============ GET MENU ITEM RESOURCE DATA =============*/ 
function getMenuResourceData($mysqli){
	try{
		$data = array();
		$MITEMID = ($_POST['MITEMID'] == 'undefined' || $_POST['MITEMID'] == '') ? 0 : $_POST['MITEMID'] ;
		if($MITEMID == 0) throw new Exception('Error : Invalid MITEMID.');
		$query = "SELECT MRESID,RESOURCEID,(SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=MR.RESOURCEID)RESOURCE,SEQNO
		FROM MENU_ITEM_RESOURCES MR WHERE ISDELETED=0 AND MITEMID=$MITEMID ORDER BY SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['MRESID'] = (int) $row['MRESID'];
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
function deleteRes($mysqli){
	try{   
		global $userid;
		$data = array();     
		$MRESID = ($_POST['MRESID'] == 'undefined' || $_POST['MRESID'] == '') ? 0 : $_POST['MRESID']; 
		if($MRESID==0)throw new Exception('MRESID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [MENU_ITEM_RESOURCES_SP] 3,$MRESID,0,0,0,$userid");
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







