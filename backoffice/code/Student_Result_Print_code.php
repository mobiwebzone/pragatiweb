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
		
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		case "getClass": getClass($conn);break;
		case "getStudent": getStudent($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getExaminationType": getExaminationType($conn);break;
		case "getQueryStudent":getQueryStudent($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
function getQueryStudent($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
       	
       $query =     "SELECT	STUDENT_ID
						,SCHOOL_ID
						,SCHOOL_NAME
						,(STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
						,FATHER_NAME
						,MOTHER_NAME
						,CONVERT(VARCHAR,DATE_OF_ADMISSION,106) DATE_OF_ADMISSION
						,CONVERT(VARCHAR,DATE_OF_LEAVING,106) DATE_OF_LEAVING
						,CONVERT(VARCHAR,DOB,106)DOB
						,SCHOLAR_NO
						,GENDER_CD
						,GENDER_DESC
						,CATEGORY_CD
						,CATEGORY_DESC
						,CASTE_CD
						,CASTE_DESC
						,RELIGION_CD
						,RELIGION_DESC
						,CLASS_CD
						,CLASS
						,RTE_CD
						,RTE_DESC
						,ADDRESS1
						,ADDRESS2
						,CITY_ID
						,CITY
						,STATE_ID
						,STATE
						,ZIP_CD
						,COUNTRY_ID
						,COUNTRY
						,STUDENT_MOBILE_NO
						,FATHER_MOBILE_NO
						,STUDENT_EMAIL_ID
						,PARENT_EMAIL_ID
						,BLOOD_GROUP_CD
						,BLOOD_GROUP
						,HEIGHT
						,WEIGHT
						,LOGIN_ID
						,PASSWORD
						,SAMAGRA_ID
						,UID
						,PEN
						,BANK_ACCOUNT_NO FROM STUDENT
						WHERE ISDELETED=0 
						and SCHOOL_ID  = $TEXT_SCHOOL_ID
						and STUDENT_ID = $TEXT_STUDENT_ID
						 ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
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


 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
       	$TEXT_EXAM_TYPE_CD  = $_POST['TEXT_EXAM_TYPE_CD'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_TYPE_CD'];

       $query =     "SELECT 
						 A.MARKS_ID
						,A.SCHOOL_ID
						,A.SCHOOL_NAME
						,A.CLASS_CD
						,A.CLASS
						,A.STUDENT_ID
						,(B.STUDENT_FIRST_NAME + ' ' + ISNULL(B.STUDENT_LAST_NAME, '')) AS STUDENT_NAME
						,A.EXAM_TYPE_CD
						,A.EXAM_TYPE
						,A.SUBJECT_CD
						,A.SUBJECT
						,A.EXAM_DATE
						,A.FY_YEAR_CD
						,A.FY_YEAR
						,A.MARKS_MASTER_ID
						,A.TOTAL_MARKS
						,A.MARKS_OBTAINED 
						FROM STUDENT_MARKS A , STUDENT B
						WHERE A.ISDELETED = 0
						AND   B.ISDELETED = 0
						AND   A.STUDENT_ID = B.STUDENT_ID 
						and A.SCHOOL_ID  = $TEXT_SCHOOL_ID
						and A.CLASS_CD   = $TEXT_CLASS_CD
						and A.FY_YEAR_CD = $TEXT_FY_YEAR_CD
						and A.STUDENT_ID = $TEXT_STUDENT_ID
						AND A.EXAM_TYPE_CD = $TEXT_EXAM_TYPE_CD ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['MARKS_ID'] = (int) $row['MARKS_ID'];
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

function getExaminationType($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	
	// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=42 and isdeleted=0";
    $query = "SELECT EXAM_TYPE_CD,EXAM_TYPE 
	             FROM SCHOOL_EXAM_TYPE 
				 WHERE ISDELETED = 0
				 AND   SCHOOL_ID = $TEXT_SCHOOL_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['EXAM_TYPE_CD'] = (int) $row['EXAM_TYPE_CD'];
				
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

function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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



function getStudent($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	
	$query = "SELECT STUDENT_ID, 
	        (STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
			FROM STUDENT 
			where  isdeleted=0 
			and SCHOOL_ID = $TEXT_SCHOOL_ID
			and CLASS_CD  = $TEXT_CLASS_CD ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
				
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


function getClass($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
	// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0 order by CODE_DETAIL_ID";
    
	$query = "SELECT CLASS_CD,CLASS FROM  SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0 order by SCHOOL_CLASS_ID";
	
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLASS_CD'] = (int) $row['CLASS_CD'];
				
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


function getschoolname($mysqli){
	try
	{
		global $userid;
		$query = "select SCHOOL_ID,SCHOOL_NAME FROM SCHOOL WHERE ISDELETED=0 
		AND SCHOOL_ID IN (SELECT SCHOOL_ID FROM SCHOOL_USER WHERE USER_ID= $userid AND ISDELETED=0)
		ORDER BY SCHOOL_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SCHOOL_ID'] = (int) $row['SCHOOL_ID'];
				
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







