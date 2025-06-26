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
		case "getBusinessprocedure":getBusinessprocedure($conn);break;
		case "getRolename":getRolename($conn);break;
		case "getBusinessProcMapping":getBusinessProcMapping($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}

// Get getBusinessprocedure
function getBusinessprocedure($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT BUSINESS_PROCEDURE_ID,BUSINESS_PROCEDURE_NAME FROM MEP_BUSINESS_PROCEDURE_MASTER WHERE ISDELETED=0";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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

// Get getRolename
function getRolename($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT ROLE_ID,ROLE_DESC FROM MEP_ROLES WHERE ISDELETED=0";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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

/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




// SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
	
		$ROLE_BUSINESS_PROCEDURE_ID  = ($_POST['ROLE_BUSINESS_PROCEDURE_ID'] == 'undefined' || $_POST['ROLE_BUSINESS_PROCEDURE_ID'] == '') ? 0 : $_POST['ROLE_BUSINESS_PROCEDURE_ID'];
		$ddlBusinessProcname  = ($_POST['ddlBusinessProcname'] == 'undefined' || $_POST['ddlBusinessProcname'] == '') ? 0 : $_POST['ddlBusinessProcname'];
		$ddlRolename  = ($_POST['ddlRolename'] == 'undefined' || $_POST['ddlRolename'] == '') ? 0 : $_POST['ddlRolename'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
        $actionid = $ROLE_BUSINESS_PROCEDURE_ID == 0 ? 1 : 2;
		
		
		if($ddlBusinessProcname == 0 ) throw new Exception("Please Select Business Procedure Name.");
		
		$querycount = "SELECT * FROM MEP_ROLE_BUSINESS_PROCEDURE WHERE BUSINESS_PROCEDURE_ID=$ddlBusinessProcname AND ROLE_ID=$ddlRolename AND ROLE_BUSINESS_PROCEDURE_ID!=$ROLE_BUSINESS_PROCEDURE_ID AND ISDELETED=0";
		$row_count = unique($querycount);
		if($row_count == 0)
		{
			$query="EXEC [MEP_ROLE_BUSINESS_PROCEDURE_SP] $actionid,$ROLE_BUSINESS_PROCEDURE_ID,$ddlBusinessProcname,$ddlRolename,'$txtRemark',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			//throw new Exception($stmt);
			if($stmt === false)
			{
				//die( print_r( sqlsrv_errors(), true));
						//throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($ROLE_BUSINESS_PROCEDURE_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
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


/*============ GET BUSINESS PROCEDURE MAPPING =============*/ 
function getBusinessProcMapping($mysqli){
	try
	{
		$data = array();

		$query = "SELECT ROLE_BUSINESS_PROCEDURE_ID,BUSINESS_PROCEDURE_ID,BUSINESS_PROCEDURE_NAME,ROLE_ID,ROLE_DESC,REMARKS 
		from MEP_ROLE_BUSINESS_PROCEDURE
		WHERE ISDELETED=0
		ORDER BY ROLE_BUSINESS_PROCEDURE_ID";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
            $ROLE_BUSINESS_PROCEDURE_ID = ($_POST['ROLE_BUSINESS_PROCEDURE_ID'] == 'undefined' || $_POST['ROLE_BUSINESS_PROCEDURE_ID'] == '') ? 0 : $_POST['ROLE_BUSINESS_PROCEDURE_ID'];
			if($ROLE_BUSINESS_PROCEDURE_ID == 0) throw new Exception('Invalid Business Procedure Mapping.');
			$query="EXEC [MEP_ROLE_BUSINESS_PROCEDURE_SP] 3,$ROLE_BUSINESS_PROCEDURE_ID,0,0,'',$userid";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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








