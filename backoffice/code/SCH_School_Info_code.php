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
        case "getQuery":getQuery($conn);break;
		case "getLocations":getLocations($conn);break;
		case "getCity": getCity($conn);break;
		case "getState": getState($conn);break;
		case "getCountry": getCountry($conn);break;
        case "delete":delete($conn);break;
		case "getschoolname":getschoolname($conn);break;
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
        $TEXT_SCHOOL_NAME  = $_POST['TEXT_SCHOOL_NAME'] == 'undefined' ? '' : $_POST['TEXT_SCHOOL_NAME'];
		$ddlLocation  = $_POST['ddlLocation'] == 'undefined' ? '' : $_POST['ddlLocation'];
        $TEXT_ADDRESS  = $_POST['TEXT_ADDRESS'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS'];
		
		$TEXT_CITY_ID  = $_POST['TEXT_CITY_ID'] == 'undefined' ? '' : $_POST['TEXT_CITY_ID'];
		$TEXT_STATE_ID  = $_POST['TEXT_STATE_ID'] == 'undefined' ? '' : $_POST['TEXT_STATE_ID'];
		$TEXT_COUNTRY_ID  = $_POST['TEXT_COUNTRY_ID'] == 'undefined' ? '' : $_POST['TEXT_COUNTRY_ID'];
		$TEXT_PINCODE  = $_POST['TEXT_PINCODE'] == 'undefined' ? '' : $_POST['TEXT_PINCODE'];
		$TEXT_MOBILE_NO = isset($_POST['TEXT_MOBILE_NO']) && $_POST['TEXT_MOBILE_NO'] != 'undefined' ? $_POST['TEXT_MOBILE_NO'] : NULL;
		$TEXT_CO_ORDINATOR  = $_POST['TEXT_CO_ORDINATOR'] == 'undefined' ? '' : $_POST['TEXT_CO_ORDINATOR'];
		$TEXT_EMAIL_ID  = $_POST['TEXT_EMAIL_ID'] == 'undefined' ? '' : $_POST['TEXT_EMAIL_ID'];
		$TEXT_LICENSE_START_DATE  = $_POST['TEXT_LICENSE_START_DATE'] == 'undefined' ? '' : $_POST['TEXT_LICENSE_START_DATE'];
		$TEXT_LICENSE_END_DATE  = $_POST['TEXT_LICENSE_END_DATE'] == 'undefined' ? '' : $_POST['TEXT_LICENSE_END_DATE'];
      

		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		if($TEXT_SCHOOL_NAME == '')
		{throw new Exception("Please Enter School Name.");}

	$sql = "SELECT * FROM SCHOOL WHERE SCHOOL_NAME='$TEXT_SCHOOL_NAME' AND SCHOOL_ID!=$pmid AND LOC_ID = $ddlLocation  AND ISDELETED=0 ";

	// throw new Exception($sql);

		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
		$query="EXEC [SCHOOL_SP] $actionid,$pmid,'$TEXT_SCHOOL_NAME',$ddlLocation,'$TEXT_ADDRESS',$TEXT_CITY_ID,$TEXT_STATE_ID,$TEXT_COUNTRY_ID,'$TEXT_PINCODE','$TEXT_LICENSE_START_DATE','$TEXT_LICENSE_END_DATE','$TEXT_CO_ORDINATOR','$TEXT_MOBILE_NO','$TEXT_EMAIL_ID','$txtremarks',$userid ";
	
	
			// echo json_encode($query);exit;

			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = true;
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

/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		 $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? '' : $_POST['TEXT_SCHOOL_ID'];
        $query = "SELECT 
						SCHOOL_ID,
						SCHOOL_NAME,
						LOC_ID,
						LOCATION,
						ADDRESS,
						CITY_ID,
						CITY_NAME,
						STATE_ID,
						STATE,
						COUNTRY_ID,
						COUNTRY_NAME,
						PINCODE,
						CASE 
							WHEN LICENSE_START_DATE IS NULL OR LICENSE_START_DATE = '1900-01-01' 
							THEN '' 
							ELSE CONVERT(VARCHAR, LICENSE_START_DATE, 106) 
						END AS LICENSE_START_DATE,
						CASE 
							WHEN LICENSE_END_DATE IS NULL OR LICENSE_END_DATE = '1900-01-01' 
							THEN '' 
							ELSE CONVERT(VARCHAR, LICENSE_END_DATE, 106) 
						END AS LICENSE_END_DATE,
						CO_ORDINATOR,
						PHONE_NO,
						EMAIL_ID,
						REMARKS 
					FROM SCHOOL 
					WHERE ISDELETED = 0 AND SCHOOL_ID = $TEXT_SCHOOL_ID
					";
			
				

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['SCHOOL_ID'] = (int) $row['SCHOOL_ID'];
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


function getLocations($mysqli){
	try
	{
		$data = array();

		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0  ";
		$data['query'] = $query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['LOC_ID'] = (int) $row['LOC_ID'];
				
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





/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('SCHOOL_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_SP] 3,$pmid,'','','','','','','','','','','','','',$userid ");
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







