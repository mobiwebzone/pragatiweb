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
        case "getUniversity":getUniversity($conn);break;
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

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $universityid  = ($_POST['universityid'] == 'undefined' || $_POST['universityid'] == '') ? 0 : $_POST['universityid'];
        $txtUniversity  = $_POST['txtUniversity'] == 'undefined' ? '' : $_POST['txtUniversity'];
		
		$actionid = $universityid == 0 ? 1 : 2;

		if($txtUniversity == '')
		{throw new Exception("Enter University Name.");}

		$sql = "SELECT * FROM UNIVERSITY_MASTER WHERE UNIVERSITY='$txtUniversity' AND UNIVERSITYID!=$universityid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			if($actionid == 1){
				$query="INSERT INTO UNIVERSITY_MASTER (UNIVERSITY,INSERTID) VALUES('$txtUniversity',$userid)";
			}else{
				$query="UPDATE UNIVERSITY_MASTER SET UNIVERSITY='$txtUniversity',UPDATEID=$userid,UPDATEDATE=GETDATE() WHERE UNIVERSITYID=$universityid";
			}
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
				if(!empty($universityid))$data['message'] = 'Record successfully updated';
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
/* ========== SAVE DATA =========== */




/* ========== GET UNIVERSITY =========== */
 function getUniversity($mysqli){
	try
	{

		$query = "SELECT UNIVERSITYID,UNIVERSITY FROM UNIVERSITY_MASTER WHERE ISDELETED=0 ORDER BY UNIVERSITY ASC";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['UNIVERSITYID'] = (int) $row['UNIVERSITYID'];
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
/* ========== GET UNIVERSITY =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $UNIVERSITYID = ($_POST['UNIVERSITYID'] == 'undefined' || $_POST['UNIVERSITYID'] == '') ? 0 : $_POST['UNIVERSITYID'];  
			if($UNIVERSITYID == 0){throw new Exception('UNIVERCITYID NOT FOUND.');}
			$delQuery = "UPDATE UNIVERSITY_MASTER SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE UNIVERSITYID=$UNIVERSITYID";
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
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







