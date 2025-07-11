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
        case "save":save($conn);break;
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		case "getGender": getGender($conn);break;
		case "getCategory": getCategory($conn);break;
		case "getCaste": getCaste($conn);break;
		case "getReligion": getReligion($conn);break;
		case "getClass": getClass($conn);break;
		case "getCity": getCity($conn);break;
		case "getState": getState($conn);break;
		case "getCountry": getCountry($conn);break;
		case "getRte": getRte($conn);break;
		case "getBloodgroup": getBloodgroup($conn);break;
		case "getSection": getSection($conn);break;
		case "delete":delete($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getClass_S": getClass_S($conn);break;
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
                  WHERE IS_VISIBLE = 'Yes' AND ISDELETED = 0 AND SCHOOL_ID = $TEXT_SCHOOL_ID AND FORM_ID=1";

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


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
    
         $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];

   		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? '' : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
        $TEXT_STUDENT_FIRST_NAME  = $_POST['TEXT_STUDENT_FIRST_NAME'] == 'undefined' ? '' : $_POST['TEXT_STUDENT_FIRST_NAME'];
		$TEXT_STUDENT_LAST_NAME  = $_POST['TEXT_STUDENT_LAST_NAME'] == 'undefined' ? '' : $_POST['TEXT_STUDENT_LAST_NAME'];
		$TEXT_SCHOLAR_NO  = $_POST['TEXT_SCHOLAR_NO'] == 'undefined' ? '' : $_POST['TEXT_SCHOLAR_NO'];
		$TEXT_DATE_OF_ADMISSION  = $_POST['TEXT_DATE_OF_ADMISSION'] == 'undefined' ? '' : $_POST['TEXT_DATE_OF_ADMISSION'];
	    $TEXT_PEN   = $_POST['TEXT_PEN'] == 'undefined' ? '' : $_POST['TEXT_PEN'];
		$TEXT_FATHER_NAME  = $_POST['TEXT_FATHER_NAME'] == 'undefined' ? '' : $_POST['TEXT_FATHER_NAME'];
		$TEXT_MOTHER_NAME  = $_POST['TEXT_MOTHER_NAME'] == 'undefined' ? '' : $_POST['TEXT_MOTHER_NAME'];
		$TEXT_DOB  = $_POST['TEXT_DOB'] == 'undefined' ? '' : $_POST['TEXT_DOB'];
		$TEXT_GENDER_CD  = $_POST['TEXT_GENDER_CD'] == 'undefined' ? '' : $_POST['TEXT_GENDER_CD'];
		$TEXT_CATEGORY_CD  = $_POST['TEXT_CATEGORY_CD'] == 'undefined' ? '' : $_POST['TEXT_CATEGORY_CD'];
		$TEXT_CASTE_CD  = $_POST['TEXT_CASTE_CD'] == 'undefined' ? '' : $_POST['TEXT_CASTE_CD'];
		$TEXT_RELIGION_CD  = $_POST['TEXT_RELIGION_CD'] == 'undefined' ? '' : $_POST['TEXT_RELIGION_CD'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? '' : $_POST['TEXT_CLASS_CD'];
		$TEXT_ADDRESS1  = $_POST['TEXT_ADDRESS1'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS1'];
		$TEXT_ADDRESS2  = $_POST['TEXT_ADDRESS2'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS2'];
		$TEXT_CITY_ID  = $_POST['TEXT_CITY_ID'] == 'undefined' ? '' : $_POST['TEXT_CITY_ID'];
		$TEXT_STATE_ID  = $_POST['TEXT_STATE_ID'] == 'undefined' ? '' : $_POST['TEXT_STATE_ID'];
		$TEXT_COUNTRY_ID  = $_POST['TEXT_COUNTRY_ID'] == 'undefined' ? '' : $_POST['TEXT_COUNTRY_ID'];
		$TEXT_ZIP_CD  = $_POST['TEXT_ZIP_CD'] == 'undefined' ? '' : $_POST['TEXT_ZIP_CD'];
		$TEXT_STUDENT_MOBILE_NO = isset($_POST['TEXT_STUDENT_MOBILE_NO']) && $_POST['TEXT_STUDENT_MOBILE_NO'] != 'undefined' ? $_POST['TEXT_STUDENT_MOBILE_NO'] : NULL;
		$TEXT_FATHER_MOBILE_NO  = $_POST['TEXT_FATHER_MOBILE_NO'] == 'undefined' ? '' : $_POST['TEXT_FATHER_MOBILE_NO'];
		$TEXT_SAMAGRA_ID  = $_POST['TEXT_SAMAGRA_ID'] == 'undefined' ? '' : $_POST['TEXT_SAMAGRA_ID'];
		$TEXT_RTE_CD  = $_POST['TEXT_RTE_CD'] == 'undefined' ? '' : $_POST['TEXT_RTE_CD'];
		$TEXT_UID  = $_POST['TEXT_UID'] == 'undefined' ? '' : $_POST['TEXT_UID'];
		$TEXT_BLOOD_GROUP  = $_POST['TEXT_BLOOD_GROUP'] == 'undefined' ? '' : $_POST['TEXT_BLOOD_GROUP'];
		$TEXT_HEIGHT  = $_POST['TEXT_HEIGHT'] == 'undefined' ? '' : $_POST['TEXT_HEIGHT'];
		$TEXT_WEIGHT  = $_POST['TEXT_WEIGHT'] == 'undefined' ? '' : $_POST['TEXT_WEIGHT'];
		$TEXT_STUDENT_EMAIL_ID  = $_POST['TEXT_STUDENT_EMAIL_ID'] == 'undefined' ? '' : $_POST['TEXT_STUDENT_EMAIL_ID'];
		$TEXT_PARENT_EMAIL_ID  = $_POST['TEXT_PARENT_EMAIL_ID'] == 'undefined' ? '' : $_POST['TEXT_PARENT_EMAIL_ID'];
		$TEXT_DATE_OF_LEAVING  = $_POST['TEXT_DATE_OF_LEAVING'] == 'undefined' ? '' : $_POST['TEXT_DATE_OF_LEAVING'];
		$TEXT_BANK_ACCOUNT_NO  = $_POST['TEXT_BANK_ACCOUNT_NO'] == 'undefined' ? '' : $_POST['TEXT_BANK_ACCOUNT_NO'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$TEXT_SECTION_ID  = $_POST['TEXT_SECTION_ID'] == 'undefined' ? '' : $_POST['TEXT_SECTION_ID'];
		
				
		$actionid = $pmid == 0 ? 1 : 2;

		
		$sql = "SELECT * FROM STUDENT 
		        WHERE STUDENT_ID!=$pmid
				AND   SCHOOL_ID = $TEXT_SCHOOL_ID
				AND   CLASS_CD  = $TEXT_CLASS_CD
				AND   STUDENT_FIRST_NAME = '$TEXT_STUDENT_FIRST_NAME'
				AND   STUDENT_LAST_NAME  = '$TEXT_STUDENT_LAST_NAME'
				-- AND   PEN = $TEXT_PEN
				-- AND   SCHOLAR_NO = $TEXT_SCHOLAR_NO
				AND   ISDELETED = 0";	
       
	    // throw new Exception($sql);
	
	   $row_count = unique($sql);
	
	
	   $data = array();
	   
	   if($row_count == 0)
	   {
	   $query="EXEC [STUDENT_REGISTRATION_SP] 
	    $actionid
	   ,$pmid
	   ,$TEXT_SCHOOL_ID
	   ,'$TEXT_STUDENT_FIRST_NAME'
	   ,'$TEXT_STUDENT_LAST_NAME'
	   ,'$TEXT_FATHER_NAME'
	   ,'$TEXT_MOTHER_NAME'
	   ,'$TEXT_DATE_OF_ADMISSION'
	   ,'$TEXT_DATE_OF_LEAVING'
	   ,'$TEXT_DOB'
	   ,'$TEXT_SCHOLAR_NO'
	   ,'$TEXT_GENDER_CD'
	   ,'$TEXT_CATEGORY_CD'
	   ,'$TEXT_CASTE_CD'
	   ,'$TEXT_RELIGION_CD'
	   ,$TEXT_CLASS_CD
	   ,'$TEXT_RTE_CD'
	   ,'$TEXT_ADDRESS1'
	   ,'$TEXT_ADDRESS2'
	   ,'$TEXT_CITY_ID'
	   ,'$TEXT_STATE_ID'
	   ,'$TEXT_ZIP_CD'
	   ,'$TEXT_COUNTRY_ID'
	   ,'$TEXT_STUDENT_MOBILE_NO'
	   ,'$TEXT_FATHER_MOBILE_NO'
	   ,'$TEXT_STUDENT_EMAIL_ID'
	   ,'$TEXT_PARENT_EMAIL_ID'
	   ,'$TEXT_BLOOD_GROUP'
	   ,'$TEXT_HEIGHT'
	   ,'$TEXT_WEIGHT'
	   ,'$TEXT_SAMAGRA_ID'
	   ,'$TEXT_UID'
	   ,'$TEXT_PEN'
	   ,$userid
	   ,'$TEXT_BANK_ACCOUNT_NO'
	   ,'$txtremarks'
	   ,'$TEXT_SECTION_ID'
	   ,$TEXT_FY_YEAR_CD 
	   "; 
	   
	// echo json_encode($query);exit;

	   $data['$sql'] = $query;
		
		   
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($pmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
			echo json_encode($data);exit;
		}

     }
     catch(Exception $e)
     {
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);exit;
		exit;
     }
 }


 function getQuery($mysqli){
	try
	{
		
       	$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD_S   = $_POST['TEXT_CLASS_CD_S'] =='undefined' ? 0 : $_POST['TEXT_CLASS_CD_S'];

       $query =     "SELECT STUDENT_ID
	                ,SCHOOL_ID
					,SCHOOL_NAME
					,STUDENT_FIRST_NAME
					,STUDENT_LAST_NAME
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
					,BLOOD_GROUP
					,HEIGHT
					,WEIGHT
					,LOGIN_ID
					,PASSWORD
					,SAMAGRA_ID
					,UID
					,PEN
					,BANK_ACCOUNT_NO
					,REMARKS
					,SECTION_ID
					,SECTION
					,FY_YEAR_CD
					,FY_YEAR
					,CASE 
    				WHEN DATE_OF_LEAVING = '1900-01-01' THEN NULL 
    				ELSE CONVERT(VARCHAR, DATE_OF_LEAVING, 106) 
  					END AS DATE_OF_LEAVING
					from STUDENT 
					WHERE ISDELETED=0
					AND   ARCHIVED =0
					AND   SCHOOL_ID = $TEXT_SCHOOL_ID ";   
					
        
           if ($TEXT_CLASS_CD_S != '') {
		            $query .= " AND CLASS_CD = $TEXT_CLASS_CD_S "; 
			        }
            $query  .= " ORDER BY CLASS_CD, STUDENT_ID ";

		$result = sqlsrv_query($mysqli, $query);
          
		//   if ($result === false) {
 		//    // Handle error
    	// 	die(print_r(sqlsrv_errors(), true)); // This will give you detailed error info
		// 	}

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



function getSection($mysqli){
	try
	{
		
	    $data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
		
		$query = "SELECT SECTION_ID,SECTION_NAME FROM SECTION 
		          WHERE SCHOOL_ID = $TEXT_SCHOOL_ID
				  AND   CLASS_CD  = $TEXT_CLASS_CD
				  ORDER BY SECTION_ID";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SECTION_ID'] = (int) $row['SECTION_ID'];
				
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


function getBloodgroup($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=39 and isdeleted=0";

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


function getCity($mysqli){
	try
	{
	
	$data = array();
	$TEXT_STATE_ID  = $_POST['TEXT_STATE_ID'] == 'undefined' ? 0 : $_POST['TEXT_STATE_ID'];	
	
	$query = "SELECT CITY_ID,CITY_NAME FROM CITY where  isdeleted=0 and STATE_ID = $TEXT_STATE_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CITY_ID'] = (int) $row['CITY_ID'];
				
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


function getState($mysqli){
	try
	{
	$data = array();
	$TEXT_COUNTRY_ID  = $_POST['TEXT_COUNTRY_ID'] == 'undefined' ? 0 : $_POST['TEXT_COUNTRY_ID'];	
	
	$query = "SELECT STATE_ID,STATE_NAME FROM STATE where  isdeleted=0 and COUNTRY_ID= $TEXT_COUNTRY_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STATE_ID'] = (int) $row['STATE_ID'];
				
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


function getCountry($mysqli){
	try
	{
		
	$query = "SELECT COUNTRYID,COUNTRY FROM COUNTRIES where  isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CCOUNTRYID'] = (int) $row['COUNTRYID'];
				
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


function getClass_S($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
   
	$query = "SELECT CLASS_CD,CLASS FROM  SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0 order by CLASS_CD";
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



function getClass($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
	// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0 order by CODE_DETAIL_ID";
    
	$query = "SELECT CLASS_CD,CLASS FROM  SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0 order by CLASS_CD";
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



function getReligion($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=38 and isdeleted=0";

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


function getGender($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=31 and isdeleted=0";

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



//Get Main server Location
function getCategory($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=36 and isdeleted=0";

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



//Get Backup server Location
function getCaste($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=37 and isdeleted=0 order by CODE_DETAIL_ID";

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




/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('STUDENT_ID Error.');
			}

	$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_REGISTRATION_SP]	3,$pmid,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',$userid,'','','','' ") ;
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







