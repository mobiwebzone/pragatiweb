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
        case "saveData":saveData($conn);break;
        case "getCategories":getCategories($conn);break;
        case "delete":delete($conn);break;
		
		// ============ SUB HEAD ===========
        case "saveDataSubHead":saveDataSubHead($conn);break;
        case "getSubCategories":getSubCategories($conn);break;
        case "deleteSubHead":deleteSubHead($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% HEAD SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$lmcid  = ($_POST['lmcid'] == 'undefined' || $_POST['lmcid'] == '') ? 0 : $_POST['lmcid'];
	$txtCategory  = $_POST['txtCategory'] == 'undefined' ? '' : $_POST['txtCategory'];
	
	$actionid = $lmcid == 0 ? 1 : 2;

	if($txtCategory == ''){throw new Exception("Please Enter 'Category Name'.");}


	$sql = "SELECT * FROM LICENSE_MAIN_CATEGORIES WHERE CATEGORY='$txtCategory' AND LMCID!=$lmcid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [LICENSE_MAIN_CATEGORIES_SP] $actionid,$lmcid,'$txtCategory',$userid";
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
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_LMCID'] = (int)$row['LMCID'];

			$data['success'] = true;
			if(!empty($lmcid))$data['message'] = 'Category successfully updated.';
			else $data['message'] = 'Category successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Category already exists';
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
 // =============== SAVE DATA ==============






/*============ GET CATEGORIES =============*/ 
function getCategories($mysqli){
	try
	{
		$data = array();
		$query = "SELECT LMCID,CATEGORY,
		(SELECT SUBCATEGORY+', ' FROM LICENSE_SUB_CATEGORIES WHERE ISDELETED=0 AND LMCID=L.LMCID FOR XML PATH(''))SUBCATEGORIES
		FROM LICENSE_MAIN_CATEGORIES L WHERE ISDELETED=0 ORDER BY CATEGORY";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['SUBCATEGORIES'] = rtrim($row['SUBCATEGORIES'],', ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Category Not Found.';
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
/*============ GET CATEGORIES =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $LMCID = ($_POST['LMCID'] == 'undefined' || $_POST['LMCID'] == '') ? 0 : $_POST['LMCID'];
			if($LMCID == 0){throw new Exception('LMCID Error.');}
			$delQuery = "EXEC [LICENSE_MAIN_CATEGORIES_SP] 3,$LMCID,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Category successfully deleted.';
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
/* =========== DELETE =========== */ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SUB CATEGORIES SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE SUB HEAD DATA ==================
function saveDataSubHead($mysqli){
	try
	{
	global $userid;
	$data = array();

	$lscid  = ($_POST['lscid'] == 'undefined' || $_POST['lscid'] == '') ? 0 : $_POST['lscid'];
	$lmcid  = ($_POST['lmcid'] == 'undefined' || $_POST['lmcid'] == '') ? 0 : $_POST['lmcid'];
	$txtSubCategory  = $_POST['txtSubCategory'] == 'undefined' ? '' : $_POST['txtSubCategory'];
	
	$actionid = $lscid == 0 ? 1 : 2;

	if($lmcid == 0){throw new Exception("LMCID Not Found.");}
	if($txtSubCategory == ''){throw new Exception("Please Enter 'Sub Category Name'.");}


	$sql = "SELECT * FROM LICENSE_SUB_CATEGORIES WHERE LMCID=$lmcid AND SUBCATEGORY='$txtSubCategory' AND LSCID!=$lscid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [LICENSE_SUB_CATEGORIES_SP] $actionid,$lscid,$lmcid,'$txtSubCategory',$userid";
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
			$data['success'] = true;
			if(!empty($lscid))$data['message'] = 'Sub Category successfully updated.';
			else $data['message'] = 'Sub Category successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Sub Category already exists';
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
 // =============== SAVE SUB HEAD DATA ==============






/*============ GET SUB CATEGORIES =============*/ 
function getSubCategories($mysqli){
	try
	{
		$data = array();
		$lmcid = ($_POST['lmcid'] == 'undefined' || $_POST['lmcid'] == '') ? 0 : $_POST['lmcid'];
		if($lmcid == 0) throw new Exception('LMCID Not Found.');

		$query = "SELECT LSCID,SUBCATEGORY FROM LICENSE_SUB_CATEGORIES WHERE ISDELETED=0 AND LMCID=$lmcid ORDER BY SUBCATEGORY";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Sub Category not found.';
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
/*============ GET SUB CATEGORIES =============*/ 






/* =========== DELETE SUB HEAD =========== */ 
function deleteSubHead($mysqli){
	try{   
			global $userid;
			$data = array();     
            $LSCID = ($_POST['LSCID'] == 'undefined' || $_POST['LSCID'] == '') ? 0 : $_POST['LSCID'];
			if($LSCID == 0){throw new Exception('LSCID Error.');}
			$delQuery = "EXEC [LICENSE_SUB_CATEGORIES_SP] 3,$LSCID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Sub Category successfully deleted.';
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
/* =========== DELETE SUB HEAD =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







