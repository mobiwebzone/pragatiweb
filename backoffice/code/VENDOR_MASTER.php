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
        case "getVendorDetails":getVendorDetails($conn);break;
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

        $vendorid  = ($_POST['vendorid'] == 'undefined' || $_POST['vendorid'] == '') ? 0 : $_POST['vendorid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtVenName  = ($_POST['txtVenName'] == 'undefined' || $_POST['txtVenName'] == '') ? '' : $_POST['txtVenName'];
        $txtAddr  = ($_POST['txtAddr'] == 'undefined' || $_POST['txtAddr'] == '') ? '' : $_POST['txtAddr'];
        $txtCont  = ($_POST['txtCont'] == 'undefined' || $_POST['txtCont'] == '') ? '' : $_POST['txtCont'];
        $txtEmail  = ($_POST['txtEmail'] == 'undefined' || $_POST['txtEmail'] == '') ? '' : $_POST['txtEmail'];
		
		$actionid = $vendorid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtVenName == '')throw new Exception("Please Enter Vendor  Name.");
		
		
		$sql = "SELECT * FROM VENDOR_MASTER WHERE LOCID=$ddlLocation AND VENDOR='$txtVenName'   AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [VENDOR_MASTER_SP] $actionid,$vendorid,$ddlLocation,'$txtVenName','$txtAddr','$txtCont','$txtEmail',$userid";
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
				if(!empty($vendorid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Vendor already exists.';
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


/*============ Get Vendor Details =============*/ 
 function getVendorDetails($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT VENDORID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=VM.LOCID)[LOCATION],
		VENDOR,VADDRESS,CONTACTNO,EMAILID FROM VENDOR_MASTER VM where ISDELETED=0 AND LOCID=$ddlLocation ORDER BY LOCATION,VENDOR";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['VENDORID'] = (int) $row['VENDORID'];
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
            $vendorid = ($_POST['vendorid'] == 'undefined' || $_POST['vendorid'] == '') ? 0 : $_POST['vendorid'];  
            $query = "EXEC [VENDOR_MASTER_SP] 3,$vendorid,0,'','','','',$userid";
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







