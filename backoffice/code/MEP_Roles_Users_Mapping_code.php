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
		case "getUser":getUser($conn);break;
		case "getRolename":getRolename($conn);break;
		case "getRoleUserMapping":getRoleUserMapping($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}

// Get getUser
function getUser($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT UID,FIRSTNAME,LASTNAME FROM USERS WHERE ISDELETED=0";
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
	
		$USER_ROLES_ID  = ($_POST['USER_ROLES_ID'] == 'undefined' || $_POST['USER_ROLES_ID'] == '') ? 0 : $_POST['USER_ROLES_ID'];
		$ddluser  = ($_POST['ddluser'] == 'undefined' || $_POST['ddluser'] == '') ? 0 : $_POST['ddluser'];
		$ddlRolename  = ($_POST['ddlRolename'] == 'undefined' || $_POST['ddlRolename'] == '') ? 0 : $_POST['ddlRolename'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
        $actionid = $USER_ROLES_ID == 0 ? 1 : 2;
		
		
		if($ddlRolename == 0 ) throw new Exception("Please Roles.");
		
		$querycount = "SELECT * FROM MEP_USER_ROLES WHERE USER_ID=$ddluser AND ROLE_ID=$ddlRolename AND USER_ROLES_ID!=$USER_ROLES_ID AND ISDELETED=0";
		$row_count = unique($querycount);
		
		if($row_count == 0)
		{
			$query="EXEC [MEP_USER_ROLES_SP] $actionid,$USER_ROLES_ID,$ddluser,$ddlRolename,'$txtRemark',$userid";
			// throw new Exception($query);
			$stmt=sqlsrv_query($mysqli, $query);
			
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
				if(!empty($USER_ROLES_ID))$data['message'] = 'Record successfully updated';
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
function getRoleUserMapping($mysqli){
	try
	{
		$data = array();

		$query = "SELECT USER_ROLES_ID,USER_ID,USER_NAME,ROLE_ID,ROLE_DESC,REMARKS 
		from MEP_USER_ROLES
		WHERE ISDELETED=0
		ORDER BY USER_ROLES_ID";

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
            $USER_ROLES_ID = ($_POST['USER_ROLES_ID'] == 'undefined' || $_POST['USER_ROLES_ID'] == '') ? 0 : $_POST['USER_ROLES_ID'];
			if($USER_ROLES_ID == 0) throw new Exception('Invalid Business Procedure Mapping.');
			$query="EXEC [MEP_USER_ROLES_SP] 3,$USER_ROLES_ID,0,0,'',$userid";
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








