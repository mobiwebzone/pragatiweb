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
        case "getActivities":getActivities($conn);break;
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

		$activityid = ($_POST['activityid'] == 'undefined' || $_POST['activityid'] == '') ? 0 : $_POST['activityid'];
		$txtActivity = $_POST['txtActivity'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtActivity']);
		$txtActivityShortDesc = $_POST['txtActivityShortDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtActivityShortDesc']);
		$txtActivityLongDesc = $_POST['txtActivityLongDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtActivityLongDesc']);

		$actionid = $activityid == 0 ? 1 : 2;

		if($txtActivity == '') throw new Exception("Enter Activity.");


		$sql = "SELECT * FROM ACTIVITIES_MASTER WHERE ACTIVITY='$txtActivity' AND ACTIVITYID!=$activityid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ACTIVITIES_MASTER_SP] $actionid,$activityid,'$txtActivity','$txtActivityShortDesc','$txtActivityLongDesc',$userid";
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
				if(!empty($activityid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET ACTIVITIES =========== */
 function getActivities($mysqli){
	try
	{
		$data = array();
		$query = "SELECT ACTIVITYID,ACTIVITY,ACTIVITY_SHORT_DESC,ACTIVITY_LONG_DESC FROM ACTIVITIES_MASTER WHERE ISDELETED=0 ORDER BY ACTIVITY";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ACTIVITYID'] = (int) $row['ACTIVITYID'];
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
/* ========== GET ACTIVITIES =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ACTIVITYID = ($_POST['ACTIVITYID'] == 'undefined' || $_POST['ACTIVITYID'] == '') ? 0 : $_POST['ACTIVITYID'];  
			if($ACTIVITYID == 0)throw new Exception('ACTIVITYID Not Found.');
			$delQuery = "EXEC [ACTIVITIES_MASTER_SP] 3,$ACTIVITYID,'','','',$userid";
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







