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
        case "getStudentCollegeRoadmap":getStudentCollegeRoadmap($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## CLASSES
        case "saveDataClass":saveDataClass($conn);break;
        case "getRoadmapClasses":getRoadmapClasses($conn);break;
        case "deleteClass":deleteClass($conn);break;
		
		
		//######## ACTIVITY
        case "saveDataActivity":saveDataActivity($conn);break;
        case "getRoadmapActivities":getRoadmapActivities($conn);break;
		case "getSelectedActivityLegend":getSelectedActivityLegend($conn);break;
        case "deleteActivity":deleteActivity($conn);break;
		
		
		//######## TESTS
        case "saveDataTest":saveDataTest($conn);break;
        case "getRoadmapTests":getRoadmapTests($conn);break;
        case "deleteTest":deleteTest($conn);break;
		
		
		//######## MAJOR/MINOR
        case "saveDataMM":saveDataMM($conn);break;
        case "getRoadmapMajorMinor":getRoadmapMajorMinor($conn);break;
        case "deleteMM":deleteMM($conn);break;
		
		
		//######## COLLEGES
        case "saveDataCollege":saveDataCollege($conn);break;
        case "getRoadmapColleges":getRoadmapColleges($conn);break;
        case "deleteCollege":deleteCollege($conn);break;
		
		
		//######## RECOMMENDATION
        case "saveDataRec":saveDataRec($conn);break;
		
		
		//######## EXTRA DATA TABLE
		case "getStudentByLoc":getStudentByLoc($conn);break;
		case "getActivityLegend":getActivityLegend($conn);break;


		
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

		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		$ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlStudentType']);
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$txtFirstName = $_POST['txtFirstName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtFirstName']);
		$txtLastName = $_POST['txtLastName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLastName']);
		$txtPhone = $_POST['txtPhone'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhone']);
		$txtEmail = $_POST['txtEmail'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmail']);
		$txtP1FName = $_POST['txtP1FName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1FName']);
		$txtP1LName = $_POST['txtP1LName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1LName']);
		$txtP1Phone = $_POST['txtP1Phone'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1Phone']);
		$txtP1Email = $_POST['txtP1Email'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP1Email']);
		$txtP2FName = $_POST['txtP2FName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2FName']);
		$txtP2LName = $_POST['txtP2LName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2LName']);
		$txtP2Phone = $_POST['txtP2Phone'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2Phone']);
		$txtP2Email = $_POST['txtP2Email'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtP2Email']);
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSchoolYear = ($_POST['ddlSchoolYear'] == 'undefined' || $_POST['ddlSchoolYear'] == '') ? 0 : $_POST['ddlSchoolYear'];
		$txtClassof = ($_POST['txtClassof'] == 'undefined' || empty($_POST['txtClassof'])) ? 0 : $_POST['txtClassof'];
		$txtSchool = $_POST['txtSchool'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtSchool']);
		$txtCounty = $_POST['txtCounty'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCounty']);
		$ddlCountry = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];
		// $ddlState = ($_POST['ddlState'] == 'undefined' || $_POST['ddlState'] == '') ? 0 : $_POST['ddlState'];
		// $ddlCity = ($_POST['ddlCity'] == 'undefined' || $_POST['ddlCity'] == '') ? 0 : $_POST['ddlCity'];	
		$txtState = $_POST['txtState'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtState']);
		$txtCity = $_POST['txtCity'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCity']);
		$txtRemarks = $_POST['txtRemarks'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks']);

		$BackOff = ($_POST['BackOff'] == 'false' || $_POST['BackOff'] == 'undefined') ? false : true;
		// $data['BackOff'] = $BackOff;
		// echo json_encode($data);exit;


		$actionid = $roadmapid == 0 ? 1 : 2;

		if($ddlStudentType == '') throw new Exception("Select Student Type.");
		if($ddlLocation == 0) throw new Exception("Select Location.");
		if($ddlStudentType == 'Registered' && $ddlStudent==0) throw new Exception("Select Student Name.");
		if($txtFirstName == '') throw new Exception("Enter Student First Name.");
		
		if(!$BackOff){
			if($txtLastName == '') throw new Exception("Enter Student Last Name.");
			if($txtPhone == '') throw new Exception("Enter Student Phone Number.");
			if($txtEmail == '') throw new Exception("Enter Student Email ID.");
		}


		$sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP WHERE REGID=$ddlStudent AND ROADMAPID!=$roadmapid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [STUDENT_COLLEGE_ROADMAP_SP] $actionid,$roadmapid,'$ddlStudentType',$ddlLocation,$ddlStudent,'$txtFirstName',
			'$txtLastName','$txtEmail','$txtPhone','$txtP1FName','$txtP1LName','$txtP1Email','$txtP1Phone','$txtP2FName','$txtP2LName',
			'$txtP2Email','$txtP2Phone',$ddlGrade,$ddlSchoolYear,$txtClassof,'$txtSchool','$txtCounty',$ddlCountry,'$txtState','$txtCity','$txtRemarks',$userid";
			$data['query'] = $query;
		// echo json_encode($data);exit;

			$stmt=sqlsrv_query($mysqli, $query);

			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET ROADMAPID
				$row = sqlsrv_fetch_array($stmt);
				$data['data'] = $row;
				$data['GET_ROADMAPID'] = $row['ROADMAPID'];
				$data['GET_REGID'] = $row['REGID'];
				$REGID = (int)$row['REGID'];
				$GET_ROADMAPID = $row['ROADMAPID'];
				
				$data['BackOff'] = $BackOff;
				if(!$BackOff && $REGID>0 && $actionid==1){
					// GET STUDENT ID/PASS
					$queryStudentID = "SELECT LOGINID,DBO.GET_CLEAR_STUDENT_PASSWORD($REGID)PASS FROM REGISTRATIONS WHERE REGID=$REGID";
					$stmtStudentID=sqlsrv_query($mysqli, $queryStudentID);
					$rowStudentID = sqlsrv_fetch_array($stmtStudentID,2);
					$data['ID']= $rowStudentID['LOGINID'];
					$data['PASS']= $rowStudentID['PASS'];
				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($roadmapid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Student Record already exists';
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


/* ========== GET STUDENT COLLEGE ROADMAP =========== */
 function getStudentCollegeRoadmap($mysqli){
	try
	{
		global $userid;
		$data = array();

		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		$regid =  ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];

		$query = "SELECT ROADMAPID,STUDENT_TYPE,LOCID,REGID,FIRSTNAME,LASTNAME,EMAILID,PHONE,PARENT1_FIRST_NAME,PARENT1_LAST_NAME,
		PARENT1_EMAILID,PARENT1_PHONE,PARENT2_FIRST_NAME,PARENT2_LAST_NAME,PARENT2_EMAILID,PARENT2_PHONE,CURRENT_GRADEID,
		(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SCR.CURRENT_GRADEID)GRADE,ADMYEARID,
		(SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=SCR.ADMYEARID)SCHOOLYEAR,CLASSOF,SCHOOL,COUNTY,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=SCR.COUNTRYID)COUNTRY,STATEID AS STATENAME,CITYID AS CITYNAME,REMARKS
		FROM STUDENT_COLLEGE_ROADMAP SCR
		WHERE ISDELETED=0";

		if($roadmapid > 0)$query .= " AND ROADMAPID=$roadmapid";
		if($regid>0)$query .= " AND REGID=$regid";
		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";
		$data['$query'] = $query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPID'] = (int) $row['ROADMAPID'];
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
/* ========== GET STUDENT COLLEGE ROADMAP =========== */


/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPID = ($_POST['ROADMAPID'] == 'undefined' || $_POST['ROADMAPID'] == '') ? 0 : $_POST['ROADMAPID'];  
			if($ROADMAPID == 0)throw new Exception('ROADMAPID Not Found.');
			$delQuery = "UPDATE STUDENT_COLLEGE_ROADMAP SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE ROADMAPID=$ROADMAPID";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CLASSES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataClass($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $roadmapcid = ($_POST['roadmapcid'] == 'undefined' || $_POST['roadmapcid'] == '') ? 0 : $_POST['roadmapcid'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ddlGrade_Class = ($_POST['ddlGrade_Class'] == 'undefined' || $_POST['ddlGrade_Class'] == '') ? 0 : $_POST['ddlGrade_Class'];
	   $ddlClassSubject_Class = ($_POST['ddlClassSubject_Class'] == 'undefined' || $_POST['ddlClassSubject_Class'] == '') ? 0 : $_POST['ddlClassSubject_Class'];
	   $txtRemarks_Class = $_POST['txtRemarks_Class'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks_Class']);

	   $actionid = $roadmapcid == 0 ? 1 : 2;

	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	   if($regid == 0) throw new Exception("REGID Error.");
	   if($ddlGrade_Class == 0) throw new Exception("Select Grade.");


	   $sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP_CLASSES WHERE ROADMAPID=$roadmapid AND REGID=$regid AND 
			GRADEID=$ddlGrade_Class AND CSUBID=$ddlClassSubject_Class AND ROADMAPCID!=$roadmapcid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [STUDENT_COLLEGE_ROADMAP_CLASSES_SP] $actionid,$roadmapcid,$roadmapid,$regid,$ddlGrade_Class,
		   $ddlClassSubject_Class,'$txtRemarks_Class',$userid";
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
			   if(!empty($roadmapcid))$data['message'] = 'Record successfully updated.';
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



/* ========== GET ROADMAP CLASSES =========== */
function getRoadmapClasses($mysqli){
	try
	{
		$data = array();
		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		if($roadmapid == 0)throw new Exception("RoadmapID Error.");
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		if($regid == 0)throw new Exception("RegID Error.");

		$query = "SELECT ROADMAPCID,ROADMAPID,REGID,GRADEID,
		(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SC.GRADEID)GRADE,CSUBID,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SC.CSUBID)CLASS_SUBJECT,REMARKS,RECOMMENDATION,
		CASE WHEN (RECOMMENDDATE is NULL OR RECOMMENDDATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,RECOMMENDDATE,106)
		END RECOMMENDDATE 
		FROM STUDENT_COLLEGE_ROADMAP_CLASSES SC
		WHERE ISDELETED=0 AND ROADMAPID=$roadmapid AND REGID=$regid
		ORDER BY GRADE DESC,CLASS_SUBJECT";

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPCID'] = (int) $row['ROADMAPCID'];
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
/* ========== GET ROADMAP CLASSES =========== */



/* =========== Delete =========== */ 
function deleteClass($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPCID = ($_POST['ROADMAPCID'] == 'undefined' || $_POST['ROADMAPCID'] == '') ? 0 : $_POST['ROADMAPCID'];  
			if($ROADMAPCID == 0)throw new Exception('ROADMAPCID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_ROADMAP_CLASSES_SP] 3,$ROADMAPCID,0,0,0,0,'',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CLASSES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% ACTIVITIES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataActivity($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $roadmapactid = ($_POST['roadmapactid'] == 'undefined' || $_POST['roadmapactid'] == '') ? 0 : $_POST['roadmapactid'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ddlGrade_Activity = ($_POST['ddlGrade_Activity'] == 'undefined' || $_POST['ddlGrade_Activity'] == '') ? 0 : $_POST['ddlGrade_Activity'];
	   $ddlActivity_Activity = ($_POST['ddlActivity_Activity'] == 'undefined' || $_POST['ddlActivity_Activity'] == '') ? 0 : $_POST['ddlActivity_Activity'];
	   $ddlJuniorVarsity_Activity = $_POST['ddlJuniorVarsity_Activity'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlJuniorVarsity_Activity']);
	   $ddlVarsity_Activity = $_POST['ddlVarsity_Activity'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlVarsity_Activity']);
	   $txtLocalClub_Activity = $_POST['txtLocalClub_Activity'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLocalClub_Activity']);
	//    $ddlDurationType_Activity = $_POST['ddlDurationType_Activity'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlDurationType_Activity']);
	//    $txtDuration_Activity = ($_POST['txtDuration_Activity'] == 'undefined' || $_POST['txtDuration_Activity'] == '') ? 0 : str_replace("'","''",$_POST['txtDuration_Activity']);
	   $Hours_Week_Activity = ($_POST['Hours_Week_Activity'] == 'undefined' || $_POST['Hours_Week_Activity'] == '') ? 0 : str_replace("'","''",$_POST['Hours_Week_Activity']);
	   $txtNoOFWeeks_Activity = ($_POST['txtNoOFWeeks_Activity'] == 'undefined' || $_POST['txtNoOFWeeks_Activity'] == '') ? 0 : str_replace("'","''",$_POST['txtNoOFWeeks_Activity']);
	   $ACTIVITY_model = (!empty($_POST['ACTIVITY_model'])) ? explode(',',$_POST['ACTIVITY_model']) : [];
	   $txtRemarks_Activity = $_POST['txtRemarks_Activity'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks_Activity']);

	   $actionid = $roadmapactid == 0 ? 1 : 2;

	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	   if($regid == 0) throw new Exception("REGID Error.");
	   if($ddlGrade_Activity == 0) throw new Exception("Select Grade.");
	   if($ddlActivity_Activity == 0) throw new Exception("Select Activity.");


	//    $sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP_CLASSES WHERE ROADMAPID=$roadmapid AND REGID=$regid AND 
	// 		GRADEID=$ddlGrade_Class AND CSUBID=$ddlClassSubject_Class AND ROADMAPCID!=$roadmapcid AND ISDELETED=0";
	//    $row_count = unique($sql);

	//    if($row_count == 0)
	//    {
		   $query="EXEC [STUDENT_COLLEGE_ROADMAP_ACTIVITIES_SP] $actionid,$roadmapactid,$roadmapid,$regid,$ddlGrade_Activity,
		   $ddlActivity_Activity,'$ddlJuniorVarsity_Activity','$ddlVarsity_Activity','$txtLocalClub_Activity',$Hours_Week_Activity,$txtNoOFWeeks_Activity,
		   '$txtRemarks_Activity',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);

		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

				$row = sqlsrv_fetch_array($stmt);
				$ROADMAPACTID = (int)$row['ROADMAPACTID'];
				

				// #### DELETE LEGEND
				if($actionid == 2){
					$queryDelLegend = "DELETE FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS WHERE ROADMAPID=$roadmapid AND ROADMAPACTID=$ROADMAPACTID";
					sqlsrv_query($mysqli, $queryDelLegend);
				}
				// #### INSERT LEGEND
				if(count($ACTIVITY_model) > 0){
					for($i=0;$i<count($ACTIVITY_model);$i++){
						$LEGENDID = $ACTIVITY_model[$i];

						$queryLegend = "EXEC [STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS_SP]1,0,$ROADMAPACTID,$roadmapid,$LEGENDID,'',$userid";
						sqlsrv_query($mysqli, $queryLegend);
						$data['$queryLegend'][]=$queryLegend;
									
					}
				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($roadmapactid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	//    	$data['success'] = false;
	//    	$data['message'] = 'Record already exists';
	//    	echo json_encode($data);exit;
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
/* ============ SAVE DATA ============= */ 



/* ========== GET ROADMAP ACTIVITY =========== */
function getRoadmapActivities($mysqli){
	try
	{
		$data = array();
		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		if($roadmapid == 0)throw new Exception("RoadmapID Error.");
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		if($regid == 0)throw new Exception("RegID Error.");

		$query = "SELECT ROADMAPACTID,ROADMAPID,REGID,GRADEID,
		(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SC.GRADEID)GRADE,ACTIVITYID,
		(SELECT ACTIVITY FROM ACTIVITIES_MASTER WHERE ACTIVITYID=SC.ACTIVITYID)ACTIVITY,JUNIOR_VARSITY,
		VARSITY,LOCAL_CLUB,HOURS_PER_WEEK,NO_OF_WEEKS,REMARKS,
		(SELECT (SELECT LEGEND FROM ACTIVITY_LEGEND_MASTER WHERE LEGENDID=SCA.LEGENDID) + ' | ' 
			FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS SCA WHERE ROADMAPACTID=SC.ROADMAPACTID AND ISDELETED=0 FOR XML PATH(''))LEGENDS,
		RECOMMENDATION,
		CASE WHEN (RECOMMENDDATE is NULL OR RECOMMENDDATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,RECOMMENDDATE,106)
		END RECOMMENDDATE 			
		FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES SC
		WHERE ISDELETED=0 AND ROADMAPID=$roadmapid AND REGID=$regid
		ORDER BY ACTIVITY,GRADE DESC";

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPACTID'] = (int) $row['ROADMAPACTID'];
				$row['LEGENDS'] = rtrim($row['LEGENDS'],' | ') ;
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
/* ========== GET ROADMAP ACTIVITY =========== */



/* ========== GET SELECTED ACTIVITY LEGENDS =========== */
function getSelectedActivityLegend($mysqli){
	try
	{
		$data = array();
		$ROADMAPACTID = ($_POST['ROADMAPACTID'] == 'undefined' || $_POST['ROADMAPACTID'] == '') ? 0 : $_POST['ROADMAPACTID'];
		if($ROADMAPACTID == 0)throw new Exception('ROADMAPACTID Error.');
		$ROADMAPID = ($_POST['ROADMAPID'] == 'undefined' || $_POST['ROADMAPID'] == '') ? 0 : $_POST['ROADMAPID'];
		if($ROADMAPID == 0)throw new Exception('ROADMAPID Error.');

		$query = "SELECT LEGENDID AS id,(SELECT LEGEND FROM ACTIVITY_LEGEND_MASTER WHERE LEGENDID=SC.LEGENDID)label 
		FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS SC
		WHERE ISDELETED=0 AND ROADMAPID=$ROADMAPID AND ROADMAPACTID=$ROADMAPACTID
		ORDER BY label";
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
/* ========== GET SELECTED ACTIVITY LEGENDS =========== */



/* =========== Delete =========== */ 
function deleteActivity($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPACTID = ($_POST['ROADMAPACTID'] == 'undefined' || $_POST['ROADMAPACTID'] == '') ? 0 : $_POST['ROADMAPACTID'];  
			if($ROADMAPACTID == 0)throw new Exception('ROADMAPACTID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_ROADMAP_ACTIVITIES_SP] 3,$ROADMAPACTID,0,0,0,0,'','','',0,0,'',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% ACTIVITIES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% TESTS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataTest($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $roadmaptestid = ($_POST['roadmaptestid'] == 'undefined' || $_POST['roadmaptestid'] == '') ? 0 : $_POST['roadmaptestid'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ddlGrade_Test = ($_POST['ddlGrade_Test'] == 'undefined' || $_POST['ddlGrade_Test'] == '') ? 0 : $_POST['ddlGrade_Test'];
	   $txtApproxTestDT_Test = $_POST['txtApproxTestDT_Test'] == 'undefined' ? '' : $_POST['txtApproxTestDT_Test'];
	   $txtTestName_Test = $_POST['txtTestName_Test'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTestName_Test']);
	   $txtTestScore_Test = $_POST['txtTestScore_Test'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTestScore_Test']);
	   $txtTestSuperScore_Test = $_POST['txtTestSuperScore_Test'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTestSuperScore_Test']);
	   $txtRemarks_Test = $_POST['txtRemarks_Test'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks_Test']);

	   $actionid = $roadmaptestid == 0 ? 1 : 2;

	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	//    if($regid == 0) throw new Exception("REGID Error.");
	   if($ddlGrade_Test == 0) throw new Exception("Select Grade.");
	   if($txtTestName_Test == '') throw new Exception("Enter Test Name.");


	   $sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP_TESTS WHERE ROADMAPID=$roadmapid AND GRADEID=$ddlGrade_Test AND 
	   	TESTNAME='$txtTestName_Test' AND TESTSCORE='$txtTestScore_Test' AND TESTSUPERSCORE='$txtTestSuperScore_Test' AND ROADMAPTESTID!=$roadmaptestid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [STUDENT_COLLEGE_ROADMAP_TESTS_SP] $actionid,$roadmaptestid,$roadmapid,$ddlGrade_Test,'$txtApproxTestDT_Test',
		   '$txtTestName_Test','$txtTestScore_Test','$txtTestSuperScore_Test','$txtRemarks_Test',$userid";
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
				if(!empty($roadmaptestid))$data['message'] = 'Record successfully updated.';
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



/* ========== GET ROADMAP TESTS =========== */
function getRoadmapTests($mysqli){
	try
	{
		$data = array();
		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		if($roadmapid == 0)throw new Exception("RoadmapID Error.");
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		if($regid == 0)throw new Exception("RegID Error.");

		$query = "SELECT ROADMAPTESTID,ROADMAPID,GRADEID,
		(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SC.GRADEID)GRADE,TESTNAME,
		CASE WHEN (APPROX_TEST_DATE is NULL OR APPROX_TEST_DATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,APPROX_TEST_DATE,106)
		END APPROX_TEST_DATE,TESTSCORE,
		TESTSUPERSCORE,REMARKS,RECOMMENDATION,
		CASE WHEN (RECOMMENDDATE is NULL OR RECOMMENDDATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,RECOMMENDDATE,106)
		END RECOMMENDDATE  
		FROM STUDENT_COLLEGE_ROADMAP_TESTS SC
		WHERE ISDELETED=0 AND ROADMAPID=$roadmapid
		ORDER BY CONVERT(DATE,APPROX_TEST_DATE,105) DESC,TESTNAME ASC";

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPTESTID'] = (int) $row['ROADMAPTESTID'];
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
/* ========== GET ROADMAP TESTS =========== */



/* =========== Delete =========== */ 
function deleteTest($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPTESTID = ($_POST['ROADMAPTESTID'] == 'undefined' || $_POST['ROADMAPTESTID'] == '') ? 0 : $_POST['ROADMAPTESTID'];  
			if($ROADMAPTESTID == 0)throw new Exception('ROADMAPTESTID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_ROADMAP_TESTS_SP] 3,$ROADMAPTESTID,0,0,'','','','','',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% TESTS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MAJOR/MINOR START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataMM($mysqli){
	try
	{
	   $data = array();
	   global $userid;


	   $roadmapmajorid = ($_POST['roadmapmajorid'] == 'undefined' || $_POST['roadmapmajorid'] == '') ? 0 : $_POST['roadmapmajorid'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ddlType_MM = $_POST['ddlType_MM'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlType_MM']);
	//    $ddlMajorMinor_MM = ($_POST['ddlMajorMinor_MM'] == 'undefined' || $_POST['ddlMajorMinor_MM'] == '') ? 0 : $_POST['ddlMajorMinor_MM'];
	   $txtMajor_MM = $_POST['txtMajor_MM'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtMajor_MM']);
	   $txtRemarks_MM = $_POST['txtRemarks_MM'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks_MM']);

	   $actionid = $roadmapmajorid == 0 ? 1 : 2;

	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	   if($regid == 0) throw new Exception("REGID Error.");
	   if($ddlType_MM == '') throw new Exception("Select Type.");
	   if($txtMajor_MM == '') throw new Exception("Enter Major/Minor.");


	   $sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS WHERE ROADMAPID=$roadmapid AND REGID=$regid AND MTYPE='$ddlType_MM' AND 
	   	MAJOR='$txtMajor_MM' AND ROADMAPMAJORID!=$roadmapmajorid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS_SP] $actionid,$roadmapmajorid,$roadmapid,$regid,'$ddlType_MM',
		   '$txtMajor_MM','$txtRemarks_MM',$userid";
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
				if(!empty($roadmapmajorid))$data['message'] = 'Record successfully updated.';
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



/* ========== GET ROADMAP MAJOR/MINOR =========== */
function getRoadmapMajorMinor($mysqli){
	try
	{
		$data = array();
		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		if($roadmapid == 0)throw new Exception("RoadmapID Error.");
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		if($regid == 0)throw new Exception("RegID Error.");

		$query = "SELECT ROADMAPMAJORID,ROADMAPID,REGID,MTYPE,MAJOR,REMARKS,RECOMMENDATION,
		CASE WHEN (RECOMMENDDATE is NULL OR RECOMMENDDATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,RECOMMENDDATE,106)
		END RECOMMENDDATE  
		FROM STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS SC
		WHERE ISDELETED=0 AND ROADMAPID=$roadmapid AND REGID=$regid
		ORDER BY MTYPE ASC,MAJOR ASC";

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPMAJORID'] = (int) $row['ROADMAPMAJORID'];
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
/* ========== GET ROADMAP MAJOR/MINOR =========== */



/* =========== Delete =========== */ 
function deleteMM($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPMAJORID = ($_POST['ROADMAPMAJORID'] == 'undefined' || $_POST['ROADMAPMAJORID'] == '') ? 0 : $_POST['ROADMAPMAJORID'];  
			if($ROADMAPMAJORID == 0)throw new Exception('ROADMAPMAJORID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS_SP] 3,$ROADMAPMAJORID,0,0,'','','',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MAJOR/MINOR END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% COLLEGES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataCollege($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $roadmapclid = ($_POST['roadmapclid'] == 'undefined' || $_POST['roadmapclid'] == '') ? 0 : $_POST['roadmapclid'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ddlUniversity_College = ($_POST['ddlUniversity_College'] == 'undefined' || $_POST['ddlUniversity_College'] == '') ? 0 : $_POST['ddlUniversity_College'];
	   $ddlCollege_College = ($_POST['ddlCollege_College'] == 'undefined' || $_POST['ddlCollege_College'] == '') ? 0 : $_POST['ddlCollege_College'];
	   $ddlInState_College = $_POST['ddlInState_College'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlInState_College']);
	   $ddlCollegeType_College = ($_POST['ddlCollegeType_College'] == 'undefined' || $_POST['ddlCollegeType_College'] == '') ? '' : $_POST['ddlCollegeType_College'];
	   $txtRemarks_College = $_POST['txtRemarks_College'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks_College']);

	   $actionid = $roadmapclid == 0 ? 1 : 2;

	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	   if($regid == 0) throw new Exception("REGID Error.");
	   if($ddlUniversity_College == 0) throw new Exception("Select University.");


	//    $sql = "SELECT * FROM STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS WHERE ROADMAPID=$roadmapid AND REGID=$regid AND MTYPE='$ddlType_MM' AND 
	//    	MAJORID=$ddlMajorMinor_MM AND ROADMAPMAJORID!=$roadmapmajorid AND ISDELETED=0";
	//    $row_count = unique($sql);

	//    if($row_count == 0)
	//    {
		   $query="EXEC [STUDENT_COLLEGE_ROADMAP_COLLEGES_SP] $actionid,$roadmapclid,$roadmapid,$regid,$ddlUniversity_College,$ddlCollege_College,
		   '$ddlInState_College','$ddlCollegeType_College','$txtRemarks_College',$userid";
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
				if(!empty($roadmapclid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	//    	$data['success'] = false;
	//    	$data['message'] = 'Record already exists';
	//    	echo json_encode($data);exit;
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
/* ============ SAVE DATA ============= */ 



/* ========== GET ROADMAP COLLEGES =========== */
function getRoadmapColleges($mysqli){
	try
	{
		$data = array();
		$roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
		if($roadmapid == 0)throw new Exception("RoadmapID Error.");
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		if($regid == 0)throw new Exception("RegID Error.");

		$query = "SELECT ROADMAPCLID,ROADMAPID,REGID,CLID,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SC.UNIVERSITYID)UNIVERSITY,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SC.CLID)COLLEGE,IN_STATE,COLTYPE,REMARKS,RECOMMENDATION,
		CASE WHEN (RECOMMENDDATE is NULL OR RECOMMENDDATE='1900-01-01 00:00:00.000')
			 THEN ''
			 ELSE CONVERT(VARCHAR,RECOMMENDDATE,106)
		END RECOMMENDDATE  
		FROM STUDENT_COLLEGE_ROADMAP_COLLEGES SC
		WHERE ISDELETED=0 AND ROADMAPID=$roadmapid AND REGID=$regid
		ORDER BY IN_STATE DESC,COLTYPE DESC,UNIVERSITY ASC";

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPCLID'] = (int) $row['ROADMAPCLID'];
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
/* ========== GET ROADMAP COLLEGES =========== */



/* =========== Delete =========== */ 
function deleteCollege($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ROADMAPCLID = ($_POST['ROADMAPCLID'] == 'undefined' || $_POST['ROADMAPCLID'] == '') ? 0 : $_POST['ROADMAPCLID'];  
			if($ROADMAPCLID == 0)throw new Exception('ROADMAPCLID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_ROADMAP_COLLEGES_SP] 3,$ROADMAPCLID,0,0,0,0,'','','',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% COLLEGES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% RECOMMENDATION START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataRec($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $ForSecID = ($_POST['ForSecID'] == 'undefined' || $_POST['ForSecID'] == '') ? 0 : $_POST['ForSecID'];
	   $roadmapid = ($_POST['roadmapid'] == 'undefined' || $_POST['roadmapid'] == '') ? 0 : $_POST['roadmapid'];
	   $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $ForSec = $_POST['ForSec'] == 'undefined' ? '' : str_replace("'","''",$_POST['ForSec']);
	   $txtRecDT_Rec = $_POST['txtRecDT_Rec'] == 'undefined' ? '' : $_POST['txtRecDT_Rec'];
	   $txtRecommendation_Rec = $_POST['txtRecommendation_Rec'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRecommendation_Rec']);


	   if($ForSecID == 0) throw new Exception("ID Error.");
	   if($roadmapid == 0) throw new Exception("ROADMAPID Error.");
	   if($regid == 0) throw new Exception("REGID Error.");
	   if($ForSec == '') throw new Exception("Error0.");
	   if($txtRecDT_Rec == '') throw new Exception("Select Date.");
	   if($txtRecommendation_Rec == '') throw new Exception("Enter Your Recommendation.");


	//    Classes
	//    Activity
	//    Tests
	//    MajorMinor
	//    College	   

		if($ForSec === 'Classes'){
			$updRec = "UPDATE STUDENT_COLLEGE_ROADMAP_CLASSES SET RECOMMENDATION='$txtRecommendation_Rec',RECOMMENDDATE='$txtRecDT_Rec' 
						WHERE ROADMAPCID=$ForSecID";
				$stmt=sqlsrv_query($mysqli, $updRec);
		}
	   	else if($ForSec === 'Activity'){
			$updRec = "UPDATE STUDENT_COLLEGE_ROADMAP_ACTIVITIES SET RECOMMENDATION='$txtRecommendation_Rec',RECOMMENDDATE='$txtRecDT_Rec' 
						WHERE ROADMAPACTID=$ForSecID";
			$stmt=sqlsrv_query($mysqli, $updRec);
		}
	   	else if($ForSec === 'Tests'){
			$updRec = "UPDATE STUDENT_COLLEGE_ROADMAP_TESTS SET RECOMMENDATION='$txtRecommendation_Rec',RECOMMENDDATE='$txtRecDT_Rec' 
						WHERE ROADMAPTESTID=$ForSecID";
			$stmt=sqlsrv_query($mysqli, $updRec);
		}
	   	else if($ForSec === 'MajorMinor'){
			$updRec = "UPDATE STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS SET RECOMMENDATION='$txtRecommendation_Rec',RECOMMENDDATE='$txtRecDT_Rec' 
						WHERE ROADMAPMAJORID=$ForSecID";
			$stmt=sqlsrv_query($mysqli, $updRec);
		}
	   	else if($ForSec === 'College'){
			$updRec = "UPDATE STUDENT_COLLEGE_ROADMAP_COLLEGES SET RECOMMENDATION='$txtRecommendation_Rec',RECOMMENDDATE='$txtRecDT_Rec' 
						WHERE ROADMAPCLID=$ForSecID";
			$stmt=sqlsrv_query($mysqli, $updRec);
		}


		   
		if($stmt === false)
		{
			$data['success'] = false;
			$data['query'] = $updRec;
			echo json_encode($data);exit;
		}
		else
		{
			$data['query'] = $updRec;
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
/* ============ SAVE DATA ============= */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% RECOMMENDATION END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












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



/*============ GET ACTIVITY LEGEND =============*/ 
function getActivityLegend($mysqli){
	try
	{
		$data = array();
		$query = "SELECT LEGENDID AS id,LEGEND AS label FROM ACTIVITY_LEGEND_MASTER WHERE ISDELETED=0 ORDER BY label";
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
/*============ GET ACTIVITY LEGEND =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







