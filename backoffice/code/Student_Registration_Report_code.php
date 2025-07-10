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
		case "getRte": getRte($conn);break;
		case "getFees": getFees($conn);break;
		case "getFeesMaster": getFeesMaster($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getSecurity": getSecurity($conn);break;
		case "getVisibleFields": getVisibleFields($conn); break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 function getVisibleFields($mysqli) {
    try {
        $TEXT_SCHOOL_ID = isset($_POST['TEXT_SCHOOL_ID']) ? (int)$_POST['TEXT_SCHOOL_ID'] : 1;

        $query = "SELECT FIELD_NAME 
                  FROM FIELD_VISIBILITY 
                  WHERE IS_VISIBLE = 'Yes' AND ISDELETED = 0 AND SCHOOL_ID = $TEXT_SCHOOL_ID AND FORM_ID= 3";

        $result = sqlsrv_query($mysqli, $query);
        if ($result === false) throw new Exception(print_r(sqlsrv_errors(), true));

        $fields = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $fields[] = $row['FIELD_NAME'];
        }

        echo json_encode([
            'success' => true,
            'visibleFields' => $fields
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}


function getSecurity($mysqli){
	try
	{
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	 	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];

	    $query = "SELECT 
				 STUDENT_SECURITY_ID
				,SCHOOL_ID
				,STUDENT_ID
				,SECURITY_DEMAND_AMOUNT
				,SECURITY_RECEIVED_AMOUNT
				,SECURITY_RETURNED_AMOUNT
				,SECURITY_BALANCE
				,SECURITY_STATUS_CD
				,SECURITY_STATUS
				,CONVERT(VARCHAR, SECURITY_PAYMENT_DATE, 106) SECURITY_PAYMENT_DATE
				,CONVERT(VARCHAR, SECURITY_RETURN_DATE, 106) SECURITY_RETURN_DATE
			 FROM STUDENT_SECURITY 
			 WHERE ISDELETED = 0 
			 AND STUDENT_ID  = $TEXT_STUDENT_ID
			 AND SCHOOL_ID   = $TEXT_SCHOOL_ID";

		$data = array();
		$result = sqlsrv_query($mysqli, $query);

		if ($result === false) {
			throw new Exception(print_r(sqlsrv_errors(), true));
		}

		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_SECURITY_ID'] = (int) $row['STUDENT_SECURITY_ID'];
			$data['data'][] = $row;
		}

		$data['success'] = !empty($data['data']);
		echo json_encode($data); exit;

	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]); exit;
	}
}




function getRte($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";

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


 function getFeesMaster($mysqli){
		try
	{
		$data = array();
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
       	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		
		
       $query =     " SELECT FEES_ID
							,SCHOOL_ID
							,SCHOOL_NAME
							,STUDENT_ID
							,STUDENT_FIRST_NAME
							,STUDENT_LAST_NAME
							,CLASS_CD
							,CLASS
							,FEES_FY_YEAR_CD
							,FEES_FY_YEAR
							,FEES_DUE
							,FEES_PAID
							,LATE_FEES_PAID
							,FEES_BALANCE
							FROM STUDENT_FEES_MASTER
							WHERE 
							FEES_FY_YEAR_CD  = $TEXT_FEES_FY_YEAR_CD
							AND SCHOOL_ID 	  = $TEXT_SCHOOL_ID
							AND CLASS_CD        = $TEXT_CLASS_CD
							AND STUDENT_ID      = $TEXT_STUDENT_ID
							AND ISDELETED       = 0 ";
														
        

        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FEES_ID'] = (int) $row['FEES_ID'];
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


 function getFees($mysqli){
		try
	{
		$data = array();
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
       	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		
		
       $query =     " SELECT A.FEES_PAYMENT_ID
							,A.SCHOOL_ID
							,A.FEES_ID
							,A.STUDENT_ID
							,CONVERT(VARCHAR,A.PAYMENT_DATE,106)PAYMENT_DATE
							,A.FEES_FY_YEAR_CD
							,A.FEES_FY_YEAR
							,A.REMARKS
							,A.CLASS_CD
							,A.CLASS 
							, (B.STUDENT_FIRST_NAME +' '+B.STUDENT_LAST_NAME) STUDENT_NAME 
							,A.PAYMENT_MODE_CD
							,A.PAYMENT_MODE
							,A.FEES_PAID
							,A.RECEIPT_NO
							FROM STUDENT_FEES_PAYMENT A, STUDENT B 
							WHERE A.ISDELETED=0 
							AND A.STUDENT_ID = B.STUDENT_ID
							AND A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
							AND A.SCHOOL_ID 	  = $TEXT_SCHOOL_ID
							AND A.CLASS_CD        = $TEXT_CLASS_CD
							AND A.STUDENT_ID      = $TEXT_STUDENT_ID
							AND B.ISDELETED       = 0 ";
														
        //  if ($TEXT_STUDENT_ID != '') 
		//  {
		//     $query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID ";
		//  }

        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FEES_PAYMENT_ID'] = (int) $row['FEES_PAYMENT_ID'];
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
		$TEXT_RTE_CD  = $_POST['TEXT_RTE_CD'] == 'undefined' ? 0 : $_POST['TEXT_RTE_CD'];
       		
       $query =     "SELECT 
	                 STUDENT_ID
					,SCHOOL_ID
					,SCHOOL_NAME
					,(STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
					,FATHER_NAME
					,MOTHER_NAME
					,CONVERT(VARCHAR,DATE_OF_ADMISSION,106)DATE_OF_ADMISSION
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
					,HEIGHT
					,WEIGHT
					,LOGIN_ID
					,PASSWORD
					,SAMAGRA_ID
					,UID
					,PEN
					,REMARKS
					,SECTION_ID
					,SECTION
					,FY_YEAR_CD
					,FY_YEAR
					,STUDENT_MOBILE_NO_1
					,ARCHIVED_BY
					,ARCHIVED_DT
					,ARCHIVED_REMARK
					,ARCHIVED
					,STUDENT_TYPE_CD
					,STUDENT_TYPE
					,SECURITY_AMOUNT
					,SECURITY_STATUS_CD
					,SECURITY_STATUS
					,CASE 
    				WHEN DATE_OF_LEAVING = '1900-01-01' THEN NULL 
    				ELSE CONVERT(VARCHAR, DATE_OF_LEAVING, 106) 
  					END AS DATE_OF_LEAVING
					from STUDENT 
					WHERE ISDELETED=0
					AND   ARCHIVED =  0
					AND SCHOOL_ID = $TEXT_SCHOOL_ID ";
					 

				 if ($TEXT_RTE_CD != '') 
				   {
		            $query .= " AND RTE_CD = $TEXT_RTE_CD "; 
			        }

				 if ($TEXT_CLASS_CD != '') 
				   {
		            $query .= " AND CLASS_CD = $TEXT_CLASS_CD "; 
			        }

				$query .= " ORDER BY CLASS_CD "; 

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


function getClass($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
   
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







