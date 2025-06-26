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
        case "savestate":savestate($conn);break;
        case "getstate":getstate($conn);break;
        case "deletestate":deletestate($conn);break;
		case "getCountry":getCountry($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function savestate($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $state_id  = ($_POST['stateid'] == 'undefined' || $_POST['stateid'] == '') ? 0 : $_POST['stateid'];
        $txtstate  = $_POST['txtstate'] == 'undefined' ? '' : $_POST['txtstate'];
        $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$txtcountry  = $_POST['txtcountry'] == 'undefined' ? '' : $_POST['txtcountry'];
		$actionid = $state_id == 0 ? 1 : 2;

		if($txtstate == '')
			{throw new Exception("Enter State Name.");}

		
		$sql = "SELECT * FROM STATE
		        WHERE STATE_ID!  = $state_id
				AND   STATE_NAME = '$txtstate'
				and   COUNTRY_ID = $txtcountry 
				AND ISDELETED = 0 ";
				
        
		// throw new Exception($sql);


		$row_count = unique($sql);

		$data = array();
	
		if($row_count == 0)
		{
			$query="EXEC [State_SP] $actionid,$state_id,'$txtstate',$txtcountry,$userid,'$txtremarks' ";
			
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
				
								
				if(!empty($state_id))
				  $data['message'] = 'Record successfully updated';
				else 
				  $data['message'] = 'Record successfully inserted.';
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

 function getCountry($mysqli){
	try
	{
		
	$query = "SELECT COUNTRYID,COUNTRY FROM COUNTRIES where  isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
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


/*============ Get Countries =============*/ 
 function getstate($mysqli){
	try
	{

		$data = array();
		$query = " SELECT 
					 STATE_ID
					,STATE_NAME
					,COUNTRY_ID
					,COUNTRY_NAME
					,REMARKS
					FROM state WHERE ISDELETED=0";
		
		$count = unique($query);
		if($count>0){
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




/* =========== Delete =========== */ 
function deletestate($mysqli){
	try{   
			global $userid;
			$data = array();     
            $state_id = $_POST['stateid'] == 'undefined' ? 0 : $_POST['stateid'];  
			
			$stmt=sqlsrv_query($mysqli, "EXEC [state_SP] 3,$state_id,'','',$userid,'' ");
			
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







