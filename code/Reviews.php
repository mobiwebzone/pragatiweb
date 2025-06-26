<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "getReviews":getReviews($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ GET REVIEWS =============*/ 
function getReviews($mysqli){
	try
	{
		$data = array();
		$query = "SELECT RVID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCID)[LOCATION],
		STUDENT_CATEGORY,REGID,FIRSTNAME,LASTNAME,REVIEW_BY,REVIEWBY_NAME,PHONE,EMAILID,REVID,
		(SELECT REVIEWMEDIA FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWMEDIA,
		(SELECT REVIEWLINK FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWLINK,
		COMMENTS_GIVEN,[STATUS],SHOW_IN_HOMEPAGE,CONVERT(VARCHAR,REVIEW_DATE,106)REVIEW_DATE,REVIEW
		FROM REVIEWS R WHERE ISDELETED=0 AND SHOW_IN_HOMEPAGE=1 ORDER BY CONVERT(DATE,REVIEW_DATE,105) DESC";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['REVIEW_DATE']=($row['REVIEW_DATE'] == '' || $row['REVIEW_DATE']=='01 Jan 1900') ? '' : $row['REVIEW_DATE']; 
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Review not found.';
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
/*============ GET REVIEWS =============*/ 



function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['MEP_USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
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







