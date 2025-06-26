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
		case "getMainMenuData":getMainMenuData($conn);break;
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
	
		$BUSINESS_PROCEDURE_ID  = ($_POST['BUSINESS_PROCEDURE_ID'] == 'undefined' || $_POST['BUSINESS_PROCEDURE_ID'] == '') ? 0 : $_POST['BUSINESS_PROCEDURE_ID'];
		$txtMainmenu  = ($_POST['txtMainmenu'] == 'undefined' || $_POST['txtMainmenu'] == '') ? '' : $_POST['txtMainmenu'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
        $actionid = $BUSINESS_PROCEDURE_ID == 0 ? 1 : 2;
		
		
		if($txtMainmenu == '') throw new Exception("Please Main Menu Name.");
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [MEP_BUSINESS_PROCEDURE_MASTER_SP] $actionid,$BUSINESS_PROCEDURE_ID,'$txtMainmenu',$userid,'$txtRemark'";
			$stmt=sqlsrv_query($mysqli, $query);
			
			//throw new Exception($stmt);
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
				if(!empty($BUSINESS_PROCEDURE_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists.';
		// 	echo json_encode($data);exit;
		// }

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
function getMainMenuData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT BUSINESS_PROCEDURE_ID
						 ,BUSINESS_PROCEDURE_NAME
						 ,REMARKS 
		from MEP_BUSINESS_PROCEDURE_MASTER
		WHERE ISDELETED=0
		ORDER BY BUSINESS_PROCEDURE_NAME";

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
            $BUSINESS_PROCEDURE_ID = ($_POST['BUSINESS_PROCEDURE_ID'] == 'undefined' || $_POST['BUSINESS_PROCEDURE_ID'] == '') ? 0 : $_POST['BUSINESS_PROCEDURE_ID'];
			if($BUSINESS_PROCEDURE_ID == 0) throw new Exception('Invalid Main Menu.');
			$query="EXEC [MEP_BUSINESS_PROCEDURE_MASTER_SP] 3,$BUSINESS_PROCEDURE_ID,'',$userid,''";
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








