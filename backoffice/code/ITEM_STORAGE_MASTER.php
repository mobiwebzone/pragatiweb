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
		case "getItemstoragemaster":getItemstoragemaster($conn);break;
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


 function save($mysqli){
     try
     {
        global $userid;
		
		$data = array();
        $stmid  = ($_POST['stmid'] == 'undefined' || $_POST['stmid'] == '') ? 0 : $_POST['stmid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtStorage  = ($_POST['txtStorage'] == 'undefined' || $_POST['txtStorage'] == '') ? '' : $_POST['txtStorage'];
        $txtDesc  = ($_POST['txtDesc'] == 'undefined' || $_POST['txtDesc'] == '') ? '' : $_POST['txtDesc'];
        $actionid = $stmid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtStorage == '')throw new Exception("Please Enter Storage  Item Name.");
	
		
		$sql = "SELECT * FROM ITEM_STORAGE_MASTER WHERE LOCID=$ddlLocation AND STORAGE='$txtStorage' AND STMID !=$stmid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [ITEM_STORAGE_MASTER_SP] $actionid,$stmid,$ddlLocation,'$txtStorage','$txtDesc',$userid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
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
				if(!empty($itemid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Item Storage already exists.';
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


/*============ Get Item Storage Master =============*/ 
function getItemstoragemaster($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT STMID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=ISM.LOCID)[LOCATION],
		STORAGE,STORAGEDESC  FROM ITEM_STORAGE_MASTER ISM WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY LOCATION,STMID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STMID'] = (int) $row['STMID'];
				
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
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $stmid = ($_POST['stmid'] == 'undefined' || $_POST['stmid'] == '') ? 0 : $_POST['stmid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [ITEM_STORAGE_MASTER_SP] 3,$stmid,0,'' ,'',$userid");
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







