<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['ROLE']))
{$role=$_SESSION['ROLE'];}
else
{$role=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "Save":Save($conn);break;
        case "getAtt":getAtt($conn);break;
        case "getTeacher":getTeacher($conn);break;
        case "getTeacherProduct":getTeacherProduct($conn);break;
        case "chkAttDT":chkAttDT($conn);break;
        case "delete":delete($conn);break;

		case "getStudentByTeacher":getStudentByTeacher($conn);break;		

		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



 /*========== SAVE DATA =========*/
 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;

		
		$attid = ($_POST['attid'] == 'undefined' || $_POST['attid'] == '') ? 0 : $_POST['attid'];
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$txtAttDate = ($_POST['txtAttDate'] == 'undefined' || $_POST['txtAttDate'] == '') ? '' : $_POST['txtAttDate'];
		$selectedStudent = (!isset($_POST['selectedStudent']) || $_POST['selectedStudent'] == 'undefined' || $_POST['selectedStudent'] == '') ? array() : json_decode($_POST['selectedStudent']);
		$txtTimeIN = ($_POST['txtTimeIN'] == 'undefined' || $_POST['txtTimeIN'] == '') ? '' : $_POST['txtTimeIN'];
		$txtTimeOUT = ($_POST['txtTimeOUT'] == 'undefined' || $_POST['txtTimeOUT'] == '') ? '' : $_POST['txtTimeOUT'];   
		$txtRemark = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];   
		
		//  $data['$selectedStudent']=$selectedStudent[0]->REQID;
		// $data['date']=$txtTimeIN.'/'.$txtTimeOUT;
		// $data['getTotalTime']=getTotalTime($txtTimeIN,$txtTimeOUT);
		// echo json_encode($data);exit;
		
		$actionid = $attid == 0 ? 1 : 2;

		if($ddlTeacher == 0) throw new Exception("Please Select Teacher.");
		if($ddlSubject == 0) throw new Exception("Please Select Teacher.");
		if($txtAttDate == '') throw new Exception("Please Select Attendance Date.");
		if(count($selectedStudent) == 0 && $actionid==1) throw new Exception("Please Select Student.");
		if($txtTimeIN == '') throw new Exception("Please Select Att From Time.");
		if($txtTimeOUT == '') throw new Exception("Please Select Att To Time.");

		$getTotalHour=getTotalTime($txtTimeIN,$txtTimeOUT);


		for($i=0; $i< count($selectedStudent); $i++)
		{

			$REQID=$selectedStudent[$i]->REQID;
			$REGID=$selectedStudent[$i]->REGID;				
			$RATE_PER_HOUR=$selectedStudent[$i]->RATE_PER_HOUR;

			$sql = "SELECT * FROM STUDENT_TUTORING_ATTENDANCE WHERE REQID=$REQID AND TEACHERID=$ddlTeacher AND CSUBID=$ddlSubject AND REGID=$REGID
			AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' AND ATTID!=$attid AND ISDELETED=0";
			$row_count = unique($sql);
			if($row_count == 0)
			{
				$query="EXEC [STUDENT_TUTORING_ATTENDANCE_SP] $actionid,$attid,$REQID,$ddlTeacher,$ddlSubject,$REGID,'$txtAttDate',
				'$txtTimeIN','$txtTimeOUT','$getTotalHour',$RATE_PER_HOUR,0,'$txtRemark',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
			}
			else{
				$STUDENTNAME=$selectedStudent[$i]->STUDENTNAME;
				throw new Exception('Record Already Exist. '.$STUDENTNAME.' ('.$txtTimeIN.'-'.$txtTimeOUT.')');
			}
			
		}

			
			// if($stmt === false)
			// {
			// 	// die( print_r( sqlsrv_errors(), true));
			// 	// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			// 	$data['success'] = true;
			// 	$data['query'] = $query;
			// 	echo json_encode($data);exit;
			// }
			// else
			// {
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($attid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
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
 /*========== SAVE DATA =========*/



 function getTotalTime($dt1, $dt2)
 {
	
	$date1 = new DateTime($dt1);
	$date2 = new DateTime($dt2);
	if ($date2 < $date1) {
		$date2->modify('+1 day');
	}
	$interval = $date1->diff($date2);
	$days = $interval->days;
	$hours = ($interval->h + $days * 24) <= 9 ? '0' . ($interval->h + $days * 24) : ($interval->h + $days * 24);
	$minutes = ($interval->i) <= 9 ? '0' . $interval->i : $interval->i;
	$seconds = ($interval->s) <= 9 ? '0' . $interval->s : $interval->s;

	return $hours.':'.$minutes.':' .$seconds;
	// return $interval->format('%a days, %h hours, %i minutes, %s seconds');
 }




/*============ Get Att =============*/ 
 function getAtt($mysqli){
	try
	{
		global $userid;
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];		

		$query = "SELECT ATTID, REQID, TEACHERID,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=ST.TEACHERID)TEACHERSNAME,
		(SELECT DBO.GET_CURRENCYCODE_BY_USER(ST.TEACHERID)) CURRENCY,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)SUBJECT,
		REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
		CONVERT(VARCHAR, ATTDATE,106)ATTDATE,
		FORMAT(ATTFROMTIME,'HH:mm:ss')ATTFROMTIME_SET,FORMAT(ATTFROMTIME,'hh:mm tt')ATTFROMTIME,
		FORMAT(ATTTOTIME,'HH:mm:ss')ATTTOTIME_SET,FORMAT(ATTTOTIME,'hh:mm tt')ATTTOTIME,
		CONVERT(VARCHAR(5), ATTHOURS, 108)ATTHOURS,RATE,AMOUNT,ISNULL(REMARKS,'-')REMARKS
		FROM STUDENT_TUTORING_ATTENDANCE ST WHERE ISDELETED=0
		AND TEACHERID IN (SELECT UID FROM USERS WHERE LOCID=$ddlLocation)";

		if($ddlTeacher>0) $query.=" AND TEACHERID=$ddlTeacher";
		$query .=" ORDER BY TEACHERSNAME, STUDENTNAME";

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ Get Att =============*/ 




/*============ Get Teacher =============*/ 
function getTeacher($mysqli){
	try
	{
		global $role;
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$userrole=($_POST['userrole'] == 'undefined' || $_POST['userrole'] == '') ? 0 : $_POST['userrole'];
		$query = "SELECT UID,FIRSTNAME+' '+LASTNAME AS FULLNAME FROM USERS WHERE LOCID=$ddlLocation AND USERROLE='TEACHER' AND ISDELETED=0 ORDER BY FULLNAME";
	
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Teacher =============*/ 




/*============ Get Teacher Product =============*/ 
function getTeacherProduct($mysqli){
	try
	{
		global $role;
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "SELECT PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TP.PRODUCTID)PRODUCT
		FROM TEACHER_PRODUCT TP
		WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count>0){
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
/*============ Get Teacher Product =============*/ 




/*============ Get Att DT =============*/ 
function chkAttDT($mysqli){
	try
	{
		global $role;
		$data = array();
		$locid=($_POST['locid'] == 'undefined' || $_POST['locid'] == '') ? 0 : $_POST['locid'];
		$query = "SELECT CONVERT(VARCHAR,CLOSED_DATE,23)CLOSED_DATE FROM TEACHER_ATTENDANCE_SETTING WHERE LOCID=1 AND ISDELETED=0";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'] = $row['CLOSED_DATE'];
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
/*============ Get Att DT =============*/ 



/*============ Get Teacher Product =============*/ 
function getStudentByTeacher($mysqli){
	try
	{
		global $role;
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

		$query = "SELECT REQPID, REQID, REGID, (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,RATE_PER_HOUR
		FROM STUDENT_TUTORING_REQUEST_PROCESS ST WHERE ISDELETED=0 AND REGID>0 AND TEACHERID=$ddlTeacher";
		if($REGID>0) $query.=" AND REGID=$REGID";
		$query.=" ORDER BY STUDENTNAME";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count>0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
/*============ Get Teacher Product =============*/ 


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ATTID = ($_POST['ATTID'] == 'undefined' || $_POST['ATTID'] == '') ? 0 : $_POST['ATTID'];  
			if($ATTID == 0)throw new Exception("ATTID Not Found.");

			$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_ATTENDANCE_SP] 3,$ATTID,0,0,0,0,'','','','',0,0,'',$userid");
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
/* =========== Delete =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







