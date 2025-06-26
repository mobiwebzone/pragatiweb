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
        case "getTransForName":getTransForName($conn);break;
		case "getTransactionsByTransForID":getTransactionsByTransForID($conn);break;
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
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$txtTRANSTYPE = ($_POST['txtTRANSTYPE'] == 'undefined' || $_POST['txtTRANSTYPE'] == '') ? '' : $_POST['txtTRANSTYPE']; 
		$txtTransactionFor = ($_POST['txtTransactionFor'] == 'undefined' || $_POST['txtTransactionFor'] == '') ? '' : $_POST['txtTransactionFor']; 
		$ddlForTransName = ($_POST['ddlForTransName'] == 'undefined' || $_POST['ddlForTransName'] == '') ? 0 : $_POST['ddlForTransName'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
			
		$query = "EXEC [RPT_ITEM_STOCK_TRANSACTIONS_DATEWISE]'$txtFromDT','$txtToDT','$txtTRANSTYPE',$ddlForTransName,$ddlLocation";
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







