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
        case "saveData":saveData($conn);break;
        case "getTeacherSchedules":getTeacherSchedules($conn);break;
        case "deleteData":deleteData($conn);break;
		
		// ============ STUDENTS ===========
        case "saveDataStudents":saveDataStudents($conn);break;
        case "getTeacherSchStudents":getTeacherSchStudents($conn);break;
        case "getStudentByPlan":getStudentByPlan($conn);break;
        case "deleteStudents":deleteStudents($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$schid = ($_POST['schid'] == 'undefined' || $_POST['schid'] == '') ? 0 : $_POST['schid'];
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$ddlDay = $_POST['ddlDay'] == 'undefined' ? '' : $_POST['ddlDay'];
		$txtEffectiveFromDT = $_POST['txtEffectiveFromDT'] == 'undefined' ? '' : $_POST['txtEffectiveFromDT'];
		$txtEffectiveToDT = $_POST['txtEffectiveToDT'] == 'undefined' ? '' : $_POST['txtEffectiveToDT'];
		$txtTimeFrom = $_POST['txtTimeFrom'] == 'undefined' ? '' : $_POST['txtTimeFrom'];
		$txtTimeTo = $_POST['txtTimeTo'] == 'undefined' ? '' : $_POST['txtTimeTo'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];;
		$txtMeetingID = $_POST['txtMeetingID'] == 'undefined' ? '' : $_POST['txtMeetingID'];
		$ddlClassType = $_POST['ddlClassType'] == 'undefined' ? '' : $_POST['ddlClassType'];
		$ddlClassIn = $_POST['ddlClassIn'] == 'undefined' ? '' : $_POST['ddlClassIn'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];

		$actionid = $schid == 0 ? 1 : 2;
		
		if($ddlTeacher == 0){throw new Exception("Please Select Teacher Name.");}
		if($ddlDay == ''){throw new Exception("Please Select Day Name.");}
		if($txtEffectiveFromDT == ''){throw new Exception("Please Select Effective From Date.");}
		if($txtEffectiveToDT == ''){throw new Exception("Please Select Effective To Date.");}
		if($txtTimeFrom == ''){throw new Exception("Please Select Time From.");}
		if($txtTimeTo == ''){throw new Exception("Please Select Time To.");}
		if($txtMeetingID == ''){throw new Exception("Please Enter Meeting ID.");}
		if($ddlClassType == ''){throw new Exception("Please Select Class Type.");}
		if($ddlClassIn == ''){throw new Exception("Please Select Class In?.");}

		// $sql = "SELECT * FROM TEACHER_SCHEDULE WHERE TEACHERID=$ddlTeacher AND [DAY]='$ddlDay' AND SCHID!=$schid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {

			$query="EXEC [TEACHER_SCHEDULE_SP]$actionid,$schid,'$txtEffectiveFromDT','$txtEffectiveToDT',$ddlTeacher,'$ddlDay','$txtTimeFrom','$txtTimeTo',
				$ddlProduct,'$txtMeetingID','$ddlClassType','$ddlClassIn','$txtRemark',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['GET_SCHID'] = (int)$row['SCHID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($schid))$data['message'] = 'Record successfully updated';
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
/* ========== SAVE DATA =========== */




/* ========== GET TEACHER SCHEDULES =========== */
 function getTeacherSchedules($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtScrhFromDT = $_POST['txtScrhFromDT'] == 'undefined' ? '' : $_POST['txtScrhFromDT'];
		$txtScrhToDT = $_POST['txtScrhToDT'] == 'undefined' ? '' : $_POST['txtScrhToDT'];
		$ddlScrhDay = $_POST['ddlScrhDay'] == 'undefined' ? '' : $_POST['ddlScrhDay'];
		$ddlScrhTeacher = ($_POST['ddlScrhTeacher'] == 'undefined' || $_POST['ddlScrhTeacher'] == '') ? 0 : $_POST['ddlScrhTeacher'];

		$query = "SELECT SCHID,CONVERT(VARCHAR,EFFECTIVE_FROM,106)EFFECTIVE_FROM,
		CONVERT(VARCHAR,EFFECTIVE_TO,106)EFFECTIVE_TO,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME +' ('+USERROLE+')' FROM USERS WHERE [UID]=TS.TEACHERID)TEACHER,
		[DAY],CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),TIME_FROM,108) AS TIME),100)TIME_FROM,
		CONVERT(VARCHAR,TIME_FROM,22)TIME_FROM_SET,
		CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),TIME_TO,108) AS TIME),100)TIME_TO,
		CONVERT(VARCHAR,TIME_TO,22)TIME_TO_SET,PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TS.PRODUCTID)PRODUCT,MEETINGID,CLASSTYPE,
		CLASSIN,REMARKS,
		ISNULL((SELECT (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TSS.REGID) + ' | ' FROM TEACHER_SCHEDULE_STUDENTS TSS WHERE ISDELETED=0 AND SCHID=TS.SCHID FOR XML PATH('')),'')STUDENTS 
		FROM TEACHER_SCHEDULE TS
		WHERE ISDELETED=0 AND TEACHERID IN (SELECT [UID] FROM USERS WHERE LOCID=$ddlLocation AND USERROLE='TEACHER')";

		if($txtScrhFromDT != '' && $txtScrhToDT != ''){
			$query .= "  AND CONVERT(DATE,INSERTDATE,105) BETWEEN '$txtScrhFromDT' AND '$txtScrhToDT'";
		}
		if($ddlScrhDay != ''){
			$query .= "  AND [DAY]='$ddlScrhDay'";
		}
		if($ddlScrhTeacher > 0){
			$query .= "  AND TEACHERID=$ddlScrhTeacher";
		}
		$query .= "  ORDER BY TEACHER";

		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SCHID'] = (int) $row['SCHID'];
				$row['STUDENTS'] = rtrim($row['STUDENTS'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET TEACHER SCHEDULES =========== */





/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCHID = ($_POST['SCHID'] == 'undefined' || $_POST['SCHID'] == '') ? 0 : $_POST['SCHID'];  
			if($SCHID == 0){throw new Exception('SCHID NOT FOUND.');}
			$delQuery = "EXEC[TEACHER_SCHEDULE_SP] 3,$SCHID,'','',0,'','','',0,'','','','',$userid";
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
/* =========== DDELETE DATA =========== */ 







// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% ADD STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE DATA ==================
function saveDataStudents($mysqli){
	try
	{
	global $userid;
	$data = array();

	$schregid  = ($_POST['schregid'] == 'undefined' || $_POST['schregid'] == '') ? 0 : $_POST['schregid'];
	$schid  = ($_POST['schid'] == 'undefined' || $_POST['schid'] == '') ? 0 : $_POST['schid'];
	$ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
	$ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];

	$actionid = $schregid == 0 ? 1 : 2;

	if($schid == 0){throw new Exception("SCHID Not Found.");}
	if($ddlStudent == 0){throw new Exception("Please Select 'Student Name'.");}


	$sql = "SELECT * FROM TEACHER_SCHEDULE_STUDENTS WHERE SCHID=$schid AND PLANID=$ddlPlan AND REGID=$ddlStudent AND SCHREGID!=$schregid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		if($actionid === 1){
			$query="INSERT INTO TEACHER_SCHEDULE_STUDENTS(SCHID,PLANID,REGID)
					VALUES($schid,$ddlPlan,$ddlStudent)";
		}else if($actionid === 2){
			$query="UPDATE TEACHER_SCHEDULE_STUDENTS SET PLANID=$ddlPlan,REGID=$ddlStudent WHERE SCHREGID=$schregid";
		}else{
			throw new Exception('Error');
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
			$data['success'] = true;
			if(!empty($schregid))$data['message'] = 'Student successfully updated.';
			else $data['message'] = 'Student successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Student already exists';
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
 // =============== SAVE DATA ==============






/*============ GET TEACHER SCHEDULE STUDENTS =============*/ 
function getTeacherSchStudents($mysqli){
	try
	{
		$data = array();
		$schid = ($_POST['schid'] == 'undefined' || $_POST['schid'] == '') ? 0 : $_POST['schid'];
		if($schid == 0) throw new Exception('SCHID Not Found.');

		$query = "SELECT SCHREGID,PLANID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TSS.REGID)STUDENT
		FROM TEACHER_SCHEDULE_STUDENTS TSS WHERE ISDELETED=0 AND SCHID=$schid ORDER BY STUDENT";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Students not found.';
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET TEACHER SCHEDULE STUDENTS =============*/ 




/* ========== GET STUDENT BY PLAN =========== */
function getStudentByPlan($mysqli){
	try
	{	
		$PLANID = ($_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];
		if($PLANID == 0)throw new Exception('PLANID Not Found.');

		$query = "SELECT REGID,(SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)FIRSTNAME,
			(SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)LASTNAME 
			FROM REGISTRATION_DETAILS RD WHERE PLANID=$PLANID AND CANCELLED=0";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET STUDENT BY PLAN  =========== */




/* =========== DELETE =========== */ 
function deleteStudents($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCHREGID = ($_POST['SCHREGID'] == 'undefined' || $_POST['SCHREGID'] == '') ? 0 : $_POST['SCHREGID'];
			if($SCHREGID == 0){throw new Exception('SCHREGID Error.');}
			$delQuery = "UPDATE TEACHER_SCHEDULE_STUDENTS SET ISDELETED=1 WHERE SCHREGID=$SCHREGID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Student successfully deleted.';
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
/* =========== DELETE =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







