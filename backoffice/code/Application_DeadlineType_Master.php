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
        case "getDeadlineTypes":getDeadlineTypes($conn);break;
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
		
		$deadlinetypeid = ($_POST['deadlinetypeid'] == 'undefined' || $_POST['deadlinetypeid'] == '') ? 0 : $_POST['deadlinetypeid'];
		$txtDeadlineType = $_POST['txtDeadlineType'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDeadlineType']);
		$txtDesc = $_POST['txtDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDesc']);
		$ddlBinding = $_POST['ddlBinding'] == 'undefined' ? '' : $_POST['ddlBinding'];
		$ddlNO_OF_CLG = $_POST['ddlNO_OF_CLG'] == 'undefined' ? '' : $_POST['ddlNO_OF_CLG'];
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);

		$actionid = $deadlinetypeid == 0 ? 1 : 2;

		if($txtDeadlineType == '') throw new Exception("Enter Deadline Type.");


		$sql = "SELECT * FROM DEADLINE_TYPES WHERE DEADLINETYPE='$txtDeadlineType' AND ISBINDING='$ddlBinding' AND 
		NOOFCOLLEGES='$ddlNO_OF_CLG' AND DEADLINETYPEID!=$deadlinetypeid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [DEADLINE_TYPES_SP] $actionid,$deadlinetypeid,'$txtDeadlineType','$txtDesc','$ddlBinding',
			'$ddlNO_OF_CLG','$txtComments',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($deadlinetypeid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET DEADLINE TYPE =========== */
 function getDeadlineTypes($mysqli){
	try
	{
		$data = array();
		$query = "SELECT DEADLINETYPEID,DEADLINETYPE,DEADLINEDESC,ISBINDING,NOOFCOLLEGES,COMMENTS
		FROM DEADLINE_TYPES WHERE ISDELETED=0 ORDER BY DEADLINETYPE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['DEADLINETYPEID'] = (int) $row['DEADLINETYPEID'];
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
/* ========== GET DEADLINE TYPE =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $DEADLINETYPEID = ($_POST['DEADLINETYPEID'] == 'undefined' || $_POST['DEADLINETYPEID'] == '') ? 0 : $_POST['DEADLINETYPEID'];  
			if($DEADLINETYPEID == 0)throw new Exception('DEADLINETYPEID Not Found.');
			$delQuery = "EXEC [DEADLINE_TYPES_SP] 3,$DEADLINETYPEID,'','','','','',$userid";
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







