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
        case "archiveUnarchiveStudent":archiveUnarchiveStudent($conn);break;
        case "getArchivedStuent":getArchivedStuent($conn);break;
        case "getProduct":getProduct($conn);break;
       
        case "getStudentByName":getStudentByName($conn);break;
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


 function archiveUnarchiveStudent($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $STUDENT_ID  = ($_POST['STUDENT_ID'] == 'undefined' || $_POST['STUDENT_ID'] == '') ? 0 : $_POST['STUDENT_ID'];
		$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		$txtArcRemark = $_POST['txtArcRemark'] == 'undefined' ? '' : $_POST['txtArcRemark'];
		
		if(!isset($userid) || $userid <= 0) throw new Exception("Invalid Login User.Please Re-login");
		if($STUDENT_ID == 0) throw new Exception("Invalid STUDENT_ID.");
		if($FOR == '') throw new Exception("Invalid TYPE.");

		if($FOR == 'ARCHIVED')
		{
			$query="UPDATE STUDENT SET ARCHIVED=1,ARCHIVED_BY=$userid,ARCHIVED_REMARK='$txtArcRemark',ARCHIVED_DT=GETDATE() WHERE STUDENT_ID=$STUDENT_ID";

		}else if('UNARCHIVED'){
			$query="UPDATE STUDENT SET ARCHIVED=0 WHERE STUDENT_ID=$STUDENT_ID";
		}else{
			throw new Exception("Invalid TYPE.");
		}
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
			if($FOR == 'ARCHIVED')$data['message'] = 'Student archived successfully.';
			else $data['message'] = 'Student unarchived successfully.';
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


/*============ GET STUDENT =============*/ 
 function getStudentByName($mysqli){
	try
	{
		$data = array();

		$TEXT_SCHOOL_ID=($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$txtSearchStudent=($_POST['txtSearchStudent'] == 'undefined' || $_POST['txtSearchStudent'] == '') ? '' : $_POST['txtSearchStudent'];

		if($TEXT_SCHOOL_ID==0) throw new Exception('Invalid School. Select School First.');
		if($txtSearchStudent=='') throw new Exception('Invalid Search Name. Enter Student Name.');

		$query = "SELECT 
		 	      STUDENT_ID
				  ,STUDENT_FIRST_NAME
				  ,STUDENT_LAST_NAME
				  ,CLASS_CD
				  ,CLASS
				  FROM STUDENT WHERE SCHOOL_ID=$TEXT_SCHOOL_ID AND (STUDENT_FIRST_NAME LIKE '%$txtSearchStudent%' OR STUDENT_LAST_NAME LIKE '%$txtSearchStudent%') 
				AND ISDELETED=0 AND ARCHIVED=0";
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


/*============ GET ARCHIVED STUDENT =============*/ 
 function getArchivedStuent($mysqli){
	try
	{
		$data = array();

		$TEXT_SCHOOL_ID=($_POST['TEXT_SCHOOL_ID'] == 'undefined' || $_POST['TEXT_SCHOOL_ID'] == '') ? 0 : $_POST['TEXT_SCHOOL_ID'];
		if($TEXT_SCHOOL_ID==0) throw new Exception('Invalid School. Select School First.');

		$query = "SELECT 
		          STUDENT_ID
				  ,STUDENT_FIRST_NAME
				  ,STUDENT_LAST_NAME
				  ,CLASS
				  ,ARCHIVED_REMARK
				  ,	ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=R.ARCHIVED_BY),'')ARCHIVED_BY,
				 CASE WHEN ARCHIVED_DT IS NULL OR CONVERT(DATE,ARCHIVED_DT,105)='01-01-1900' THEN ''
					ELSE CONVERT(VARCHAR,ARCHIVED_DT,106)
				END ARCHIVED_DT
				FROM STUDENT R WHERE SCHOOL_ID=$TEXT_SCHOOL_ID  AND ISDELETED=0 AND ARCHIVED=1";
	
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







