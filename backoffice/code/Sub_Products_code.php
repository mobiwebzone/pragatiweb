<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['USERID']))
{$userid=$_SESSION['USERID'];}
else
{$userid=0;}

if($_SESSION['ROLE'] != 'SUPERADMIN')
{
	if(!empty($_SESSION['CLID']))
	{$userclid=$_SESSION['CLID'];}
	else
	{$userclid=0;}
}
else
{
	$userclid=0;
}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "saveSubProduct":saveSubProduct($conn);break;
        case "getSubProduct":getSubProduct($conn);break;
        case "getProduct":getProduct($conn);break;
        case "deleteSubProduct":deleteSubProduct($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveSubProduct($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $sub_productid  = ($_POST['sub_productid'] == 'undefined' || $_POST['sub_productid'] == '') ? 0 : $_POST['sub_productid'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
        $txtSubProduct  = $_POST['txtSubProduct'] == 'undefined' ? '' : $_POST['txtSubProduct'];
        $txtSubProductDesc  = $_POST['txtSubProductDesc'] == 'undefined' ? '' : $_POST['txtSubProductDesc'];
		
		$actionid = $sub_productid == 0 ? 1 : 2;

		if($ddlProduct == 0)
		{throw new Exception("Please Enter Product Name.");}

		$sql = "SELECT * FROM SUB_PRODUCTS WHERE PRODUCT_ID=$ddlProduct AND SUB_PRODUCT='$txtSubProduct' AND SUB_PRODUCT_ID!=$sub_productid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [SUB_PRODUCTS_SP] $actionid,$sub_productid,$ddlProduct,'$txtSubProduct','$txtSubProductDesc',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($sub_productid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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


/*============ Get getSub_Product =============*/ 
 function getSubProduct($mysqli){
	try
	{
		$query = "SELECT SUB_PRODUCT_ID,PRODUCT_ID,
				(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=SP.PRODUCT_ID)PRODUCT,
				SUB_PRODUCT,SUB_PRODUCT_DESC
				FROM SUB_PRODUCTS SP WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['SUB_PRODUCT_ID'] = (int) $row['SUB_PRODUCT_ID'];
			$data['data'][] = $row;
		}
		$data['success'] = true;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}


/*============ Get getProduct =============*/ 
 function getProduct($mysqli){
	try
	{
		$query = "SELECT PRODUCT_ID,PRODUCT,PRODUCT_DESC,DISPLAY_ORDER,DISPLAY_COLOR FROM PRODUCTS WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PRODUCT_ID'] = (int) $row['PRODUCT_ID'];
			$row['DISPLAY_ORDER'] = (int) $row['DISPLAY_ORDER'];
			$data['data'][] = $row;
		}
		$data['success'] = true;
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
function deleteSubProduct($mysqli){
	try{   
			global $userid;
			$data = array();     
            $sub_productid = ($_POST['sub_productid'] == 'undefined' || $_POST['sub_productid'] == '') ? 0 : $_POST['sub_productid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [SUB_PRODUCTS_SP] 3,$sub_productid,0,'','',$userid");
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



function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
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







