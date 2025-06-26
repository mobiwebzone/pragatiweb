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
		
		case "getSubjects": getSubjects($conn);break;
		case "getMonth": getMonth($conn);break;
		case "delete":delete($conn);break;
		case "getTeacher": getTeacher($conn);break;
		case "getWeekday": getWeekday($conn);break;
		
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
    
        $marksid  = ($_POST['marksid'] == 'undefined' || $_POST['marksid'] == '') ? 0 : $_POST['marksid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];
		$TEXT_SUBJECT_CD  = $_POST['TEXT_SUBJECT_CD'] == 'undefined' ? 0 : $_POST['TEXT_SUBJECT_CD'];
		
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		$TEXT_MONTH_CD  = $_POST['TEXT_MONTH_CD'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_CD'];
		$TEXT_DAYOFWEEK_CD  = $_POST['TEXT_DAYOFWEEK_CD'] == 'undefined' ? 0 : $_POST['TEXT_DAYOFWEEK_CD'];
		
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $marksid == 0 ? 1 : 2;

				
				$sql = "SELECT * FROM TIMETABLE
		        WHERE TIMETABLE_ID!=$marksid
				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
				AND   CLASS_CD   = $TEXT_CLASS_CD
				AND   FY_YEAR_CD = $TEXT_FY_YEAR_CD
                AND   SUBJECT_ID = $TEXT_SUBJECT_CD
				AND   MONTH_CD   = $TEXT_MONTH_CD
				AND   DAYOFWEEK_CD = $TEXT_DAYOFWEEK_CD
				AND   TEACHER_ID   = $TEXT_TEACHER_ID
				AND   ISDELETED = 0 ";	
       
	//    throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SCHOOL_TIMETABLE_SP] $actionid,$marksid,$TEXT_SCHOOL_ID,$TEXT_FY_YEAR_CD,$TEXT_MONTH_CD,$TEXT_DAYOFWEEK_CD,'$TEXT_TEACHER_ID',$TEXT_CLASS_CD,$TEXT_SUBJECT_CD,'','','','',$userid,'$txtremarks' ";
		
	
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
				if(!empty($marksid))
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
		
        $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD_S  = $_POST['TEXT_CLASS_CD_S'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD_S'];
        
		$query =     "SELECT
					    TIMETABLE_ID
						,SCHOOL_ID
						,SCHOOL_NAME
						,FY_YEAR_CD
						,FY_YEAR
						,MONTH_CD
						,MONTH
						,DAYOFWEEK_CD
						,DAYOFWEEK
						,CLASS_CD
						,CLASS
						,SUBJECT_ID
						,SUBJECT
						,TEACHER_ID
						,TEACHER
						,CLASSROOM_ID
						,DATE
						,STARTTIME
						,ENDTIME
						,REMARKS
						from TIMETABLE 
						 WHERE ISDELETED  = 0 
						 AND   SCHOOL_ID  = $TEXT_SCHOOL_ID 
						  ";

 					if ($TEXT_CLASS_CD_S != '') {
		            $query .= " AND CLASS_CD = $TEXT_CLASS_CD_S "; 
			        }		

					// if ($TEXT_STUDENT_ID_S != '') {
		            // $query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID_S "; 
			        // }


        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TIMETABLE_ID'] = (int) $row['TIMETABLE_ID'];
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


function getMonth($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=47 and isdeleted=0";

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


function getWeekday($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=54 and isdeleted=0";

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



function getSubjects($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=41 and isdeleted=0";

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
            $marksid = ($_POST['marksid'] == 'undefined' || $_POST['marksid'] == '') ? 0 : $_POST['marksid'];  

					
			if($marksid == 0){
				throw new Exception('STUDENT_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_TIMETABLE_SP]	3,$marksid,'','','','','','','','','','','',$userid,'' ") ;
				
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







