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
        case "saveData":saveData($conn);break;
        case "getProductByTest":getProductByTest($conn);break;
        case "getAllSelectedProduct":getAllSelectedProduct($conn);break;
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



// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        // $secid  = $_POST['secid'] == 'undefined' ? 0 : $_POST['secid'];
        $ddlTest  = $_POST['ddlTest'] == 'undefined' ? '' : $_POST['ddlTest'];
        $chkProduct  = (empty($_POST['chkProduct']) || $_POST['chkProduct'] == 'undefined') ? '' : $_POST['chkProduct'];
		if($chkProduct == ''){throw new Exception ('Select Product First.');}
		$chkProduct = explode(',',$chkProduct);
		// $data['chkProducts']=$chkProduct;
		// echo json_encode($data);exit;
		

		if($ddlTest == 0)
		{throw new Exception("Please Select Test Name First.");}

		$delOldTest = "DELETE FROM TESTS_TO_PRODUCTS WHERE TESTID=$ddlTest";
		$stmtDOT=sqlsrv_query($mysqli, $delOldTest);

		for($i=0; $i<COUNT($chkProduct); $i++){
			$Product = $chkProduct[$i];

			if($Product > 0){
				$query="INSERT INTO TESTS_TO_PRODUCTS (PRODUCTID,TESTID) VALUES($Product,$ddlTest)";
				$stmt=sqlsrv_query($mysqli, $query);
			}

		}
			
		if($stmt === false)
		{
			$data['success'] = true;
			$data['query'] = $query;
		}
		else
		{
			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Record successfully inserted.';
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





/*============ GET PRODUCT BY TEST =============*/ 
 function getProductByTest($mysqli){
	try
	{
		$data = array();
		$ddlTest = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];

		$PRODUCTID = array();
		// $query = "SELECT TPID,CONVERT(VARCHAR, PRODUCTID) PRODUCTID FROM TESTS_TO_PRODUCTS WHERE TESTID=$ddlTest";
		$query = "SELECT 
				CASE WHEN PRODUCT_ID=(SELECT PRODUCTID FROM TESTS_TO_PRODUCTS WHERE ISDELETED=0 AND PRODUCTID=P.PRODUCT_ID AND TESTID=$ddlTest)
					THEN CONVERT(VARCHAR,PRODUCT_ID) ELSE '0' 
				END PRODUCTID
				FROM PRODUCTS P WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);

		$ProductSUM = 0;
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
			$ProductSUM = $ProductSUM + (int) $row['PRODUCTID'];
			array_push($PRODUCTID, $row['PRODUCTID']);
		}
		$data['PRODUCTID'] = $PRODUCTID;
		$data['ProductSUM'] = $ProductSUM;
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
/*============ GET PRODUCT BY TEST =============*/ 





/*============ GET ALL SELECTED PRODUCT =============*/ 
 function getAllSelectedProduct($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TESTID,(SELECT TESTDESC + ' (' + CONVERT(VARCHAR,TESTYEAR) + ')' FROM TEST_MASTER WHERE ISDELETED=0 AND TESTID=TTP.TESTID)TEST,
		(SELECT (SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=TTP2.PRODUCTID AND ISDELETED=0) + ' , ' FROM TESTS_TO_PRODUCTS TTP2 WHERE TESTID=TTP.TESTID AND ISDELETED=0 FOR XML PATH('')) PRODUCTS
		FROM TESTS_TO_PRODUCTS TTP WHERE ISDELETED=0 GROUP BY TESTID ORDER BY TESTID";

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PRODUCTS'] = rtrim($row['PRODUCTS'],' , ');
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
/*============ GET ALL SELECTED PRODUCT =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
			if($testid == 0){throw new Exception('Testid Not Found.');}

			$stmt=sqlsrv_query($mysqli, "UPDATE TESTS_TO_PRODUCTS SET ISDELETED=1 WHERE TESTID = $testid");
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







