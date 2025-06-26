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
        case "getPublisherManufacture":getPublisherManufacture($conn);break;
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
		$data = array();
        global $userid;
		
		$data = array();
        $pubmainid  = ($_POST['pubmainid'] == 'undefined' || $_POST['pubmainid'] == '') ? 0 : $_POST['pubmainid'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtPM  = ($_POST['txtPM'] == 'undefined' || $_POST['txtPM'] == '') ? '' : $_POST['txtPM'];
        $txtDesc  = ($_POST['txtDesc'] == 'undefined' || $_POST['txtDesc'] == '') ? '' : $_POST['txtDesc'];
		
		$actionid = $pubmainid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtPM == '')throw new Exception("Please Enter Publisher Manufacturer  Name.");
		
		
		$sql = "SELECT * FROM PUBLISHER_MANUFACTURER_MASTER WHERE LOCID=$ddlLocation AND PUBLISHER_MANUFACTURER='$txtPM' AND PUBMANID!=$pubmainid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [PUBLISHER_MANUFACTURER_MASTER_SP] $actionid,$pubmainid,$ddlLocation,'$txtPM','$txtDesc',$userid";
			// $data['$query'] = $query;
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
				if(!empty($pubmainid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Pulisher/Manufacturer already exists.';
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


/*============ Get Publisher/Manufacture =============*/ 
 function getPublisherManufacture($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT PUBMANID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PM.LOCID)[LOCATION],
				PUBLISHER_MANUFACTURER,DESCR FROM PUBLISHER_MANUFACTURER_MASTER PM WHERE ISDELETED=0 AND LOCID=$ddlLocation 
				ORDER BY LOCATION,PUBLISHER_MANUFACTURER";
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PUBMANID'] = (int) $row['PUBMANID'];
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
            $pubmainid = ($_POST['pubmainid'] == 'undefined' || $_POST['pubmainid'] == '') ? 0 : $_POST['pubmainid'];  
            $query = "EXEC [PUBLISHER_MANUFACTURER_MASTER_SP] 3,$pubmainid,0,'','',$userid";
            $data['$query']= $query;
			$stmt=sqlsrv_query($mysqli,$query );
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







