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
		case "getClass": getClass($conn);break;
		case "getExamType": getExamType($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "delete":delete($conn);break;
		case "getQueryFinal":getQueryFinal($conn);break;
		case "getStudent": getStudent($conn);break;
		
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
    
        $weightageid  = ($_POST['weightageid'] == 'undefined' || $_POST['weightageid'] == '') ? 0 : $_POST['weightageid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
    	$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
	  
		
		$query="EXEC [CALCULATE_FINAL_RESULT_SP] 
										   $TEXT_SCHOOL_ID
										  ,$TEXT_CLASS_CD
										  ,$TEXT_FY_YEAR_CD
										  ,$userid  
										  ";
	
			
		  	$data['$sql'] = $query;
		   
		
			$stmt = sqlsrv_query($mysqli, $query);

				if ($stmt === false) {
					$errors = sqlsrv_errors();
					$cleanMessage = 'SQL execution failed.';
					
					if (isset($errors[0]['message'])) {
						// Remove driver info from message
						$msgParts = explode(']', $errors[0]['message']);
						$cleanMessage = trim(end($msgParts));
					}

					$data['success'] = false;
					$data['message'] = $cleanMessage;
					echo json_encode($data); exit;
				} else {
					// ✅ Success case handled here
					$data['success'] = true;
					$data['message'] = 'Final Result Prepared.';
					echo json_encode($data); exit;
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
	$TEXT_SCHOOL_ID   = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	$TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];	
	$TEXT_FY_YEAR_CD   = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];	
   $TEXT_STUDENT_ID = ($_POST['TEXT_STUDENT_ID'] == 'undefined' || $_POST['TEXT_STUDENT_ID'] == '') ? 0 : $_POST['TEXT_STUDENT_ID'];

       $query =     " SELECT
							 A.STUDENT_ID
							,(C.STUDENT_FIRST_NAME + ' ' + ISNULL(C.STUDENT_LAST_NAME, '')) AS STUDENT_NAME
							,A.SCHOOL_ID
							,A.CLASS_CD
							,A.FY_YEAR_CD
							,A.SCHOOL_SUBJECT_ID
							,A.FINAL_PERCENTAGE
							,A.FINAL_GRADE
							,B.SUBJECT 
							,B.CLASS
							FROM FINAL_RESULT A , SCHOOL_SUBJECTS B , STUDENT C
							WHERE A.SCHOOL_SUBJECT_ID = B.SCHOOL_SUBJECT_ID 
							AND   A.SCHOOL_ID         = B.SCHOOL_ID
							AND   B.ISDELETED         = 0
							AND   A.STUDENT_ID        = C.STUDENT_ID
							AND   A.SCHOOL_ID  = $TEXT_SCHOOL_ID
							AND   A.CLASS_CD   = $TEXT_CLASS_CD
							AND   A.FY_YEAR_CD = $TEXT_FY_YEAR_CD 
						      ";
        
							if ($TEXT_STUDENT_ID != '') {
								$query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID"; 
								}
							$query  .= " ORDER BY  A.STUDENT_ID ";

		
        $result = sqlsrv_query($mysqli, $query); 
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
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


function getQueryFinal($mysqli){
		try
	{
	$data = array();
	$TEXT_SCHOOL_ID   = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	$TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];	
	$TEXT_FY_YEAR_CD   = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];	
   $TEXT_STUDENT_ID = ($_POST['TEXT_STUDENT_ID'] == 'undefined' || $_POST['TEXT_STUDENT_ID'] == '') ? 0 : $_POST['TEXT_STUDENT_ID'];


       $query =     " SELECT
							 A.STUDENT_ID
							,(B.STUDENT_FIRST_NAME + ' ' + ISNULL(B.STUDENT_LAST_NAME, '')) AS STUDENT_NAME
							,A.SCHOOL_ID
							,A.CLASS_CD
							,A.FY_YEAR_CD
							,A.OVERALL_PERCENTAGE
							,A.OVERALL_GRADE
							,B.CLASS
							FROM OVERALL_RESULT A  , STUDENT B
							WHERE A.SCHOOL_ID         = B.SCHOOL_ID
							AND   A.STUDENT_ID        = B.STUDENT_ID
							AND   B.ISDELETED         = 0
							AND   A.SCHOOL_ID  = $TEXT_SCHOOL_ID
							AND   A.CLASS_CD   = $TEXT_CLASS_CD
							AND   A.FY_YEAR_CD = $TEXT_FY_YEAR_CD 
						      ";
        
	                        if ($TEXT_STUDENT_ID != '') {
								$query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID"; 
								}
							$query  .= " ORDER BY  A.STUDENT_ID ";
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
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

function getStudent($mysqli){
	

	try
	{
	$data = array();	
	
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
    $TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	
	$query = "SELECT 
	              STUDENT_ID
				  ,(STUDENT_FIRST_NAME+' '+STUDENT_LAST_NAME) STUDENT_NAME 
	         FROM  STUDENT  
			 where ISDELETED = 0
			 AND   ARCHIVED =  0
			 and   CLASS_CD = $TEXT_CLASS_CD 
			 and   SCHOOL_ID = $TEXT_SCHOOL_ID ";

		$data['query'] = $query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
				
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


function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0 order by CODE_DETAIL_ID desc";

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




function getExamType($mysqli){
	try
	{
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	

	$query = "SELECT EXAM_ID,EXAM_NAME FROM EXAMS_MASTER 
	          where SCHOOL_ID = $TEXT_SCHOOL_ID 
			  and isdeleted   = 0 ";

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



function getClass($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
   
	$query = "SELECT CLASS_CD,CLASS FROM  SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0 order by SCHOOL_CLASS_ID";
	
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLASS_CD'] = (int) $row['CLASS_CD'];
				
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
    try {
        global $userid;
        $data = array();

        $weightageid = ($_POST['weightageid'] == 'undefined' || $_POST['weightageid'] == '') ? 0 : $_POST['weightageid'];

        // Set default 0s instead of empty strings for numeric parameters
        $stmt = sqlsrv_query($mysqli, "EXEC [EXAM_WEIGHTAGE_MASTER_SP] 3, $weightageid, 0, 0, 0, 0, 0, $userid");

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            throw new Exception($errors[0]['message']);
        } else {
            $data['success'] = true;
            $data['message'] = 'Record successfully deleted';
        }

        echo json_encode($data);
        exit;

    } catch (Exception $e) {
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







