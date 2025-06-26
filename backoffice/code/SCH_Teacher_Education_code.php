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
		case "getTeacher": getTeacher($conn);break;
		case "getExam": getExam($conn);break;
		case "getYearofPassing": getYearofPassing($conn);break;
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
    
        $feesid  = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_BOARD_UNIVERSITY  = $_POST['TEXT_BOARD_UNIVERSITY'] == 'undefined' ? 0 : $_POST['TEXT_BOARD_UNIVERSITY'];
        $TEXT_EXAM_CD  = $_POST['TEXT_EXAM_CD'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_CD'];
		$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];
		$TEXT_YEAR_OF_PASSING_CD  = $_POST['TEXT_YEAR_OF_PASSING_CD'] == 'undefined' ? 0 : $_POST['TEXT_YEAR_OF_PASSING_CD'];
		$TEXT_FINAL_PERCENTAGE  = $_POST['TEXT_FINAL_PERCENTAGE'] == 'undefined' ? 0 : $_POST['TEXT_FINAL_PERCENTAGE'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $feesid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM TEACHER_EDUCATION
		        WHERE TEACHER_EDUCATION_ID!=$feesid
				AND   SCHOOL_ID 		  =  $TEXT_SCHOOL_ID
				AND   TEACHER_ID 		  =  $TEXT_TEACHER_ID
				and   YEAR_OF_PASSING_CD  =  $TEXT_YEAR_OF_PASSING_CD
				AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [TEACHER_EDUCATION_SP] $actionid,$feesid,$TEXT_SCHOOL_ID,$TEXT_TEACHER_ID,$TEXT_EXAM_CD,$TEXT_BOARD_UNIVERSITY,$TEXT_YEAR_OF_PASSING_CD,$TEXT_FINAL_PERCENTAGE,$userid,'$txtremarks' ";
		
				
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
				if(!empty($feesid))
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
	                 A.TEACHER_EDUCATION_ID
					,A.TEACHER_ID
					,A.SCHOOL_ID
					,A.SCHOOL_NAME
					,A.TEACHER_NAME
					,A.EXAM_CD
					,A.EXAM
					,A.BOARD_UNIVERSITY
					,A.YEAR_OF_PASSING_CD
					,A.YEAR_OF_PASSING
					,A.FINAL_PERCENTAGE
					,A.REMARKS
					from TEACHER_EDUCATION  , TEACHER B 
					WHERE A.ISDELETED = 0
					AND   B.ISDELETED = 0
					AND   B.ARCHIVED  = 0
					AND   A.STUDENT_ID = B.STUDENT_ID 
					AND   SCHOOL_ID = $TEXT_SCHOOL_ID ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TEACHER_EDUCATION_ID'] = (int) $row['TEACHER_EDUCATION_ID'];
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



function getYearofPassing($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=46 and isdeleted=0";

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



function getExam($mysqli){
	try
	{
	$data = array();
	
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=45 and isdeleted=0 order by CODE_DETAIL_ID";

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


function getTeacher($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];

	$query = "SELECT TEACHER_ID,TEACHER_NAME FROM TEACHER 
	          where  isdeleted = 0 
			  AND    ARCHIVED  = 0
			  AND SCHOOL_ID = $TEXT_SCHOOL_ID ORDER BY TEACHER_NAME"; 

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TEACHER_ID'] = (int) $row['TEACHER_ID'];
				
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
            $feesid = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];  

					
			if($feesid == 0){
				throw new Exception('STUDENT_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_EDUCATION_SP]	3,$feesid,'','','','','',NULL,$userid,'' ") ;
				
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







