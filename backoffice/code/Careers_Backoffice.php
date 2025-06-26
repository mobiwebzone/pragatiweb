<?php
session_start();
require_once 'connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getCareers":getCareers($conn);break;        
        case "deleteCareers":deleteCareers($conn);break;        
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ Get Countries =============*/ 
 function getCareers($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT CONVERT(VARCHAR,INSERTDATE,105)[DATE],CID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=C.LOCID AND ISDELETED=0)LOCATION
				,NAME,EMAIL,PHONE,MADDRESS,PRODUCT
				,EDUBACKGROUND,WORKEXPERIENCE,ADDINFO FROM CAREERS C WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY CID DESC";

		if($_SESSION['USER_LOCID'] != '1'){
			$query .=" AND LOCID=".$_SESSION['USER_LOCID']."";
		}

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CID'] = (int) $row['CID'];
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
function deleteCareers($mysqli){
	try{   
			global $userid;
			$data = array();     
            $cid = $_POST['cid'] == 'undefined' ? 0 : $_POST['cid'];  
            $del="UPDATE CAREERS SET ISDELETED=1, DELETEDATE=GETDATE(), DELETEID=$userid WHERE CID=$cid";
			$stmt=sqlsrv_query($mysqli, $del);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
                $data['del'] = $del;
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







