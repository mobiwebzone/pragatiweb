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
		case "getOrganization":getOrganization($conn);break;
		case "getProjects":getProjects($conn);break;
		case "getTaskCategory":getTaskCategory($conn);break;

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
        $TEXT_TASK_CAT_ID  = $_POST['TEXT_TASK_CAT_ID'] == 'undefined' ? '' : $_POST['TEXT_TASK_CAT_ID'];
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? 0 : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID  = ($_POST['TEXT_PROJECT_ID'] == 'undefined' || $_POST['TEXT_PROJECT_ID'] == '') ? 0 : $_POST['TEXT_PROJECT_ID'];
        $TEXT_TASK_SUB_CAT  = $_POST['TEXT_TASK_SUB_CAT'] == 'undefined' ? '' : $_POST['TEXT_TASK_SUB_CAT'];
		
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		

	$sql = "SELECT * FROM ISSUES_SUB_CATEGORIES 
	        WHERE TASK_SUB_CAT_ID!=$pmid
			AND   TASK_CAT_ID   = $TEXT_TASK_CAT_ID 
			AND   PROJECT_ID    = $TEXT_PROJECT_ID
			AND   ORG_ID        = $TEXT_ORG_ID
			AND   TASK_SUB_CAT  = '$TEXT_TASK_SUB_CAT'
			AND   ISDELETED=0 ";

        // throw new Exception( $sql );
		
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ISSUES_SUB_CATEGORIES_PM] $actionid,$pmid,'$TEXT_TASK_CAT_ID','$TEXT_ORG_ID','$TEXT_PROJECT_ID','$TEXT_TASK_SUB_CAT','$txtremarks',$userid ";
			
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
        $query = "SELECT 
						 TASK_SUB_CAT_ID
						,ORG_ID
						,ORG_NAME
						,PROJECT_ID
						,PROJECT_NAME
						,TASK_CAT_ID
						,TASK_CAT
						,TASK_SUB_CAT
						,REMARKS 
						FROM ISSUES_SUB_CATEGORIES 
						WHERE ISDELETED  = 0
						AND   PROJECT_ID = $TEXT_PROJECT_ID
						AND   ORG_ID     = $TEXT_ORG_ID 
						 ORDER BY TASK_CAT_ID ";
						

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TASK_SUB_CAT_ID'] = (int) $row['TASK_SUB_CAT_ID'];
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

function getTaskCategory($mysqli){
	try
	{
		global $userid;
        $TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];

        $query =   "SELECT TASK_CAT_ID, TASK_CAT 
		            FROM ISSUES_MAIN_CATEGORIES 
					WHERE ORG_ID   = $TEXT_ORG_ID 
					AND PROJECT_ID = $TEXT_PROJECT_ID 
					AND ISDELETED=0 ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TASK_CAT_ID'] = (int) $row['TASK_CAT_ID'];
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
				throw new Exception('TASK_SUB_CAT_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [ISSUES_SUB_CATEGORIES_PM] 3,$pmid,'','','','','',$userid ");
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







