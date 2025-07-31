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
	    $TEXT_EXAM_ID  = $_POST['TEXT_EXAM_ID'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_ID'];
		$TEXT_WEIGHTAGE_PERCENT = isset($_POST['TEXT_WEIGHTAGE_PERCENT']) && is_numeric($_POST['TEXT_WEIGHTAGE_PERCENT'])
                                 ? floatval($_POST['TEXT_WEIGHTAGE_PERCENT']): 0;

		
		$actionid = $weightageid == 0 ? 1 : 2;
	
				$sql = "SELECT * FROM EXAM_WEIGHTAGE_MASTER
		        WHERE WEIGHTAGE_ID!=$weightageid
				AND   SCHOOL_ID  =  $TEXT_SCHOOL_ID
				AND   CLASS_CD   =  $TEXT_CLASS_CD
			    AND   FY_YEAR_CD =  $TEXT_FY_YEAR_CD
				AND   EXAM_ID    =  $TEXT_EXAM_ID
				AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
		   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [EXAM_WEIGHTAGE_MASTER_SP] 
										  $actionid
										  ,$weightageid
										  ,$TEXT_SCHOOL_ID
										  ,$TEXT_CLASS_CD
										  ,$TEXT_EXAM_ID
										  ,$TEXT_WEIGHTAGE_PERCENT
										  ,$TEXT_FY_YEAR_CD
										  ,$userid  ";
	
		
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
				if(!empty($schoolsubjectid))
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
	$TEXT_SCHOOL_ID   = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	$TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];	
	$TEXT_FY_YEAR_CD   = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];	
       		
       $query =     "SELECT 
							 A.WEIGHTAGE_ID
							,A.SCHOOL_ID
							,A.CLASS_CD
							,A.CLASS
							,A.EXAM_ID
							,A.WEIGHTAGE_PERCENT
							,A.FY_YEAR_CD
							,A.FY_YEAR
							,B.EXAM_NAME
							FROM EXAM_WEIGHTAGE_MASTER A , EXAMS_MASTER B
							WHERE A.EXAM_ID    = B.EXAM_ID
							AND   A.ISDELETED  = 0
							AND   B.ISDELETED  = 0  
							AND   A.SCHOOL_ID  = $TEXT_SCHOOL_ID
						      ";
        
	
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['WEIGHTAGE_ID'] = (int) $row['WEIGHTAGE_ID'];
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

	$query = "SELECT 	 A.EXAM_ID
							,B.EXAM_NAME
							FROM SCHOOL_EXAMS_MAPPING A , EXAMS_MASTER B , SCHOOL C
							WHERE A.EXAM_ID = B.EXAM_ID
							AND   A.SCHOOL_ID = C.SCHOOL_ID
							AND   A.ISDELETED = 0
							AND   B.ISDELETED = 0
							AND   C.ISDELETED = 0 
							AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID
							ORDER BY A.EXAM_ID ";

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







