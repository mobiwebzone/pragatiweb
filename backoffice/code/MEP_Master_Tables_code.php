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
		case "getmastertable":getmastertable($conn);break;
		case "getTableData":getTableData($conn);break;
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

 // Get getmastertable
 function getmastertable($mysqli){
	 try
	 {
		 $data = array();
		 
		 $query = "SELECT OBJMASTER_ID,OBJMASTER_DESC FROM MEP_OBJECT_MASTER WHERE ISDELETED=0 AND OBJTYPEID=6";
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
 


// SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
	
		$MASTER_TABLES_ID  = ($_POST['MASTER_TABLES_ID'] == 'undefined' || $_POST['MASTER_TABLES_ID'] == '') ? 0 : $_POST['MASTER_TABLES_ID'];
		$ddlTBname  = ($_POST['ddlTBname'] == 'undefined' || $_POST['ddlTBname'] == '') ? 0 : $_POST['ddlTBname'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
        $actionid = $MASTER_TABLES_ID == 0 ? 1 : 2;
		
		
		if($ddlTBname == '') throw new Exception("Please Select Table.");

		$querycount = "SELECT * FROM MEP_MASTER_TABLES WHERE TABLE_NAME='$ddlTBname' AND MASTER_TABLES_ID!=$MASTER_TABLES_ID AND ISDELETED=0";
		$row_count = unique($querycount);
		 if($row_count == 0)
		 {
			$query="EXEC [MEP_MASTER_TABLES_SP] $actionid,$MASTER_TABLES_ID,$ddlTBname,$userid,'$txtRemark'";
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
				if(!empty($MASTER_TABLES_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
		 }
		 else
		 {
			
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
function getTableData($mysqli){
	try
	{
		$data = array();

		$query = " SELECT MASTER_TABLES_ID,TABLE_ID,TABLE_NAME,REMARKS FROM MEP_MASTER_TABLES WHERE ISDELETED=0
		ORDER BY MASTER_TABLES_ID";

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
            $MASTER_TABLES_ID = ($_POST['MASTER_TABLES_ID'] == 'undefined' || $_POST['MASTER_TABLES_ID'] == '') ? 0 : $_POST['MASTER_TABLES_ID'];
			if($MASTER_TABLES_ID == 0) throw new Exception('Invalid Role.');
			$query="EXEC [MEP_MASTER_TABLES_SP] 3,$MASTER_TABLES_ID,0,$userid,''";
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






