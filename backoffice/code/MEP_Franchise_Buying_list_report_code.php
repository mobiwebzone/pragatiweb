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
		case "getfLocations":getfLocations($conn);break;
		case "getitemcategory":getitemcategory($conn);break;
		case "getCurrency":getCurrency($conn);break;
        case "getItemvendor":getItemvendor($conn);break;
		case "getItemData":getItemData($conn);break;
		case "getMastertask":getMastertask($conn);break;
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

// Get Module
function getModule($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=12 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

// Get getitemcategory
function getitemcategory($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=16 and isdeleted=0";
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

// Get getCurrency
function getCurrency($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=15 and isdeleted=0";
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

// Get getItemvendor
function getItemvendor($mysqli){
	try
	{
		$data = array();
		
		// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=17 and isdeleted=0";
		$query = "SELECT VENDORID,VENDOR FROM VENDOR_MASTER WHERE ISDELETED=0";
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




// Get getfLocations
function getfLocations($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=12 and isdeleted=0";
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

// Get getMastertask
function getMastertask($mysqli){
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


/* SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		$BUYING_LIST_ID  = ($_POST['BUYING_LIST_ID'] == 'undefined' || $_POST['BUYING_LIST_ID'] == '') ? 0 : $_POST['BUYING_LIST_ID'];
		$ddlfLocation  = ($_POST['ddlfLocation'] == 'undefined' || $_POST['ddlfLocation'] == '') ? 0 : $_POST['ddlfLocation'];
        $ddlItemctgy  = ($_POST['ddlItemctgy'] == 'undefined' || $_POST['ddlItemctgy'] == '') ? 0 : $_POST['ddlItemctgy'];
        $txtItemname  = ($_POST['txtItemname'] == 'undefined' || $_POST['txtItemname'] == '') ? '' : $_POST['txtItemname'];
        $txtItemqnty  = ($_POST['txtItemqnty'] == 'undefined' || $_POST['txtItemqnty'] == '') ? '' : $_POST['txtItemqnty'];
		$txtItemPrice  = ($_POST['txtItemPrice'] == 'undefined' || $_POST['txtItemPrice'] == '') ? '' : $_POST['txtItemPrice'];
		$ddlCurrency  = ($_POST['ddlCurrency'] == 'undefined' || $_POST['ddlCurrency'] == '') ? 0 : $_POST['ddlCurrency'];
		$txtitemmodel  = ($_POST['txtitemmodel'] == 'undefined' || $_POST['txtitemmodel'] == '') ? '' : $_POST['txtitemmodel'];
		$ddlItemvendor  = ($_POST['ddlItemvendor'] == 'undefined' || $_POST['ddlItemvendor'] == '') ? 0 : $_POST['ddlItemvendor'];
		$txtPlink1  = ($_POST['txtPlink1'] == 'undefined' || $_POST['txtPlink1'] == '') ? '' : $_POST['txtPlink1'];
		$txtPlink2  = ($_POST['txtPlink2'] == 'undefined' || $_POST['txtPlink2'] == '') ? '' : $_POST['txtPlink2'];
		$ddlMastertask  = ($_POST['ddlMastertask'] == 'undefined' || $_POST['ddlMastertask'] == '') ? 0 : $_POST['ddlMastertask'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $BUYING_LIST_ID == 0 ? 1 : 2;
		
		
		if($ddlItemctgy == 0)
		{throw new Exception("Please Select Item Category.");}
	
		
		// $sql = "SELECT * FROM MEP_FRANCHISE_BUYING_lIST WHERE FRANCHISE_NAME=$ddlfranchiseID AND TASK_DESC='$txtTask' AND TASK_ID!=$TASK_ID AND ISDELETED=0";
		// $data['$query'] = $sql;
		// echo json_encode($data);exit;
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [FRANCHISE_BUYING_LIST_SP] $actionid,$BUYING_LIST_ID,$ddlfLocation,$ddlItemctgy,'$txtItemname',$txtItemqnty,$txtItemPrice,$ddlCurrency,
			'$txtitemmodel',$ddlItemvendor,'$txtPlink1','$txtPlink2',$userid,'$txtRemark', $ddlMastertask";
			$stmt=sqlsrv_query($mysqli, $query);
			// throw new Exception($query);
			
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
				if(!empty($BUYING_LIST_ID))$data['message'] = 'Record successfully updated';
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
*/

/*============ GET ITEM DATA =============*/ 
function getItemData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT BUYING_LIST_ID,FRANCHISE_LOCATION_ID,FRANCHISE_lOCATION,ITEM_CATG_ID,ITEM_CATG_DESC,ITEM,ITEM_QUANTITY,ITEM_UNIT_PRICE,PRICE_CURRENCY_ID,
		PRICE_CURRENCY,ITEM_MODEL,ITEM_VENDOR_ID,ITEM_VENDOR,PURCHASE_LINK1,PURCHASE_LINK2,MASTER_TASK_CD,MASTER_TASK_DESC,REMARKS 
		from MEP_FRANCHISE_BUYING_lIST
		WHERE ISDELETED=0
		ORDER BY BUYING_LIST_ID";

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





/* =========== Delete ===========  
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BUYING_LIST_ID = ($_POST['BUYING_LIST_ID'] == 'undefined' || $_POST['BUYING_LIST_ID'] == '') ? 0 : $_POST['BUYING_LIST_ID'];
			if($BUYING_LIST_ID == 0) throw new Exception('Invalid Item ID.');
			$query="EXEC [FRANCHISE_BUYING_LIST_SP] 3,$BUYING_LIST_ID,0,0,'',0,0,0,'',0,'','',$userid,'',0";
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


*/ 

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}








