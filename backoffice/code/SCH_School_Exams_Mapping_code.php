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
		case "getExamType":getExamType($conn);break;
		case "getCalculationFlag":getCalculationFlag($conn);break;
	
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
	    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_EXAM_ID = $_POST['TEXT_EXAM_ID'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_ID'];
		$TEXT_CALCULATION_FLAG_CD  = $_POST['TEXT_CALCULATION_FLAG_CD'] == 'undefined' ? 0 : $_POST['TEXT_CALCULATION_FLAG_CD'];
		
		
		$actionid = $examsid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM SCHOOL_EXAMS_MAPPING
		        WHERE SCHOOL_EXAMS_MAPPING_ID!=$examsid
				AND   SCHOOL_ID    = $TEXT_SCHOOL_ID
				AND   EXAM_ID      = $TEXT_EXAM_ID
				AND   ISDELETED    = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SCHOOL_EXAMS_MAPPING_SP] 
											$actionid
											,$examsid
											,$TEXT_SCHOOL_ID
											,$TEXT_EXAM_ID
											,$TEXT_CALCULATION_FLAG_CD
											,$userid ";
	
		
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
	
      $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
       		
       $query =     "SELECT 
							A.SCHOOL_EXAMS_MAPPING_ID
							,A.SCHOOL_ID
							,A.EXAM_ID
							,A.CALCULATION_FLAG_CD
							,A.CALCULATION_FLAG
							,B.EXAM_NAME
							,C.SCHOOL_NAME
							FROM SCHOOL_EXAMS_MAPPING A , EXAMS_MASTER B , SCHOOL C
							WHERE A.EXAM_ID = B.EXAM_ID
							AND   A.SCHOOL_ID = C.SCHOOL_ID
							AND   A.ISDELETED = 0
							AND   B.ISDELETED = 0
							AND   C.ISDELETED = 0 
							AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID
							ORDER BY A.SCHOOL_ID,A.EXAM_ID";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['SCHOOL_EXAMS_MAPPING_ID'] = (int) $row['SCHOOL_EXAMS_MAPPING_ID'];
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



function getExamType($mysqli){
	try
	{
		
	$query = "SELECT EXAM_ID,EXAM_NAME FROM EXAMS_MASTER where  isdeleted=0 ";
	

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['EXAM_ID'] = (int) $row['EXAM_ID'];
				
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

function getCalculationFlag($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20and isdeleted=0 ";
	

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
            $examsid = ($_POST['examsid'] == 'undefined' || $_POST['examsid'] == '') ? 0 : $_POST['examsid'];  

					
			if($examsid == 0){
				throw new Exception('SCHOOL_EXAMS_MAPPING_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_EXAMS_MAPPING_SP]	3,$examsid,'','','' ,$userid ") ;
				
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







