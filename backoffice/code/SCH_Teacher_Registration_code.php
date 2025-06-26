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
		case "getBank": getBank($conn);break;
		case "getCity": getCity($conn);break;
		case "getState": getState($conn);break;
		case "getCountry": getCountry($conn);break;
		case "getNationality": getNationality($conn);break;
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
    
         $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];

   		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? '' : $_POST['TEXT_SCHOOL_ID'];
        $TEXT_TEACHER_NAME  = $_POST['TEXT_TEACHER_NAME'] == 'undefined' ? '' : $_POST['TEXT_TEACHER_NAME'];
		$TEXT_NATIONALITY_CD  = $_POST['TEXT_NATIONALITY_CD'] == 'undefined' ? '' : $_POST['TEXT_NATIONALITY_CD'];
		$TEXT_DATE_OF_JOINING  = $_POST['TEXT_DATE_OF_JOINING'] == 'undefined' ? '' : $_POST['TEXT_DATE_OF_JOINING'];
		$TEXT_FATHER_HUSBAND_NAME  = $_POST['TEXT_FATHER_HUSBAND_NAME'] == 'undefined' ? '' : $_POST['TEXT_FATHER_HUSBAND_NAME'];
		$TEXT_DOB  = $_POST['TEXT_DOB'] == 'undefined' ? '' : $_POST['TEXT_DOB'];
		$TEXT_GENDER_CD  = $_POST['TEXT_GENDER_CD'] == 'undefined' ? '' : $_POST['TEXT_GENDER_CD'];
		$TEXT_ADDRESS1  = $_POST['TEXT_ADDRESS1'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS1'];
		$TEXT_ADDRESS2  = $_POST['TEXT_ADDRESS2'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS2'];
		$TEXT_CITY_ID  = $_POST['TEXT_CITY_ID'] == 'undefined' ? '' : $_POST['TEXT_CITY_ID'];
		$TEXT_STATE_ID  = $_POST['TEXT_STATE_ID'] == 'undefined' ? '' : $_POST['TEXT_STATE_ID'];
		$TEXT_COUNTRY_ID  = $_POST['TEXT_COUNTRY_ID'] == 'undefined' ? '' : $_POST['TEXT_COUNTRY_ID'];
		$TEXT_ZIP_CD  = $_POST['TEXT_ZIP_CD'] == 'undefined' ? '' : $_POST['TEXT_ZIP_CD'];
		$TEXT_TEACHER_MOBILE_NO = isset($_POST['TEXT_TEACHER_MOBILE_NO']) && $_POST['TEXT_TEACHER_MOBILE_NO'] != 'undefined' ? $_POST['TEXT_TEACHER_MOBILE_NO'] : NULL;
		$TEXT_UID  = $_POST['TEXT_UID'] == 'undefined' ? '' : $_POST['TEXT_UID'];
		$TEXT_TEACHER_EMAIL_ID  = $_POST['TEXT_TEACHER_EMAIL_ID'] == 'undefined' ? '' : $_POST['TEXT_TEACHER_EMAIL_ID'];
		$TEXT_DATE_OF_LEAVING  = $_POST['TEXT_DATE_OF_LEAVING'] == 'undefined' ? '' : $_POST['TEXT_DATE_OF_LEAVING'];
		$TEXT_BANK_CD  = $_POST['TEXT_BANK_CD'] == 'undefined' ? '' : $_POST['TEXT_BANK_CD'];
		$TEXT_BANK_ACCOUNT_NO  = $_POST['TEXT_BANK_ACCOUNT_NO'] == 'undefined' ? '' : $_POST['TEXT_BANK_ACCOUNT_NO'];
		$TEXT_BANK_IFSC_CODE  = $_POST['TEXT_BANK_IFSC_CODE'] == 'undefined' ? '' : $_POST['TEXT_BANK_IFSC_CODE'];
		$TEXT_BANK_BRANCH  = $_POST['TEXT_BANK_BRANCH'] == 'undefined' ? '' : $_POST['TEXT_BANK_BRANCH'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $pmid == 0 ? 1 : 2;

		
		$sql = "SELECT * FROM TEACHER 
		        WHERE TEACHER_ID!=$pmid
				AND   SCHOOL_ID = $TEXT_SCHOOL_ID
                AND   TEACHER_NAME = '$TEXT_TEACHER_NAME'
				AND   ISDELETED = 0 ";	
       
	    // throw new Exception($sql);
	
	   $row_count = unique($sql);
 
	
	   $data = array();
	   
	   if($row_count == 0)
	   {
	   $query="EXEC [TEACHER_REGISTRATION_SP] $actionid,$pmid,$TEXT_SCHOOL_ID,'$TEXT_TEACHER_NAME','$TEXT_FATHER_HUSBAND_NAME','$TEXT_DOB',$TEXT_NATIONALITY_CD,'$TEXT_DATE_OF_JOINING','$TEXT_DATE_OF_LEAVING',$TEXT_GENDER_CD,'$TEXT_ADDRESS1','$TEXT_ADDRESS2',$TEXT_CITY_ID,$TEXT_STATE_ID,'$TEXT_ZIP_CD',$TEXT_COUNTRY_ID,'$TEXT_TEACHER_MOBILE_NO','$TEXT_TEACHER_EMAIL_ID','$TEXT_UID','$TEXT_BANK_ACCOUNT_NO','$TEXT_BANK_IFSC_CODE','$TEXT_BANK_CD','$TEXT_BANK_BRANCH',$userid,'$txtremarks' ";
	   
	//    echo json_encode($query);exit;

	
	
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
		echo json_encode($data);
		exit;
     }
 }


 function getQuery($mysqli){
	try
	{
		
       	$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];

       $query = "SELECT TEACHER_ID
					,SCHOOL_ID
					,SCHOOL_NAME
					,TEACHER_NAME
					,FATHER_HUSBAND_NAME
					,NATIONALITY_CD
					,NATIONALITY
					,CONVERT(VARCHAR,DATE_OF_JOINING,106)DATE_OF_JOINING
					,CONVERT(VARCHAR,DOB,106)DOB
					,GENDER_CD
					,GENDER_DESC
					,ADDRESS1
					,ADDRESS2
					,CITY_ID
					,CITY
					,STATE_ID
					,STATE
					,ZIP_CD
					,COUNTRY_ID
					,COUNTRY
					,TEACHER_MOBILE_NO
					,TEACHER_EMAIL_ID
					,LOGIN_ID
					,PASSWORD
					,UID
					,BANK_ACCOUNT_NO
					,BANK_IFSC_CODE
					,BANK_CD
					,BANK
					,BANK_BRANCH
					,REMARKS
					,CASE 
    				WHEN DATE_OF_LEAVING = '1900-01-01' THEN NULL 
    				ELSE CONVERT(VARCHAR, DATE_OF_LEAVING, 106) 
  					END AS DATE_OF_LEAVING
					from TEACHER 
					WHERE ISDELETED = 0
					AND   ARCHIVED  = 0
					AND   SCHOOL_ID = $TEXT_SCHOOL_ID ";

		

        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TEACHER_ID'] = (int) $row['TEACHER_ID'];
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


function getNationality($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=44 and isdeleted=0";

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


function getBank($mysqli){
	try
	{
		
	$query = "SELECT BANKID, BANKNAME FROM BANKS where isdeleted=0 ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['BANKID'] = (int) $row['BANKID'];
				
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
	$query = "SELECT CITY_ID,CITY_NAME FROM CITY where  isdeleted=0 and STATE_ID = $TEXT_STATE_ID";

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
				throw new Exception('TEACHER_ID Error.');
			}

	$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_REGISTRATION_SP]	3,$pmid,'','','','','','','','','','','','','','','','','','','','','',$userid,'' ") ;
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







