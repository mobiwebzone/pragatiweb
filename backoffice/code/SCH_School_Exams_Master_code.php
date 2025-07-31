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
    
        $examsid  = ($_POST['examsid'] == 'undefined' || $_POST['examsid'] == '') ? 0 : $_POST['examsid'];
	  
		$TEXT_EXAM_NAME  = $_POST['TEXT_EXAM_NAME'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_NAME'];
		
		
		$actionid = $examsid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM EXAMS_MASTER
		        WHERE EXAM_ID!=$examsid
				AND   EXAM_NAME  = '$TEXT_EXAM_NAME'
				AND   ISDELETED    = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [EXAMS_MASTER_SP] $actionid,$examsid,'$TEXT_EXAM_NAME',$userid ";
	
		
		$data['$sql'] = $query;
		
		   
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
				if(!empty($examsid))
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
						  EXAM_ID
						  ,EXAM_NAME
						  ,EXAM_DATE
						  ,ISDELETED
						from EXAMS_MASTER 
						WHERE ISDELETED = 0
						ORDER BY EXAM_ID";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['EXAM_ID'] = (int) $row['EXAM_ID'];
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





function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $examsid = ($_POST['examsid'] == 'undefined' || $_POST['examsid'] == '') ? 0 : $_POST['examsid'];  

					
			if($examsid == 0){
				throw new Exception('EXAM_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [EXAMS_MASTER_SP]	3,$examsid,'' ,$userid ") ;
				
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







