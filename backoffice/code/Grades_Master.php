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
        case "getGrades":getGrades($conn);break;
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

		$gradeid = ($_POST['gradeid'] == 'undefined' || $_POST['gradeid'] == '') ? 0 : $_POST['gradeid'];
		$txtGrade = $_POST['txtGrade'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGrade']);
		$txtGradeDesc = $_POST['txtGradeDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGradeDesc']);

		$actionid = $gradeid == 0 ? 1 : 2;

		if($txtGrade == '') throw new Exception("Enter Grade.");


		$sql = "SELECT * FROM GRADES_MASTER WHERE GRADE='$txtGrade' AND GRADEID!=$gradeid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [GRADES_MASTER_SP] $actionid,$gradeid,'$txtGrade','$txtGradeDesc',$userid";
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
				if(!empty($gradeid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET GRADES =========== */
 function getGrades($mysqli){
	try
	{
		$data = array();
		$query = "SELECT GRADEID,GRADE,GRADE_DESC FROM GRADES_MASTER WHERE ISDELETED=0 ORDER BY GRADE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['GRADEID'] = (int) $row['GRADEID'];
				$row['GRADE'] = (int) $row['GRADE'];
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
/* ========== GET GRADES =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];  
			if($GRADEID == 0)throw new Exception('GRADEID Not Found.');
			$delQuery = "EXEC [GRADES_MASTER_SP] 3,$GRADEID,'','',$userid";
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







