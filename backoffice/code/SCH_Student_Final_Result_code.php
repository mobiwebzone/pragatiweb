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
		case "getStudent": getStudent($conn);break;
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
    
        $resultid  = ($_POST['resultid'] == 'undefined' || $_POST['resultid'] == '') ? 0 : $_POST['resultid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		$TEXT_TOTAL_MARKS  = $_POST['TEXT_TOTAL_MARKS'] == 'undefined' ? 0 : $_POST['TEXT_TOTAL_MARKS'];
		$TEXT_MARKS_OBTAINED  = $_POST['TEXT_MARKS_OBTAINED'] == 'undefined' ? 0 : $_POST['TEXT_MARKS_OBTAINED'];
		$TEXT_TOTAL_DAYS  = $_POST['TEXT_TOTAL_DAYS'] == 'undefined' ? 0 : $_POST['TEXT_TOTAL_DAYS'];
		$TEXT_PRESENT_DAYS  = $_POST['TEXT_PRESENT_DAYS'] == 'undefined' ? 0 : $_POST['TEXT_PRESENT_DAYS'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $resultid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM STUDENT_FINAL_RESULT 
		        WHERE RESULT_ID!=$resultid
				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
				AND   STUDENT_ID = $TEXT_STUDENT_ID
				and   CLASS_CD   = $TEXT_CLASS_CD
				and   FY_YEAR_CD = $TEXT_FY_YEAR_CD
				AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [FINAL_RESULT_SP] $actionid,$resultid,$TEXT_STUDENT_ID,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_FY_YEAR_CD,$TEXT_TOTAL_MARKS,$TEXT_MARKS_OBTAINED,$TEXT_TOTAL_DAYS,$TEXT_PRESENT_DAYS,$userid,'$txtremarks' ";
		
	
			
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
				if(!empty($resultid))
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
		
       		
       $query =     "SELECT RESULT_ID
						,SCHOOL_ID
						,CLASS_CD
						,CLASS
						,STUDENT_ID
						,(STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
						,SCHOLAR_NO
						,FY_YEAR_CD
						,FY_YEAR
						,TOTAL_MARKS
						,MARKS_OBTAINED
						,MARKS_PERCENTAGE
						,GRADE_CD
						,GRADE
						,TOTAL_DAYS
						,PRESENT_DAYS
						,REMARKS
						from STUDENT_FINAL_RESULT WHERE ISDELETED=0 ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['RESULT_ID'] = (int) $row['RESULT_ID'];
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
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0";

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



function getStudent($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	
	$query = "SELECT STUDENT_ID, 
	        (STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
			FROM STUDENT 
			where  isdeleted=0 
			and SCHOOL_ID = $TEXT_SCHOOL_ID
			and CLASS_CD  = $TEXT_CLASS_CD ";

		$data = array();
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


function getClass($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0 order by CODE_DETAIL_ID";

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
            $resultid = ($_POST['resultid'] == 'undefined' || $_POST['resultid'] == '') ? 0 : $_POST['resultid'];  
			if($resultid == 0){
				throw new Exception('RESULT_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "[FINAL_RESULT_SP]	3,$resultid,'','','','','','','','',$userid,'' ") ;
			
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







