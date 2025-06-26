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
        case "saveResourceSEQ":saveResourceSEQ($conn);break;
        case "getCategory":getCategory($conn);break;
        case "getCategoryByUnderId":getCategoryByUnderId($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 function saveResourceSEQ($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $id  = ($_POST['id'] == 'undefined' || $_POST['id'] == '') ? 0 : $_POST['id'];
        $ALLCategory  = ($_POST['ALLCategory'] == 'undefined' || $_POST['ALLCategory'] == '') ? 0 : $_POST['ALLCategory'];
		$ALLCategory = explode(",",$ALLCategory);
        $txtSEQNo  = ($_POST['txtSEQNo'] == 'undefined' || $_POST['txtSEQNo'] == '') ? 0 : $_POST['txtSEQNo'];
		$txtSEQNo = explode(",",$txtSEQNo);

		// $data['txtSEQNo'] =  $ALLCategory;
		// echo json_encode($data);exit;
		for($i=0;$i<count($ALLCategory);$i++){
			$CTID = $ALLCategory[$i];
			$SEQ = $txtSEQNo[$i];

			$query="UPDATE FREE_RESOURCES SET SEQNO=$SEQ WHERE ISDELETED=0 AND ID=$CTID";
			$stmt=sqlsrv_query($mysqli, $query);
		}
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
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


 /*============ Get Category By UnderId =============*/ 
function getCategoryByUnderId($mysqli){
	try
	{
		$ddlCategory = ($_POST['ddlCategory'] == 'undefined' || $_POST['ddlCategory'] == '') ? 0 : $_POST['ddlCategory'];
		$query = "SELECT ID ,RESOURCE_CATEGORY_TEXT,RESOURCE_CATEGORY,SEQNO FROM FREE_RESOURCES WHERE ISDELETED=0";

		if($ddlCategory > 0){
			$query .=" AND UNDER_ID=$ddlCategory";
		}else{
			$query .=" AND UNDER_ID=0";
		}

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
			$data['data'][] = $row;
			$data['ID'][] = (int) $row['ID'];
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



/*============ Get Category =============*/ 
function getCategory($mysqli){
	try
	{
		$query = "SELECT ID,RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category'";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







