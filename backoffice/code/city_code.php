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
        case "savecity":savecity($conn);break;
        case "getcity":getcity($conn);break;
        case "deletecity":deletecity($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function savecity($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $cityid  = ($_POST['cityid'] == 'undefined' || $_POST['cityid'] == '') ? 0 : $_POST['cityid'];
		$txtcity  = $_POST['txtcity'] == 'undefined' ? '' : $_POST['txtcity'];
        $txtstate  = $_POST['txtstate'] == 'undefined' ? '' : $_POST['txtstate'];
        $txtcountry  = $_POST['txtcountry'] == 'undefined' ? '' : $_POST['txtcountry'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		$actionid = $cityid == 0 ? 1 : 2;

		if($txtstate == '')
		{throw new Exception("Enter State Name.");}

		


		$sql = "SELECT * FROM CITY
		        WHERE CITY_NAME= '$txtcity' 
				AND CITY_ID!=$cityid AND ISDELETED=0 ";
        
		// throw new Exception($sql);


		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [CITY_SP] $actionid,$cityid,'$txtcity','$txtstate','$txtcountry',$userid,'$txtremarks' ";
			
			$stmt=sqlsrv_query($mysqli, $query);
	
			// echo json_encode($stmt);exit;

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
				if(!empty($cityid))$data['message'] = 'Record successfully updated';
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
 function getcity($mysqli){
	try
	{

		$data = array();
		$query = "SELECT 
					 CITY_ID
					,CITY_NAME
					,STATE_NAME
					,COUNTRY_NAME
					,REMARKS
					FROM CITY WHERE ISDELETED=0 ";
		$count = unique($query);
		if($count>0){
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




/* =========== Delete =========== */ 
function deletecity($mysqli){
	try{   
			global $userid;
			$data = array();     
            $cityid = $_POST['cityid'] == 'undefined' ? 0 : $_POST['cityid'];  
			
			$stmt=sqlsrv_query($mysqli, "EXEC [CITY_SP] 3,$cityid,'','','',$userid,'' ");
			
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







