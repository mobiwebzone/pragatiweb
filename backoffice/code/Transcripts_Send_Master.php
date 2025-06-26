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
        case "getTranscriptSends":getTranscriptSends($conn);break;
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
		
		$tsendid = ($_POST['tsendid'] == 'undefined' || $_POST['tsendid'] == '') ? 0 : $_POST['tsendid'];
		$txtSendMethod = $_POST['txtSendMethod'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSendMethod']);
		$txtDesc = $_POST['txtDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDesc']);
		$txtLink = $_POST['txtLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLink']);
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);

		$actionid = $tsendid == 0 ? 1 : 2;

		if($txtSendMethod == '') throw new Exception("Enter Send Method.");


		// $sql = "SELECT * FROM TRANSCRIPT_SENDING_MASTER WHERE TSENDMETHOD='$txtSendMethod' AND ISBINDING='$ddlBinding' AND 
		// NOOFCOLLEGES='$ddlNO_OF_CLG' AND DEADLINETYPEID!=$tsendid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$query="EXEC [TRANSCRIPT_SENDING_MASTER_SP] $actionid,$tsendid,'$txtSendMethod','$txtDesc','$txtLink','$txtComments',$userid";
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
				if(!empty($tsendid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET TRANSCRIPTS SEND =========== */
 function getTranscriptSends($mysqli){
	try
	{
		$data = array();
		$query = "SELECT TSENDID,TSENDMETHOD,TSENDDESC,TSENDLINK,COMMENTS
		FROM TRANSCRIPT_SENDING_MASTER WHERE ISDELETED=0 ORDER BY TSENDMETHOD";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TSENDID'] = (int) $row['TSENDID'];
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
/* ========== GET TRANSCRIPTS SEND =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TSENDID = ($_POST['TSENDID'] == 'undefined' || $_POST['TSENDID'] == '') ? 0 : $_POST['TSENDID'];  
			if($TSENDID == 0)throw new Exception('TSENDID Not Found.');
			$delQuery = "EXEC [TRANSCRIPT_SENDING_MASTER_SP] 3,$TSENDID,'','','','',$userid";
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







