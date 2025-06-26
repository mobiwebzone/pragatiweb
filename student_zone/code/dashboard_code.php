<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getUserDetailsForUpdate":getUserDetailsForUpdate($conn);break;
        case "updateParent":updateParent($conn);break;
        case "updateGradeClassof":updateGradeClassof($conn);break;
        // case "updatePersonalDet":updatePersonalDet($conn);break;
        case "updateClassesAtSchool":updateClassesAtSchool($conn);break;

        case "getStudentPlanData":getStudentPlanData($conn);break;
        case "getStudentPlans":getStudentPlans($conn);break;
        case "getAttendance":getAttendance($conn);break;
        case "getSms":getSms($conn);break;
        case "getMeetingLinks":getMeetingLinks($conn);break;
        case "getLocationReview":getLocationReview($conn);break;
        case "getResources":getResources($conn);break;
        case "LinkVoting":LinkVoting($conn);break;
        case "getAnnouncement":getAnnouncement($conn);break;
		case "getSupport":getSupport($conn);break;
		
        case "saveReferral":saveReferral($conn);break;
        case "getReferrals":getReferrals($conn);break;

        case "saveHomeWork":saveHomeWork($conn);break;
        case "getTutoringAttendance":getTutoringAttendance($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}



/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

// #############################################################################################
// ###### UPDATE DETAILS START
// #############################################################################################
/*============ Get Student Details =============*/ 
function getUserDetailsForUpdate($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

		// PARENT 1
		// $queryP1 = "SELECT P1_FIRSTNAME,P1_LASTNAME,P1_EMAIL,P1_PHONE FROM REGISTRATIONS 
		// WHERE ((P1_FIRSTNAME='' OR P1_FIRSTNAME='TBD' OR LEN(P1_FIRSTNAME)<2 OR P1_FIRSTNAME='NA' OR P1_FIRSTNAME='N/A') 
		// 	OR (P1_LASTNAME='' OR P1_LASTNAME='TBD' OR LEN(P1_LASTNAME)<2 OR P1_LASTNAME='NA' OR P1_LASTNAME='N/A')  
		// 	OR (P1_PHONE='' OR P1_PHONE='TBD' OR LEN(P1_PHONE)<10 OR P1_PHONE='NA' OR P1_PHONE='N/A')
		// 	OR (P1_EMAIL='' OR P1_EMAIL='TBD' OR P1_EMAIL='NA' OR P1_EMAIL='N/A' OR P1_EMAIL NOT LIKE '%@%')
		// ) 
		$queryP1 = "SELECT P1_FIRSTNAME,P1_LASTNAME,P1_EMAIL,P1_PHONE FROM REGISTRATIONS R
		WHERE REGID=$REGID AND                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
		(
		(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))) IS NULL
			OR
		DATEDIFF(DD,(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))),GETDATE())>60
		)";
		$CountP1 = unique($queryP1);
		if($CountP1 > 0){
			$resultP1 = sqlsrv_query($mysqli, $queryP1);
			$rowP1 = sqlsrv_fetch_array($resultP1,SQLSRV_FETCH_ASSOC);
			$data['data_P1'] = $rowP1;
			$data['success_P1'] = true;
		}
		else{
			$data['success_P1'] = false;
		}

		// PARENT 2
		// $queryP2 = "SELECT P2_FIRSTNAME,P2_LASTNAME,P2_EMAIL,P2_PHONE FROM REGISTRATIONS 
		// WHERE ((P2_FIRSTNAME='' OR P2_FIRSTNAME='TBD' OR LEN(P2_FIRSTNAME)<2 OR P2_FIRSTNAME='NA' OR P2_FIRSTNAME='N/A') 
		// 	OR (P2_LASTNAME='' OR P2_LASTNAME='TBD' OR LEN(P2_LASTNAME)<2 OR P2_LASTNAME='NA' OR P2_LASTNAME='N/A') 
		// 	OR (P2_PHONE='' OR P2_PHONE='TBD' OR LEN(P2_PHONE)<10 OR P2_PHONE='NA' OR P2_PHONE='N/A')
		// 	OR (P2_EMAIL='' OR P2_EMAIL='TBD' OR P2_EMAIL='NA' OR P2_EMAIL='N/A' OR P2_EMAIL NOT LIKE '%@%')
		// ) 
		// AND REGID=$REGID";
		$queryP2 = "SELECT P2_FIRSTNAME,P2_LASTNAME,P2_EMAIL,P2_PHONE FROM REGISTRATIONS R
		WHERE REGID=$REGID AND                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
		(
		(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))) IS NULL
			OR
		DATEDIFF(DD,(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))),GETDATE())>60
		)";

		$CountP2 = unique($queryP2);
		if($CountP2 > 0){
			$resultP2 = sqlsrv_query($mysqli, $queryP2);
			$rowP2 = sqlsrv_fetch_array($resultP2,SQLSRV_FETCH_ASSOC);
			
			$data['data_P2'] = $rowP2;
			$data['success_P2'] = true;
		}
		else{
			$data['success_P2'] = false;
		}


		// GRADE & CLASSOF
		$queryGC = "SELECT CLASSOF,GRADE FROM REGISTRATIONS R
		WHERE REGID=$REGID AND                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
		(
		(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((GRADE_FROM!='' AND GRADE_FROM IS NOT NULL) OR (CLASSOF_FROM>0 AND CLASSOF_FROM IS NOT NULL))) IS NULL
			OR
		DATEDIFF(DD,(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((GRADE_FROM!='' AND GRADE_FROM IS NOT NULL) OR (CLASSOF_FROM>0 AND CLASSOF_FROM IS NOT NULL))),GETDATE())>60
		)";
		$CountGC = unique($queryGC);
		if($CountGC > 0){
			$resultGC = sqlsrv_query($mysqli, $queryGC);
			$rowGC = sqlsrv_fetch_array($resultGC,SQLSRV_FETCH_ASSOC);
			$data['data_GC'] = $rowGC;
			$data['success_GC'] = true;
		}
		else{
			$data['success_GC'] = false;
		}
		

		// PERSONAL
		// $queryP = "SELECT PHONE,EMAIL,SCHOOL FROM REGISTRATIONS 
		// WHERE ((PHONE='' OR PHONE='TBD' OR LEN(PHONE)<10 OR PHONE='NA' OR PHONE='N/A' OR PHONE='null' OR PHONE IS NULL)
		// 	OR (EMAIL='' OR EMAIL='TBD' OR LEN(EMAIL)<5 OR EMAIL='NA' OR EMAIL='N/A' OR EMAIL='null' OR EMAIL NOT LIKE '%@%' OR EMAIL LIKE '%tbd%' OR PHONE IS NULL) 
		// 	OR (SCHOOL='' OR SCHOOL='TBD' OR SCHOOL='NA' OR SCHOOL='N/A' OR SCHOOL='null' OR SCHOOL IS NULL)
		// ) 
		// AND REGID=$REGID";
		$queryP = "SELECT PHONE,EMAIL,SCHOOL FROM REGISTRATIONS R
		WHERE REGID=$REGID AND                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
		(
		(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))) IS NULL
			OR
		DATEDIFF(DD,(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
				AND ((PHONE_FROM!='' AND PHONE_FROM IS NOT NULL) OR (EMAIL_FROM!='' AND EMAIL_FROM IS NOT NULL) OR (SCHOOL_FROM!='' AND SCHOOL_FROM IS NOT NULL))),GETDATE())>60
		)";

		$CountP = unique($queryP);
		if($CountP > 0){
			$resultP = sqlsrv_query($mysqli, $queryP);
			$rowP = sqlsrv_fetch_array($resultP,SQLSRV_FETCH_ASSOC);
			$data['data_P'] = $rowP;
			$data['success_P'] = true;
		}
		else{
			$data['success_P'] = false;
		}


		// CLASSES AT SCHOOL
		$queryChk_CAS = "SELECT * FROM STUDENT_PROPOSED_COURSES WHERE ISDELETED=0 AND REGID=$REGID";
		$CountChk_CAS = unique($queryChk_CAS);
		if($CountChk_CAS==0){
			$data['success_CAS'] = true;
			$data['data_CAS'] = array();
		}else{
			$queryCAS = "SELECT GRADEID,(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=R.GRADEID)GRADE,
			CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=R.CSUBID)[SUBJECT],FINAL_DRAFT 
			FROM STUDENT_PROPOSED_COURSES R
			WHERE ISDELETED=0 AND REGID=$REGID AND                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
			(
			(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
					AND (CLASSES_AT_SCHOOL!='' AND CLASSES_AT_SCHOOL IS NOT NULL)) IS NULL
				OR
			DATEDIFF(DD,(SELECT MAX(LOG_DATE) FROM REGISTRATION_UPDATE_LOG WHERE REGID=R.REGID 
					AND (CLASSES_AT_SCHOOL!='' AND CLASSES_AT_SCHOOL IS NOT NULL)),GETDATE())>60
			)";
	
			$CountCAS = unique($queryCAS);
			if($CountCAS > 0){
				$resultCAS = sqlsrv_query($mysqli, $queryCAS);
				while($rowCAS = sqlsrv_fetch_array($resultCAS,SQLSRV_FETCH_ASSOC)){
					$data['data_CAS'][] = $rowCAS;
				}
				$data['success_CAS'] = true;
			}
			else{
				$data['success_CAS'] = false;
			}
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


/* ============ UPDATE PARENT DETAILS ============= */ 
function updateParent($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $txtFirstNameP1 = $_POST['txtFirstNameP1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtFirstNameP1']);
	   $txtLastNameP1 = $_POST['txtLastNameP1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLastNameP1']);
	   $txtPhoneNumberP1 = $_POST['txtPhoneNumberP1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberP1']);
	   $txtEmailIdP1 = $_POST['txtEmailIdP1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdP1']);
	   $txtFirstNameP2 = $_POST['txtFirstNameP2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtFirstNameP2']);
	   $txtLastNameP2 = $_POST['txtLastNameP2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLastNameP2']);
	   $txtPhoneNumberP2 = $_POST['txtPhoneNumberP2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberP2']);
	   $txtEmailIdP2 = $_POST['txtEmailIdP2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdP2']);
	   
	   $txtPhoneNumberPD = $_POST['txtPhoneNumberPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberPD']);
	   $txtPhoneNumberPD_old = $_POST['txtPhoneNumberPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberPD_old']);
	   $txtEmailIdPD = $_POST['txtEmailIdPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdPD']);
	   $txtEmailIdPD_old = $_POST['txtEmailIdPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdPD_old']);
	   $txtSchoolPD = $_POST['txtSchoolPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSchoolPD']);
	   $txtSchoolPD_old = $_POST['txtSchoolPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSchoolPD_old']);
	   
	   if($txtPhoneNumberPD=='')throw new Exception('Enter Phone Number.');
	   if($txtEmailIdPD=='')throw new Exception('Enter Email ID.');
	   if($txtSchoolPD=='')throw new Exception('Enter School Name.');
	   
	   if($txtFirstNameP1=='')throw new Exception('Enter Parent 1 First Name.');
	   if($txtLastNameP1=='')throw new Exception('Enter Parent 1 Last Name.');
	   if($txtPhoneNumberP1=='')throw new Exception('Enter Parent 1 Phone Number.');
	   if(strlen($txtPhoneNumberP1)<10)throw new Exception('Error : Invalid Parent 1 Phone Number.');

	   if($txtFirstNameP2=='')throw new Exception('Enter Parent 2 First Name.');
	   if($txtLastNameP2=='')throw new Exception('Enter Parent 2 Last Name.');
	   if($txtPhoneNumberP2=='')throw new Exception('Enter Parent 2 Phone Number.');
	   if(strlen($txtPhoneNumberP2)<10)throw new Exception('Error : Invalid Parent 2 Phone Number.');


		$query="UPDATE REGISTRATIONS SET 
				P1_FIRSTNAME='$txtFirstNameP1',P1_LASTNAME='$txtLastNameP1',P1_EMAIL='$txtEmailIdP1',P1_PHONE='$txtPhoneNumberP1',
				P2_FIRSTNAME='$txtFirstNameP2',P2_LASTNAME='$txtLastNameP2',P2_EMAIL='$txtEmailIdP2',P2_PHONE='$txtPhoneNumberP2',
				PHONE='$txtPhoneNumberPD',EMAIL='$txtEmailIdPD',SCHOOL='$txtSchoolPD' 
				WHERE REGID=$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		$data['query'] = $query;
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{

			// INSERT LOG
			$queryLog="INSERT INTO REGISTRATION_UPDATE_LOG (REGID,PHONE_FROM,PHONE_TO,EMAIL_FROM,EMAIL_TO,SCHOOL_FROM,SCHOOL_TO) 
					VALUES ($userid,'$txtPhoneNumberPD','$txtPhoneNumberPD_old','$txtEmailIdPD','$txtEmailIdPD_old','$txtSchoolPD','$txtSchoolPD_old')";
			$data['queryLog'] = $queryLog;
			sqlsrv_query($mysqli, $queryLog);


			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Details successfully updated.';
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
/* ============ UPDATE PARENT DETAILS ============= */ 


/* ============ UPDATE GRADE & CLASSOF DETAILS ============= */ 
function updateGradeClassof($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $txtGrade = $_POST['txtGrade'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGrade']);
	   $txtGrade_old = $_POST['txtGrade_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGrade_old']);
	   $txtClassof = ($_POST['txtClassof'] == 'undefined' || $_POST['txtClassof'] == '') ? 0 : $_POST['txtClassof'];
	   $txtClassof_old = ($_POST['txtClassof_old'] == 'undefined' || $_POST['txtClassof_old'] == '') ? 0 : $_POST['txtClassof_old'];
	   
	   if($txtGrade=='')throw new Exception('Enter Grade.');
	   if($txtClassof==0)throw new Exception('Enter Classof.');

		$query="UPDATE REGISTRATIONS SET GRADE='$txtGrade',CLASSOF=$txtClassof WHERE REGID=$userid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{
			// INSERT LOG
			$queryLog="INSERT INTO REGISTRATION_UPDATE_LOG (REGID,GRADE_FROM,GRADE_TO,CLASSOF_FROM,CLASSOF_TO) 
					VALUES ($userid,'$txtGrade_old','$txtGrade',$txtClassof_old,$txtClassof)";
			$data['queryLog'] = $queryLog;
			sqlsrv_query($mysqli, $queryLog);

			$data['success'] = true;
			$data['message'] = 'Details successfully updated.';
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
/* ============ UPDATE GRADE & CLASSOF DETAILS ============= */ 


/* ============ UPDATE PERSONAL DETAILS ============= */ 
// function updatePersonalDet($mysqli){
// 	try
// 	{
// 	   $data = array();
// 	   global $userid;

// 	   $txtPhoneNumberPD = $_POST['txtPhoneNumberPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberPD']);
// 	   $txtPhoneNumberPD_old = $_POST['txtPhoneNumberPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneNumberPD_old']);
// 	   $txtEmailIdPD = $_POST['txtEmailIdPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdPD']);
// 	   $txtEmailIdPD_old = $_POST['txtEmailIdPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailIdPD_old']);
// 	   $txtSchoolPD = $_POST['txtSchoolPD'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSchoolPD']);
// 	   $txtSchoolPD_old = $_POST['txtSchoolPD_old'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSchoolPD_old']);
	   
// 	   if($txtPhoneNumberPD=='')throw new Exception('Enter Phone Number.');
// 	   if($txtEmailIdPD=='')throw new Exception('Enter Email ID.');
// 	   if($txtSchoolPD=='')throw new Exception('Enter School Name.');

// 		$query="UPDATE REGISTRATIONS SET PHONE='$txtPhoneNumberPD',EMAIL='$txtEmailIdPD',SCHOOL='$txtSchoolPD' WHERE REGID=$userid";
// 		$data['query'] = $query;
// 		$stmt=sqlsrv_query($mysqli, $query);
		
// 		if($stmt === false)
// 		{
// 			// die( print_r( sqlsrv_errors(), true));
// 			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
// 			$data['success'] = false;
// 		}
// 		else
// 		{
// 			// INSERT LOG
// 			$queryLog="INSERT INTO REGISTRATION_UPDATE_LOG (REGID,PHONE_FROM,PHONE_TO,EMAIL_FROM,EMAIL_TO,SCHOOL_FROM,SCHOOL_TO) 
// 					VALUES ($userid,'$txtPhoneNumberPD','$txtPhoneNumberPD_old','$txtEmailIdPD','$txtEmailIdPD_old','$txtSchoolPD','$txtSchoolPD_old')";
// 			$data['queryLog'] = $queryLog;
// 			sqlsrv_query($mysqli, $queryLog);

// 			$data['success'] = true;
// 			$data['message'] = 'Details successfully updated.';
// 		}
// 		echo json_encode($data);exit;
// 	}
// 	catch(Exception $e)
// 	{
// 	   $data = array();
// 	   $data['success'] = false;
// 	   $data['message'] = $e->getMessage();
// 	   echo json_encode($data);
// 	   exit;
// 	}
// }
/* ============ UPDATE PERSONAL DETAILS ============= */ 


/* ============ UPDATE CLASS AT SCHOOL ============= */ 
function updateClassesAtSchool($mysqli){
	try
	{
		$data = array();
		global $userid;

		$selectedCourse = (isset($_POST['selectedCourse'])) ? json_decode($_POST['selectedCourse'],true) : array();
		//    $data['check'] = $_POST['selectedCourse'];
		
		if(count($selectedCourse)==0)throw new Exception('Class At School Not Added.');

		// INSERT STUDENT PROPOSED COURSES
		$delCourse = "DELETE FROM STUDENT_PROPOSED_COURSES WHERE REGID=$userid";
		sqlsrv_query($mysqli, $delCourse);
		$CLASSES_AT_SCHOOL = '';
	   	if(count($selectedCourse)>0){
			foreach($selectedCourse as $Details){
				$GRADEID = $Details['GRADEID'];
				$CSUBID = $Details['CSUBID'];
				$FINAL_DRAFT = $Details['FINAL_DRAFT'];
				$CLASSES_AT_SCHOOL .=$GRADEID.'|'.$CSUBID.'|'.$FINAL_DRAFT.',';

				$insertCourse = "EXEC [STUDENT_PROPOSED_COURSES_SP] 1,0,$userid,$GRADEID,$CSUBID,'$FINAL_DRAFT',$userid";
				sqlsrv_query($mysqli, $insertCourse);
			}

			// INSERT LOG
			$CLASSES_AT_SCHOOL = rtrim($CLASSES_AT_SCHOOL,',');
			$queryLog="INSERT INTO REGISTRATION_UPDATE_LOG (REGID,CLASSES_AT_SCHOOL) 
			VALUES ($userid,'$CLASSES_AT_SCHOOL')";
			$data['queryLog'] = $queryLog;
			sqlsrv_query($mysqli, $queryLog);

			$data['success'] = true;
			$data['message'] = 'Details successfully updated.';
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
/* ============ UPDATE CLASS AT SCHOOL ============= */ 


// #############################################################################################
// ###### UPDATE DETAILS END
// #############################################################################################




/*============ Get Student Plan Data =============*/ 
function getStudentPlanData($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$DATA = '';

		
		$query = "SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN] FROM REGISTRATION_DETAILS RD 
				WHERE CANCELLED=0 AND REGID=$REGID AND ACTIVATE=1";
		$data['$$REGID']=$REGID;
		$result = sqlsrv_query($mysqli, $query);
		$PlanCount = unique($query);

		if($PlanCount > 0){
			$s=0;
			while ($row = sqlsrv_fetch_array($result)){
				$row['PLANID'] = (int) $row['PLANID'];
				$PLANID = $row['PLANID'];
				// $data['data'][] = $row;
				
				$DATA .='<div class="row justify-content-lg-between align-content-start bg-light pt-2 my-3" style="min-height: 400px;">
							<div class="col-12">
								<div class="row justify-content-between">
									<div class="col-lg-3 col-md-3 col-sm-12 text-center text-md-left pt-2">
										<h4 class="px-2 text-light rounded-lg py-1" style="background-color: #3c404f!important;"><b class="text-warning">Your Plan : </b> '.$row['PLAN'].'</h4>
									</div>
	
									<div class="col-lg-2 col-md-2 col-sm-12 text-center text-md-right pt-2">
										<h4 class="px-2 text-light rounded-lg py-1" style="background-color: #3c404f!important;"><b class="text-warning">Your Grade : </b> {{GRADE}}</h4>
									</div>
								</div>
							</div>';
	
				// ######### GET ATTENDANCE ##########
				// <th colspan="2">
				// <h4 class="text-center font-weight-bold  mb-0">Class/Home Work</h4>
				// </th>

				
				$DATA .='<div class="col-sm-12 col-md-12 col-lg-6 bg-light">
							<div class="table-responsive custom-scrollbar Sticky-table-head-foot mt-3 border" style="max-height:450px">
							<table class="table table-bordered table-hover bg-white table-sm mb-0">
	
							<thead>
								<tr class="my_table_head">
									<th colspan="5">
									<h4 class="text-center font-weight-bold mb-0">ATTENDANCE</h4>
									</th>
									
								</tr>
								<tr class="bg-light">
									<th>Date</th>
									<th>Teacher</th>
									<th>Inventory</th>
									<th>Chapter</th>
									<th>Remark</th>
									
								</tr>
							</thead>';
							// <th class="text-nowrap">Class Work</th>
							// <th class="text-nowrap">Home Work</th>
	
							$DATA .='</tbody>';
							
							// $queryATT = "SELECT ATTID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,
							// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SA.TEACHERID)TEACHER,PRESENT,
							// (SELECT CLASSWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE AND TEACHERID=SA.TEACHERID)CLASSWORK,
							// (SELECT HOMEWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE AND TEACHERID=SA.TEACHERID)HOMEWORK
							// FROM STUDENT_ATTENDANCE SA WHERE ISDELETED=0 AND REGID=$REGID AND LOCID=$LOCID AND PLANID=$PLANID AND PRESENT=1
							// ORDER BY CONVERT(DATETIME,ATTDATE,103) DESC";

							$queryATT = "SELECT ATTID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,
							(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SA.TEACHERID)TEACHER,PRESENT,
							--INVENTORY
							(SELECT DISTINCT (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) + ', ' FROM STUDENT_COURSE_COVERAGE SCC WHERE 
								ISDELETED=0 AND CONVERT(DATE,CDATE,105)=CONVERT(DATETIME,SA.ATTDATE,105) AND PLANID=SA.PLANID AND --PRODUCTID=SA.PRODUCTID AND
								SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE REGID=SA.REGID AND ISDELETED=0) FOR XML PATH(''))INVENTORY,
							--CHAPTER
							(SELECT (SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID) + ', ' FROM STUDENT_COURSE_COVERAGE SCC WHERE 
								ISDELETED=0 AND CONVERT(DATE,CDATE,105)=CONVERT(DATETIME,SA.ATTDATE,105) AND PLANID=SA.PLANID AND --PRODUCTID=SA.PRODUCTID AND
								SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE REGID=SA.REGID AND ISDELETED=0) FOR XML PATH(''))CHAPTER,
							--REMARK
							(SELECT CASE WHEN (REMARK = '' OR REMARK is null) THEN '-' ELSE REMARK END + ', ' FROM STUDENT_COURSE_COVERAGE SCC WHERE 
								ISDELETED=0 AND CONVERT(DATE,CDATE,105)=CONVERT(DATETIME,SA.ATTDATE,105) AND PLANID=SA.PLANID AND --PRODUCTID=SA.PRODUCTID AND
								SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE REGID=SA.REGID AND ISDELETED=0) FOR XML PATH(''))REAMRK,

							(SELECT CLASSWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE AND TEACHERID=SA.TEACHERID)CLASSWORK,
							(SELECT HOMEWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE AND TEACHERID=SA.TEACHERID)HOMEWORK
							FROM STUDENT_ATTENDANCE SA WHERE ISDELETED=0 AND REGID=$REGID AND LOCID=$LOCID AND PLANID=$PLANID AND PRESENT=1
							ORDER BY CONVERT(DATETIME,ATTDATE,103) DESC";

							$data['$queryATT']=$queryATT;
							$ATTCOUNT = unique($queryATT);
							$resultATT = sqlsrv_query($mysqli, $queryATT);
	
							if($ATTCOUNT > 0){
								$ATT=1;
								while ($rowATT = sqlsrv_fetch_array($resultATT)) {
									$rowATT['ATTID'] = (int) $rowATT['ATTID'];
									// $data['data'][] = $rowATT;
									$CLASSWORK = $rowATT['CLASSWORK'] == null ? '-' : $rowATT['CLASSWORK'];
									$HOMEWORK = $rowATT['HOMEWORK'] == null ? '-' : $rowATT['HOMEWORK'];

									$rowATT['INVENTORY'] = rtrim($rowATT['INVENTORY'],', ');
									$rowATT['INVENTORY'] = str_replace(', ', ",\n", $rowATT['INVENTORY']);
									$rowATT['CHAPTER'] = rtrim($rowATT['CHAPTER'],', ');
									$rowATT['CHAPTER'] = str_replace(', ', ",\n", $rowATT['CHAPTER']);
									$rowATT['REAMRK'] = rtrim($rowATT['REAMRK'],', ');
									$rowATT['REAMRK'] = str_replace(', ', ",\n", $rowATT['REAMRK']);

		
									
		
									$DATA .='<tr>
												<td class="text-nowrap">'.$rowATT['ATTDATE'].'</td>
												<td>'.$rowATT['TEACHER'].'</td>
												<td><p class="my-0 text-dark" style="white-space: pre-line;">'.$rowATT['INVENTORY'].'</p></td>
												<td><p class="my-0 text-dark" style="white-space: pre-line;">'.$rowATT['CHAPTER'].'</p></td>
												<td><p class="my-0 text-dark" style="white-space: pre-line;">'.$rowATT['REAMRK'].'</p></td>';
		
												// if($rowATT['PRESENT'] == 1){
												// 	$DATA .='<td class="text-success">Present</td>';
												// }else{
												// 	$DATA .='<td class="text-danger">Absent</td>';
												// }
									// $DATA .='   <td>'.$CLASSWORK.'</td>
									// 			<td>'.$HOMEWORK.'</td>
									// 		</tr>';
									$DATA .='</tr>';
									$ATT++;
								}
							}
							else{
								$DATA .='<tr>
								<td colspan="100%" class="text-center text-danger">No Record</td>
								</tr>';
							}
							$DATA .='</tbody>';
							
				$DATA .='</table></div>';						
				
				
				// ######### GET ANNOUNCEMENT ##########
				$DATA .='<div class="table-responsive mt-3 custom-scrollbar Sticky-table-head-foot" style="max-height:500px">
						<table class="table table-bordered table-hover bg-white table-sm mb-0">
							<thead>
								<tr class="my_table_head">
									<th colspan="100%">
									<h4 class="text-center font-weight-bold mb-0">ANNOUNCEMENT</h4>
									</th>
								</tr>
								<tr class="bg-light">
									<th>Date</th>
									<th>Announcement</th>
								</tr>
							</thead>';
							
							$DATA .='<tbody';
							
							$queryANNOUNCE = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
							WHERE ISDELETED=0 AND LOCID =
							CASE 
								WHEN LOCID <= 0 THEN 0
								WHEN LOCID >0 THEN $LOCID
							END
							AND PLANID = 
							CASE 
								WHEN PLANID <= 0 THEN 0
								WHEN PLANID >0 THEN $PLANID
							END ORDER BY CONVERT(DATE,ANDATE,106)";
	
							$data['$queryANNOUNCE']=$queryANNOUNCE;
							$ANOUNCECOUNT = unique($queryANNOUNCE);
							$resultANNOUNCE = sqlsrv_query($mysqli, $queryANNOUNCE);
	
							if($ANOUNCECOUNT > 0){
								$ANN=1;
								while ($rowANNOUNCE = sqlsrv_fetch_array($resultANNOUNCE)) {
									$rowANNOUNCE['ANID'] = (int) $rowANNOUNCE['ANID'];
									// $data['data'][] = $rowANNOUNCE;
	
		
						
									$DATA .='<tr title="'.$rowANNOUNCE['ANID'].'">
														<td>'.$rowANNOUNCE['ANDATE'].'</td>
														<td>'.$rowANNOUNCE['ANNOUNCEMENT'].'</td>
											</tr>';
									$ANN++;
								}
							}
							else{
								$DATA .='<tr>
											<td colspan="100%" class="text-center text-danger">No Record</td>
										</tr>';
							}
	
							
				$DATA .='</tbody></table></div>
						</div>';						
				
				
				
				// ######### GET MEETING LINKS ##########
				$DATA .='<div class="col-sm-12 col-md-12 col-lg-5 bg-light">
							<table class="table table-bordered table-hover bg-white table-sm my-3">
							<tr class="my_table_head">
							<th colspan="4">
								<h4 class="text-center font-weight-bold mb-0">MEETING LINKS</h4>
							</th>
							</tr>
							<tr style="background:#efefef">
								<th>Meeting ID</th>
								<th>Passcode</th>
								<th class="text-center">Meeting Link</th>
							</tr>';
	
	
							
							$queryMeetingLink = "SELECT MTID,MEETINGID,MEETINGLINK,MPASSCODE FROM ONLINE_MEETINGS_LINKS WHERE ISDELETED=0 AND PLANID=$PLANID";
							// $data['$queryMeetingLink']=$queryMeetingLink;
							$MLCOUNT = unique($queryMeetingLink);
							$resultML = sqlsrv_query($mysqli, $queryMeetingLink);
	
							if($MLCOUNT > 0){
								$ML=1;
								while ($rowML = sqlsrv_fetch_array($resultML)) {
									$rowML['MTID'] = (int) $rowML['MTID'];
									// $data['data'][] = $rowML;
									
		
									$DATA .='<tr title="'.$rowML['MTID'].'">
												<td>'.$rowML['MEETINGID'].'</td>
												<td>'.$rowML['MPASSCODE'].'</td>
												<td class="text-center">
													<a href="'.$rowML['MEETINGLINK'].'" target="_blank" title="click here" class="btn btn-sm btn-outline-primary px-2 py-1"><span class="px-2"><b>Open</b></span></a>
												</td>
											</tr>';
									$ML++;
								}
							}
							else{
								$DATA .='<tr>
											<td colspan="100%" class="text-center text-danger">No Meeting Links</td>
										</tr>';
							}
							
				$DATA .='</table>';						
				
				
				
				
				
				// ######### GET REVIEW LINKS ##########
				$DATA .='<table class="table table-bordered table-hover bg-white table-sm my-3">
							<tr class="my_table_head">
							<th colspan="4">
								<h4 class="text-center font-weight-bold mb-0">REVIEW LINKS</h4>
							</th>
							</tr>';
	
	
							
							$queryReviewLink = "SELECT REVID,REVIEWMEDIA,REVIEWLINK FROM LOCATION_REVIEWS WHERE ISDELETED=0 AND LOC_ID=$LOCID";
							// $data['$queryReviewLink']=$queryReviewLink;
							$RLCOUNT = unique($queryReviewLink);
							$resultRL = sqlsrv_query($mysqli, $queryReviewLink);
	
							if($RLCOUNT > 0){
								$RL=1;
								while ($rowRL = sqlsrv_fetch_array($resultRL)) {
									$rowRL['REVID'] = (int) $rowRL['REVID'];
									// $data['data'][] = $rowRL;
									
		
									$DATA .='<tr title="'.$rowRL['REVIEWMEDIA'].'">
												<td class="px-3">'.$rowRL['REVIEWMEDIA'].'</td>
												<td style="width: 100px;" class="text-center">
													<a href="'.$rowRL['REVIEWLINK'].'" target="_blank" title="click here" class="btn btn-sm btn-outline-primary px-2 py-1"><span class="px-2"><b>Open</b></span></a>
												</td>
											</tr>';
									$RL++;
								}
							}
							else{
								$DATA .='<tr>
											<td colspan="100%" class="text-center text-danger">No Review Links</td>
										</tr>';
							}
							
				$DATA .='</table>';						
				
				
				
				
				// <div class="table-responsive rlink-table my-3 border">
				// ######### GET RESOURCE LINKS ##########
				$DATA .='<div class="table-responsive custom-scrollbar Sticky-table-head-foot" style="max-height:500px">
						<table class="table table-borderless table-hover bg-white table-sm mb-0">
							<thead>
								<tr class="my_table_head">
									<th colspan="100%">
										<h4 class="text-center font-weight-bold mb-0">RESOURCE LINKS</h4>
									</th>
								</tr>
								<tr class="bg-light">
									<th>Product</th>
									<th>Resource</th>
									<th>Link</th>
									<th style="width:100px"></th>
								</tr>
							</thead>';
	
					$DATA .='<tbody>';
	
	
						
							$queryRSLink = "SELECT SEQNO,RESLID,RESID,
							(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)PRODUCT_ID,
							(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID))PRODUCT,
							(SELECT RESOURCEID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_NAME,
							(SELECT RESOURCE_DESC FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_DESC,
							RESOURCE_LINK
							FROM TOPIC_RESOURCES_LINKS TRL WHERE ISDELETED=0 AND 
							(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID) IN 
							((SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID=$PLANID AND ISDELETED=0))
							ORDER BY RESID,SEQNO";
	
							$data['$queryRSLink']=$queryRSLink;
							$RSLCOUNT = unique($queryRSLink);
							$resultRSL = sqlsrv_query($mysqli, $queryRSLink);
							$prevProduct='';
							$prevResc='';
							if($RSLCOUNT > 0){
								$RSL=0;
								while ($rowRSL = sqlsrv_fetch_array($resultRSL)) {
									$rowRSL['RESID'] = (int) $rowRSL['RESID'];
									$RESLID = (int) $rowRSL['RESLID'];
									$data['data'][] = $rowRSL;
									
									// ng-bind="x.PRODUCT == post.getResources[$index-1].PRODUCT ? '' : x.PRODUCT"
									$DATA .='<tr>';
											if($prevProduct==$rowRSL['PRODUCT'])
											{
												$DATA .='<td></td>';
											}
											else {
												$DATA .='<td>'.$rowRSL['PRODUCT'].'</td>';
											}
											
											if($prevResc==$rowRSL['RESOURCE_NAME'])
											{
												$DATA .='<td></td>';
											}
											else {
												$DATA .='<td>'.$rowRSL['RESOURCE_NAME'].'</td>';
											}
	
										$DATA .='<td class="text-break" style="width:50%">
													<a href="'.$rowRSL['RESOURCE_LINK'].'" target="_blank" title="click here" class="text-primary">
														'.$rowRSL['RESOURCE_LINK'].'
													</a>
												</td>';
	
										$DATA .='<td class="text-center text-nowrap">';
													$UP_VAL = 0;
													$DOWN_VAL = 0;
	
													$chkOld_UD = "SELECT RESLID,UP,DOWN FROM TOPIC_RESOURCES_LINKS_VOTE WHERE RESLID=$RESLID AND REGID=$userid";
													$result_UD = sqlsrv_query($mysqli, $chkOld_UD);
													$COUNT_UD = unique($chkOld_UD);
	
													if($COUNT_UD > 0){
														while ($rowUD = sqlsrv_fetch_array($result_UD)) {
															$data['chkOld_UD======'][] = $rowUD;
															
															$UP_VAL = (int)$rowUD['UP'];
															$DOWN_VAL = (int)$rowUD['DOWN'];
	
															
															$DATA .='<div class="btn-group-toggle" data-toggle="radio" ng-init="">
																		<label class="btn btn-outline-success btn-sm rounded-circle py-1 px-2" ng-init="UPDOWN'.$s.$RSL.' = '.$UP_VAL.'" ng-class="UPDOWN'.$s.$RSL.' == 1 ? \'active\' : \'\'" ng-click="LinkVoting(\'UP\','.$RESLID.',UPDOWN'.$s.$RSL.'); ">
																			<input type="radio" name="voting'.$s.'_'.$RSL.'" ng-value="1" id="VOTING'.$s.'_'.$RSL.'" ng-model="UPDOWN'.$s.$RSL.'"> 
																			<i style="font-size: medium;" class="fa fa-thumbs-o-up"></i>
																		</label>
																		<label class="btn btn-outline-danger btn-sm rounded-circle py-1 px-2" ng-init="UPDOWN'.$s.$RSL.' = '.$UP_VAL.'" ng-class="UPDOWN'.$s.$RSL.' == 0 ? \'active\' : \'\'" ng-click="LinkVoting(\'DOWN\','.$RESLID.',UPDOWN'.$s.$RSL.')">
																			<input type="radio" name="voting'.$s.'_'.$RSL.'" ng-value="0" id="VOTING'.$s.'_'.$RSL.'" ng-model="UPDOWN'.$s.$RSL.'">
																			<i style="font-size: medium;" class="fa fa-thumbs-o-down"></i>
																		</label>
																	</div>';
	
														}
													}else{
															$DATA .='<div class="btn-group-toggle" data-toggle="radio" ng-init="">
																		<label class="btn btn-outline-success btn-sm rounded-circle py-1 px-2" ng-class="UPDOWN'.$s.$RSL.' == 1 ? \'active\' : \'\'" ng-click="LinkVoting(\'UP\','.$RESLID.',UPDOWN'.$s.$RSL.')">
																			<input type="radio" name="voting'.$s.'_'.$RSL.'" ng-value="1" id="VOTING'.$s.'_'.$RSL.'" ng-model="UPDOWN'.$s.$RSL.'"> 
																			<i style="font-size: medium;" class="fa fa-thumbs-o-up"></i>
																		</label>
																		<label class="btn btn-outline-danger btn-sm rounded-circle py-1 px-2" ng-class="UPDOWN'.$s.$RSL.' == 0 ? \'active\' : \'\'" ng-click="LinkVoting(\'DOWN\','.$RESLID.',UPDOWN'.$s.$RSL.')">
																			<input type="radio" name="voting'.$s.'_'.$RSL.'" ng-value="0" id="VOTING'.$s.'_'.$RSL.'" ng-model="UPDOWN'.$s.$RSL.'">
																			<i style="font-size: medium;" class="fa fa-thumbs-o-down"></i>
																		</label>
																	</div>';
													}
	
													
										$DATA .='</td>
											</tr>';
	
	
									$RSL++;
									$prevProduct=$rowRSL['PRODUCT'];
									$prevResc=$rowRSL['RESOURCE_NAME'];
								}
							}
							else{
								$DATA .='<tr>
											<td colspan="100%" class="text-center text-danger">No Review Links</td>
										</tr>';
							}
					$DATA .='<tbody>		
						</table>
						</div>
					</div>
				</div>';
				
	
				$s++;
			}
			$data['success'] = true;
			$data['ALLDATA']=$DATA;
			echo json_encode($data);exit;
		}
		else{
			$data['success'] = false;
			$data['PlanCount']=$PlanCount;
			echo json_encode($data);exit;
		}
		

	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}


/*============ Get Student Plans =============*/ 
function getStudentPlans($mysqli){
	try
	{
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

		$query = "SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN] FROM REGISTRATION_DETAILS RD 
				WHERE CANCELLED=0 AND REGID=$REGID";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PLANID'] = (int) $row['PLANID'];
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



/*============ Get Attendance =============*/ 
function getAttendance($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];

		// $query = "SELECT ATTID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,
		// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SA.TEACHERID)TEACHER,PRESENT,
		// (SELECT CLASSWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE)CLASSWORK,
		// (SELECT HOMEWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0 AND PLANID=SA.PLANID AND HWDATE=SA.ATTDATE)HOMEWORK
		// FROM STUDENT_ATTENDANCE SA WHERE ISDELETED=0 AND REGID=$REGID AND LOCID=$LOCID AND PLANID=$PLANID";
		$query = "SELECT SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID)PLANNAME,
		PRODUCTID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT 'GRADE-'+GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SCC.PRODUCTID)
			WHEN ATT_SOURCE='HOURLY' THEN (SELECT 'HOURLY TUTORING BY-'+FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SCC.PRODUCTID)
			ELSE (SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SCC.PRODUCTID) END PRODUCT,
		INVID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SCC.INVID)
			WHEN ATT_SOURCE='HOURLY' THEN (SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SCC.INVID)
			ELSE (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) END INVENTORY,
		CHAPID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=SCC.CHAPID)
			ELSE ISNULL((SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID),(SELECT 'HOURLY TUTORING BY-'+FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SCC.PRODUCTID)) END CHAPTER,
		REMARK,HOMEWORK,HOMEWORK_DOC,
		(SELECT CASE WHEN REMARK='' THEN '' ELSE REMARK +' || ' END FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$userid FOR XML PATH(''))REMARK_CHILD,
		(SELECT HOMEWORK_DONE FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$userid)HOMEWORK_DONE,
		(SELECT STUDENTWORK FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$userid)STUDENTWORK,
		(SELECT HOMEWORK_IMG FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$userid)HOMEWORK_IMG,
		(SELECT DOC FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$userid)DOC
		FROM STUDENT_COURSE_COVERAGE SCC
		WHERE ISDELETED=0 AND
		SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID=$userid)
		ORDER BY CONVERT(DATE,CDATE,105) DESC,INVENTORY,CHAPTER";
		$data['$query']=$query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SCCID'] = (int) $row['SCCID'];
				$row['DOC_TYPE'] = $row['DOC'] !='' ? substr($row['DOC'], strpos($row['DOC'], ".") + 1):'';
				$row['HOMEWORK_DOC_TYPE'] = $row['HOMEWORK_DOC'] !='' ? substr($row['HOMEWORK_DOC'], strpos($row['HOMEWORK_DOC'], ".") + 1):'';
				$row['REMARK_CHILD'] = rtrim($row['REMARK_CHILD'],' || ');
				$row['REMARK'] = rtrim($row['REMARK'],'#_#');
				$row['REMARK'] = str_replace("#_#","<br>",$row['REMARK']);
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



/*============ Get SMS =============*/ 
function getSms($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$FROM = ($_POST['FROM'] == 'undefined' || $_POST['FROM'] == '') ? '' : $_POST['FROM'];

		$query = '';
		if($FROM == 'DASH') $query .= "SELECT TOP 10 ";
		if($FROM == 'HISTORY') $query .= "SELECT";
		if($FROM == '') $query .= "SELECT";

		$query .= " MSGID,CONVERT(VARCHAR,MSGDATE,21)MSGDATE,MOBILENO,TEXTMESSAGE
		FROM TEXT_MESSAGES WHERE MSGTYPE='OUTGOING API' AND STUDENTTYPE='Registered' AND REGID=$REGID ORDER BY MSGID DESC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MSGID'] = (int) $row['MSGID'];
				$date=date_create($row['MSGDATE']);
				$row['MSGDATE'] = date_format($date,"M/d/Y  h:i A");
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
/*============ Get SMS =============*/ 



/*============ Get Meeting Links =============*/ 
function getMeetingLinks($mysqli){
	try
	{
		global $userid;
		$data = array();

		$query = "SELECT MTID,MEETINGID,MEETINGLINK,MPASSCODE,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=OML.PLANID)PLANNAME,
		(SELECT 
			CASE WHEN CONVERT(DATE,ENDDATE,105)>CONVERT(DATE,GETDATE(),105)
				THEN 'No'
				ELSE 'YES'
			END EXPIRE
		FROM PLANS WHERE PLANID=OML.PLANID)EXPIRE
		FROM ONLINE_MEETINGS_LINKS OML WHERE ISDELETED=0 AND 
		PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE ISDELETED=0 AND REGID=$userid AND CANCELLED=0 AND ACTIVATE=1)";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MTID'] = (int) $row['MTID'];
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


/*============ Get Location Reviews =============*/ 
function getLocationReview($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];

		$query = "SELECT REVID,REVIEWMEDIA,REVIEWLINK FROM LOCATION_REVIEWS WHERE ISDELETED=0 AND LOC_ID=$LOCID";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REVID'] = (int) $row['REVID'];
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


/*============ Get Resources =============*/ 
function getResources($mysqli){
	try
	{
		global $userid;
		$data = array();

		// $query = "SELECT TR.RESID,(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=TR.PRODUCT_ID) PRODUCT,RESOURCE_DESC,RL.RESOURCE_LINK AS LINK
		// FROM TOPIC_RESOURCES  TR,TOPIC_RESOURCES_LINKS RL
		// WHERE TR.RESID=RL.RESID AND
		// PRODUCT_ID IN (SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID=$PLANID)";
		
		
		// $query = "SELECT SEQNO,RESLID,RESID,
		// (SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)PRODUCT_ID,
		// (SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID))PRODUCT,
		// (SELECT RESOURCEID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_NAME,
		// (SELECT RESOURCE_DESC FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_DESC,
		// RESOURCE_LINK
		// FROM TOPIC_RESOURCES_LINKS TRL WHERE ISDELETED=0 AND 
		// (SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID) IN 
		// ((SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$userid AND ISDELETED=0 AND CANCELLED=0 AND ACTIVATE=1) AND ISDELETED=0))
		// ORDER BY RESID,SEQNO";
		 
		// $data['$query']=$query;
		// $result = sqlsrv_query($mysqli, $query);
		// $count = unique($query);
		// if($count > 0){
		// 	while ($row = sqlsrv_fetch_array($result)) {
		// 		$row['RESID'] = (int) $row['RESID'];
		// 		$data['data'][] = $row;
		// 	}
		// 	$data['success'] = true;
		// }else{
		// 	$data['success'] = false;
		// }


		$DATA = '';
		$DATA .='<div class="table-responsive custom-scrollbar Sticky-table-head-foot border" style="max-height:500px">
						<table class="table table-borderless table-hover bg-white table-sm mb-0">
							<thead>
								<tr class="bg-dark">
									<th colspan="100%">
										<h4 class="text-center font-weight-bold mb-0 text-warning">RESOURCE LINKS</h4>
									</th>
								</tr>
								<tr class="bg-light">
									<th>Product</th>
									<th>Resource</th>
									<th>Link</th>
									<th style="width:100px"></th>
								</tr>
							</thead>';
	
					$DATA .='<tbody>';
	
	
						
							$queryRSLink = "SELECT SEQNO,RESLID,RESID,
							(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)PRODUCT_ID,
							(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID))PRODUCT,
							(SELECT RESOURCEID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_NAME,
							(SELECT RESOURCE_DESC FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_DESC,
							RESOURCE_LINK
							FROM TOPIC_RESOURCES_LINKS TRL WHERE ISDELETED=0 AND 
							(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID) IN 
							((SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$userid AND ISDELETED=0 AND CANCELLED=0 AND ACTIVATE=1) AND ISDELETED=0))
							ORDER BY RESID,SEQNO";
	
							$data['$queryRSLink']=$queryRSLink;
							$RSLCOUNT = unique($queryRSLink);
							$resultRSL = sqlsrv_query($mysqli, $queryRSLink);
							$prevProduct='';
							$prevResc='';
							if($RSLCOUNT > 0){
								$RSL=0;
								while ($rowRSL = sqlsrv_fetch_array($resultRSL)) {
									$rowRSL['RESID'] = (int) $rowRSL['RESID'];
									$RESLID = (int) $rowRSL['RESLID'];
									$data['data'][] = $rowRSL;
									
									// ng-bind="x.PRODUCT == post.getResources[$index-1].PRODUCT ? '' : x.PRODUCT"
									$DATA .='<tr>';
											if($prevProduct==$rowRSL['PRODUCT'])
											{
												$DATA .='<td></td>';
											}
											else {
												$DATA .='<td>'.$rowRSL['PRODUCT'].'</td>';
											}
											
											if($prevResc==$rowRSL['RESOURCE_NAME'])
											{
												$DATA .='<td></td>';
											}
											else {
												$DATA .='<td>'.$rowRSL['RESOURCE_NAME'].'</td>';
											}
	
										$DATA .='<td class="text-break" style="width:50%">
													<a href="'.$rowRSL['RESOURCE_LINK'].'" target="_blank" title="click here" class="text-primary">
														'.$rowRSL['RESOURCE_LINK'].'
													</a>
												</td>';
	
										$DATA .='<td class="text-center text-nowrap">';
													$UP_VAL = 0;
													$DOWN_VAL = 0;
	
													$chkOld_UD = "SELECT RESLID,UP,DOWN FROM TOPIC_RESOURCES_LINKS_VOTE WHERE RESLID=$RESLID AND REGID=$userid";
													$data['$chkOld_UD ']=$chkOld_UD;
													$result_UD = sqlsrv_query($mysqli, $chkOld_UD);
													$COUNT_UD = unique($chkOld_UD);
	
													if($COUNT_UD > 0){
														while ($rowUD = sqlsrv_fetch_array($result_UD)) {
															$data['chkOld_UD======'][] = $rowUD;
															
															$UP_VAL = (int)$rowUD['UP'];
															$DOWN_VAL = (int)$rowUD['DOWN'];
	
															
															$DATA .='<div class="btn-group-toggle" data-toggle="radio" ng-init="">
																		<label class="btn btn-outline-success btn-sm rounded-circle py-1 px-2" ng-init="UPDOWN'.$RSL.' = '.$UP_VAL.'" ng-class="UPDOWN'.$RSL.' == 1 ? \'active\' : \'\'" ng-click="LinkVoting(\'UP\','.$RESLID.',UPDOWN'.$RSL.'); ">
																			<input type="radio" name="voting'.'_'.$RSL.'" ng-value="1" id="VOTING'.'_'.$RSL.'" ng-model="UPDOWN'.$RSL.'"> 
																			<i style="font-size: medium;" class="fa fa-thumbs-o-up"></i>
																		</label>
																		<label class="btn btn-outline-danger btn-sm rounded-circle py-1 px-2" ng-init="UPDOWN'.$RSL.' = '.$UP_VAL.'" ng-class="UPDOWN'.$RSL.' == 0 ? \'active\' : \'\'" ng-click="LinkVoting(\'DOWN\','.$RESLID.',UPDOWN'.$RSL.')">
																			<input type="radio" name="voting'.'_'.$RSL.'" ng-value="0" id="VOTING'.'_'.$RSL.'" ng-model="UPDOWN'.$RSL.'">
																			<i style="font-size: medium;" class="fa fa-thumbs-o-down"></i>
																		</label>
																	</div>';
	
														}
													}else{
															$DATA .='<div class="btn-group-toggle" data-toggle="radio" ng-init="">
																		<label class="btn btn-outline-success btn-sm rounded-circle py-1 px-2" ng-class="UPDOWN'.$RSL.' == 1 ? \'active\' : \'\'" ng-click="LinkVoting(\'UP\','.$RESLID.',UPDOWN'.$RSL.')">
																			<input type="radio" name="voting'.'_'.$RSL.'" ng-value="1" id="VOTING'.'_'.$RSL.'" ng-model="UPDOWN'.$RSL.'"> 
																			<i style="font-size: medium;" class="fa fa-thumbs-o-up"></i>
																		</label>
																		<label class="btn btn-outline-danger btn-sm rounded-circle py-1 px-2" ng-class="UPDOWN'.$RSL.' == 0 ? \'active\' : \'\'" ng-click="LinkVoting(\'DOWN\','.$RESLID.',UPDOWN'.$RSL.')">
																			<input type="radio" name="voting'.'_'.$RSL.'" ng-value="0" id="VOTING'.'_'.$RSL.'" ng-model="UPDOWN'.$RSL.'">
																			<i style="font-size: medium;" class="fa fa-thumbs-o-down"></i>
																		</label>
																	</div>';
													}
	
													
										$DATA .='</td>
											</tr>';
	
	
									$RSL++;
									$prevProduct=$rowRSL['PRODUCT'];
									$prevResc=$rowRSL['RESOURCE_NAME'];
								}
							}
							else{
								$DATA .='<tr>
											<td colspan="100%" class="text-center text-danger">No Review Links</td>
										</tr>';
							}
					$DATA .='</tbody>		
						</table>
						</div>';

		$data['success'] = true;
		$data['ALLDATA']=$DATA;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}



// ================ VOTING ================
function LinkVoting($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
		   $RESLID = ($_POST['RESLID'] == 'undefined' || $_POST['RESLID'] == '') ? 0 : $_POST['RESLID'];
		   $VOTE = ($_POST['VOTE'] == 'undefined' || $_POST['VOTE'] == '') ? '' : $_POST['VOTE'];
		   $voteVal = ($_POST['voteVal'] == 'undefined' || $_POST['voteVal'] == '') ? 'noval' : $_POST['voteVal'];

		   if($RESLID == 0){
			throw new Exception('RESLID Error.');
		   }
		   if($VOTE == ''){
			throw new Exception('VOTE Error.');
		   }
		   if($voteVal == 'noval'){
			throw new Exception('VOTEVAL Error.');
		   }

		   $UP = $VOTE == 'UP' ? 1 : 0;
		   $DOWN = $VOTE == 'DOWN' ? 1 : 0;



		   // CHECK UP DOWN VALUE
		   $ChkUPDOWN = "SELECT UP,DOWN FROM TOPIC_RESOURCES_LINKS_VOTE WHERE REGID=$userid AND RESLID=$RESLID";

		   $stmtUPDOWN=sqlsrv_query($mysqli, $ChkUPDOWN);
		   $Count_UD = unique($ChkUPDOWN);
		   if($Count_UD > 0){

				if($VOTE == 'UP'){
					$Upd_VOTE="UPDATE TOPIC_RESOURCES_LINKS_VOTE SET DOWN=0,UP=1,INSERTDATE=GETDATE() WHERE REGID=$userid AND RESLID=$RESLID";
				}
				else if($VOTE == 'DOWN'){
					$Upd_VOTE="UPDATE TOPIC_RESOURCES_LINKS_VOTE SET DOWN=1,UP=0,INSERTDATE=GETDATE() WHERE REGID=$userid AND RESLID=$RESLID";
				}

				$stmtUpd_VOTE=sqlsrv_query($mysqli, $Upd_VOTE);
				if($stmtUpd_VOTE === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['Upd_VOTE'] = $Upd_VOTE;
					echo json_encode($data);exit;
				}
				else
				{
					$data['Upd_VOTE'] = $Upd_VOTE;
					$data['success'] = true;
					echo json_encode($data);exit;
				}
				   
		   }else{

			   $Insert_VOTE="INSERT INTO TOPIC_RESOURCES_LINKS_VOTE (RESLID,REGID,UP,DOWN)
			   				VALUES($RESLID,$userid,$UP,$DOWN)"; 
				$stmtInsert_VOTE=sqlsrv_query($mysqli, $Insert_VOTE);

				if($stmtInsert_VOTE === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['Insert_VOTE'] = $Insert_VOTE;
					echo json_encode($data);exit;
				}
				else
				{
					$data['Insert_VOTE'] = $Insert_VOTE;
					$data['success'] = true;
					echo json_encode($data);exit;
				}
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


/*============ Get Announcement =============*/ 
function getAnnouncement($mysqli){
	try
	{
		global $userid;
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];

		$query = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
		WHERE ISDELETED=0 AND (PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$userid AND CANCELLED=0 AND ACTIVATE=1) OR PLANID=0)
		AND (LOCID=$LOCID OR LOCID=0)
		ORDER BY CONVERT(DATE,ANDATE,105) DESC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ANID'] = (int) $row['ANID'];
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



/*============ Get Support Ticket =============*/ 
function getSupport($mysqli){
	try
	{
		global $userid;
		$data = array();

		$query = "SELECT TOP 5 CONVERT(VARCHAR,TICKETDATE,106)TICKETDATE,[SUBJECT],[PRIORITY],[STATUS]
		FROM SUPPORT_TICKETS WHERE CANCELLED=0 AND REGID=$userid
		ORDER BY CONVERT(DATE,TICKETDATE,105) DESC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
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



/* ============ SAVE REFERRALS ============= */ 
function saveReferral($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $REFBYID = ($_POST['REFBYID'] == 'undefined' || $_POST['REFBYID'] == '') ? 0 : $_POST['REFBYID'];
	   $ddlReferralTypeRef = ($_POST['ddlReferralTypeRef'] == 'undefined' || $_POST['ddlReferralTypeRef'] == '') ? 0 : $_POST['ddlReferralTypeRef'];
	   $txtRelationRef = $_POST['txtRelationRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRelationRef']);
	   $txtCourseRef = $_POST['txtCourseRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCourseRef']);
	   $txtFirstNameRef = $_POST['txtFirstNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtFirstNameRef']);
	   $txtLastNameRef = $_POST['txtLastNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLastNameRef']);
	   $txtPhoneRef = $_POST['txtPhoneRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhoneRef']);
	   $txtEmailRef = $_POST['txtEmailRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmailRef']);
	   $txtP1FirstNameRef = $_POST['txtP1FirstNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1FirstNameRef']);
	   $txtP1LastNameRef = $_POST['txtP1LastNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1LastNameRef']);
	   $txtP1PhoneRef = $_POST['txtP1PhoneRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1PhoneRef']);
	   $txtP1EmailRef = $_POST['txtP1EmailRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1EmailRef']);
	   $txtP2FirstNameRef = $_POST['txtP2FirstNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2FirstNameRef']);
	   $txtP2LastNameRef = $_POST['txtP2LastNameRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2LastNameRef']);
	   $txtP2PhoneRef = $_POST['txtP2PhoneRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2PhoneRef']);
	   $txtP2EmailRef = $_POST['txtP2EmailRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2EmailRef']);
	   $ddlDiscloseRef = $_POST['ddlDiscloseRef'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlDiscloseRef']);
	   $REF_BY = $_POST['REF_BY']=='undefined' ? '' : $_POST['REF_BY'];
	   
	   //    $actionid = $activityid == 0 ? 1 : 2;
	   
	   if($REF_BY=='')throw new Exception('Referral By Not Found.');
	   if($REFBYID == 0) throw new Exception("UserID Not Found.");
	   if($ddlReferralTypeRef == 0) throw new Exception("Select Referral Type.");


	//    $sql = "SELECT * FROM REFERRALS WHERE ACTIVITY='$txtActivity' AND ACTIVITYID!=$activityid AND ISDELETED=0";
	//    $row_count = unique($sql);

	//    $data = array();
	//    if($row_count == 0)
	//    {
		   $query="EXEC [REFERRALS_SP] 1,0,'$REF_BY',$REFBYID,$ddlReferralTypeRef,'$txtRelationRef','$txtCourseRef','$txtFirstNameRef','$txtLastNameRef',
				'$txtPhoneRef','$txtEmailRef','$txtP1FirstNameRef','$txtP1LastNameRef','$txtP1PhoneRef','$txtP1EmailRef',
				'$txtP2FirstNameRef','$txtP2LastNameRef','$txtP2PhoneRef','$txtP2EmailRef','$ddlDiscloseRef',$REFBYID";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
			   $data['query'] = $query;
			   $data['success'] = true;
			//    if(!empty($activityid))$data['message'] = 'Record successfully updated.';else
			   $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	// 	   $data['success'] = false;
	// 	   $data['message'] = 'Record already exists';
	// 	   echo json_encode($data);exit;
	//    }

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
/* ============ SAVE REFERRALS ============= */ 




/*============ GET REFERRALS =============*/ 
function getReferrals($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REF_BYID = ($_POST['REF_BYID']=='undefined' || $_POST['REF_BYID']=='') ? 0 : $_POST['REF_BYID'];
		$REF_BY = $_POST['REF_BY']=='undefined' ? '' : $_POST['REF_BY'];
		if($REF_BYID==0)throw new Exception('UserID Not Found.');
		if($REF_BY=='')throw new Exception('Referral By Not Found.');
		$query = "SELECT REFID,CONVERT(VARCHAR,REFDATE,106)REFDATE,REFBYID,REFMID,
		(SELECT REF_DESC FROM REFERRAL_MASTER WHERE REFMID=R.REFMID)REFERRAL_NAME,
		RELATION,COURSE,FIRSTNAME,LASTNAME,CONTACTNO,EMAILID,
		P1_FIRSTNAME,P1_LASTNAME,P1_CONTACTNO,P1_EMAILID,P2_FIRSTNAME,P2_LASTNAME,P2_CONTACTNO,P2_EMAILID,REF_DISCLOSE,
		REF_STATUS,REF_REMARKS,REFERRAL_AMT,
		CASE WHEN REFERRAL_ON IS NULL OR CONVERT(DATE,REFERRAL_ON,105)='1900-01-01' THEN '' ELSE CONVERT(VARCHAR,REFERRAL_ON,20) END REFERRAL_ON
		FROM REFERRALS R
		WHERE REFBY='$REF_BY' AND REFBYID=$REF_BYID 
		ORDER BY CONVERT(DATE,REFDATE,105) DESC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REFID'] = (int) $row['REFID'];
				if($row['REFERRAL_ON'] && $row['REFERRAL_ON']!=''){
					$date=date_create($row['REFERRAL_ON']);
					$row['REFERRAL_ON_SHOW']= date_format($date,"d-m-Y");
				}
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
/*============ GET REFERRALS =============*/ 



/*============ SAVE HOMEWORK =============*/
function saveHomeWork($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $SCCID  = ($_POST['SCCID'] == 'undefined' || $_POST['SCCID'] == '') ? 0 : $_POST['SCCID'];
	   $hwDone  = ($_POST['hwDone'] == 'undefined' || $_POST['hwDone'] == '' || $_POST['hwDone'] == '0') ? 0 : 1;
	   $txtStudentwork  = $_POST['txtStudentwork'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtStudentwork']);
	   
	   //==== IMAGE
	   if(isset($_FILES['txtHwImg']['name']) && $_FILES['txtHwImg']['size'] > 0 && $_FILES['txtHwImg']['size'] > 2097152) throw new Exception('File size too large.');
	   $existingHWImage  = $_POST['existingHWImage'] == 'undefined' ? '' : $_POST['existingHWImage'];
	   $txtHwImg = '';
	   if(isset($_FILES['txtHwImg']['name']) && $_FILES['txtHwImg']['size'] > 0){
		   $ext = pathinfo($_FILES['txtHwImg']['name'],PATHINFO_EXTENSION);
		   $txtHwImg .= strtolower(time().'.'.$ext);
	   }
	   else
	   {
		   $txtHwImg="";
	   }
	   //==== IMAGE

	   
	   
	   
	   
	   //==== CHECK VARIABLE
	   if($SCCID == 0){throw new Exception("SCCID Missing.");}
	   //==== CHECK VARIABLE



		$query="UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET HOMEWORK_DONE=$hwDone,STUDENTWORK='$txtStudentwork',HOMEWORK_IMG='$txtHwImg' WHERE SCCID=$SCCID AND REGID=$userid";
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

			if($txtHwImg != ''){

				if(isset($_FILES['txtHwImg']['name']) && $_FILES['txtHwImg']['size'] > 0)
				{
					move_uploaded_file($_FILES["txtHwImg"]["tmp_name"], '../images/homework/'.$txtHwImg);
				}

				
				if(isset($_FILES['txtHwImg']['name']) && $existingHWImage != '')
				{
					if (file_exists('../images/homework/'.$existingHWImage))
					{
						unlink('../images/homework/'.$existingHWImage);
					}
				}
			

			}

			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Home work successfully submitted.';
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


/*============ GET TUTORING ATTENDANCE =============*/ 
function getTutoringAttendance($mysqli){
	try
	{
		global $userid;
		$data = array();

		$query = "SELECT TOP 200 ATTID,REQID,TEACHERID, 
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=ST.TEACHERID)TEACHERNAME,CSUBID,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)SUBJECT,
		CONVERT(VARCHAR,ATTDATE,106)ATTDATE,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,FORMAT(ATTFROMTIME,'hh:mm tt')ATTFROMTIME,
		FORMAT(ATTTOTIME,'hh:mm tt')ATTTOTIME,CONVERT(VARCHAR(5), ATTHOURS, 108)ATTHOURS,RATE,AMOUNT,
		ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ISDELETED=0 AND ATTID=ST.ATTID),0)PAID,REMARKS
		FROM STUDENT_TUTORING_ATTENDANCE ST
		WHERE ISDELETED=0 AND REGID=$userid
		ORDER BY CONVERT(DATE,ATTDATE,105) DESC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['RATE'] = (float) $row['RATE'];
				$row['AMOUNT'] = (float) $row['AMOUNT'];
				$row['PAID'] = (float) $row['PAID'];
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







