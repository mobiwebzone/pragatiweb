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
		case "login":login($conn);break;
        case "saveProduct":saveProduct($conn);break;
        case "getProduct":getProduct($conn);break;
        case "deleteProduct":deleteProduct($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveProduct($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $productid  = $_POST['product_id'] == 'undefined' ? 0 : $_POST['product_id'];
        $txtProduct  = $_POST['txtProduct'] == 'undefined' ? '' : $_POST['txtProduct'];
        $txtProductDesc  = $_POST['txtProductDesc'] == 'undefined' ? '' : $_POST['txtProductDesc'];
		$txtDisplayOrder = ($_POST['txtDisplayOrder'] == 'undefined' || $_POST['txtDisplayOrder'] == '') ? 0 : $_POST['txtDisplayOrder'];
		$txtDisplayColor = $_POST['txtDisplayColor'] == 'undefined' ? 0 : $_POST['txtDisplayColor'];
		
		$actionid = $productid == 0 ? 1 : 2;

		if($txtProduct == '')
		{throw new Exception("Please Enter Product Name.");}

		$sql = "SELECT * FROM PRODUCTS WHERE PRODUCT='$txtProduct' AND PRODUCT_ID!=$productid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [PRODUCTS_SP] $actionid,$productid,'$txtProduct','$txtProductDesc',$txtDisplayOrder,'$txtDisplayColor',$userid";
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
				if(!empty($productid))$data['message'] = 'Record successfully updated';
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
function deleteProduct($mysqli){
	try{   
			global $userid;
			$data = array();     
            $productid = ($_POST['productid'] == 'undefined' || $_POST['productid'] == '') ? 0 : $_POST['productid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [PRODUCTS_SP] 3,$productid,'','',0,'',$userid");
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







