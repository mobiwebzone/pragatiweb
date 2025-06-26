<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['USERID']))
{$userid=$_SESSION['USERID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "saveCourse":saveCourse($conn);break;
        case "getCourse":getCourse($conn);break;
        case "deleteCourse":deleteCourse($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveCourse($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $courseid  = $_POST['course_id'] == 'undefined' ? 0 : $_POST['course_id'];
        $txtCourse  = $_POST['txtCourse'] == 'undefined' ? '' : $_POST['txtCourse'];
        $txtCourseDesc  = $_POST['txtCourseDesc'] == 'undefined' ? '' : $_POST['txtCourseDesc'];
		$txtDisplayOrder = ($_POST['txtDisplayOrder'] == 'undefined' || $_POST['txtDisplayOrder'] == '') ? 0 : $_POST['txtDisplayOrder'];
		$txtDisplayColor = $_POST['txtDisplayColor'] == 'undefined' ? 0 : $_POST['txtDisplayColor'];
		
		$actionid = $courseid == 0 ? 1 : 2;

		if($txtCourse == '')
		{throw new Exception("Please Enter Course Name.");}

		$sql = "SELECT * FROM COURSES WHERE COURSE='$txtCourse' AND COURSE_ID!=$courseid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [COURSES_SP] $actionid,$courseid,'$txtCourse','$txtCourseDesc',$txtDisplayOrder,'$txtDisplayColor',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($courseid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
			echo json_encode($data);exit;
		}

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


/*============ Get getCourse =============*/ 
 function getCourse($mysqli){
	try
	{
		$query = "SELECT COURSE_ID,COURSE,COURSE_DESC,DISPLAY_ORDER,DISPLAY_COLOR FROM COURSES WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['COURSE_ID'] = (int) $row['COURSE_ID'];
			$row['DISPLAY_ORDER'] = (int) $row['DISPLAY_ORDER'];
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



/* =========== Delete =========== */ 
function deleteCourse($mysqli){
	try{   
			global $userid;
			$data = array();     
            $courseid = $_POST['courseid'] == 'undefined' ? 0 : $_POST['courseid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [COURSES_SP] 3,$courseid,'','',0,'',$userid");
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







