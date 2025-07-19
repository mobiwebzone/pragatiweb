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
		case "getGradingScheme": getGradingScheme($conn);break;
		
		
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
    
        $gradingschemedetailid  = ($_POST['gradingschemedetailid'] == 'undefined' || $_POST['gradingschemedetailid'] == '') ? 0 : $_POST['gradingschemedetailid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_GRADING_SCHEME_ID  = $_POST['TEXT_GRADING_SCHEME_ID'] == 'undefined' ? 0 : $_POST['TEXT_GRADING_SCHEME_ID'];
		$TEXT_GRADE_NAME  = $_POST['TEXT_GRADE_NAME'] == 'undefined' ? 0 : $_POST['TEXT_GRADE_NAME'];
     	$TEXT_MARKS_FROM  = $_POST['TEXT_MARKS_FROM'] == 'undefined' ? 0 : $_POST['TEXT_MARKS_FROM'];
     	$TEXT_MARKS_TO  = $_POST['TEXT_MARKS_TO'] == 'undefined' ? 0 : $_POST['TEXT_MARKS_TO'];
     	$TEXT_REMARKS  = $_POST['TEXT_REMARKS'] == 'undefined' ? 0 : $_POST['TEXT_REMARKS'];
		
		$actionid = $gradingschemedetailid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM GRADING_SCHEME_DETAILS
		        WHERE GRADING_SCHEME_DETAIL_ID!=$gradingschemedetailid
				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
				AND   GRADING_SCHEME_ID   = $TEXT_GRADING_SCHEME_ID
				AND   GRADE_NAME    = '$TEXT_GRADE_NAME'
			 	AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [GRADING_SCHEME_DETAILS_SP] 
												 $actionid
												,$gradingschemedetailid
												,$TEXT_SCHOOL_ID
												,$TEXT_GRADING_SCHEME_ID
												,'$TEXT_GRADE_NAME'
												,$TEXT_MARKS_FROM
												,$TEXT_MARKS_TO
												,'$TEXT_REMARKS'
												,$userid 
												";
	
		
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
				if(!empty($gradingschemedetailid))
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
    $TEXT_GRADING_SCHEME_ID  = $_POST['TEXT_GRADING_SCHEME_ID'] == 'undefined' ? 0 : $_POST['TEXT_GRADING_SCHEME_ID'];
       		
       $query =         "SELECT 
						 A.GRADING_SCHEME_DETAIL_ID
						,A.SCHOOL_ID
						,A.GRADING_SCHEME_ID
						,A.GRADE_NAME
						,A.MARKS_FROM
						,A.MARKS_TO
						,A.REMARKS
						,B.SCHEME_NAME
						FROM GRADING_SCHEME_DETAILS A , GRADING_SCHEME_MASTER B
						WHERE A.GRADING_SCHEME_ID = B.GRADING_SCHEME_ID
						AND   A.SCHOOL_ID = B.SCHOOL_ID
						AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID 
						AND   A.GRADING_SCHEME_ID = $TEXT_GRADING_SCHEME_ID 
						AND   A.ISDELETED = 0
						AND   B.ISDELETED = 0 ORDER BY A.GRADE_NAME ";

        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['GRADING_SCHEME_DETAIL_ID'] = (int) $row['GRADING_SCHEME_DETAIL_ID'];
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


function getGradingScheme($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
   
	$query = "SELECT GRADING_SCHEME_ID,SCHEME_NAME FROM GRADING_SCHEME_MASTER where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0
	         ";
	
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['GRADING_SCHEME_ID'] = (int) $row['GRADING_SCHEME_ID'];
				
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
            $gradingschemedetailid = ($_POST['gradingschemedetailid'] == 'undefined' || $_POST['gradingschemedetailid'] == '') ? 0 : $_POST['gradingschemedetailid'];  

					
			if($gradingschemedetailid == 0){
				throw new Exception('GRADING_SCHEME_DETAIL_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [GRADING_SCHEME_DETAILS_SP]	3,$gradingschemedetailid,'','','','','','',$userid ") ;
				
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







