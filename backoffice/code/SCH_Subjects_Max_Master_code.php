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
		case "getSubjects": getSubjects($conn);break;
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
    
        $subjectmaxmarksid  = ($_POST['subjectmaxmarksid'] == 'undefined' || $_POST['subjectmaxmarksid'] == '') ? 0 : $_POST['subjectmaxmarksid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
    	$TEXT_SUBJECT_CD  = $_POST['TEXT_SUBJECT_CD'] == 'undefined' ? 0 : $_POST['TEXT_SUBJECT_CD'];
	    $TEXT_EXAM_ID  = $_POST['TEXT_EXAM_ID'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_ID'];
		// $TEXT_MAX_MARKS  = $_POST['TEXT_MAX_MARKS'] == 'undefined' ? '' : $_POST['TEXT_MAX_MARKS'];
		$TEXT_MAX_MARKS = isset($_POST['TEXT_MAX_MARKS']) && is_numeric($_POST['TEXT_MAX_MARKS'])
                                 ? floatval($_POST['TEXT_MAX_MARKS']): 0;
		
		$actionid = $subjectmaxmarksid == 0 ? 1 : 2;
	
				$sql = "SELECT * FROM SUBJECT_MAX_MARKS
		        WHERE SUBJECT_MAX_MARKS_ID!=$subjectmaxmarksid
				AND   SCHOOL_ID  =  $TEXT_SCHOOL_ID
				AND   CLASS_CD   =  $TEXT_CLASS_CD
			    AND   SCHOOL_SUBJECT_ID =  $TEXT_SUBJECT_CD
				AND   EXAM_ID    =  $TEXT_EXAM_ID
				AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
		   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SUBJECT_MAX_MARKS_SP] $actionid
										  ,$subjectmaxmarksid
										  ,$TEXT_SCHOOL_ID
										  ,$TEXT_CLASS_CD
										  ,$TEXT_SUBJECT_CD
										  ,$TEXT_EXAM_ID
										  ,$TEXT_MAX_MARKS
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
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
       		
       $query =     "SELECT 
							A.SUBJECT_MAX_MARKS_ID
							,A.SCHOOL_ID
							,A.CLASS_CD
							,A.SCHOOL_SUBJECT_ID
							,A.EXAM_ID
							,A.MAX_MARKS
							,B.CLASS
							,C.SUBJECT
							,D.EXAM_NAME
							FROM SUBJECT_MAX_MARKS A , SCHOOL_CLASSES B, School_Subjects C, EXAMS_MASTER D
							WHERE A.CLASS_CD = B.CLASS_CD
							AND   A.SCHOOL_ID = B.SCHOOL_ID
							AND   A.SCHOOL_SUBJECT_ID = C.SCHOOL_SUBJECT_ID
							AND   A.SCHOOL_ID = C.SCHOOL_ID
							AND   A.EXAM_ID    = D.EXAM_ID
							AND   A.ISDELETED =0
							AND   B.ISDELETED =0
							AND   C.ISDELETED =0
							AND   D.ISDELETED =0
							AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID
						    AND   A.CLASS_CD = $TEXT_CLASS_CD ";
        
	
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['SUBJECT_MAX_MARKS_ID'] = (int) $row['SUBJECT_MAX_MARKS_ID'];
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


function getSubjects($mysqli){
	try
	{

	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];	

	$query = "SELECT SCHOOL_SUBJECT_ID , SUBJECT FROM SCHOOL_SUBJECTS 
	          WHERE ISDELETED =0 
			  AND SCHOOL_ID = $TEXT_SCHOOL_ID
			  AND CLASS_CD  = $TEXT_CLASS_CD
              ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SCHOOL_SUBJECT_ID'] = (int) $row['SCHOOL_SUBJECT_ID'];
				
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
	

	$query = "SELECT EXAM_ID,EXAM_NAME FROM EXAMS_MASTER 
	          where  isdeleted   = 0 ";

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
	try{   
			global $userid;
			$data = array();     
            $subjectmaxmarksid = ($_POST['subjectmaxmarksid'] == 'undefined' || $_POST['subjectmaxmarksid'] == '') ? 0 : $_POST['subjectmaxmarksid'];  

					
			if($subjectmaxmarksid == 0){
				throw new Exception('SUBJECT_MAX_MARKS_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SUBJECT_MAX_MARKS_SP]3,$subjectmaxmarksid,'','','','','',$userid ") ;
				
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







