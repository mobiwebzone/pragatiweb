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
        case "getPublishers":getPublishers($conn);break;
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
    
        $pubid  = ($_POST['pubid'] == 'undefined' || $_POST['pubid'] == '') ? 0 : $_POST['pubid'];
        $txtPublisherName  = $_POST['txtPublisherName'] == 'undefined' ? '' : $_POST['txtPublisherName'];
		$actionid = $pubid == 0 ? 1 : 2;
		
		if($txtPublisherName == ''){throw new Exception("Enter Publisher Name.");}

		$sql = "SELECT * FROM MAKE_PUBLISHERS WHERE MAKE_PUB='$txtPublisherName' AND PUBID!=$pubid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [MAKE_PUBLISHERS_SP]$actionid,$pubid,'$txtPublisherName',$userid";
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
				if(!empty($pubid))$data['message'] = 'Record successfully updated';
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




/* ========== GET PUBLISHERS =========== */
 function getPublishers($mysqli){
	try
	{
		$query = "SELECT PUBID,MAKE_PUB FROM MAKE_PUBLISHERS WHERE ISDELETED=0 ORDER BY MAKE_PUB";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PUBID'] = (int) $row['PUBID'];
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
/* ========== GET PUBLISHERS =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $PUBID = ($_POST['PUBID'] == 'undefined' || $_POST['PUBID'] == '') ? 0 : $_POST['PUBID'];  
			if($PUBID == 0){throw new Exception('PUBID NOT FOUND.');}
			$delQuery = "EXEC [MAKE_PUBLISHERS_SP] 3,$PUBID,'',$userid";
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







