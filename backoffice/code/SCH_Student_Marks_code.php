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
		
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		case "getClass": getClass($conn);break;
		case "getStudent": getStudent($conn);break;
		
		case "getExaminationType": getExaminationType($conn);break;
		case "delete":delete($conn);break;
		case "saveTemp": saveTemp($conn); break;
		case "saveAll": saveAll($conn); break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

function saveTemp($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_EXAM_TYPE_CD  = $_POST['TEXT_EXAM_TYPE_CD'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_TYPE_CD'];
		
		
		$query="EXEC [INSERT_ALL_STUDENT_MARKS_SP] $TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_EXAM_TYPE_CD,$userid ";
		
				
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



function saveAll($mysqli) {
    try {
        $data = array();
        global $userid;
        $marksArray = json_decode($_POST['marksArray'], true);

        foreach ($marksArray as $row) {
            $actionid = $row['MARKS_ID'] == 0 ? 1 : 2;
            $query = "EXEC [STUDENT_MARKS_SP]
                      $actionid,
                      {$row['MARKS_ID']},
                      {$row['STUDENT_ID']},
                      {$row['SCHOOL_ID']},
                      {$row['CLASS_CD']},
                      {$row['FY_YEAR_CD']},
                      {$row['SCHOOL_SUBJECT_ID']},
                      {$row['EXAM_ID']},
                      {$row['MAX_MARKS']},
                      {$row['MARKS_OBTAINED']},
                      $userid";

            $stmt = sqlsrv_query($mysqli, $query);
            if ($stmt === false) {
                throw new Exception("Error executing for subject {$row['SCHOOL_SUBJECT_ID']}");
            }
        }

        $data['success'] = true;
        $data['message'] = "All marks saved successfully.";
        echo json_encode($data); exit;

    } catch (Exception $e) {
        $data = array();
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data);
        exit;
    }
}


function getQuery($mysqli){
  try {
    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
    $TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
    $TEXT_EXAM_TYPE_CD = $_POST['TEXT_EXAM_TYPE_CD'] == 'undefined' ? 0 : $_POST['TEXT_EXAM_TYPE_CD'];
    $TEXT_STUDENT_ID = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];

    $query = "
      SELECT
        A.MARKS_ID,
        A.STUDENT_ID,
        A.SCHOOL_ID,
        A.CLASS_CD,
        A.FY_YEAR_CD,
        A.SCHOOL_SUBJECT_ID,
        A.EXAM_ID,
        A.MAX_MARKS,
        A.MARKS_OBTAINED,
        A.GRADE_NAME,
        (B.STUDENT_FIRST_NAME + ' ' + ISNULL(B.STUDENT_LAST_NAME, '')) AS STUDENT_NAME,
        B.FY_YEAR,
        B.CLASS,
        C.SUBJECT,
        D.EXAM_NAME
      FROM STUDENT_MARKS A
      INNER JOIN STUDENT B ON A.STUDENT_ID = B.STUDENT_ID
      INNER JOIN SCHOOL_SUBJECTS C ON A.SCHOOL_SUBJECT_ID = C.SCHOOL_SUBJECT_ID AND A.SCHOOL_ID = C.SCHOOL_ID
      INNER JOIN EXAMS_MASTER D ON A.EXAM_ID = D.EXAM_ID
      WHERE A.ISDELETED = 0 
        AND B.ISDELETED = 0 
        AND C.ISDELETED = 0 
        AND D.ISDELETED = 0 
        AND A.SCHOOL_ID = $TEXT_SCHOOL_ID 
        AND A.CLASS_CD = $TEXT_CLASS_CD 
        AND A.EXAM_ID = $TEXT_EXAM_TYPE_CD 
        AND A.STUDENT_ID = $TEXT_STUDENT_ID
    ";

    $result = sqlsrv_query($mysqli, $query);
    $data = array();

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
      $row['MARKS_ID']         = (int) $row['MARKS_ID'];
      $row['STUDENT_ID']       = (int) $row['STUDENT_ID'];
      $row['SCHOOL_ID']        = (int) $row['SCHOOL_ID'];
      $row['CLASS_CD']         = (int) $row['CLASS_CD'];
      $row['FY_YEAR_CD']       = (int) $row['FY_YEAR_CD'];
      $row['SCHOOL_SUBJECT_ID']= (int) $row['SCHOOL_SUBJECT_ID'];
      $row['EXAM_ID']          = (int) $row['EXAM_ID'];
      $row['MAX_MARKS']        = is_numeric($row['MAX_MARKS']) ? (float) $row['MAX_MARKS'] : 0;
      $row['MARKS_OBTAINED']   = is_numeric($row['MARKS_OBTAINED']) ? (float) $row['MARKS_OBTAINED'] : 0;
      $data['data'][] = $row;
    }

    $data['success'] = true;
    echo json_encode($data); exit;

  } catch (Exception $e) {
    $data = array();
    $data['success'] = false;
    $data['message'] = $e->getMessage();
    echo json_encode($data); exit;
  }
}


function getExaminationType($mysqli){
	try
	{
		
	    $data = array();
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
							ORDER BY A.EXAM_ID
				 					";

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
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_MARKS_SP]	3,$marksid,'','','','','','','','',$userid ") ;
				
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







