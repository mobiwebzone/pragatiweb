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
		case "getRolesData":getRolesData($conn);break;
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




// SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
	
		$ROLE_ID  = ($_POST['ROLE_ID'] == 'undefined' || $_POST['ROLE_ID'] == '') ? 0 : $_POST['ROLE_ID'];
		$txtRoles  = ($_POST['txtRoles'] == 'undefined' || $_POST['txtRoles'] == '') ? '' : $_POST['txtRoles'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
        $actionid = $ROLE_ID == 0 ? 1 : 2;
		
		
		if($txtRoles == '') throw new Exception("Please Enter Role.");

		$querycount = "SELECT * FROM MEP_ROLES WHERE ROLE_DESC='$txtRoles' AND ROLE_ID!=$ROLE_ID AND ISDELETED=0";
		// throw new Exception($querycount);
		$row_count = unique($querycount);
		 if($row_count == 0)
		 {
			$query="EXEC [MEP_ROLES_SP] $actionid,$ROLE_ID,'$txtRoles',$userid,'$txtRemark'";
			$stmt=sqlsrv_query($mysqli, $query);
			
			// throw new Exception($stmt);
			if($stmt === false)
			{
				die( print_r( sqlsrv_errors(), true));
						throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($ROLE_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
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


/*============ GET MAIN MENU DATA =============*/ 
function getRolesData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT ROLE_ID,ROLE_DESC,REMARKS 
		from MEP_ROLES
		WHERE ISDELETED=0
		ORDER BY ROLE_ID";

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
            $ROLE_ID = ($_POST['ROLE_ID'] == 'undefined' || $_POST['ROLE_ID'] == '') ? 0 : $_POST['ROLE_ID'];
			if($ROLE_ID == 0) throw new Exception('Invalid Role.');
			$query="EXEC [MEP_ROLES_SP] 3,$ROLE_ID,'',$userid,''";
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






