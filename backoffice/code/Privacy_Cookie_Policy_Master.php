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
        case "getPolicy":getPolicy($conn);break;
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
        $pid  = ($_POST['pid'] == 'undefined' || $_POST['pid'] == '') ? 0 : $_POST['pid'];
        $ddlPolicyType  = $_POST['ddlPolicyType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlPolicyType']);
        $txtPolicy  = $_POST['txtPolicy'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPolicy']);
		
		if($ddlPolicyType == '') throw new Exception("Invalid Policy Type..");
		if($txtPolicy == '') throw new Exception("Enter Your Policy.");
		
		if($pid==0){
			$query="INSERT INTO POLICY_MASTER (POLICY,POLICY_TYPE) VALUES('$txtPolicy','$ddlPolicyType')";
		}else{
			$query="UPDATE POLICY_MASTER SET POLICY='$txtPolicy' WHERE POLICY_TYPE='$ddlPolicyType'";
		}
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Policy successfully updated.';
		}
		echo json_encode($data);exit;
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


/*============ Get Policy =============*/ 
 function getPolicy($mysqli){
	try
	{
		$data = array();
		$ddlPolicyType = ($_POST['ddlPolicyType'] == 'undefined' || $_POST['ddlPolicyType'] == '') ? '' : $_POST['ddlPolicyType'];
		$query = "SELECT PID,POLICY FROM POLICY_MASTER WHERE POLICY_TYPE='$ddlPolicyType'";

		$row_count = unique($query);
		if($row_count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'] = $row;
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
function deleteTerm($mysqli){
	try{   
			global $userid;
			$data = array();     
            $termid = ($_POST['termid'] == 'undefined' || $_POST['termid'] == '') ? 0 : $_POST['termid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [TERMS_SP] 3,$termid,'',$userid,0");
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







