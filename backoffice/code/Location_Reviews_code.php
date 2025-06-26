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
		case "login":login($conn);break;
        case "save":save($conn);break;
        case "getReviewLink":getReviewLink($conn);break;
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
    
        $revid  = ($_POST['revid'] == 'undefined' || $_POST['revid'] == '') ? 0 : $_POST['revid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtReviewMedia  = $_POST['txtReviewMedia'] == 'undefined' ? '' : $_POST['txtReviewMedia'];
        $txtLink  = $_POST['txtLink'] == 'undefined' ? '' : $_POST['txtLink'];
		
		$actionid = $revid == 0 ? 1 : 2;

		if($ddlLocation == 0)
		{throw new Exception("Please Select Location.");}

		$sql = "SELECT * FROM LOCATION_REVIEWS WHERE LOC_ID=$ddlLocation AND REVIEWMEDIA='$txtReviewMedia' AND REVIEWLINK='$txtLink' AND revid!=$revid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [LOCATION_REVIEWS_SP] $actionid,$ddlLocation,$revid,'$txtReviewMedia','$txtLink',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($revid))$data['message'] = 'Record successfully updated';
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


/*============ GET Review Link =============*/ 
 function getReviewLink($mysqli){
	try
	{
		$query = "SELECT REVID,LOC_ID,
		(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=LR.LOC_ID)LOCATION,
		REVIEWMEDIA,REVIEWLINK FROM LOCATION_REVIEWS LR WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['REVID'] = (int) $row['REVID'];
			$data['data'][] = $row;
		}
		$data['success'] = true;
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
            $revid = ($_POST['revid'] == 'undefined' || $_POST['revid'] == '') ? 0 : $_POST['revid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [LOCATION_REVIEWS_SP] 3,0,$revid,'','',$userid");
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







