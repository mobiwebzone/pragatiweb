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
        case "SetApproveRevoke":SetApproveRevoke($conn);break;
        case "getProduct":getProduct($conn);break;
        case "getPlans":getPlans($conn);break;
        case "getStudentData":getStudentData($conn);break;
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


 function SetApproveRevoke($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $REGID  = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$FOR=$_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		
		if($REGID == 0)
		{throw new Exception("REGID ERROR");}

		if($FOR == 'APPROVAL')
		{
			$query="UPDATE REGISTRATIONS SET APPROVED=1 WHERE REGID=$REGID";

		}else{
			$query="UPDATE REGISTRATIONS SET APPROVED=0 WHERE REGID=$REGID";
		}
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
				if($FOR == 'APPROVAL')$data['message'] = 'Student successfully approve.';
				else $data['message'] = 'Student successfully revoke.';
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




/*============ Get Plans =============*/ 
 function getPlans($mysqli){
	try
	{
		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PLANID'] = (int) $row['PLANID'];
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


/*============ GET STUDENT FOR APPROVAL/APPROVED =============*/ 
 function getStudentData($mysqli){
	try
	{
		$data = array();

		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$FOR=$_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];

		if($FOR == 'APPROVAL'){
			$query = "EXEC [GET_STUDENTS_FOR_APPROVAL]$ddlLocation,$ddlPlan";
		}
		else{
			$query = "EXEC [GET_STUDENTS_APPROVED]$ddlLocation,$ddlPlan";
		}
		// $data['query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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







