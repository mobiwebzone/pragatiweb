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
        case "saveData":saveData($conn);break;
        case "getClassTypes":getClassTypes($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$classtypeid = ($_POST['classtypeid'] == 'undefined' || $_POST['classtypeid'] == '') ? 0 : $_POST['classtypeid'];
		$txtClassType = $_POST['txtClassType'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtClassType']);

		$actionid = $classtypeid == 0 ? 1 : 2;

		if($txtClassType == '') throw new Exception("Enter Class Type.");


		$sql = "SELECT * FROM CLASS_TYPE_MASTER WHERE CLASS_TYPE='$txtClassType' AND CLASSTYPEID!=$classtypeid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [CLASS_TYPE_MASTER_SP] $actionid,$classtypeid,'$txtClassType',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($classtypeid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET CLASS TYPES =========== */
 function getClassTypes($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CLASSTYPEID,CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE ISDELETED=0 ORDER BY CLASS_TYPE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLASSTYPEID'] = (int) $row['CLASSTYPEID'];
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
/* ========== GET CLASS TYPES =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CLASSTYPEID = ($_POST['CLASSTYPEID'] == 'undefined' || $_POST['CLASSTYPEID'] == '') ? 0 : $_POST['CLASSTYPEID'];  
			if($CLASSTYPEID == 0)throw new Exception('CLASSTYPEID Not Found.');
			$delQuery = "EXEC [CLASS_TYPE_MASTER_SP] 3,$CLASSTYPEID,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







