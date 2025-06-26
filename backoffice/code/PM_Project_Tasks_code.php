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
		case "getOrganization":getOrganization($conn);break;
		case "getProjects":getProjects($conn);break;
		case "getAssignedToUser":getAssignedToUser($conn);break;
		case "getTaskMainCategory":getTaskMainCategory($conn);break;
		case "getTaskCategory":getTaskCategory($conn);break;
		case "getQuery":getQuery($conn);break;
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
		
		$data = array();
		$pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        $TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? 0 : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID  = ($_POST['TEXT_PROJECT_ID'] == 'undefined' || $_POST['TEXT_PROJECT_ID'] == '') ? 0 : $_POST['TEXT_PROJECT_ID'];
        
		$TEXT_ASSIGNED_TO_ID  = ($_POST['TEXT_ASSIGNED_TO_ID'] == 'undefined' || $_POST['TEXT_ASSIGNED_TO_ID'] == '') ? 0 : $_POST['TEXT_ASSIGNED_TO_ID'];

		$TEXT_TASK_CAT_ID  = ($_POST['TEXT_TASK_CAT_ID'] == 'undefined' || $_POST['TEXT_TASK_CAT_ID'] == '') ? 0 : $_POST['TEXT_TASK_CAT_ID'];
		
		$TEXT_TASK_SUB_CAT_ID  = ($_POST['TEXT_TASK_SUB_CAT_ID'] == 'undefined' || $_POST['TEXT_TASK_SUB_CAT_ID'] == '') ? '' : $_POST['TEXT_TASK_SUB_CAT_ID'];
    	$txtStartDT  = ($_POST['txtStartDT'] == 'undefined' || $_POST['txtStartDT'] == '') ? '' : $_POST['txtStartDT'];
        $txtEndDT  = ($_POST['txtEndDT'] == 'undefined' || $_POST['txtEndDT'] == '') ? '' : $_POST['txtEndDT'];
		$txtTaskDesc  = ($_POST['txtTaskDesc'] == 'undefined' || $_POST['txtTaskDesc'] == '') ? '' : $_POST['txtTaskDesc'];
        $remarks = ($_POST['remarks'] == 'undefined' || $_POST['remarks'] == '') ? '' : $_POST['remarks'];

		$actionid = $pmid == 0 ? 1 : 2;
		
		$sql = "SELECT * FROM TASK_USERS 
		        WHERE TASK_ID!=$pmid 
				AND   ORG_ID = $TEXT_ORG_ID
				AND   PROJECT_ID = $TEXT_PROJECT_ID
				AND   TASK_CAT_ID = $TEXT_TASK_CAT_ID
				AND   TASK_SUB_CAT_ID    = $TEXT_TASK_SUB_CAT_ID 
				AND   TASK_DESC      = '$txtTaskDesc'
				AND   ASSIGNED_TO_ID = $TEXT_ASSIGNED_TO_ID
				AND   ISDELETED  = 0 " ;
		  
		// throw new Exception( $sql );
		
		$row_count = unique($sql);
		$data['sql'] = $sql;
		
		if($row_count == 0)
		{
			$query="EXEC [CREATE_TASKS_SP] $actionid,$pmid,$TEXT_ORG_ID,$TEXT_PROJECT_ID, $TEXT_TASK_CAT_ID, $TEXT_TASK_SUB_CAT_ID, '$TEXT_ASSIGNED_TO_ID',
			    '','$txtStartDT','$txtEndDT','$txtTaskDesc','$remarks',$userid";
			
				// echo json_encode($query);exit;
		
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
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
		$TEXT_TASK_CAT_ID_S  = ($_POST['TEXT_TASK_CAT_ID_S'] == 'undefined' || $_POST['TEXT_TASK_CAT_ID_S'] == '') ? '' : $_POST['TEXT_TASK_CAT_ID_S'];
		

       $query =    " SELECT 
						TASK_ID,
						ORG_ID,
						ORG_NAME,
						PROJECT_ID,
						PROJECT_NAME,
						TASK_CAT_ID,
						TASK_CAT,
						TASK_SUB_CAT_ID,
						TASK_SUB_CAT,
						ASSIGNED_TO_ID,
						ASSIGNED_TO,
						CASE 
							WHEN NULLIF(STARTDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, STARTDATE, 106)
						END AS STARTDATE,
						CASE 
							WHEN NULLIF(ENDDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, ENDDATE, 106)
						END AS ENDDATE,
						TASK_DESC,
						TASK_STATUS_CD,
						TASK_STATUS,
						REMARKS
					FROM 
						TASK_USERS  
					WHERE 
						ISDELETED = 0

					AND   ORG_ID = $TEXT_ORG_ID
					AND   PROJECT_ID = $TEXT_PROJECT_ID
					  ";
        
		if ($TEXT_TASK_CAT_ID_S != '') {
			$query .= " AND TASK_CAT_ID = $TEXT_TASK_CAT_ID_S "; 

			}
	     	
		
			
		$query  .= " ORDER BY TASK_CAT_ID,TASK_ID ";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();

		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TASK_ID'] = (int) $row['TASK_ID'];
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
                  WHERE ISDELETED =0 AND ORG_ID IN (SELECT ORG_ID FROM ORGANIZATION_PROJECT_USER WHERE USER_ID = $userid AND ISDELETED=0) ";

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

function getProjects($mysqli){
	try
	{
		global $userid;
        $TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
        $query =   "SELECT PROJECT_ID, PROJECT_NAME  FROM PROJECTS
					WHERE ISDELETED =0 
					AND PROJECT_ID IN (SELECT PROJECT_ID FROM ORGANIZATION_PROJECT_USER 
				    WHERE USER_ID = $userid
				    AND ORG_ID = $TEXT_ORG_ID
				    AND ISDELETED=0) ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
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

function getTaskMainCategory($mysqli){
	try
	{
		$data = array();
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
		$TEXT_TASK_CAT_ID_S = ($_POST['TEXT_TASK_CAT_ID_S'] =='undefined' || $_POST['TEXT_TASK_CAT_ID_S'] =='') ? 0 : $_POST['TEXT_TASK_CAT_ID_S'];

		$query = "SELECT 
		          TASK_CAT_ID
				 ,TASK_CAT 
				FROM TASK_MAIN_CATEGORIES  
				WHERE ISDELETED=0 
				AND   ORG_ID = $TEXT_ORG_ID
				AND   PROJECT_ID = $TEXT_PROJECT_ID ";
		
		if ($TEXT_TASK_CAT_ID_S != '') {
			$query .= " AND TASK_CAT_ID = $TEXT_TASK_CAT_ID_S "; 
			}
	    $query  .= " ORDER BY TASK_CAT_ID ";



		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASK_CAT_ID'] = (int) $row['TASK_CAT_ID'];
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
/*============ Get Task Main Category Master=============*/ 



/*============ GET TASK SUB CATEGORY =============*/ 
function getTaskCategory($mysqli){
	try
	{
		$data = array();
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
		$TEXT_TASK_CAT_ID = ($_POST['TEXT_TASK_CAT_ID'] =='undefined' || $_POST['TEXT_TASK_CAT_ID'] =='') ? 0 : $_POST['TEXT_TASK_CAT_ID'];
		
		
		$query = "SELECT TASK_SUB_CAT_ID,TASK_SUB_CAT
				  FROM TASK_SUB_CATEGORIES 
				  WHERE ISDELETED=0 
				  AND   TASK_CAT_ID= $TEXT_TASK_CAT_ID
				  AND   ORG_ID     = $TEXT_ORG_ID 
				  AND   PROJECT_ID = $TEXT_PROJECT_ID ORDER BY TASK_SUB_CAT_ID ";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASK_SUB_CAT_ID'] = (int) $row['TASK_SUB_CAT_ID'];
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



/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
		$query = "EXEC [CREATE_TASKS_SP] 3,$pmid,'','','','','','','','','','',$userid ";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
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
/* =========== DELETE =========== */ 



function getAssignedToUser($mysqli){
	try
	{
	
	$data = array();
	$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];	
	$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
	
	$query =    "SELECT A.UID, (A.FIRSTNAME+' '+A.LASTNAME) USERNAME 
				FROM  USERS A ,ORGANIZATION_PROJECT_USER B
				WHERE A.UID = B.USER_ID
				AND   B.ORG_ID = $TEXT_ORG_ID
				AND   B.PROJECT_ID = $TEXT_PROJECT_ID ";

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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







