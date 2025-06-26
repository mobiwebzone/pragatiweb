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
        case "archiveUnarchiveTeacher":archiveUnarchiveTeacher($conn);break;
        case "getArchivedTeacher":getArchivedTeacher($conn);break;
        case "getTeacherByName":getTeacherByName($conn);break;
		case "getschoolname":getschoolname($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

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


 function archiveUnarchiveTeacher($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $TEACHER_ID  = ($_POST['TEACHER_ID'] == 'undefined' || $_POST['TEACHER_ID'] == '') ? 0 : $_POST['TEACHER_ID'];
		$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		$txtArcRemark = $_POST['txtArcRemark'] == 'undefined' ? '' : $_POST['txtArcRemark'];
		
		if(!isset($userid) || $userid <= 0) throw new Exception("Invalid Login User.Please Re-login");
		if($TEACHER_ID == 0) throw new Exception("Invalid TEACHER_ID.");
		if($FOR == '') throw new Exception("Invalid TYPE.");

		if($FOR == 'ARCHIVED')
		{
			$query="UPDATE TEACHER SET ARCHIVED=1,ARCHIVED_BY=$userid,ARCHIVED_REMARK='$txtArcRemark',ARCHIVED_DT=GETDATE() 
			WHERE TEACHER_ID=$TEACHER_ID";

		}else if('UNARCHIVED'){
			$query="UPDATE TEACHER SET ARCHIVED=0 WHERE TEACHER_ID=$TEACHER_ID";
		}else{
			throw new Exception("Invalid TYPE.");
		}
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{
			$data['query'] = $query;
			$data['success'] = true;
			if($FOR == 'ARCHIVED')$data['message'] = 'Teacher archived successfully.';
			else $data['message'] = 'Teacher unarchived successfully.';
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




// /*============ Get Plans =============*/ 
//  function getPlans($mysqli){
// 	try
// 	{
// 		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0";
// 		$result = sqlsrv_query($mysqli, $query);
// 		$data = array();
// 		while ($row = sqlsrv_fetch_array($result)) {
// 			$row['PLANID'] = (int) $row['PLANID'];
// 			$data['data'][] = $row;
// 		}
// 		$data['success'] = true;
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }


/*============ GET TEACHER =============*/ 
 function getTeacherByName($mysqli){
	try
	{
		$data = array();

		$TEXT_SCHOOL_ID=($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$txtSearchTeacher=($_POST['txtSearchTeacher'] == 'undefined' || $_POST['txtSearchTeacher'] == '') ? '' : $_POST['txtSearchTeacher'];

		if($TEXT_SCHOOL_ID==0) throw new Exception('Invalid Location. Select Location First.');
		if($txtSearchTeacher=='') throw new Exception('Invalid Search Name. Enter Teacher Name.');

		$query = "  SELECT 
					TEACHER_ID
					,TEACHER_NAME
					FROM TEACHER 
					WHERE SCHOOL_ID=1 
					AND ISDELETED=0 
					AND ARCHIVED=0
					AND (TEACHER_NAME LIKE '%$txtSearchTeacher%' ) 
					AND SCHOOL_ID = $TEXT_SCHOOL_ID
					";

		$data['query']=$query;
		// echo json_encode($data);exit;
		$COUNT = unique($query);
		if($COUNT>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


/*============ GET ARCHIVED TEACHER =============*/ 
 function getArchivedTeacher($mysqli){
	try
	{
		$data = array();

		$TEXT_SCHOOL_ID=($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? 0 : $_POST['TEXT_SCHOOL_ID'];
		if($TEXT_SCHOOL_ID==0) throw new Exception('Invalid School. Select School First.');

		$query =    "SELECT 
					TEACHER_ID
					,TEACHER_NAME
					,ARCHIVED_REMARK
					,ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=U.ARCHIVED_BY),'')ARCHIVED_BY,
					CASE WHEN ARCHIVED_DT IS NULL OR CONVERT(DATE,ARCHIVED_DT,105)='01-01-1900' THEN ''
					ELSE CONVERT(VARCHAR,ARCHIVED_DT,106)
					END ARCHIVED_DT
					FROM TEACHER U WHERE SCHOOL_ID = $TEXT_SCHOOL_ID AND ARCHIVED=1
					";
		$data['query']=$query;
		// echo json_encode($data);exit;
		$COUNT = unique($query);
		if($COUNT>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







