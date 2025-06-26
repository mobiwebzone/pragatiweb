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
		case "getScript":getScript($conn);break;
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
        $SCRIPTID  = ($_POST['SCRIPTID'] == 'undefined' || $_POST['SCRIPTID'] == '') ? 0 : $_POST['SCRIPTID'];
    
        $txtScript  = ($_POST['txtScript'] == 'undefined' || $_POST['txtScript'] == '') ? '' : $_POST['txtScript'];
		
		$actionid = $SCRIPTID == 0 ? 1 : 2;
		

		if($txtScript == '')throw new Exception("Please Enter Script.");
		
		$sql = "SELECT * FROM SCRIPT_MASTER WHERE  SCRIPT_NAME='$txtScript' AND SCRIPTID!=$SCRIPTID AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [SCRIPT_MASTER_SP] $actionid,$SCRIPTID,'$txtScript',$userid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			
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
				if(!empty($SCRIPTID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Script already exists.';
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


/*============ GET SCRIPT =============*/ 
function getScript($mysqli){
	try
	{
		$data = array();
		$query = "SELECT SCRIPTID ,SCRIPT_NAME FROM  SCRIPT_MASTER  WHERE ISDELETED=0";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SCRIPTID'] = (int) $row['SCRIPTID'];
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
/*============ GET SCRIPT =============*/ 


/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCRIPTID = ($_POST['SCRIPTID'] == 'undefined' || $_POST['SCRIPTID'] == '') ? 0 : $_POST['SCRIPTID'];  
			$query = "EXEC [SCRIPT_MASTER_SP] 3,$SCRIPTID,'',$userid";
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
/* =========== DELETE =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







