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
		case "getUserRole":getUserRole($conn);break;
        case "getMenu":getMenu($conn);break;
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
    	$TEXT_MENUID  = ($_POST['TEXT_MENUID'] == 'undefined' || $_POST['TEXT_MENUID'] == '') ? 0 : $_POST['TEXT_MENUID'];
		$TEXT_USER_ROLE_CD  = ($_POST['TEXT_USER_ROLE_CD'] == 'undefined' || $_POST['TEXT_USER_ROLE_CD'] == '') ? 0 : $_POST['TEXT_USER_ROLE_CD'];

		$actionid = $pmid == 0 ? 1 : 2;
		
	    $sql = "SELECT * FROM MENU_TEMP
	        WHERE MENU_TEMP_ID!=$pmid 
			AND   MENU_ID     = $TEXT_MENUID
			AND   USER_ROLE_CD   =  $TEXT_USER_ROLE_CD
			 ";

        // throw new Exception( $sql );
		
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [PM_CREATE_MENU_ROLE_SP] $actionid,$pmid,'$TEXT_MENUID','$TEXT_USER_ROLE_CD' ";
			
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
			$data['message'] = 'Mapping of the User with this Project already done!.';
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
		
	     $query =   "SELECT 
					A.MENU_TEMP_ID AS MENU_TEMP_ID
					,A.MENU_ID AS MENU_ID
					,A.USER_ROLE_CD AS USER_ROLE_CD
					,B.MENU AS  MENU
					,C.CODE_DETAIL_DESC AS USER_ROLE
					FROM MENU_TEMP A , BO_MENU B , MEP_CODE_DETAILS C
					WHERE A.MENU_ID =B.MENUID
					AND   A.USER_ROLE_CD = C.CODE_DETAIL_ID ORDER BY CODE_DETAIL_DESC
					";
						

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['MENU_TEMP_ID'] = (int) $row['MENU_TEMP_ID'];
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



function getUserRole($mysqli){
	try
	{
		global $userid;

        $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=53 and isdeleted=0 order by code_detail_id desc ";

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


function getMenu($mysqli){
	try
	{
		
	
	$query =    "SELECT MENUID, MENU 
				FROM  BO_MENU 
				WHERE ISDELETED = 0
				 ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MENUID'] = (int) $row['MENUID'];
				
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
				throw new Exception('MENU_TEMP_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [PM_CREATE_MENU_ROLE_SP] 3,$pmid,'','' ");
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







