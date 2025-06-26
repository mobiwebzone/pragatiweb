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
	$footid  = ($_POST['footid'] == 'undefined' || $_POST['footid'] == '') ? 0 : $_POST['footid'];
	$txtHeading  = ($_POST['txtHeading'] == 'undefined' || $_POST['txtHeading'] == '') ? '' : $_POST['txtHeading'];
	$txtSeqno  = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
	$txtColor  = ($_POST['txtColor'] == 'undefined' || $_POST['txtColor'] == '') ? '#000000' : $_POST['txtColor'];
	
	$actionid = $footid == 0 ? 1 : 2;
	
	if($txtHeading == '') throw new Exception("Please Enter Menu Name.");
	
	$sql = "SELECT * FROM MENU_FOOTER WHERE HEADING='$txtHeading' AND FOOTID!=$footid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MENU_FOOTER_SP] $actionid,$footid,'$txtHeading',$txtSeqno,'$txtColor',$userid";
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
			// GET FOOTID
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['FOOTID'] = $row['FOOTID'];

			$data['success'] = true;
			if(!empty($footid))$data['message'] = 'Record successfully updated.';
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
		$query = "SELECT FOOTID,HEADING,SEQNO,COLOR FROM MENU_FOOTER WHERE ISDELETED=0 ORDER BY SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['FOOTID'] = (int) $row['FOOTID'];
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
		echo json_encode($data);exit;
	}
}



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$FOOTID = ($_POST['FOOTID'] == 'undefined' || $_POST['FOOTID'] == '') ? 0 : $_POST['FOOTID']; 
		if($FOOTID==0)throw new Exception('FOOTID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [MENU_FOOTER_SP] 3,$FOOTID,'',0,'',$userid");
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
	$footdetid  = ($_POST['footdetid'] == 'undefined' || $_POST['footdetid'] == '') ? 0 : $_POST['footdetid'];
	$footid  = ($_POST['footid'] == 'undefined' || $_POST['footid'] == '') ? 0 : $_POST['footid'];
	$ddlProductResource  = ($_POST['ddlProductResource'] == 'undefined' || $_POST['ddlProductResource'] == '') ? '' : $_POST['ddlProductResource'];
	$PROC_RES_ID  = ($_POST['PROC_RES_ID'] == 'undefined' || $_POST['PROC_RES_ID'] == '') ? 0 : $_POST['PROC_RES_ID'];
	$txtSeqno  = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
	$isHeader  = ($_POST['isHeader'] == 'undefined' || $_POST['isHeader'] == '' || $_POST['isHeader'] == '0') ? 0 : 1;
    $txtHeader  = ($_POST['txtHeader'] == 'undefined' || $_POST['txtHeader'] == 'null') ? '' : str_replace("'","''",$_POST['txtHeader']);
	
	$actionid = $footdetid == 0 ? 1 : 2;
	
	if($footid == 0) throw new Exception("Error : Invalid FOOTID.");
	if($ddlProductResource == '') throw new Exception("Please Select Product/Resource.");
	if($PROC_RES_ID == 0) throw new Exception("Please Select Product/Resource Name.");
	
	$sql = "SELECT * FROM MENU_FOOTER_DETAILS WHERE FOOTID=$footid AND PRODUCT_RESOURCE='$ddlProductResource' AND PRODUCT_RESOURCE_ID=$PROC_RES_ID AND FOOTDETID!=$footdetid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MENU_FOOTER_DETAILS_SP] $actionid,$footdetid,$footid,'$ddlProductResource',$PROC_RES_ID,$txtSeqno,$isHeader,'$txtHeader',$userid";
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
			if(!empty($footdetid))$data['message'] = 'Record successfully updated.';
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
		$FOOTID = ($_POST['FOOTID'] == 'undefined' || $_POST['FOOTID'] == '') ? 0 : $_POST['FOOTID'] ;
		if($FOOTID == 0) throw new Exception('Error : Invalid FOOTID.');
		$query = "SELECT FOOTDETID,PRODUCT_RESOURCE,PRODUCT_RESOURCE_ID,
		CASE WHEN PRODUCT_RESOURCE='PRODUCT' 
			THEN ISNULL((SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0 AND PDMID=MF.PRODUCT_RESOURCE_ID),'')
			WHEN PRODUCT_RESOURCE='RESOURCE' 
			THEN ISNULL((SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=MF.PRODUCT_RESOURCE_ID),'')
			ELSE ''
		END PRODUCT_RESOURCE_NAME,SEQNO,ISHEADER,HEADER
		FROM MENU_FOOTER_DETAILS MF WHERE ISDELETED=0 AND FOOTID=$FOOTID
		ORDER BY SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ISHEADER'] = (string) $row['ISHEADER'];
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
		$FOOTDETID = ($_POST['FOOTDETID'] == 'undefined' || $_POST['FOOTDETID'] == '') ? 0 : $_POST['FOOTDETID']; 
		if($FOOTDETID==0)throw new Exception('FOOTDETID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [MENU_FOOTER_DETAILS_SP] 3,$FOOTDETID,0,'',0,0,0,'',$userid");
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







