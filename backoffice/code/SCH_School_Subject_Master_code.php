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
    
        $schoolsubjectid  = ($_POST['schoolsubjectid'] == 'undefined' || $_POST['schoolsubjectid'] == '') ? 0 : $_POST['schoolsubjectid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
    	$TEXT_SUBJECT_CD  = $_POST['TEXT_SUBJECT_CD'] == 'undefined' ? 0 : $_POST['TEXT_SUBJECT_CD'];
	    $TEXT_GRADING_SCHEME_ID  = $_POST['TEXT_GRADING_SCHEME_ID'] == 'undefined' ? 0 : $_POST['TEXT_GRADING_SCHEME_ID'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $schoolsubjectid == 0 ? 1 : 2;
	
				$sql = "SELECT * FROM SCHOOL_SUBJECTS
		        WHERE SCHOOL_SUBJECT_ID!=$schoolsubjectid
				AND   SCHOOL_ID  =  $TEXT_SCHOOL_ID
				AND   CLASS_CD   = $TEXT_CLASS_CD
			    AND   SUBJECT    = '$TEXT_SUBJECT_CD'
				AND   GRADING_SCHEME_ID = $TEXT_GRADING_SCHEME_ID
				AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SCHOOL_SUBJECTS_SP] $actionid
										  ,$schoolsubjectid
										  ,$TEXT_SCHOOL_ID
										  ,$TEXT_CLASS_CD
										  ,'$TEXT_SUBJECT_CD'
										  ,$userid
										  ,'$txtremarks'
										  ,$TEXT_GRADING_SCHEME_ID ";
	
		
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
						 A.SCHOOL_SUBJECT_ID
						,A.SCHOOL_ID
						,A.SCHOOL_NAME
						,A.CLASS_CD
						,A.CLASS
						,A.SUBJECT
						,A.REMARKS
						,A.GRADING_SCHEME_ID
						,B.SCHEME_NAME
						,A.REMARKS
						from SCHOOL_SUBJECTS A, GRADING_SCHEME_MASTER B
						 WHERE A.SCHOOL_ID = B.SCHOOL_ID
						 AND   A.GRADING_SCHEME_ID = B.GRADING_SCHEME_ID
						 AND   A.ISDELETED   = 0
						 AND   B.ISDELETED = 0 
						 AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID
						 AND   A.CLASS_CD = $TEXT_CLASS_CD ";
        
	
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['SCHOOL_SUBJECT_ID'] = (int) $row['SCHOOL_SUBJECT_ID'];
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
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	

	$query = "SELECT GRADING_SCHEME_ID,SCHEME_NAME FROM GRADING_SCHEME_MASTER 
	          where SCHOOL_ID = $TEXT_SCHOOL_ID 
			  and isdeleted   = 0 ";

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
            $schoolsubjectid = ($_POST['schoolsubjectid'] == 'undefined' || $_POST['schoolsubjectid'] == '') ? 0 : $_POST['schoolsubjectid'];  

					
			if($schoolsubjectid == 0){
				throw new Exception('SCHOOL_SUBJECT_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_SUBJECTS_SP]	3,$schoolsubjectid,'','','',$userid,'','' ") ;
				
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







