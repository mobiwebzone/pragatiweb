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
		//######## MASTER
        case "saveData":saveData($conn);break;
        case "getStudentApplications":getStudentApplications($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getStudentApplications_DET":getStudentApplications_DET($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE
		case "getStudentByLoc":getStudentByLoc($conn);break;

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;	

		$applid = ($_POST['applid'] == 'undefined' || $_POST['applid'] == '') ? 0 : $_POST['applid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$ddlAdmYear = ($_POST['ddlAdmYear'] == 'undefined' || $_POST['ddlAdmYear'] == '') ? 0 : $_POST['ddlAdmYear'];
		// $ddlApp = ($_POST['ddlApp'] == 'undefined' || $_POST['ddlApp'] == '') ? 0 : $_POST['ddlApp'];


		$actionid = $applid == 0 ? 1 : 2;

		if($ddlAdmYear == 0) throw new Exception("Select Admission Year.");


		// $sql = "SELECT * FROM SCHOLARSHIP_MASTER WHERE SCHOLARSHIP='$txtScholarship' AND SCHMID!=$schmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$query="EXEC [STUDENT_APPLICATIONS_SP] $actionid,$applid,$ddlLocation,$ddlStudent,$ddlAdmYear,0,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET APPLID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_APPLID'] = $row['APPLID'];
				$GET_APPLID = $row['APPLID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($applid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists';
		// 	echo json_encode($data);exit;
		// }

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
/* ============ SAVE DATA ============= */ 




/* ========== GET STUDENT APPLICATION =========== */
 function getStudentApplications($mysqli){
	try
	{
		$data = array();
		$query = "SELECT APPLID,LOCID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SA.REGID)STUDENTNAME,
		ADMYEARID,(SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=SA.ADMYEARID)ADMYEAR 
		FROM STUDENT_APPLICATIONS SA
		WHERE ISDELETED=0
		ORDER BY ADMYEAR";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPLID'] = (int) $row['APPLID'];
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
/* ========== GET STUDENT APPLICATION =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPLID = ($_POST['APPLID'] == 'undefined' || $_POST['APPLID'] == '') ? 0 : $_POST['APPLID'];  
			if($APPLID == 0)throw new Exception('APPLID Not Found.');
			$delQuery = "EXEC [STUDENT_APPLICATIONS_SP] 3,$APPLID,0,0,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataDET($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $appldid = ($_POST['appldid'] == 'undefined' || $_POST['appldid'] == '') ? 0 : $_POST['appldid'];
	   $applid = ($_POST['applid'] == 'undefined' || $_POST['applid'] == '') ? 0 : $_POST['applid'];
	   $ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
	   $ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
	   $ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];
	   $ddlCollegeMajor = ($_POST['ddlCollegeMajor'] == 'undefined' || $_POST['ddlCollegeMajor'] == '') ? 0 : $_POST['ddlCollegeMajor'];
	   $ddlDeadlineType = ($_POST['ddlDeadlineType'] == 'undefined' || $_POST['ddlDeadlineType'] == '') ? 0 : $_POST['ddlDeadlineType'];
	   $ddlApp = ($_POST['ddlApp'] == 'undefined' || $_POST['ddlApp'] == '') ? 0 : $_POST['ddlApp'];
	   $txtCommentsDetails = $_POST['txtCommentsDetails'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCommentsDetails']);

	   $actionid = $appldid == 0 ? 1 : 2;

	   if($applid == 0) throw new Exception("APPLID Error.");
	   if($ddlUniversity == 0) throw new Exception("Select University Name.");
	   if($ddlDeadlineType == 0) throw new Exception("Select Deadline Type.");


	   $sql = "SELECT * FROM STUDENT_APPLICATION_DETAILS WHERE APPLID=$applid AND UNIVERSITYID=$ddlUniversity AND 
			CLID=$ddlCollege AND MAJORID=$ddlCollegeMajor AND DEADLINETYPEID=$ddlDeadlineType AND APPID=$ddlApp AND APPLDID!=$appldid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [STUDENT_APPLICATION_DETAILS_SP] $actionid,$appldid,$applid,$ddlStudent,$ddlUniversity,$ddlCollege,$ddlCollegeMajor,
		   $ddlDeadlineType,$ddlApp,'$txtCommentsDetails',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($appldid))$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 





/* ========== GET STUDENT APPLICATION DETAILS =========== */
function getStudentApplications_DET($mysqli){
	try
	{
		$data = array();
		$applid = ($_POST['applid'] == 'undefined' || $_POST['applid'] == '') ? 0 : $_POST['applid'];
		if($applid == 0)throw new Exception("ApplID Error.");

		$query = "SELECT APPLDID,APPLID,REGID,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SAD.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SAD.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=SAD.MAJORID)MAJOR,DEADLINETYPEID,
		(SELECT DEADLINETYPE FROM DEADLINE_TYPES WHERE DEADLINETYPEID=SAD.DEADLINETYPEID)DEADLINETYPE,APPID,
		(SELECT APPNAME FROM APP_MASTER WHERE APPID=SAD.APPID)APPNAME,COMMENTS
		FROM STUDENT_APPLICATION_DETAILS SAD
		WHERE ISDELETED=0 AND APPLID=$applid 
		ORDER BY UNIVERSITY";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPLDID'] = (int) $row['APPLDID'];
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
/* ========== GET STUDENT APPLICATION DETAILS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPLDID = ($_POST['APPLDID'] == 'undefined' || $_POST['APPLDID'] == '') ? 0 : $_POST['APPLDID'];  
			if($APPLDID == 0)throw new Exception('APPLDID Not Found.');
			$delQuery = "EXEC [STUDENT_APPLICATION_DETAILS_SP] 3,$APPLDID,0,0,0,0,0,0,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 






/*============ GET STUDENT BY LOCATION =============*/ 
function getStudentByLoc($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');

		$query = "SELECT REGID,MODE,FIRSTNAME,LASTNAME,PHONE,EMAIL,GRADE,ADDRESSLINE1,ADDRESSLINE2,P1_FIRSTNAME,P1_LASTNAME,P1_PHONE,P1_EMAIL,
		P2_FIRSTNAME,P2_LASTNAME,P2_PHONE,P2_EMAIL,CLASSOF FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation AND ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Student not found.';
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
/*============ GET STUDENT BY LOCATION =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







