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
		case "getComponentType": getComponentType($conn);break;
		
		
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
    
        $componentid  = ($_POST['componentid'] == 'undefined' || $_POST['componentid'] == '') ? 0 : $_POST['componentid'];
		$TEXT_COMPONENT_TYPE_CD  = $_POST['TEXT_COMPONENT_TYPE_CD'] == 'undefined' ? 0 : $_POST['TEXT_COMPONENT_TYPE_CD'];
       	$TEXT_COMPONENT_NAME  = $_POST['TEXT_COMPONENT_NAME'] == 'undefined' ? 0 : $_POST['TEXT_COMPONENT_NAME'];
     	
		
		$actionid = $componentid == 0 ? 1 : 2;

		
				$sql = "SELECT * FROM SALARY_COMPONENT_MASTER
		        WHERE COMPONENT_ID!=$componentid
				AND   COMPONENT_TYPE_CD   =  $TEXT_COMPONENT_TYPE_CD
				AND   COMPONENT_NAME      = '$TEXT_COMPONENT_NAME'
			 	AND   ISDELETED           = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SALARY_COMPONENT_MASTER_SP] 
												$actionid
												,$componentid
												,'$TEXT_COMPONENT_TYPE_CD'
												,'$TEXT_COMPONENT_NAME'
												,$userid 
												";
	
		
		   
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
				if(!empty($componentid))
				$data['message'] = 'Record successfully updated';
				else 
				$data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
		}
		echo json_encode($data);exit;

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
	

       		
       $query =     "SELECT 
							 COMPONENT_ID
							,COMPONENT_NAME
							,COMPONENT_TYPE_CD
							,COMPONENT_TYPE
							,IS_TAXABLE
							,IS_FIXED
							FROM SALARY_COMPONENT_MASTER
							WHERE ISDELETED	= 0
											";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['COMPONENT_ID'] = (int) $row['COMPONENT_ID'];
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



function getComponentType($mysqli){
	try
	{
	$data = array();
		
   
	$query = "SELECT CODE_DETAIL_ID, CODE_DETAIL_DESC FROM MEP_CODE_DETAILS 
			  where isdeleted=0  AND CODE_ID = 62";
	
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


function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $componentid = ($_POST['componentid'] == 'undefined' || $_POST['componentid'] == '') ? 0 : $_POST['componentid'];  

					
			if($componentid == 0){
				throw new Exception('COMPONENT_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SALARY_COMPONENT_MASTER_SP]	3,$componentid,'','',$userid ") ;
				
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







