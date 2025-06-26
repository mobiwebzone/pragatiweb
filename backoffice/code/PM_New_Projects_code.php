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
		case "getOrganization":getOrganization($conn);break;
		case "getLocations": getLocations($conn);break;
		case "delete":delete($conn);break;
		case "getProjecttype":getProjecttype($conn);break;
		case "getcapacityunit":getcapacityunit($conn);break;
		case "getProjectstatus":getProjectstatus($conn);break;

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
   		
		$TEXT_ORG_ID  = $_POST['TEXT_ORG_ID'] == 'undefined' ? '' : $_POST['TEXT_ORG_ID'];
	    $TEXT_LOC_ID   = $_POST['TEXT_LOC_ID'] == 'undefined' ? '' : $_POST['TEXT_LOC_ID'];
		$TEXT_PROJECT_NAME  = $_POST['TEXT_PROJECT_NAME'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_NAME'];
		$TEXT_PROJECT_TYPE_CD  = ($_POST['TEXT_PROJECT_TYPE_CD'] == 'undefined' || $_POST['TEXT_PROJECT_TYPE_CD'] == '') ? 0 : $_POST['TEXT_PROJECT_TYPE_CD'];
      	$TEXT_PM_NAME  = $_POST['TEXT_PM_NAME'] == 'undefined' ? '' : $_POST['TEXT_PM_NAME'];
		$TEXT_PROJECT_START_DATE  = $_POST['TEXT_PROJECT_START_DATE'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_START_DATE'];
        $TEXT_PROJECT_END_DATE  = $_POST['TEXT_PROJECT_END_DATE'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_END_DATE'];
    	$TEXT_PROJECT_CAPACITY  = $_POST['TEXT_PROJECT_CAPACITY'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_CAPACITY'];
		$TEXT_PROJECT_STATUS_CD  = $_POST['TEXT_PROJECT_STATUS_CD'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_STATUS_CD'];
	    $TEXT_PROJECT_CAPACITY_UNIT_CD  = $_POST['TEXT_PROJECT_CAPACITY_UNIT_CD'] == 'undefined' ? '' : $_POST['TEXT_PROJECT_CAPACITY_UNIT_CD'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		$actionid = $pmid == 0 ? 1 : 2;

	
		$sql = "SELECT * FROM PROJECTS 
				WHERE PROJECT_ID!=$pmid 
				AND  ORG_ID   = $TEXT_ORG_ID
				AND  ISDELETED   = 0
				AND  LOC_ID = $TEXT_LOC_ID
				and  PROJECT_NAME = '$TEXT_PROJECT_NAME' ";	
        // throw new Exception( $sql );

		$row_count = unique($sql);
		if($row_count == 0)
		{
			$query="EXEC [PROJECTS_SP]$actionid,$pmid,'$TEXT_PROJECT_NAME','$TEXT_ORG_ID','$TEXT_LOC_ID','$TEXT_PROJECT_TYPE_CD','$TEXT_PM_NAME','$TEXT_PROJECT_START_DATE','$TEXT_PROJECT_END_DATE','$TEXT_PROJECT_CAPACITY','$TEXT_PROJECT_CAPACITY_UNIT_CD','$TEXT_PROJECT_STATUS_CD','$txtremarks',$userid";
	
			// echo json_encode($query);exit();

			$data['query'] = $query;
			
			
			
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
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
			$data['message'] = 'data already exists.';
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
		$TEXT_ORG_ID  = $_POST['TEXT_ORG_ID'] == 'undefined' ? '' : $_POST['TEXT_ORG_ID'];
		

       $query = "SELECT 
	             PROJECT_ID
				,PROJECT_NAME
				,ORG_ID
				,ORG_NAME
				,LOC_ID
				,LOC_DESC
				,PROJECT_TYPE_CD
				,PROJECT_TYPE
				,PM_NAME
				,CONVERT(VARCHAR,PROJECT_START_DATE,106) PROJECT_START_DATE
				,CONVERT(VARCHAR,PROJECT_END_DATE,106) PROJECT_END_DATE
				,PROJECT_CAPACITY
				,PROJECT_CAPACITY_UNIT_CD
				,PROJECT_CAPACITY_UNIT
				,PROJECT_STATUS_CD
				,PROJECT_STATUS
				,REMARKS FROM PROJECTS  WHERE ISDELETED=0 AND ORG_ID =$TEXT_ORG_ID ";

		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['PROJECT_ID'] = (int) $row['PROJECT_ID'];
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

function getOrganization($mysqli){
	try
	{
		global $userid;

        $query = "SELECT ORG_ID, ORG_NAME  FROM ORGANIZATION
                  WHERE ISDELETED =0 AND ORG_ID IN (SELECT ORG_ID FROM USERS WHERE UID = $userid AND ISDELETED=0) ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ORG_ID'] = (int) $row['ORG_ID'];
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


function getProjecttype($mysqli){
	try
	{
		global $userid;

        $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=55 and isdeleted=0 order by code_detail_id desc ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
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


function getcapacityunit($mysqli){
	try
	{
		global $userid;

        $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=57 and isdeleted=0 order by code_detail_id desc ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
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


function getProjectstatus($mysqli){
	try
	{
		global $userid;

        $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=56 and isdeleted=0 order by code_detail_id desc ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('PROJECT_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [PROJECTS_SP] 3,$pmid,'','','','','','','','','','','',$userid ");
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







