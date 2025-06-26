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
		// COUNTRY
        case "getCountries":getCountries($conn);break;

		// TEACHER
        case "getTeachersByLocation":getTeachersByLocation($conn);break;
        case "getTeachersPlans":getTeachersPlans($conn);break;

		// L & A
        case "getLAGrades":getLAGrades($conn);break;
        case "getBatchesByLocation":getBatchesByLocation($conn);break;
        case "getBatchesByLocationMultiple":getBatchesByLocationMultiple($conn);break;
        case "getTopicsByLoc_Grade_Subject":getTopicsByLoc_Grade_Subject($conn);break;
        case "getSlidesByTopic_ForMulti":getSlidesByTopic_ForMulti($conn);break;
        case "getLaSubjectMaster":getLaSubjectMaster($conn);break;

		// GET student
        case "getStudentByPlanLocation":getStudentByPlanLocation($conn);break;
        case "getStudentByLocation":getStudentByLocation($conn);break;
		// GET TEACHERS
        case "getTeacherByLocation":getTeacherByLocation($conn);break;
        case "getTeacherVolunteerByLocation":getTeacherVolunteerByLocation($conn);break;
		// GET USER
        case "getUsersByLocation":getUsersByLocation($conn);break;

		// PLANS
		case "getPlans":getPlans($conn);break;
		case "getPlans_MultiSelect":getPlans_MultiSelect($conn);break;
		
		// INDEX
		case "getProductDisplay":getProductDisplay($conn);break;
		case "getProductDisplayWithoutLoc":getProductDisplayWithoutLoc($conn);break;
		case "getResourcesTopLavel":getResourcesTopLavel($conn);break;

		// LOGIN LOG
		case "getLoginTypes":getLoginTypes($conn);break;

		// CLASS SUBJECT
		case "getClassSubjectMaster_Multi":getClassSubjectMaster_Multi($conn);break;
		case "getClassSubjectMaster":getClassSubjectMaster($conn);break;
		
		// PAYMENT MODES
		case "getPaymentModesMaster":getPaymentModesMaster($conn);break;
		
		
		// TASK
		case "getTaskMainCategory":getTaskMainCategory($conn);break;
		case "getTaskCategory":getTaskCategory($conn);break;

		// LA GRADE BY LOC
		case "getLAGradeMaster":getLAGradeMaster($conn);break;
		case "getGrades":getGrades($conn);break;

		case "getTermByLocation":getTermByLocation($conn);break;

		// PRODUCT
		case "getProducts":getProducts($conn);break;
		case "getProductMaster":getProductMaster($conn);break;

		// MEP OBJECT
		case "getObjectMaster":getObjectMaster($conn);break;

		// MARKETING 
		case "getMarketingChannels":getMarketingChannels($conn);break;

		// BACKOFFICE MENU
		case "getHasLinkMenu":getHasLinkMenu($conn);break;

		// BRANDS
		case "getBrandsByLocation":getBrandsByLocation($conn);break;

		// LOCATIONS
		case "getUserLocationsWithMainLocation":getUserLocationsWithMainLocation($conn);break;
		case "getLocations":getLocations($conn);break;

		// OPEN ISSUE
		case "saveOpenIssue":saveOpenIssue($conn);break;

		// INVENTORY
		case "getBookInventories":getBookInventories($conn);break;
		case "getInvChapters":getInvChapters($conn);break;
		
		// TEST MASTER
		case "getTestMasters":getTestMasters($conn);break;

		// GPT TOKEN MASTER
		case "getEssayTokens":getEssayTokens($conn);break;

		// ESSAY
		case "getEssays":getEssays($conn);break;
		
		// UNIVERSITY
		case "getUniversities":getUniversities($conn);break;

		// COLLEGE
		case "getColleges":getColleges($conn);break;

		default:invalidRequest();
	}
}else{
	invalidRequest();
}



/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ========== GET COUNTRIES =========== */
 function getCountries($mysqli){
	try
	{
		$data = array();
		$query = "SELECT COUNTRYID,COUNTRY,COUNTRY_SC,FLAG_ICON FROM COUNTRIES WHERE ISDELETED=0 ORDER BY COUNTRY";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
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
/* ========== GET COUNTRIES =========== */



/* ========== GET TEACHERS BY LOCATION =========== */
 function getTeachersByLocation($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('Error : LOCID Not Found.');
		$query = "SELECT [UID] TEACHERID,FIRSTNAME+' '+LASTNAME TEACHERNAME  FROM USERS WHERE ISDELETED=0 AND ARCHIVED=0 AND USERROLE='TEACHER' AND LOCID=$LOCID
					ORDER BY TEACHERNAME";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
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
/* ========== GET TEACHERS BY LOCATION =========== */


/* ========== GET TEACHERS PLANS =========== */
 function getTeachersPlans($mysqli){
	try
	{
		$data = array();
		$TEACHERID = ($_POST['TEACHERID'] == 'undefined' || $_POST['TEACHERID'] == '') ? 0 : $_POST['TEACHERID'];
		if($TEACHERID == 0)throw new Exception('Error : TEACHERID Not Found.');
		$query = "SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=TP.PLANID)PLANNAME 
		FROM TEACHER_PLAN TP 
		WHERE ISDELETED=0 AND TEACHERID=$TEACHERID AND PLANID NOT IN (SELECT PLANID FROM PLANS WHERE ISDELETED=1)";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
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
/* ========== GET TEACHERS PLANS =========== */


/* ========== GET LA GRADE BY LOCATION =========== */
 function getLAGrades($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('Error : LOCID Not Found.');
		$query = "SELECT GRADEID,GRADE FROM LA_GRADE_MASTER WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY LEN(GRADE),GRADE";
		$count = unique($query);

		if($count > 0){
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
/* ========== GET LA GRADE BY LOCATION =========== */


/* ========== GET BATCHES BY LOCATION =========== */
 function getBatchesByLocation($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('Error : LOCID Not Found.');
		$query = "SELECT BATCHID,BATCHNAME,BATCHDESC FROM LA_STUDENT_BATCHES WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY BATCHNAME";
		$count = unique($query);

		if($count > 0){
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
/* ========== GET BATCHES BY LOCATION =========== */


/* ========== GET BATCHES BY LOCATION MULTIPLE =========== */
 function getBatchesByLocationMultiple($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('Error : LOCID Not Found.');
		$query = "SELECT BATCHID AS id,BATCHNAME AS label FROM LA_STUDENT_BATCHES WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY BATCHNAME";
		$count = unique($query);

		if($count > 0){
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
/* ========== GET BATCHES BY LOCATION MULTIPLE =========== */

/*============ GET TOPICS =============*/ 
function getTopicsByLoc_Grade_Subject($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		if($LOCID == 0) throw new Exception('Select Location First.');
		if($GRADEID == 0) throw new Exception('Select Grade First.');
		if($SUBID == 0) throw new Exception('Select Subject First.');

		// $query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID 
		// FROM LA_TOPICS_MASTER TM 
		// WHERE ISDELETED=0 AND LOCID IN ($ddlLocation,1) AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
		// AND TOPICID NOT IN (SELECT TOPICID FROM LA_HIDE_TOPICS_LOCATIONWISE WHERE TOPICID=TM.TOPICID AND LOCID=$ddlLocation AND HIDE=1)
		// ORDER BY LOCID DESC, UNDER,TOPIC";		

		$query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID 
				FROM LA_TOPICS_MASTER TM 
				WHERE ISDELETED=0 AND LOCID=$LOCID AND GRADEID=$GRADEID AND SUBID=$SUBID
				ORDER BY UNDER,TOPIC";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Topic not found.';
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
/*============ GET TOPICS =============*/ 


/*============ GET SLIDES =============*/ 
function getSlidesByTopic_ForMulti($mysqli){
	try
	{
		$data = array();
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];
		if($TOPICID == 0) throw new Exception('Error : Invalid TOPICID.');

		$query = "SELECT SLIDEID AS id,SLIDEHEADING AS label FROM LA_TOPIC_SLIDE_MASTER WHERE ISDELETED=0 AND TOPICID=$TOPICID ORDER BY SLIDEHEADING";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Slides not found.';
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
/*============ GET SLIDES =============*/ 


/*============ GET LA SUBJECT MASTER =============*/ 
function getLaSubjectMaster($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$query = "SELECT SUBID, SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE LOCID=$LOCID ORDER BY SUBJECTNAME";
		$data['$query']=$query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'LA Subject not found.';
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
/*============ GET LA SUBJECT MASTER =============*/ 



/*============ GET STUDENT BY LOCATION =============*/
function getStudentByLocation($mysqli){
	try
	{	
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');

		$query = "SELECT REGID,FIRSTNAME,LASTNAME FROM REGISTRATIONS WHERE ISDELETED=0 AND ARCHIVED=0 AND APPROVED=1 AND LOCATIONID=$LOCID
				ORDER BY FIRSTNAME";
		$data['$query'] = $query;
		
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



/*============ GET STUDENT BY PLAN & LOCATION =============*/
function getStudentByPlanLocation($mysqli){
	try
	{	
		$data = array();
		$PLANID = (!isset($_POST['PLANID']) || $_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];
		if($PLANID == 0)throw new Exception('PLANID Not Found.');
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');

		// $query = "SELECT REGID,(SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)FIRSTNAME,
		// 	(SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)LASTNAME 
		// 	FROM REGISTRATION_DETAILS RD WHERE PLANID IN ($PLANID) AND CANCELLED=0 AND 
		// 	REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$LOCID AND APPROVED=1 AND ARCHIVED=0) ORDER BY FIRSTNAME";
		$query = "SELECT REGID, FIRSTNAME,LASTNAME
			FROM REGISTRATIONS 
			WHERE ISDELETED=0 AND LOCATIONID=$LOCID AND APPROVED=1 AND ARCHIVED=0
			AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE PLANID IN ($PLANID) AND CANCELLED=0)
			ORDER BY FIRSTNAME";
		$data['$query'] = $query;
		
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['REGID'] = (int) $row['REGID'];
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
/*============ GET STUDENT BY PLAN & LOCATION =============*/ 



/*============ GET TEACHER BYLOCATION =============*/
function getTeacherByLocation($mysqli){
	try
	{		
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');

		$query = "SELECT UID,FIRSTNAME,LASTNAME,USERROLE FROM USERS WHERE ISDELETED=0 AND ARCHIVED=0 AND USERROLE='TEACHER' AND LOCID=$LOCID ORDER BY FIRSTNAME";
		$data['$query'] = $query;
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['UID'] = (int) $row['UID'];
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
/*============ GET TEACHER BYLOCATION =============*/


/*============ GET TEACHER/VOLUNTEER BYLOCATION =============*/
function getTeacherVolunteerByLocation($mysqli){
	try
	{		
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');

		$query = "SELECT UID,FIRSTNAME,LASTNAME,USERROLE FROM USERS WHERE ISDELETED=0 AND ARCHIVED=0 AND USERROLE IN ('TEACHER','VOLUNTEER') AND LOCID=$LOCID ORDER BY USERROLE,FIRSTNAME";
		$data['$query'] = $query;
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['UID'] = (int) $row['UID'];
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
/*============ GET TEACHER BYLOCATION =============*/

/*============ Get Users ByLOCATION =============*/ 
function getUsersByLocation($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] =='undefined' || $_POST['LOCID'] =='') ? 0 : $_POST['LOCID'];
		$query = "SELECT [UID],FIRSTNAME,LASTNAME,USERROLE,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=U.LOCID)[LOCATION],LOCID,SUPERVISOR,
		ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=U.SUPERVISOR),'')SUPERVISOR_NAME
		 FROM USERS U WHERE ISDELETED=0 AND ARCHIVED=0 AND LOCID=$LOCID ORDER BY USERROLE,FIRSTNAME";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['UID'] = (int) $row['UID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

/*============ Get Plans Multi Select =============*/ 
function getPlans_MultiSelect($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PLANID,PLANNAME, PLANID AS id,PLANNAME AS label
		--,convert(varchar, STARTDATE, 106)STARTDATE,
		--convert(varchar, ENDDATE, 106)ENDDATE,PRICE,INST_AMOUNT,INST_FREQ,INST_NO,
		--convert(varchar, DISPLAYFROMDATE, 106)DISPLAYFROMDATE,
		--convert(varchar, DISPLAYTODATE, 106)DISPLAYTODATE,DISPLAYCOLOR,ACTIVE
		FROM PLANS WHERE ISDELETED=0 ORDER BY label";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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
/*============ Get Plans Multi Select =============*/ 

/*============ Get Plans =============*/ 
function getPlans($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PLANID, PLANNAME FROM PLANS WHERE ISDELETED=0 ORDER BY PLANNAME";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
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
/*============ Get Plans Multi Select =============*/ 



/*============ Get getProductDisplay =============*/ 
function getProductDisplay($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PM.PDMID,PM.DISPLAY_PRODUCT, 
		(SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0) +', ' FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=PM.PDMID AND ISDELETED=0 FOR XML PATH('')) PRODUCTS,
		PM.[ORDER],PM.COLORCODE,'P'PTYPE,PM.ISHEADER,PM.HEADER
		FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0
		ORDER BY PTYPE DESC, [ORDER]";

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PDMID'] = (int) $row['PDMID'];
				$data['data']['PRODUCTS'][] = $row;
			}

			
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}

		// GET_LOCATION_WISE
		$queryLoc = "SELECT LOC_ID PDMID,[LOCATION] DISPLAY_PRODUCT,'' PRODUCTS,0 [ORDER],'#000000'COLORCODE,'L'PTYPE,0 ISHEADER,'' HEADER,HIDE_IN_DISPLAY 
					FROM LOCATIONS WHERE ISDELETED=0 AND HIDE_IN_DISPLAY=0 
					ORDER BY PTYPE DESC, [ORDER]";
		$countLoc = unique($queryLoc);
		if($countLoc>0){
			$resultLoc = sqlsrv_query($mysqli, $queryLoc);
			while ($rowLoc = sqlsrv_fetch_array($resultLoc,SQLSRV_FETCH_ASSOC)) {
				$rowLoc['PDMID'] = (int) $rowLoc['PDMID'];
				$data['data']['LOCATIONS'][] = $rowLoc;
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
/*============ Get getProductDisplay =============*/ 


/*============ Get getProductDisplay Without Location =============*/ 
function getProductDisplayWithoutLoc($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PM.PDMID,PM.DISPLAY_PRODUCT, 
		(SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0) +', ' FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=PM.PDMID AND ISDELETED=0 FOR XML PATH('')) PRODUCTS,PM.[ORDER],PM.COLORCODE,'P'PTYPE
		FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0
		ORDER BY PTYPE DESC, [ORDER]";

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PDMID'] = (int) $row['PDMID'];
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
/*============ Get getProductDisplay Without Location =============*/ 


/*============ Get Resources Top Level =============*/ 
function getResourcesTopLavel($mysqli){
	try{
		$data = array();
		$query = "SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category' AND UNDER_ID=0 order by SEQNO";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ID'] = (int) $row['ID'];
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
/*============ Get Resources Top Level =============*/ 



/*============ Get Login Types =============*/ 
function getLoginTypes($mysqli){
	try
	{
		$data = array();
		$query = "SELECT DISTINCT LOGINTYPE FROM LOGINS_LOG ORDER BY LOGINTYPE";
		$count = unique($query);
		if($count>0){
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
/*============ Get Login Types =============*/ 



/*============ Get Class Subject Master=============*/ 
function getClassSubjectMaster_Multi($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CSUBID As id,SHORT_DESC As label FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0 ORDER BY label";
		$count = unique($query);
		if($count>0){
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
/*============ Get Class Subject Master=============*/ 

/*============ Get Class Subject Master=============*/ 
function getClassSubjectMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CSUBID, SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0 ORDER BY SHORT_DESC";
		$count = unique($query);
		if($count>0){
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
/*============ Get Class Subject Master=============*/ 


/*============ Get Paymentmodes Master=============*/ 
function getPaymentModesMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PMID,PAYMENTMODE FROM PAYMENTMODES WHERE ISDELETED=0 ORDER BY PAYMENTMODE";
		$count = unique($query);
		if($count>0){
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
/*============ Get Paymentmodes Master=============*/ 



/*============ Get Task Main Category Master=============*/
function getTaskMainCategory($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] =='undefined' || $_POST['LOCID'] =='') ? 0 : $_POST['LOCID'];
		$query = "SELECT TASKMAINCATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKMAINCAT 
				FROM TASK_MAIN_CATEGORIES GM 
				WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY [LOCATION],LEN(TASKMAINCAT),TASKMAINCAT";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKMAINCATID'] = (int) $row['TASKMAINCATID'];
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
/*============ Get Task Main Category Master=============*/ 



/*============ GET TASK SUB CATEGORY =============*/ 
function getTaskCategory($mysqli){
	try
	{
		$data = array();
		$TASKMAINCATID = ($_POST['TASKMAINCATID'] =='undefined' || $_POST['TASKMAINCATID'] =='') ? 0 : $_POST['TASKMAINCATID'];
		$query = "SELECT TASKCATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKCAT 
				FROM TASK_CATEGORIES GM 
				WHERE ISDELETED=0 AND TASKMAINCATID=$TASKMAINCATID ORDER BY [LOCATION],LEN(TASKCAT),TASKCAT";
		// $data['$query'] =$query;
		// echo json_encode($data);exit;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKCATID'] = (int) $row['TASKCATID'];
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
/*============ GET TASK SUB CATEGORY =============*/ 


/*============ GET LA GRADE BY LOC =============*/ 
function getLAGradeMaster($mysqli){
	try
	{
		$data = array();
		
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT GRADEID,LOCID,GRADE
				FROM LA_GRADE_MASTER  WHERE ISDELETED=0  AND LOCID=$ddlLocation";
		$data['query'] = $query;

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
	
			// echo json_encode($data);
			// exit;
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


/*============ GET GRADES =============*/ 
function getGrades($mysqli){
	try
	{
		$data = array();
		$query = "SELECT GRADEID,GRADE,GRADE_DESC,
		CASE WHEN LTRIM(RTRIM(GRADE))=LTRIM(RTRIM(GRADE_DESC)) THEN GRADE ELSE GRADE+' ('+GRADE_DESC+')' END GRADE_WITH_DESC  
		FROM GRADES_MASTER  WHERE ISDELETED=0  ORDER BY LEN(GRADE), GRADE";
		$data['query'] = $query;

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
	
			// echo json_encode($data);
			// exit;
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


/*============ Get Terms =============*/ 
function getTermByLocation($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$query = "SELECT TERMID,TERM,CONVERT(VARCHAR,FROM_DATE,107)FROMDATE,
				CONVERT(VARCHAR,TO_DATE,107)TODATE,ISNULL(LOC_ID,0),
				(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TERMS.LOC_ID)LOCATION
				FROM TERMS WHERE ISDELETED=0 AND LOC_ID=$LOCID";

		$row_count = unique($query);
		if($row_count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TERMID'] = (int) $row['TERMID'];
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


/*============ Get Product =============*/ 
function getProducts($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PRODUCT_ID,PRODUCT,PRODUCT_DESC,DISPLAY_ORDER,DISPLAY_COLOR 
		FROM PRODUCTS WHERE ISDELETED=0 ORDER BY DISPLAY_ORDER";
		$row_count = unique($query);
		if($row_count>0){
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


/*============ Get Product Master =============*/ 
function getProductMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PDMID,DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE ISDELETED=0 ORDER BY [ORDER]";
		$row_count = unique($query);
		if($row_count>0){
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


/*============ Get Object Master =============*/ 
function getObjectMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT OBJMASTER_ID,OBJMASTER_DESC,OBJECT_TYPE_DESC FROM MEP_OBJECT_MASTER WHERE ISDELETED=0 ORDER BY OBJMASTER_DESC";
		$row_count = unique($query);
		if($row_count>0){
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


/*============ Get Marketing Channels =============*/ 
function getMarketingChannels($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID == 0) throw new Exception('Invalid LOCID.');
		$query = "SELECT MCHID,CHANNEL,DESCR FROM MKT_CHANNELS WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY CHANNEL";
		$row_count = unique($query);
		if($row_count>0){
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

/*============ Get Has Link Menu =============*/ 
function getHasLinkMenu($mysqli){
	try{
		global $userid;
		$data = array();
		
		$query = "SELECT MENUID,MENU,PAGE_LINK,PAGE_DESC,UNDER_MENUID FROM BO_MENU M WHERE ISDELETED=0 AND HAS_LINK=1 ORDER BY PAGE_LINK";
		$count = unique($query);
		if($count > 0){
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

/*============ Get Brands =============*/ 
function getBrandsByLocation($mysqli){
	try{
		global $userid;
		$data = array();
		
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];

		$query = "SELECT BRANDID,BRANDNAME,LOGO,LOGO_DESC FROM BRANDS WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY BRANDNAME";
		$count = unique($query);
		if($count > 0){
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


function getUserLocationsWithMainLocation($mysqli){
	try
	{
		$data = array();
		
		if($_SESSION['USER_LOCID'] == '1')
		{
			$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 ORDER BY LOCATION";
		}
		else{
			$USER_LOCID = isset($_SESSION['USER_LOCID']) ? $_SESSION['USER_LOCID'] : 0;
			$LOCATIONS = $USER_LOCID==1 ? "1" : "1 ,$USER_LOCID";
			$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID IN ($LOCATIONS) ORDER BY LOCATION";
		}
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['LOC_ID'] = (int) $row['LOC_ID'];
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


/*============ Get Location =============*/ 
function getLocations($mysqli){
	try
	{
		$query = "SELECT LOC_ID,LOCATION,LOC_DESC,LOC_CONTACT,LOC_PERSON,LOC_EMAIL,COMPANY_NAME,TAXID,LOC_ADDRESS_LINE1,
		LOC_ADDRESS_LINE2,LOC_CITY,LOC_STATE,LOC_COUNTRY,LOC_ZIPCODE,IS_ET,LOC_ET_DIFF,CURRENCY_ID,
		(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=LOCATIONS.CURRENCY_ID)CURRENCY_CODE,
		LOGIN_ID,DBO.GET_CLEAR_LOCATION_PASSWORD(LOC_ID) LOGIN_PWD,
		(SELECT FLAG_ICON FROM COUNTRIES WHERE ISDELETED=0 AND COUNTRYID=LOCATIONS.LOC_COUNTRY)FLAG_ICON,
		(SELECT LANGUAGE_NAME FROM LANGUAGE_MASTER WHERE LANID=LOCATIONS.LANID)LAN,LANID,
		(SELECT SCRIPT_NAME FROM SCRIPT_MASTER WHERE SCRIPTID=LOCATIONS.SCRIPTID)SCRIPT,SCRIPTID,LA_NOOF_NEXT_TOPICS,HIDE_IN_DISPLAY 
		FROM LOCATIONS WHERE ISDELETED=0 AND HIDE_IN_DISPLAY=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
			$row['CURRENCY_ID'] = (int) $row['CURRENCY_ID'];
			$row['HIDE_IN_DISPLAY'] = (string) $row['HIDE_IN_DISPLAY'];
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


/*=========== SAVE OPEN ISSUE ===========*/
function saveOpenIssue($mysqli){
	try
	{
	   $data = array();
	   global $userid;	

	   $REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
	   $ISSUE = $_POST['ISSUE'] == 'undefined' ? '' : str_replace("'","''",$_POST['ISSUE']);

	   if($REGID == 0) throw new Exception("Invalid REGID.");
	   if($ISSUE == '') throw new Exception("Please Enter Your Issue First.");


		$query="EXEC [STUDENT_RELATED_ISSUES_SP] $REGID,'PAYMENT','$ISSUE','ADMIN','$userid'";
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
			$data['message'] = 'Record successfully inserted.';
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



/* ========== GET BOOK INVENTORY =========== */
function getBookInventories($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT INVID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=I.LOCID)[LOCATION],ITID,
		ISNULL((SELECT INVTYPE FROM INVENTORY_TYPES WHERE ITID=I.ITID),'')INVTYPE,SECID,
		(SELECT SECTION FROM SECTION_MASTER WHERE SECID=I.SECID)SECTION,CATID,
		(SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=I.CATID)CATEGORY,SUBCATID,
		(SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=I.SUBCATID)SUBCATEGORY,TOPICID,
		(SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=I.TOPICID)TOPIC,PRODUCTID,
		ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=I.PRODUCTID),'')PRODUCT,TITLE,DESCR,COST,PUBID,
		ISNULL((SELECT MAKE_PUB FROM MAKE_PUBLISHERS WHERE PUBID=I.PUBID),'')PUBLISHER,ITYPE 
		FROM INVENTORY I WHERE ISDELETED=0 AND ITID IN (ISNULL((SELECT ITID FROM INVENTORY_TYPES WHERE ISDELETED=0 AND INVTYPE='BOOKS'),0)) 
		AND LOCID=$ddlLocation ORDER BY TITLE";
		$count = unique($query);
		if($count > 0){
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
/* ========== GET BOOK INVENTORY =========== */



/* ========== GET INVENTORY CHAPTERS =========== */
function getInvChapters($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlProduct = ($_POST['ddlProduct'] =='undefined' || $_POST['ddlProduct'] =='') ? 0 : $_POST['ddlProduct'];
		$ddlInventory = ($_POST['ddlInventory'] =='undefined' || $_POST['ddlInventory'] =='') ? 0 : $_POST['ddlInventory'];

		if($ddlLocation == 0) throw new Exception('Invalid Location.');
		if($ddlProduct == 0) throw new Exception('Invalid Product.');
		if($ddlInventory == 0) throw new Exception('Invalid Inventory.');

		$query = "SELECT CHAPID,DESCR,CHAPNO,CHAPTER,DURATION_MIN FROM INV_CHAPTERS WHERE LOCID=$ddlLocation AND PRODUCTID=$ddlProduct AND INVID=$ddlInventory ORDER BY CHAPTER";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET INVENTORY CHAPTERS =========== */


/* ========== GET TEST MASTERS =========== */
function getTestMasters($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TESTID,TESTDESC,TESTYEAR,TESTOPEN,REMARKS,MULTIPLEATTEMPTS,NUMOFATTEMPTS,FREE_TEST,ADAPTIVE 
				FROM TEST_MASTER
				WHERE ISDELETED=0
				ORDER BY TESTDESC";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET TEST MASTERS =========== */


/* ========== GET TOKENS =========== */
function getEssayTokens($mysqli){
	try
	{
		$data = array();
		
		$LOCID = ($_POST['LOCID'] =='undefined' || $_POST['LOCID'] =='') ? 0 : $_POST['LOCID'];
		if($LOCID == 0) throw new Exception('Invalid Location.');

		$query = "SELECT TOKENID,TOKEN
				FROM GPT_TOKEN
				WHERE ISDELETED=0 AND LOCID=$LOCID AND TOKENCATID IN (SELECT TOKENCATID FROM GPT_TOKEN_CATEGORIES WHERE ISDELETED=0 AND CATEGORY='ESSAY')
				ORDER BY TOKEN";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET TOKENS =========== */


/* ========== GET ESSAYS =========== */
function getEssays($mysqli){
	try
	{
		$data = array();
		
		$TESTID = ($_POST['TESTID'] =='undefined' || $_POST['TESTID'] =='') ? 0 : $_POST['TESTID'];
		$TSECID = ($_POST['TSECID'] =='undefined' || $_POST['TSECID'] =='') ? 0 : $_POST['TSECID'];
		if($TESTID == 0) throw new Exception('Invalid TESTID.');
		if($TSECID == 0) throw new Exception('Invalid TSECID.');

		$query = "SELECT ESSID,ESSTOPIC,LIMITON,LIMIT,TIMEALLOWED 
		FROM ESSAYS WHERE ISDELETED=0 AND TESTID=$TESTID AND TSECID=$TSECID 
		ORDER BY ESSTOPIC";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET ESSAYS =========== */


/* ========== GET UNIVERSITIES =========== */
function getUniversities($mysqli){
	try
	{
		$data = array();
		$query = "SELECT UNIVERSITYID, UNIVERSITY FROM UNIVERSITY_MASTER WHERE ISDELETED=0 ORDER BY UNIVERSITY";
		// $data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET UNIVERSITIES =========== */


/* ========== GET COLLEGES =========== */
function getColleges($mysqli){
	try
	{
		$data = array();
		$UNIVERSITYID = ($_POST['UNIVERSITYID'] == 'undefined' || $_POST['UNIVERSITYID']=='') ? 0 : $_POST['UNIVERSITYID'];
		$query = "SELECT CLID, COLLEGE, ADDRESSLINE1, ADDRESSLINE2, CITY, STATE, ZIPCODE, COUNTRYID, PHONE, EMAILID, WEBSITE 
		FROM COLLEGES_MASTER WHERE ISDELETED=0";
		if($UNIVERSITYID > 0) $query .= " AND UNIVERSITYID=$UNIVERSITYID";
		$query .= "  ORDER BY COLLEGE";
		// $data['query'] = $query;
		$count = unique($query);
		if($count > 0){
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
/* ========== GET ESSAYS =========== */

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







