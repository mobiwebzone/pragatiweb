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
        case "getReport":getReport($conn);break;
        case "getItems":getItems($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET REPORT =========== */
 function getReport($mysqli){
	try
	{
		$data = array();
		$ddlItem = ($_POST['ddlItem'] == 'undefined' || $_POST['ddlItem'] == '') ? 0 : $_POST['ddlItem'];
		if($ddlItem==0)throw new Exception('Please Select Item Name.');
			
		$query = "EXEC [RPT_ITEM_LEDGER]$ddlItem";
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET REPORT =========== */



/*============ Get Items =============*/ 
function getItems($mysqli){
	try
	{
		$ddlItemCategory = ($_POST['ddlItemCategory'] == 'undefined' || $_POST['ddlItemCategory'] == '') ? 0 : $_POST['ddlItemCategory'] ;
		if($ddlItemCategory == 0)throw new Exception("ICATID Invalid.");
		$query = "SELECT ITEMID,ITEM,ITEMDESC FROM ITEM_MASTER IM WHERE ISDELETED=0 AND ICATID=$ddlItemCategory ORDER BY ITEM";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ITEMID'] = (int) $row['ITEMID'];
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
/*============ Get Items =============*/ 

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







