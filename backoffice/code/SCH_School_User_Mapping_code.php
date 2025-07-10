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
		case "getLocations": getLocations($conn);break;
		case "getSchool":getSchool($conn);break;
		
        case "getOrgUser":getOrgUser($conn);break;
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
    	$TEXT_SCHOOL_ID  = ($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_LOC_ID  = ($_POST['TEXT_LOC_ID'] == 'undefined' || $_POST['TEXT_LOC_ID'] == '') ? 0 : $_POST['TEXT_LOC_ID'];
		$TEXT_USER_ID  = ($_POST['TEXT_USER_ID'] == 'undefined' || $_POST['TEXT_USER_ID'] == '') ? 0 : $_POST['TEXT_USER_ID'];
		$txtremarks  = ($_POST['txtremarks'] == 'undefined' || $_POST['txtremarks'] == '') ? 0 : $_POST['txtremarks'];

		$actionid = $pmid == 0 ? 1 : 2;
		

	$sql = "SELECT * FROM SCHOOL_USER
	        WHERE SCHOOL_USER_ID!=$pmid 
			AND   SCHOOL_ID      = $TEXT_SCHOOL_ID
			AND   USER_ID        = $TEXT_USER_ID
			AND   LOC_ID         = $TEXT_LOC_ID
			AND   ISDELETED      = 0 ";

        // throw new Exception( $sql );
		
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [SCHOOL_USER_SP] $actionid,$pmid,'$TEXT_USER_ID','$TEXT_SCHOOL_ID','$TEXT_LOC_ID',$txtremarks,$userid";
			
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
			$data['message'] = 'Mapping of the User with this School and Location is already done!.';
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		$TEXT_SCHOOL_ID  = ($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? '' : $_POST['TEXT_SCHOOL_ID'];
	     $query =   "SELECT 
					A.SCHOOL_USER_ID
					,A.SCHOOL_ID
					,A.USER_ID
					,A.LOC_ID
					,A.REMARKS
					,B.LOCATION
					,C.SCHOOL_NAME
					,(D.FIRSTNAME +' ' +D.LASTNAME )USERNAME
					FROM SCHOOL_USER A , LOCATIONS B , SCHOOL C, USERS D
					WHERE A.LOC_ID = B.LOC_ID
					AND   A.ISDELETED =0
					AND   B.ISDELETED =0
					AND   C.ISDELETED=0
					AND   D.ISDELETED=0
					AND   A.SCHOOL_ID= C.SCHOOL_ID
					AND   A.USER_ID = D.UID

					AND   A.SCHOOL_ID =  $TEXT_SCHOOL_ID  ";
						

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['SCHOOL_USER_ID'] = (int) $row['SCHOOL_USER_ID'];
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

function getSchool($mysqli){
	try
	{
		global $userid;

        $query = "SELECT SCHOOL_ID, SCHOOL_NAME  FROM SCHOOL
                  WHERE ISDELETED =0  ";

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
        // $TEXT_SCHOOL_ID  = ($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? '' : $_POST['TEXT_SCHOOL_ID'];

		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 ";
																						
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




function getOrgUser($mysqli){
	try
	{
	
	$data = array();
	$TEXT_LOC_ID  = ($_POST['TEXT_LOC_ID'] == 'undefined' || $_POST['TEXT_LOC_ID'] == '') ? '' : $_POST['TEXT_LOC_ID'];	
	
	$query =    "SELECT UID, (FIRSTNAME+' '+LASTNAME) USERNAME 
				FROM  USERS 
				WHERE ISDELETED = 0
				AND   LOCID    = $TEXT_LOC_ID ";
				 

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['UID'] = (int) $row['UID'];
				
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
				throw new Exception('SCHOOL_USER_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_USER_SP] 3,$pmid,'','','','',$userid ");
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







