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
        case "getProductReviews":getProductReviews($conn);break;
        case "getProduct":getProduct($conn);break;
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


 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$revid = ($_POST['revid'] == 'undefined' || $_POST['revid'] == '') ? 0 : $_POST['revid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$txtReviewDT = $_POST['txtReviewDT'] == 'undefined' ? '' : $_POST['txtReviewDT'];
		$txtReviewer = $_POST['txtReviewer'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtReviewer']);
		$txtReview = $_POST['txtReview'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtReview']);
		
		$actionid = $revid == 0 ? 1 : 2;

		if($ddlProduct == 0)throw new Exception("Select Product Name.");
		if($ddlLocation == 0)throw new Exception("Select Location Name.");
		if($txtReviewDT == '')throw new Exception("Select Review Date.");
		if($txtReviewer == '')throw new Exception("Enter Reviewer.");
		if($txtReview == '')throw new Exception("Enter Review.");

		$sql = "SELECT * FROM PRODUCT_REVIEWS WHERE LOCID=$ddlLocation AND REVDATE='$txtReviewDT' AND PDMID=$ddlProduct AND REVIEWER='$txtReviewer' AND REVID!=$revid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [PRODUCT_REVIEWS_SP] $actionid,$revid,$ddlLocation,'$txtReviewDT',$ddlProduct,'$txtReviewer','$txtReview',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			// $data['query'] = $query;
			// echo json_encode($data);exit;
			
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
				// $data['query'] = $query;
				$data['success'] = true;
				if(!empty($revid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
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


/*============ Get Data =============*/ 
 function getProductReviews($mysqli){
	try
	{
		$data = array();
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		if($ddlProduct == 0)throw new Exception('Error : PDMID Missing, Please Select Product Name.');
		if($ddlLocation == 0)throw new Exception('Error : LOCID Missing, Please Select Location Name.');
		$query = "SELECT REVID,CONVERT(VARCHAR,REVDATE,106)REVDATE,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PR.LOCID)[LOCATION],PDMID,
		(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PR.PDMID)PRODUCT,REVIEWER,REVIEW 
		FROM PRODUCT_REVIEWS PR WHERE ISDELETED=0 AND PDMID=$ddlProduct AND LOCID=$ddlLocation";
		$CNT = unique($query);
		if($CNT>0){
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



/*============ Get getProduct =============*/ 
function getProduct($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PDMID,DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE ISDELETED=0 ORDER BY [ORDER]";
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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
		$REVID = ($_POST['REVID'] == 'undefined' || $_POST['REVID'] == '') ? 0 : $_POST['REVID'];
		if($REVID == 0)throw new Exception('Error : REVID Missing.');
		$delQuery = "EXEC [PRODUCT_REVIEWS_SP] 3,$REVID,0,'',0,'','',$userid";
		$stmt=sqlsrv_query($mysqli, $delQuery);
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







