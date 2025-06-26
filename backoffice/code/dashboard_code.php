<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['USER_LOCID']))
{$userid_locid=$_SESSION['USER_LOCID'];}
else
{$userid_locid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getTotals":getTotals($conn);break;
        case "getTotalsByDT":getTotalsByDT($conn);break;

        case "getTotalTeacherHour":getTotalTeacherHour($conn);break;
        case "getTotalST_Rec_Analysis":getTotalST_Rec_Analysis($conn);break;
        case "getTotalStudentAtt":getTotalStudentAtt($conn);break;

        case "getStudentFeesOutstanding":getStudentFeesOutstanding($conn);break;

        case "getTeacher":getTeacher($conn);break;
        case "getTeacherLeave":getTeacherLeave($conn);break;
        case "CancelTeacherLeave":CancelTeacherLeave($conn);break;
        case "SubstituteTeacher":SubstituteTeacher($conn);break;
        
		case "getStudentLeave":getStudentLeave($conn);break;
        case "CancelStudentLeave":CancelStudentLeave($conn);break;
		
		case "getVolunteerReq":getVolunteerReq($conn);break;
        case "CancelApprove":CancelApprove($conn);break;
		
		case "getDiscontinueReq":getDiscontinueReq($conn);break;
        case "DiscontinueApprove":DiscontinueApprove($conn);break;

		case "getTeacherWrongAttMark":getTeacherWrongAttMark($conn);break;
		case "getST_NO_ATT":getST_NO_ATT($conn);break;

		case "getTeacher_NO_ATT":getTeacher_NO_ATT($conn);break;
		case "getDuplicateStudent":getDuplicateStudent($conn);break;
		case "getITDashBoard":getITDashBoard($conn);break;
		case "getGlobalTime":getGlobalTime($conn);break;
		case "getStudentAccountDeleteRequest":getStudentAccountDeleteRequest($conn);break;
		case "deleteAccount":deleteAccount($conn);break;
		
		
		case "saveTaskTrackingDetails":saveTaskTrackingDetails($conn);break;
		case "getTaskTrackDetails":getTaskTrackDetails($conn);break;
		case "ClosedTask":ClosedTask($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get Totals =============*/ 
function getTotals($mysqli){
	try
	{
		global $userid,$userid_locid;
		$data = array();

		// ========== REGISTRATION ===========
		$queryReg = "SELECT COUNT(*)TOTAL FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$userid_locid";
		$resultReg = sqlsrv_query($mysqli, $queryReg);
		while ($rowReg = sqlsrv_fetch_array($resultReg)) {
			$data['TotalRegistrations'] = $rowReg;
		}
		// ========== REGISTRATION ===========
		
		
		// ========== APPROVED ===========
		$queryApp = "SELECT COUNT(*)TOTAL FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND LOCATIONID=$userid_locid";
		$resultApp = sqlsrv_query($mysqli, $queryApp);
		while ($rowApp = sqlsrv_fetch_array($resultApp)) {
			$data['TotalApproved'] = $rowApp;
		}
		// ========== APPROVED ===========
		
		
		// ========== ADMINS ===========
		$queryAdmin = "SELECT COUNT(*)TOTAL FROM USERS WHERE ISDELETED=0 AND LOCID=$userid_locid AND USERROLE IN ('ADMINISTRATOR','SUPERADMIN')";
		$resultAdmin = sqlsrv_query($mysqli, $queryAdmin);
		while ($rowAdmin = sqlsrv_fetch_array($resultAdmin)) {
			$data['TotalAdmins'] = $rowAdmin;
		}
		// ========== ADMINS ===========
		
		
		// ========== TEACHERS ===========
		$queryTeacher = "SELECT COUNT(*)TOTAL FROM USERS WHERE ISDELETED=0 AND LOCID=$userid_locid AND USERROLE=('TEACHER')";
		$resultTeacher = sqlsrv_query($mysqli, $queryTeacher);
		while ($rowTeacher = sqlsrv_fetch_array($resultTeacher)) {
			$data['TotalTeachers'] = $rowTeacher;
		}
		// ========== TEACHERS ===========
		
		
		// ========== VOLUNTEERS ===========
		$queryVolunteer = "SELECT COUNT(*)TOTAL FROM VOLUNTEER_REQUESTS WHERE CANCELLED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$userid_locid)";
		$resultVolunteer = sqlsrv_query($mysqli, $queryVolunteer);
		while ($rowVolunteer = sqlsrv_fetch_array($resultVolunteer)) {
			$data['TotalVolunteers'] = $rowVolunteer;
		}
		// ========== VOLUNTEERS ===========



		// ========== TASK REVIEW COUNT===========
		// $queryTaskReviewCount = "SELECT COUNT(*)TOTAL FROM TASK_TRACKING TT WHERE ISDELETED=0 AND  LOCID IN
		// (SELECT LOCID FROM TASK_USERS WHERE LOCID=$userid_locid AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID AND TASKCATID=TT.TASKCATID AND REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid )
		// AND  TASKSTATUS!='CLOSED'";
		$queryTaskReviewCount = "SELECT COUNT(*)TOTAL FROM TASK_USERS  WHERE ISDELETED=0 AND 
		TASKSTATUS IN ('OPEN','WIP') AND
		ASSIGNEDTO IN ('ADMINS','TEACHERS','VOLUNTEERS') AND  
		LOCID=$userid_locid AND (ASSIGNEDTO_ID=$userid OR REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid )";
		$data['$queryTaskReviewCount'] = $queryTaskReviewCount;
		$resultTaskReviewCount = sqlsrv_query($mysqli, $queryTaskReviewCount);
		while ($rowTaskReviewCount = sqlsrv_fetch_array($resultTaskReviewCount,SQLSRV_FETCH_ASSOC)) {
			$data['TotalTaskReviewCount'] = $rowTaskReviewCount;
		}
		// ========== TASK REVIEW COUNT===========


		// ========== TASK REVIEW ===========
		// $queryTaskReview = "SELECT TTID,CONVERT(varchar,TTDATE,(106))TTDATE,
		// LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TT.LOCID)LOCATIONS,
		// TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=TT.TASKCATID)TASKCAT,
		// ASSIGNEDTO_ID,
		// CASE WHEN ASSIGNEDTO_ID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO='STUDENTS' AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID) 
		// 	THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
		// 	ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID IN (SELECT ASSIGNEDTO_ID FROM TASK_USERS WHERE ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID)) 
		// END ASSIGNEDTO_NAME ,
		// GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=TT.GRADEID)GRADE,
		// CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=TT.CSUBID)SUBJECT,
		// TASK,TASKFILE,CONVERT(varchar,TASKUPLOADEDON,(106))TASKUPLOADEDON 
		// FROM TASK_TRACKING TT WHERE ISDELETED=0 AND TASKSTATUS IN ('OPEN','WIP') AND
		// LOCID IN (SELECT LOCID FROM TASK_USERS WHERE LOCID=$userid_locid AND ASSIGNEDTO_ID=TT.ASSIGNEDTO_ID AND TASKCATID=TT.TASKCATID AND REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid )";
		$queryTaskReview = "SELECT TASKMGMTID, LOCID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TU.LOCID)LOCATIONS,
		TASKCATID,(SELECT TASKCAT FROM TASK_CATEGORIES WHERE TASKCATID=TU.TASKCATID)TASKCAT,
		CONVERT(VARCHAR,STARTDATE,(106))STARTDATE,CONVERT(VARCHAR,ENDDATE,(106))ENDDATE, 
		(SELECT (SELECT TASKMAINCAT FROM TASK_MAIN_CATEGORIES WHERE TASKMAINCATID=TC.TASKMAINCATID) FROM TASK_CATEGORIES TC WHERE TASKCATID=TU.TASKCATID)TASKMAINCAT,
		ASSIGNEDTO_ID,
		CASE WHEN ASSIGNEDTO='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TU.ASSIGNEDTO_ID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TU.ASSIGNEDTO_ID) 
		END ASSIGNEDTO_NAME ,
		GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER  WHERE ISDELETED=0 AND GRADEID=TU.GRADEID)GRADE,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0  AND CSUBID=TU.CSUBID)SUBJECT,TASKSTATUS,
		CLOSEDBY,CLOSEDBYID,
		CASE WHEN CLOSEDBY='STUDENTS' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TU.CLOSEDBYID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TU.CLOSEDBYID) 
		END CLOSEDBYNAME,
		CASE WHEN CLOSEDON='1900-01-01 00:00:00.000' OR CLOSEDON IS NULL THEN '' ELSE FORMAT(CLOSEDON,'dd MMM yyyy hh:mm tt') END CLOSEDON,
		TASK_DESC,TASKFILE--,CONVERT(VARCHAR,TASKUPDATEON,(106))TASKUPDATEON 
		FROM TASK_USERS TU
		WHERE ISDELETED=0 AND TASKSTATUS IN ('OPEN','WIP') AND
		LOCID=$userid_locid AND ASSIGNEDTO IN ('ADMINS','TEACHERS','VOLUNTEERS') AND (ASSIGNEDTO_ID=$userid OR REVIEW1_ID=$userid OR REVIEW2_ID=$userid  OR REVIEW3_ID=$userid)
		";
			// $data['queryTaskReview'] = $queryTaskReview;
			// echo json_encode($data);exit;
		$resultTaskReview = sqlsrv_query($mysqli, $queryTaskReview);
		while ($rowTaskReview = sqlsrv_fetch_array($resultTaskReview,SQLSRV_FETCH_ASSOC)) {
			$data['TotalTaskView'][] = $rowTaskReview;
		}
		// ========== TASK REVIEW  ===========

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
/*============ Get Totals =============*/ 


/*============ Get Totals New =============*/ 
function getTotalsByDT($mysqli){
	try
	{
		global $userid_locid;
		$data = array();

		$USER_LOCATION = $_POST['USER_LOCATION'] == 'undefined' ? '' : $_POST['USER_LOCATION'];
		$txtFromDate = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$queryReg = "EXEC [RPT_DASHBOARD_TOTALS]'$txtFromDate','$txtToDate',$ddlLocation";
		$data['$queryReg'] = $queryReg;
		$resultReg = sqlsrv_query($mysqli, $queryReg);
		while ($rowReg = sqlsrv_fetch_array($resultReg,SQLSRV_FETCH_ASSOC)) {
			$rowReg['PAYMENTS_AMOUNT'] = round($rowReg['PAYMENTS_AMOUNT']);
			$rowReg['PAYMENTS_AMOUNT_NO_ATT'] = round($rowReg['PAYMENTS_AMOUNT_NO_ATT']);
			$data['data'] = $rowReg;
		}
		// $data['TYPEOF'] = typeOf $data['data']; 
		foreach($data['data'] as $val=>$key){
			// if($val!=='')
			if($val == 'REGISTRATIONS'){
				$bg = 'lightsalmon';
				$goto = 'Registration.html';
			} 
			if($val == 'CONTACTUS'){
				$val = 'CONTACT US';
				$bg = 'lemonchiffon';
				$goto = 'ContactUs_BackOffice.html';
			} 
			if($val == 'PAYMENTS'){
				$bg = 'aquamarine';
				$goto = 'StudentPaymentRec.html';
			}
			if($val == 'CC_STUDENTS'){
				$val = 'COURSE COVERAGE STUDENTS';
				$bg = 'darkkhaki';
				$goto = 'Student_Course_Coverage.html';
			}
			if($val == 'APPROVED_STUDENTS'){
				$val = 'APPROVED STUDENTS';
				$bg = 'lightgrey';
				$goto = 'LoginApproval.html';
			} 
			if($val == 'PAYMENTS_AMOUNT'){
				$val = 'PAYMENTS AMOUNT';
				$bg = 'gold';
				$goto = 'Student_Attendance_Payment_Report.html';
			} 
			if($val == 'PAYMENTS_AMOUNT_NO_ATT'){
				$val = 'PAYMENTS AMOUNT NO ATT';
				$bg = 'springgreen';
				$goto = 'Student_Attendance_Payment_Report_P2.html';
			} 
			if($val == 'STUDENT_SUPPORT'){
				$val = 'STUDENT SUPPORT';
				$bg = 'lightskyblue';
				$goto = 'BO_Student_Support.html';
			} 
			if($val == 'EXPENSES'){
				$bg = 'pink';
				$goto = 'Expenses.html';
			} 
			if($val == 'REVENUE'){
				$bg = 'burlywood';
				$goto = 'Revenue.html';
			} 
			if($val == 'STATISTICS'){
				$bg = 'plum';
				$goto = 'Statistics.html';
			} 
			// $data['data_final'][] = ['HEAD'=>$val,'VAL'=>$key,'BG'=>$bg,'GOTO'=>$goto];
			if(strpos($USER_LOCATION, 'HQ') !== false){
				$IS_HQ = true;
			}else{
				$IS_HQ = false;
			}

			if($IS_HQ && $val!='PAYMENTS' && $val!='REVENUE' && $val!='PAYMENTS AMOUNT'){
				$data['data_final'][] = ['HEAD'=>$val,'VAL'=>$key,'BG'=>$bg,'GOTO'=>$goto];
				$data['chk'][]=1;
			}else if(!$IS_HQ){
				$data['data_final'][] = ['HEAD'=>$val,'VAL'=>$key,'BG'=>$bg,'GOTO'=>$goto];
				$data['chk'][]=0;
			}
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
/*============ Get Totals New =============*/ 





/*============ Get Total Teacher Hour =============*/ 
 function getTotalTeacherHour($mysqli){
	try
	{
		global $userid_locid;

		$txtFromDate = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "EXEC [RPT_TEACHER_ATT_EXPENSES] '$txtFromDate','$txtToDate',$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$hours=array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'] = $row;
			array_push($hours,$row['HOURS_CURRENT_YEAR'],$row['HOURS_PREVIOUS_YEAR'],$row['HOURS_PREVIOUS_MONTH']);
		}
		$data['$query'] = $query;
		$data['hours'] = $hours;
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
/*============ Get Total Teacher Hour =============*/ 






/*============ Get Total Student Rec Analysis =============*/ 
 function getTotalST_Rec_Analysis($mysqli){
	try
	{
		global $userid_locid;
		$txtFromDate = $_POST['txtFromDate_SRA'] == 'undefined' ? '' : $_POST['txtFromDate_SRA'];
		$txtToDate = $_POST['txtToDate_SRA'] == 'undefined' ? '' : $_POST['txtToDate_SRA'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "EXEC [RPT_STUDENT_RECEIPTS_ANALYSIS] '$txtFromDate','$txtToDate',$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$amount=array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'] = $row;
			array_push($amount,$row['CURRENT_YEAR'],$row['PREVIOUS_YEAR'],$row['PREVIOUS_MONTH']);
		}
		$data['$query'] = $query;
		$data['amount'] = $amount;
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
/*============ Get Total Student Rec Analysis =============*/ 






/*============ Get Total Student Att =============*/ 
 function getTotalStudentAtt($mysqli){
	try
	{
		global $userid_locid;
		$txtFromDate_AT = $_POST['txtFromDate_AT'] == 'undefined' ? '' : $_POST['txtFromDate_AT'];
		$txtToDate_AT = $_POST['txtToDate_AT'] == 'undefined' ? '' : $_POST['txtToDate_AT'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "EXEC [RPT_STUDENT_COUNT_PLANWISE_ANALYSIS] '$txtFromDate_AT','$txtToDate_AT',$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$CYhours=array();
		$PYhours=array();
		$PMhours=array();
		$plans=array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
			$row['PLANNAME'] = (string) $row['PLANNAME'];
			$plans[] = $row['PLANNAME'];
			$CYhours[]=$row['CURRENT_YEAR'];
			$PYhours[]=$row['PREVIOUS_YEAR'];
			$PMhours[]=$row['PREVIOUS_MONTH'];
		}
		$data['$query'] = $query;
		$data['CYhours'] = $CYhours;
		$data['PYhours'] = $PYhours;
		$data['PMhours'] = $PMhours;
		$data['plansTS'] = $plans;
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
/*============ Get Total Student Att =============*/ 







/*============ Get Student Fees Outstanding =============*/ 
 function getStudentFeesOutstanding($mysqli){
	try
	{

		$query = "EXEC [RPT_STUDENT_FEES_OUTSTANDING]";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
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
/*============ Get Student Fees Outstanding =============*/ 









/*============ Get Teacher =============*/ 
function getTeacher($mysqli){
	try
	{
		global $role;
		$USER_LOCID=($_POST['USER_LOCID'] == 'undefined' || $_POST['USER_LOCID'] == '') ? 0 : $_POST['USER_LOCID'];
		// $userrole=($_POST['userrole'] == 'undefined' || $_POST['userrole'] == '') ? 0 : $_POST['userrole'];
		// if($role == 'TEACHER' || $role == 'VOLUNTEER'){
		// 	$query = "SELECT UID,FIRSTNAME+' '+LASTNAME AS FULLNAME FROM USERS WHERE LOCID=$USER_LOCID AND USERROLE='$userrole' AND ISDELETED=0";
		// }else{
			$query = "SELECT UID,FIRSTNAME+' '+LASTNAME +' — '+' ('+USERROLE+')' AS FULLNAME FROM USERS WHERE LOCID=$USER_LOCID AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0 ORDER BY USERROLE";
		// }
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);

		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ Get Teacher =============*/ 








/*============ Get Teacher Leave =============*/ 
 function getTeacherLeave($mysqli){
	try
	{

		$query = "SELECT TOP 100 REQID,REQ_BY_ID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.REQ_BY_ID)FULLNAME,
		CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,CANCELLED,CANCELID,
		CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS,CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE,
		SUBSTITUTE_TEACHER,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=RL.SUBSTITUTE_TEACHER)SUBS_TEACHER_NAME,
		SUBSTITUTE_REMARK
		FROM REQUEST_LEAVE RL WHERE STUDENT_TEACHER='TEACHER'
		ORDER BY CONVERT(DATE,FROMDT,105) DESC";
		// $query = "SELECT REQID,REQ_BY_ID,
		// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=RL.REQ_BY_ID)FULLNAME,
		// CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,CANCELLED,CANCELID,
		// CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS
		// FROM REQUEST_LEAVE RL WHERE STUDENT_TEACHER='TEACHER' AND CONVERT(DATE,INSERTDATE,105) >= CONVERT(DATE,GETDATE(),105)";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['REQID'] = (int) $row['REQID'];
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
/*============ Get Teacher Leave =============*/







/* =========== Cancel Teacher Leave =========== */ 
function CancelTeacherLeave($mysqli){
	try{   
			global $userid;
			$data = array();     
            $reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];  
            $txtCancelRemark = $_POST['txtCancelRemark'] == 'undefined' ? '' : $_POST['txtCancelRemark'];  

			$cancel = "UPDATE REQUEST_LEAVE SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE(),CANCELREMARKS='$txtCancelRemark' WHERE REQID=$reqid";
			$data['Tcancel'] = $cancel;
			$stmt=sqlsrv_query($mysqli, $cancel);
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Cancel Teacher Leave =========== *







/* =========== SAVE SUBSTITUTE TEACHER =========== */ 
function SubstituteTeacher($mysqli){
	try{   
			global $userid;
			$data = array();     
            $reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];  
			if($reqid==0){throw new Exception('REQID Not Found.');}
            $ddlTeacherSubs = ($_POST['ddlTeacherSubs'] == 'undefined' || $_POST['ddlTeacherSubs'] == '') ? 0 : $_POST['ddlTeacherSubs'];  
			if($ddlTeacherSubs == 0){throw new Exception('First Select Substitute Teacher.');}
            $txtRemarkSubs = $_POST['txtRemarkSubs'] == 'undefined' ? '' : $_POST['txtRemarkSubs'];  

			$query = "UPDATE REQUEST_LEAVE SET SUBSTITUTE_TEACHER=$ddlTeacherSubs,SUBSTITUTE_REMARK='$txtRemarkSubs' WHERE REQID=$reqid";
			$stmt=sqlsrv_query($mysqli, $query);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Teacher substitute successfully added.';
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
/* =========== SAVE SUBSTITUTE TEACHER =========== *







/*============ Get Student Leave =============*/ 
 function getStudentLeave($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT REQID,REQ_BY_ID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RL.REQ_BY_ID)FULLNAME,
		CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,REMARKS,CANCELLED,CANCELID,
		CONVERT(VARCHAR,CANCELDATE,106)CANCELDATE,CANCELREMARKS
		FROM REQUEST_LEAVE RL WHERE STUDENT_TEACHER='STUDENT' AND REQ_BY_ID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND CONVERT(DATE,INSERTDATE,105) >= CONVERT(DATE,GETDATE(),105)
		ORDER BY CONVERT(DATE,FROMDT,105) DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['REQID'] = (int) $row['REQID'];
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
/*============ Get Student Leave =============*/ 







/* =========== Cancel Student Leave =========== */ 
function CancelStudentLeave($mysqli){
	try{   
			global $userid;
			$data = array();     
            $reqid = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];  
            $txtCancelRemark = $_POST['txtCancelRemark'] == 'undefined' ? '' : $_POST['txtCancelRemark'];  

			$cancel = "UPDATE REQUEST_LEAVE SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE(),CANCELREMARKS='$txtCancelRemark' WHERE REQID=$reqid";
			$data['Tcancel'] = $cancel;
			$stmt=sqlsrv_query($mysqli, $cancel);
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Cancel Student Leave =========== */







/*============ Get Volunteer Request =============*/ 
function getVolunteerReq($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT VRID,CONVERT(VARCHAR,REQDATE,106)REQDATE,
		CONVERT(VARCHAR,FROMDT,106)FROMDT,CONVERT(VARCHAR,TODT,106)TODT,
		REMARKS,APPROVED,APPROVEDBY,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=VR.REGID)FULLNAME,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=VR.APPROVEDBY AND ISDELETED=0)APPROVEDBY,
		APPR_REMARKS,
		ISNULL((SELECT (SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=VRD.PRODUCTID) + ', ' FROM VOLUNTEER_REQUEST_DETAILS VRD WHERE ISDELETED=0 AND VRID=VR.VRID
        FOR XML PATH ('')),'-')PRODUCTS,CANCELLED,CANCELREMARKS
		FROM VOLUNTEER_REQUESTS VR WHERE REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND CONVERT(DATE,REQDATE,105) >= CONVERT(DATE,GETDATE(),105)
		ORDER BY CONVERT(DATE,FROMDT,105) DESC";
		// $data['query'] = $query;
		// echo json_encode($data);exit;
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['VRID'] = (int) $row['VRID'];
				$row['PRODUCTS'] = rtrim($row['PRODUCTS'], ", ");
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
/*============ Get Volunteer Request =============*/ 






/* =========== Cancel-Approve =========== */ 
function CancelApprove($mysqli){
	try{   
			global $userid;
			$data = array();     
            $VRID = ($_POST['VRID'] == 'undefined' || $_POST['VRID'] == '') ? 0 : $_POST['VRID'];  
            $txtRemarkCA = $_POST['txtRemarkCA'] == 'undefined' ? '' : $_POST['txtRemarkCA'];  
            $FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR']; 

			if($FOR == 'Cancel'){
				$cancel = "UPDATE VOLUNTEER_REQUESTS SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE(),CANCELREMARKS='$txtRemarkCA' WHERE VRID=$VRID";
			}else if($FOR == 'Approve'){
				$cancel = "UPDATE VOLUNTEER_REQUESTS SET APPROVED=1,APPROVEDBY=$userid,APPR_REMARKS='$txtRemarkCA' WHERE VRID=$VRID";
			}

			$data['Tcancel'] = $cancel;
			$stmt=sqlsrv_query($mysqli, $cancel);
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Cancel-Approve =========== */







/*============ Get Discontinue Request =============*/ 
function getDiscontinueReq($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT TOP 10 REGDID,PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN],
		DISCONTINUE_REQ,CONVERT(VARCHAR,DISCONTINUE_REQ_DATE,106)DISCONTINUE_REQ_DATE,
		DISCONTINUE_REMARK,REGID,DISCONTINUE_APPROVE_BY_ID,DISCONTINUE_APPROVED,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)FULLNAME
		FROM REGISTRATION_DETAILS RD 
		WHERE CANCELLED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND DISCONTINUE_REQ=1 ORDER BY CONVERT(DATE,DISCONTINUE_REQ_DATE,106) DESC";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANID'] = (int) $row['PLANID'];
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
/*============ Get Discontinue Request =============*/ 







/* =========== Discontinue Approve =========== */ 
function DiscontinueApprove($mysqli){
	try{   
			global $userid;
			$data = array();     
            $REGDID = ($_POST['REGDID'] == 'undefined' || $_POST['REGDID'] == '') ? 0 : $_POST['REGDID'];  
            $REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];  

			if($REGDID == 0){

				throw new Exception('REGDID Error');
			}



			$ApprDis = "UPDATE REGISTRATION_DETAILS SET DISCONTINUE_APPROVED=1, DISCONTINUE_REQ_APPR_DATE=GETDATE(),DISCONTINUE_APPROVE_BY_ID=$userid,
						ACTIVATE=0 WHERE REGDID=$REGDID";

			$stmt=sqlsrv_query($mysqli, $ApprDis);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), false));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['ApprDis'] = $ApprDis;
				$data['message'] = 'Request successfully approved.';
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
/* =========== Discontinue Approve =========== */ 








/*============ Get WRONG ATT MARKED =============*/ 
function getTeacherWrongAttMark($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT TAID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,TEACHERID,
		(SELECT LASTNAME + ' '+ FIRSTNAME FROM USERS WHERE UID=TA.TEACHERID) TEACHER,
		CONVERT(VARCHAR,TIME_IN,100)TIME_IN,CONVERT(VARCHAR,TIME_OUT,100)TIME_OUT,
		CONVERT(VARCHAR(5),DATEADD(SECOND, - DATEDIFF(SECOND, TIME_OUT, TIME_IN), '00:00:00'),8) AS NO_OF_HOURS,
		CAST(DATEDIFF(second, CONVERT(VARCHAR,TIME_IN,8), CONVERT(VARCHAR,TIME_OUT,8)) / 3600.0 AS decimal(10,2)) AS NO_OF_HOURS_DECIMAL
		FROM TEACHER_ATTENDANCE TA
		WHERE ISDELETED=0 AND TEACHERID IN (SELECT UID FROM USERS WHERE LOCID=$ddlLocation) AND 
		(DATEDIFF(second, CONVERT(VARCHAR,TIME_IN,8), CONVERT(VARCHAR,TIME_OUT,8)) / 3600.0>4 OR
		DATEDIFF(second, CONVERT(VARCHAR,TIME_IN,8), CONVERT(VARCHAR,TIME_OUT,8)) / 3600.0<0)";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TAID'] = (int) $row['TAID'];
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
/*============ Get WRONG ATT MARKED =============*/








/*============ STUDENT NO ATTENDANCE LAST TOW WEEK =============*/ 
function getST_NO_ATT($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT REGID,FIRSTNAME,LASTNAME,GRADE,P1_FIRSTNAME,P1_LASTNAME,P1_PHONE,P1_EMAIL,
		P2_FIRSTNAME,P2_LASTNAME,P2_PHONE,P2_EMAIL
		FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation AND REGID NOT IN (
		SELECT REGID FROM STUDENT_ATTENDANCE WHERE CONVERT(DATE,ATTDATE,105) BETWEEN CONVERT(DATE,GETDATE()-14,105) AND CONVERT(DATE,GETDATE(),105) AND
		 PRESENT=0
		) AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,COURSE_STARTDT,105) AND CONVERT(DATE,COURSE_ENDDT,105))
		AND ISDELETED=0 AND APPROVED=1";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data not found.';
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
/*============ STUDENT NO ATTENDANCE LAST TOW WEEK =============*/








/*============ TEACHER NO ATTENDANCE LAST TOW WEEK =============*/ 
function getTeacher_NO_ATT($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT [UID],FIRSTNAME,LASTNAME,MOBILE,EMAIL,
		ISNULL((SELECT (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TL.LOCID) + ' | ' FROM TEACHER_LOCATION TL WHERE TEACHERID=U.[UID] AND ISDELETED=0 FOR XML PATH('')),'-')[LOCATION]
		FROM USERS U WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE='TEACHER' AND
		[UID] NOT IN (SELECT TEACHERID FROM TEACHER_ATTENDANCE 
		WHERE CONVERT(DATE,ATTDATE,105) BETWEEN CONVERT(DATE,GETDATE()-14,105) AND CONVERT(DATE,GETDATE(),105))";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['UID'] = (int) $row['UID'];
				$row['LOCATION'] = rtrim($row['LOCATION'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data not found.';
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
/*============ TEACHER NO ATTENDANCE LAST TOW WEEK =============*/








/*============ DUPLICATE STUDENT LIST =============*/ 
function getDuplicateStudent($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT FIRSTNAME, LASTNAME,P1_FIRSTNAME,P1_LASTNAME, COUNT(*)TOTAL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation
		GROUP BY FIRSTNAME, LASTNAME, P1_FIRSTNAME,P1_LASTNAME
		HAVING COUNT(*) > 1
		ORDER BY FIRSTNAME";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data not found.';
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
/*============ DUPLICATE STUDENT LIST =============*/ 




/*============ STUDENT ACCOUNT DELETE REQUEST LIST =============*/ 
function getStudentAccountDeleteRequest($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT ADRID,USERID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ADR.USERID)STUDENTNAME,
		(SELECT P1_FIRSTNAME+' '+P1_LASTNAME FROM REGISTRATIONS WHERE REGID=ADR.USERID)PARENT1,REASON,
		CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE
		FROM ACCOUNT_DELETE_REQUEST ADR WHERE USERTYPE='STUDENT' AND DELETE_CONFIRM=0 AND 
		USERID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";
		
		$data['$query'] = $query;
		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data not found.';
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
/*============ STUDENT ACCOUNT DELETE REQUEST LIST =============*/ 




/*============ DELETE USER ACCOUNT =============*/ 
function deleteAccount($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$ADRID = ($_POST['ADRID'] == 'undefined' || $_POST['ADRID'] == '') ? 0 : $_POST['ADRID'];

		$query = "UPDATE REGISTRATIONS SET ARCHIVED=1,ARCHIVED_BY=$userid,ARCHIVED_DT=GETDATE() WHERE REGID=$REGID;
				UPDATE ACCOUNT_DELETE_REQUEST SET DELETE_CONFIRM=1,DELETEBYID=$userid,DELETEDATE=GETDATE() WHERE ADRID=$ADRID";
		
		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		if($result==false){
			$data['success'] = false;
			$data['message'] = 'Error in deleting user account.';
		}else{
			$data['success'] = true;
			$data['message'] = 'User account deleted successfully.';
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
/*============ STUDENT ACCOUNT DELETE REQUEST LIST =============*/ 




/*============ IT Dashboard =============*/ 
function getITDashBoard($mysqli){
	try
	{
		$data = array();
		global $userid_locid;
		global $userid;

		$txtFromDate_AT = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate_AT = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		
		$data = array();
        
		
		
		//$userid  = ($_POST['userid'] == 'undefined' || $_POST['userid'] == '') ? 0 : $_POST['userid'];
		
		$query="EXEC [IT_DASHBOARD_SUMMARY_REPORT_SP] $userid_locid,'$txtToDate_AT','$txtFromDate_AT'";
		
		$result = sqlsrv_query($mysqli, $query);
		
		
		$data = array();
		
		$query = "SELECT DASHBOARD_SUMMARY_ID,PARAMETER,VALUE from IT_DASHBOARD_SUMMARY_REPORT";
						
       
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data not found.';
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
/*============ IT Dashboard =============*/ 


/*============ Get Global Time =============*/ 

header('Content-Type: application/json');

function getGlobalTime(){
    try {
        $data = array();
        date_default_timezone_set('Asia/Calcutta');
        $time = date("h:i a");
        $data['time'][] = ['LOCATION'=>'India','TIME'=>$time];
		
		date_default_timezone_set("America/New_York");
		$time = date("h:i a");
        $data['time'][] = ['LOCATION'=>'Virginia, USA','TIME'=>$time];
		
       date_default_timezone_set('America/Vancouver');
 		$time = date("h:i a");
        $data['time'][] = ['LOCATION'=>'Surrey, Canada','TIME'=>$time];
		
		date_default_timezone_set('Africa/Casablanca');
 		$time = date("h:i a");
        $data['time'][] = ['LOCATION'=>'Morocco','TIME'=>$time];

		date_default_timezone_set('America/Paramaribo');
 		$time = date("h:i a");
        $data['time'][] = ['LOCATION'=>'Suriname','TIME'=>$time];
		
		$data['success'] = true;
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array();
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data);
        exit;
    }
}

getGlobalTime();


/*============ Get Global Time =============*/ 






function saveTaskTrackingDetails($mysqli){
	try
	{
	   $data = array();
	   
	   $data['TASK_CLOSED'] = false;
	   $TTDETID  = ($_POST['TTDETID'] == 'undefined' || $_POST['TTDETID'] == '') ? 0 : $_POST['TTDETID'];
	   $userid  = ($_POST['userid'] == 'undefined' || $_POST['userid'] == '') ? 0 : $_POST['userid'];
	   $TASKMGMTID  = ($_POST['TASKMGMTID'] == 'undefined' || $_POST['TASKMGMTID'] == '') ? 0 : $_POST['TASKMGMTID'];
	   $txtReview  = ($_POST['txtReview'] == 'undefined' || $_POST['txtReview'] == '') ? '' : str_replace("'","''",$_POST['txtReview']);
	   $txtLinkReview  = ($_POST['txtLinkReview'] == 'undefined' || $_POST['txtLinkReview'] == '') ? '' : str_replace("'","''",$_POST['txtLinkReview']);
	   
	   if($txtReview == '')throw new Exception("Please Enter Review.");

		// CHECK STATUS CLOSED OR OPEN
		$queryChk = "SELECT TASKSTATUS FROM TASK_USERS WHERE TASKMGMTID=$TASKMGMTID";
		$countChk = unique($queryChk);
		if($countChk>0){
			$stmtChk = sqlsrv_query($mysqli,$queryChk);
			$rowChk = sqlsrv_fetch_array($stmtChk,SQLSRV_FETCH_ASSOC);
			$TASKSTATUS = $rowChk['TASKSTATUS'];
			if($TASKSTATUS=='CLOSED'){
				$data['success'] = false;
				$data['TASK_CLOSED'] = true;
				$data['message'] = 'Task Closed.';
				echo json_encode($data);exit;
			}
		}

		$query="INSERT INTO TASK_TRACKING_DETAILS (TASKMGMTID,REVIEWEDBYID,REVIEWEDON,REVIEW,SEND_BY,FILELINK,INSERTDATE)VALUES($TASKMGMTID,$userid,GETDATE(),'$txtReview','ADMIN','$txtLinkReview',$userid)";
		$query.=";UPDATE TASK_USERS SET TASKSTATUS='WIP' WHERE TASKMGMTID=$TASKMGMTID";
		
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
			if(!empty($TTDETID))$data['message'] = 'Record successfully updated';
			else $data['message'] = 'Record successfully inserted.';
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



/*============ GET TRASK TRACKING DETAILS =============*/ 
function getTaskTrackDetails($mysqli){
	try
	{
		$data = array();
		$TASKMGMTID = ($_POST['TASKMGMTID'] =='undefined' || $_POST['TASKMGMTID'] =='') ? 0 : $_POST['TASKMGMTID'];
		if($TASKMGMTID==0) throw new Exception('Error : TASKMGMTID Not Found.');
		$query = "SELECT TTDETID,REVIEWEDBYID,CONVERT(VARCHAR,REVIEWEDON,106)REVIEWEDON
		,REVIEW,FILELINK,
		CASE WHEN SEND_BY='STUDENTS'
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TTD.REVIEWEDBYID) 
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TTD.REVIEWEDBYID) 
		END REVIEWERNAME 	
		FROM TASK_TRACKING_DETAILS TTD WHERE TASKMGMTID=$TASKMGMTID  ORDER BY TTDETID DESC";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
		
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TTDETID'] = (int) $row['TTDETID'];
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
/*============ GET TRASK TRACKING DETAILS =============*/ 



/* =========== Cloase TAsk =========== */ 
function ClosedTask($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TASKMGMTID = ($_POST['TASKMGMTID'] == 'undefined' || $_POST['TASKMGMTID'] == '') ? 0 : $_POST['TASKMGMTID'];  
			$CLOSEDBY = ($_POST['CLOSEDBY'] == 'undefined' || $_POST['CLOSEDBY'] == '') ? 0 : $_POST['CLOSEDBY'];
			if($TASKMGMTID==0) throw new Exception('Error : TASKMGMTID Not Found.');
			$query="UPDATE TASK_USERS SET TASKSTATUS='CLOSED',CLOSEDBY='$CLOSEDBY',CLOSEDBYID=$userid,CLOSEDON=GETDATE() WHERE TASKMGMTID=$TASKMGMTID";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Task successfully closed.';
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







?>