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
        case "saveCountries":saveCountries($conn);break;
        case "getCountries":getCountries($conn);break;
        case "deleteCountries":deleteCountries($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveCountries($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $countryid  = ($_POST['countryid'] == 'undefined' || $_POST['countryid'] == '') ? 0 : $_POST['countryid'];
        $txtCountry  = $_POST['txtCountry'] == 'undefined' ? '' : $_POST['txtCountry'];
        $txtSortName  = $_POST['txtSortName'] == 'undefined' ? '' : $_POST['txtSortName'];
        $txtFlagIcon  = $_POST['txtFlagIcon'] == 'undefined' ? '' : $_POST['txtFlagIcon'];
		
		$actionid = $countryid == 0 ? 1 : 2;

		if($txtCountry == '')
		{throw new Exception("Enter Country Name.");}

		if($txtSortName == '')
		{throw new Exception("Enter Country Short Name.");}


		$sql = "SELECT * FROM COUNTRIES WHERE COUNTRY='$txtCountry' AND COUNTRYID!=$countryid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [COUNTRIES_SP] $actionid,$countryid,'$txtCountry','$txtSortName','$txtFlagIcon',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($countryid))$data['message'] = 'Record successfully updated';
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


/*============ Get Countries =============*/ 
 function getCountries($mysqli){
	try
	{

		$data = array();
		$query = "SELECT COUNTRYID,COUNTRY,COUNTRY_SC,FLAG_ICON FROM COUNTRIES WHERE ISDELETED=0";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['COUNTRYID'] = (int) $row['COUNTRYID'];
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
function deleteCountries($mysqli){
	try{   
			global $userid;
			$data = array();     
            $countryid = $_POST['countryid'] == 'undefined' ? 0 : $_POST['countryid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [COUNTRIES_SP] 3,$countryid,'','','',$userid");
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







