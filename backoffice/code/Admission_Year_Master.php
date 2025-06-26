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
        case "getAdmYears":getAdmYears($conn);break;
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
    
		$admyearid = ($_POST['admyearid'] == 'undefined' || $_POST['admyearid'] == '') ? 0 : $_POST['admyearid'];
		$txtYear = $_POST['txtYear'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtYear']);
		$txtSemester = $_POST['txtSemester'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSemester']);
		$txtOtherSemester = $_POST['txtOtherSemester'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtOtherSemester']);
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);

		$actionid = $admyearid == 0 ? 1 : 2;

		if($txtYear == '') throw new Exception("Enter Adm Year.");
		if($txtSemester == '') throw new Exception("Enter Semester.");


		$sql = "SELECT * FROM ADM_YEAR_MASTER WHERE ADMYEAR='$txtYear' AND SEMESTER='$txtOtherSemester' AND ADMYEARID!=$admyearid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ADM_YEAR_MASTER_SP] $actionid,$admyearid,'$txtYear','$txtSemester','$txtOtherSemester','$txtComments',$userid";
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
				if(!empty($admyearid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET ADM YEARS =========== */
 function getAdmYears($mysqli){
	try
	{
		$data = array();
		$query = "SELECT ADMYEARID,ADMYEAR,SEMESTER,OTHER_SEMESTER,COMMENTS 
		FROM ADM_YEAR_MASTER WHERE ISDELETED=0 ORDER BY ADMYEAR";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ADMYEARID'] = (int) $row['ADMYEARID'];
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
/* ========== GET ADM YEARS =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ADMYEARID = ($_POST['ADMYEARID'] == 'undefined' || $_POST['ADMYEARID'] == '') ? 0 : $_POST['ADMYEARID'];  
			if($ADMYEARID == 0)throw new Exception('ADMYEARID Not Found.');
			$delQuery = "EXEC [ADM_YEAR_MASTER_SP] 3,$ADMYEARID,'','','','',$userid";
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







