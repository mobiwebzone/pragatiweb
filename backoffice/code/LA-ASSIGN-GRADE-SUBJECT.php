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
        case "assignGradeSubject":assignGradeSubject($conn);break;
        case "assignGradeSubject_Teacher":assignGradeSubject_Teacher($conn);break;
        case "getAssignedData":getAssignedData($conn);break;
        case "getStudents":getStudents($conn);break;
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


function assignGradeSubject($mysqli){
	try
	{
	$data = array();
	global $userid;
	
	$sgsid  = ($_POST['sgsid'] == 'undefined' || $_POST['sgsid'] == '') ? 0 : $_POST['sgsid'];
	$studentIdList  = (empty($_POST['studentIdList']) ||  $_POST['studentIdList'] == 'undefined' || $_POST['studentIdList'] == '') ? array() : explode(',',$_POST['studentIdList']);
	$ddlGrade  = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
	$ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? array() : explode(",",$_POST['ddlSubject']);
	// $ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];

	// $data['ddlSubject']=$ddlSubject;
	// echo json_encode($data);exit;
	
	$actionid = $sgsid == 0 ? 1 : 2;
	
	
	if($ddlGrade == 0)throw new Exception("Please Select Grade.");
	if(count($ddlSubject)<=0)throw new Exception("Please Select Subject Name.");
	
	// $sql = "SELECT * FROM LA_SUBJECT_MASTER WHERE LOCID=$ddlLocation AND SUBJECTNAME='$txtSubject' AND SUBID!=$subid AND ISDELETED=0";
	// $row_count = unique($sql);
	
	
	// if($row_count == 0)
	// {
		if($actionid==1){
			if(count($studentIdList) == 0)throw new Exception("Student Not Found");
			if(count($studentIdList)>0){
				foreach($studentIdList as $val){
					$REGID = $val;

					foreach($ddlSubject as $SUBID){
						if($REGID > 0 && $SUBID>0){
							// CHECK DUPLICATE
							$chk = "SELECT * FROM STUDENTS_GRADES_SUBJECTS WHERE ISDELETED=0 AND REGID=$REGID AND GRADEID=$ddlGrade AND SUBID=$SUBID";
							$CNT = unique($chk);
							if($CNT == 0){
								$query="INSERT INTO STUDENTS_GRADES_SUBJECTS (REGID,GRADEID,SUBID,INSERTID)
										VALUES($REGID,$ddlGrade,$SUBID,$userid)";
								$data['$query'] = $query;
								// echo json_encode($data);exit;
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
								}	
							}
						}
					}
				}
				
			}else{
				throw new Exception('Student Not Found.');
			}
		}
		else if($actionid==2){
			$query="UPDATE STUDENTS_GRADES_SUBJECTS SET GRADEID=$ddlGrade,UPDATEID=$userid,UPDATEDATE=GETDATE() 
					WHERE SGSID=$sgsid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
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
			}	
		}else{
			throw new Exception('Error : actionid');
		}

		if(!empty($sgsid))$data['message'] = 'Record successfully updated';
		else $data['message'] = 'Record successfully inserted.';
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Subject already exists.';
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


function assignGradeSubject_Teacher($mysqli){
	try
	{
	$data = array();
	global $userid;
	
	$sgsid  = ($_POST['sgsid'] == 'undefined' || $_POST['sgsid'] == '') ? 0 : $_POST['sgsid'];
	$teacheridList  = (empty($_POST['teacheridList']) ||  $_POST['teacheridList'] == 'undefined' || $_POST['teacheridList'] == '') ? array() : explode(',',$_POST['teacheridList']);
	$ddlGrade  = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
	$ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? array() : explode(",",$_POST['ddlSubject']);
	// $ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];

	// $data['teacheridList']=$teacheridList;
	// echo json_encode($data);exit;
	
	$actionid = $sgsid == 0 ? 1 : 2;
	
	
	if($ddlGrade == 0)throw new Exception("Please Select Grade.");
	if(count($ddlSubject)<=0)throw new Exception("Please Select Subject Name.");
	
	// $sql = "SELECT * FROM LA_SUBJECT_MASTER WHERE LOCID=$ddlLocation AND SUBJECTNAME='$txtSubject' AND SUBID!=$subid AND ISDELETED=0";
	// $row_count = unique($sql);
	
	
	// if($row_count == 0)
	// {
		if($actionid==1){
			if(count($teacheridList) == 0)throw new Exception("Teacher Not Found");
			if(count($teacheridList)>0){
				foreach($teacheridList as $val){
					$TEACHERID = $val;
					foreach($ddlSubject as $SUBID){
						if($TEACHERID > 0 && $SUBID>0){
							// CHECK DUPLICATE
							$chk = "SELECT * FROM TEACHERS_GRADES_SUBJECTS WHERE ISDELETED=0 AND TEACHERID=$TEACHERID AND GRADEID=$ddlGrade AND SUBID=$SUBID";
							$CNT = unique($chk);
							if($CNT == 0){
								$query="INSERT INTO TEACHERS_GRADES_SUBJECTS (TEACHERID,GRADEID,SUBID,INSERTID)
										VALUES($TEACHERID,$ddlGrade,$SUBID,$userid)";
								$data['$query'] = $query;
								// echo json_encode($data);exit;
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
								}	
							}
						}
					}
					
				}
				
			}else{
				throw new Exception('Teacher Not Found.');
			}
		}
		else if($actionid==2){
			$query="UPDATE TEACHERS_GRADES_SUBJECTS SET GRADEID=$ddlGrade,UPDATEID=$userid,UPDATEDATE=GETDATE() 
					WHERE SGSID=$sgsid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
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
			}	
		}else{
			throw new Exception('Error : actionid');
		}

		if(!empty($sgsid))$data['message'] = 'Record successfully updated';
		else $data['message'] = 'Record successfully inserted.';
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Subject already exists.';
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


/*============ GET ASSIGNED DATA =============*/ 
function getAssignedData($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlStudentTeacher = ($_POST['ddlStudentTeacher'] =='undefined' || $_POST['ddlStudentTeacher'] =='') ? '' : $_POST['ddlStudentTeacher'];
		$ddlGrade = ($_POST['ddlGrade'] =='undefined' || $_POST['ddlGrade'] =='') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] =='undefined' || $_POST['ddlSubject'] =='') ? 0 : $_POST['ddlSubject'];
		if($ddlStudentTeacher=='') throw new Exception('Invalid Assign For.');
		if($ddlStudentTeacher=='STUDENT'){
			$query = "SELECT 'STUDENT' STUDENT_TEACHER,SGSID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)ASSIGNTO_NAME,
			(SELECT (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SGS.REGID)[LOCATION],
			GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SGS.GRADEID)GRADE,
			SUBID,(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SGS.SUBID)[SUBJECT]
			FROM STUDENTS_GRADES_SUBJECTS SGS
			WHERE ISDELETED=0 AND REGID NOT IN(SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1) 
			AND (SELECT LOCATIONID FROM REGISTRATIONS R WHERE REGID=SGS.REGID)=$ddlLocation";
		}else{
			$query = "SELECT 'TEACHER' STUDENT_TEACHER, SGSID,TEACHERID,(SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE [UID]=GS.TEACHERID)ASSIGNTO_NAME,
			(SELECT (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=U.LOCID) FROM USERS U WHERE [UID]=GS.TEACHERID)[LOCATION],
			GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=GS.GRADEID)GRADE,
			SUBID,(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=GS.SUBID)[SUBJECT]
			FROM TEACHERS_GRADES_SUBJECTS GS
			WHERE ISDELETED=0 AND TEACHERID NOT IN(SELECT [UID] FROM USERS WHERE ARCHIVED=1) 
			AND (SELECT LOCID FROM USERS WHERE [UID]=GS.TEACHERID)=$ddlLocation";
		}
		if($ddlGrade>0)$query .= " AND GRADEID=$ddlGrade";
		if($ddlSubject>0)$query .= " AND SUBID=$ddlSubject";
		$query .= " ORDER BY [LOCATION],ASSIGNTO_NAME";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);

			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SGSID'] = (int) $row['SGSID'];
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
/*============ GET ASSIGNED DATA =============*/ 


/*============ Get Student =============*/ 
function getStudents($mysqli){
	try
	{
		$data = array();
        // $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        // $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT REGID,FIRSTNAME+' '+LASTNAME STUDENT,
		P1_FIRSTNAME+' '+P1_LASTNAME PARENT
		FROM REGISTRATIONS SR WHERE ISDELETED=0 AND ARCHIVED=0 AND LOCATIONID=$ddlLocation
		AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND PLANID=$ddlPlan)
		ORDER BY STUDENT";

		// if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
		// $query .=" ORDER BY STUDENT";

		$data['$query ']=$query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Student =============*/



/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SGSID = ($_POST['SGSID'] == 'undefined' || $_POST['SGSID'] == '') ? 0 : $_POST['SGSID'];  
			$ddlStudentTeacher = ($_POST['ddlStudentTeacher'] =='undefined' || $_POST['ddlStudentTeacher'] =='') ? '' : $_POST['ddlStudentTeacher'];

			if($SGSID==0) throw new Exception('Error : SGSID Not Found.');
			if($ddlStudentTeacher=='') throw new Exception('Invalid Assign For.');
			if($ddlStudentTeacher=='STUDENT'){
				$query = "UPDATE STUDENTS_GRADES_SUBJECTS SET ISDELETED=1, DELETEID=$userid, DELETEDATE=GETDATE() WHERE SGSID=$SGSID";
			}else{
				$query = "UPDATE TEACHERS_GRADES_SUBJECTS SET ISDELETED=1, DELETEID=$userid, DELETEDATE=GETDATE() WHERE SGSID=$SGSID";
			}
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
				$data['message'] = 'Record successfully deleted.';
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







