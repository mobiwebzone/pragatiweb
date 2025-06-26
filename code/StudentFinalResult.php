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
        case "getStudentFinalResult":getStudentFinalResult($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ========== GET STUDENT FINAL RESULT =========== */
function getStudentFinalResult($mysqli){
	try
	{
		$query = "SELECT SFRID,STUDENT_TYPE,PLANID,REGID,
		FIRSTNAME,LASTNAME,PRODUCTID1,PRODUCTID2,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SFR.PRODUCTID1)PRODUCT1,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SFR.PRODUCTID2)PRODUCT2,
		CONVERT(VARCHAR,STUDIED_FROM,106)STUDIED_FROM,CONVERT(VARCHAR,STUDIED_UPTO,106)STUDIED_UPTO,
		SELECTED,SUPERSCORE,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SFR.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SFR.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=SFR.MAJORID)MAJOR,REMARK,SHOW_IN_HOMEPAGE
		FROM STUDENT_FINAL_RESULTS SFR
		WHERE ISDELETED=0 AND SHOW_IN_HOMEPAGE=1 ORDER BY FIRSTNAME";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SFRID'] = (int) $row['SFRID'];
				$row['STUDIED_FROM'] = ($row['STUDIED_FROM'] == '' || $row['STUDIED_FROM']=='01 Jan 1900') ? '' : $row['STUDIED_FROM'];
				$row['STUDIED_UPTO'] = ($row['STUDIED_UPTO'] == '' || $row['STUDIED_UPTO']=='01 Jan 1900') ? '' : $row['STUDIED_UPTO'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET STUDENT FINAL RESULT =========== */



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







