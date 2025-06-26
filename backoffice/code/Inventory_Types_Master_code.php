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
        case "getInventoryType":getInventoryType($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $itid  = ($_POST['itid'] == 'undefined' || $_POST['itid'] == '') ? 0 : $_POST['itid'];
        $txtInventoryType  = $_POST['txtInventoryType'] == 'undefined' ? '' : $_POST['txtInventoryType'];
        $txtDescription  = $_POST['txtDescription'] == 'undefined' ? '' : $_POST['txtDescription'];

		$actionid = $itid == 0 ? 1 : 2;
		
		if($txtInventoryType == ''){throw new Exception("Enter Inventory Type.");}

		$sql = "SELECT * FROM INVENTORY_TYPES WHERE INVTYPE='$txtInventoryType' AND ITID!=$itid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [INVENTORY_TYPES_SP]$actionid,$itid,'$txtInventoryType','$txtDescription',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
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
				if(!empty($itid))$data['message'] = 'Record successfully updated';
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
/* ========== SAVE DATA =========== */




/* ========== GET INVENTORY TYPE =========== */
 function getInventoryType($mysqli){
	try
	{
		$query = "SELECT ITID,INVTYPE,INVDESC FROM INVENTORY_TYPES WHERE ISDELETED=0 ORDER BY INVTYPE";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ITID'] = (int) $row['ITID'];
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
/* ========== GET INVENTORY TYPE =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ITID = ($_POST['ITID'] == 'undefined' || $_POST['ITID'] == '') ? 0 : $_POST['ITID'];  
			if($ITID == 0){throw new Exception('ITID NOT FOUND.');}
			$delQuery = "EXEC [INVENTORY_TYPES_SP] 3,$ITID,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







