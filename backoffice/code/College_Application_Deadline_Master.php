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
        case "CollegeAppDeadlines":CollegeAppDeadlines($conn);break;
		case "getSelectedAppsAllowed":getSelectedAppsAllowed($conn);break;
		case "getSelectedRecommend":getSelectedRecommend($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "CollegeAppDeadlines_DET":CollegeAppDeadlines_DET($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE
        case "getAppNames":getAppNames($conn);break;
		case "getRecommendations":getRecommendations($conn);break;

		
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

		$applicationid = ($_POST['applicationid'] == 'undefined' || $_POST['applicationid'] == '') ? 0 : $_POST['applicationid'];
		$ddlAdmYear = ($_POST['ddlAdmYear'] == 'undefined' || $_POST['ddlAdmYear'] == '') ? 0 : $_POST['ddlAdmYear'];
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];
		$ddlCollegeMajor = ($_POST['ddlCollegeMajor'] == 'undefined' || $_POST['ddlCollegeMajor'] == '') ? 0 : $_POST['ddlCollegeMajor'];
		$ddlTS_SendMethode = ($_POST['ddlTS_SendMethode'] == 'undefined' || $_POST['ddlTS_SendMethode'] == '') ? 0 : $_POST['ddlTS_SendMethode'];
		$ddlTS_WhenSend =  $_POST['ddlTS_WhenSend'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlTS_WhenSend']);
		$txtTS_SendOther = $_POST['txtTS_SendOther'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTS_SendOther']);
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);
		$APPS_model = (!empty($_POST['APPS_model'])) ? explode(',',$_POST['APPS_model']) : [];
		$RECOMMEND_model = (!empty($_POST['RECOMMEND_model'])) ? explode(',',$_POST['RECOMMEND_model']) : [];
		
		// $data['APPS_model'] = $APPS_model;
		// $data['RECOMMEND_model'] = $RECOMMEND_model;
		// $data['success'] = false;
		// echo json_encode($data);exit;

		$actionid = $applicationid == 0 ? 1 : 2;

		if($ddlAdmYear == 0) throw new Exception("Select Admission Year.");


		// $sql = "SELECT * FROM SCHOLARSHIP_MASTER WHERE SCHOLARSHIP='$txtScholarship' AND SCHMID!=$schmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$query="EXEC [COLLEGE_APPLICATION_DEADLINES_MASTER_SP] $actionid,$applicationid,$ddlAdmYear,$ddlUniversity,$ddlCollege,
			$ddlCollegeMajor,$ddlTS_SendMethode,'$ddlTS_WhenSend','$txtTS_SendOther','$txtComments',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET APPLICATIONID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_APPLICATIONID'] = $row['APPLICATIONID'];
				$GET_APPLICATIONID = $row['APPLICATIONID'];


				// #### DELETE APPS ALLOWED
				if($actionid == 2){
					$queryDelApp = "DELETE FROM COLLEGE_APPLICATION_APPS WHERE APPLICATIONID=$applicationid";
					sqlsrv_query($mysqli, $queryDelApp);
				}
				// #### INSERT APPS ALLOWED
				if(count($APPS_model) > 0){
					for($i=0;$i<count($APPS_model);$i++){
						$APPID = $APPS_model[$i];

						$queryApp = "INSERT INTO COLLEGE_APPLICATION_APPS (APPLICATIONID,APPID,INSERTID)
									VALUES($GET_APPLICATIONID,$APPID,$userid)";
						sqlsrv_query($mysqli, $queryApp);									
									
					}
				}


				// #### DELETE RECOMMENDATIONS
				if($actionid == 2){
					$queryDelRec = "DELETE FROM COLLEGE_APPLICATION_RECOMMENDATIONS WHERE APPLICATIONID=$applicationid";
					sqlsrv_query($mysqli, $queryDelRec);
				}
				// #### INSERT RECOMMENDATIONS
				// $data['$RECOMMEND_model']=$RECOMMEND_model;
				// echo json_encode($data);exit;

				if(count($RECOMMEND_model) > 0){
					for($i=0;$i<count($RECOMMEND_model);$i++){
						$RECOMMENDID = $RECOMMEND_model[$i];

						$queryRec = "INSERT INTO COLLEGE_APPLICATION_RECOMMENDATIONS (APPLICATIONID,RECOMMENDID,INSERTID)
									VALUES($GET_APPLICATIONID,$RECOMMENDID,$userid)";
						sqlsrv_query($mysqli, $queryRec);									
									
					}
				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($applicationid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET COLLEGE APPLICATION DEADLINES =========== */
 function CollegeAppDeadlines($mysqli){
	try
	{
		$data = array();
		$query = "SELECT APPLICATIONID,ADMYEARID,
		(SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=CAD.ADMYEARID)ADMYEAR,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=CAD.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=CAD.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=CAD.MAJORID)MAJOR,TSENDID,
		(SELECT TSENDMETHOD FROM TRANSCRIPT_SENDING_MASTER WHERE TSENDID=CAD.TSENDID)TSENDMETHOD,
		SENDWHEN,SENDWHEN_OTHER,COMMENTS,
		ISNULL((SELECT (SELECT APPNAME FROM APP_MASTER WHERE APPID=CAA.APPID) + ' | ' FROM COLLEGE_APPLICATION_APPS CAA 
			WHERE ISDELETED=0 AND APPLICATIONID=CAD.APPLICATIONID FOR XML PATH('')),'')APPSALLOWED,
		ISNULL((SELECT (SELECT RECOMMENDTYPE FROM RECOMMENDATION_TYPE_MASTER WHERE RECOMMENDID=CAR.RECOMMENDID) + ' | ' 
			FROM COLLEGE_APPLICATION_RECOMMENDATIONS CAR WHERE ISDELETED=0 AND APPLICATIONID=CAD.APPLICATIONID 
			FOR XML PATH('')),'')RECOMMENDATIONS
		FROM COLLEGE_APPLICATION_DEADLINES CAD
		WHERE ISDELETED=0
		ORDER BY ADMYEAR";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPLICATIONID'] = (int) $row['APPLICATIONID'];
				$row['APPSALLOWED'] = rtrim($row['APPSALLOWED'],' | ');
				$row['RECOMMENDATIONS'] = rtrim($row['RECOMMENDATIONS'],' | ');
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
/* ========== GET COLLEGE APPLICATION DEADLINES =========== */




/* ========== GET SELECTED APPS ALLOWED =========== */
 function getSelectedAppsAllowed($mysqli){
	try
	{
		$data = array();
		$APPLICATIONID = ($_POST['APPLICATIONID'] == 'undefined' || $_POST['APPLICATIONID'] == '') ? 0 : $_POST['APPLICATIONID'];
		if($APPLICATIONID == 0)throw new Exception('APPLICATIONID Error.');

		$query = "SELECT APPID AS id,
		(SELECT APPNAME FROM APP_MASTER WHERE APPID=CAA.APPID) label
		FROM COLLEGE_APPLICATION_APPS CAA
		WHERE ISDELETED=0 AND APPLICATIONID=$APPLICATIONID
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
/* ========== GET SELECTED APPS ALLOWED =========== */




/* ========== GET SELECTED RECOMMEND =========== */
 function getSelectedRecommend($mysqli){
	try
	{
		$data = array();
		$APPLICATIONID = ($_POST['APPLICATIONID'] == 'undefined' || $_POST['APPLICATIONID'] == '') ? 0 : $_POST['APPLICATIONID'];
		if($APPLICATIONID == 0)throw new Exception('APPLICATIONID Error.');

		$query = "SELECT RECOMMENDID AS id,
		(SELECT RECOMMENDTYPE FROM RECOMMENDATION_TYPE_MASTER WHERE RECOMMENDID=CAR.RECOMMENDID) label
		FROM COLLEGE_APPLICATION_RECOMMENDATIONS CAR
		WHERE ISDELETED=0 AND APPLICATIONID=$APPLICATIONID
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
/* ========== GET SELECTED RECOMMEND =========== */






/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPLICATIONID = ($_POST['APPLICATIONID'] == 'undefined' || $_POST['APPLICATIONID'] == '') ? 0 : $_POST['APPLICATIONID'];  
			if($APPLICATIONID == 0)throw new Exception('APPLICATIONID Not Found.');
			$delQuery = "EXEC [COLLEGE_APPLICATION_DEADLINES_MASTER_SP] 3,$APPLICATIONID,0,0,0,0,0,'','','',$userid";
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

	   $applicationdetid = ($_POST['applicationdetid'] == 'undefined' || $_POST['applicationdetid'] == '') ? 0 : $_POST['applicationdetid'];
	   $applicationid = ($_POST['applicationid'] == 'undefined' || $_POST['applicationid'] == '') ? 0 : $_POST['applicationid'];
	   $ddlDeadlineType = ($_POST['ddlDeadlineType'] == 'undefined' || $_POST['ddlDeadlineType'] == '') ? 0 : $_POST['ddlDeadlineType'];
	   $txtDeadlineDT = $_POST['txtDeadlineDT'] == 'undefined' ? '' : $_POST['txtDeadlineDT'];
	   $txtOpenFromDT = $_POST['txtOpenFromDT'] == 'undefined' ? '' : $_POST['txtOpenFromDT'];
	   $txtCloseFromDT = $_POST['txtCloseFromDT'] == 'undefined' ? '' : $_POST['txtCloseFromDT'];
	   $txtResultETADT = $_POST['txtResultETADT'] == 'undefined' ? '' : $_POST['txtResultETADT'];
	   $txtCommentsDetails = $_POST['txtCommentsDetails'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCommentsDetails']);

	   $actionid = $applicationdetid == 0 ? 1 : 2;

	   if($applicationid == 0) throw new Exception("ApplicationID Error.");
	   if($ddlDeadlineType == 0) throw new Exception("Select Deadline Type.");


	   $sql = "SELECT * FROM COLLEGE_APPLICATION_DEADLINE_DETAILS WHERE APPLICATIONID=$applicationid AND DEADLINETYPEID=$ddlDeadlineType AND 
			CONVERT(DATE,DEADLINE,105)='$txtDeadlineDT' AND APPLCATIONDETID!=$applicationdetid AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {
		   $query="EXEC [COLLEGE_APPLICATION_DEADLINE_DETAILS_SP] $actionid,$applicationdetid,$applicationid,$ddlDeadlineType,'$txtDeadlineDT',
		   '$txtOpenFromDT','$txtCloseFromDT','$txtResultETADT','$txtCommentsDetails',$userid";
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
			   if(!empty($applicationdetid))$data['message'] = 'Record successfully updated.';
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





/* ========== GET COLLEGE APPLICATION DEADLINES DETAILS =========== */
function CollegeAppDeadlines_DET($mysqli){
	try
	{
		$data = array();
		$applicationid = ($_POST['applicationid'] == 'undefined' || $_POST['applicationid'] == '') ? 0 : $_POST['applicationid'];
		if($applicationid == 0)throw new Exception("ApplicationID Error.");

		$query = "SELECT APPLCATIONDETID,APPLICATIONID,DEADLINETYPEID,
		(SELECT DEADLINETYPE FROM DEADLINE_TYPES WHERE DEADLINETYPEID=CADD.DEADLINETYPEID)DEADLINETYPE,
		CASE WHEN (DEADLINE = '1900-01-01 00:00:00.000' OR DEADLINE = null)
			THEN ''
			ELSE CONVERT(VARCHAR,DEADLINE,106)
		END DEADLINE,
		CASE WHEN (OPENDATE = '1900-01-01 00:00:00.000' OR OPENDATE = null)
			THEN ''
			ELSE CONVERT(VARCHAR,OPENDATE,106)
		END OPENDATE,
		CASE WHEN (ENDDATE = '1900-01-01 00:00:00.000' OR ENDDATE = null)
			THEN ''
			ELSE CONVERT(VARCHAR,ENDDATE,106)
		END ENDDATE,
		CASE WHEN (RESULT_ETA = '1900-01-01 00:00:00.000' OR RESULT_ETA = null)
			THEN ''
			ELSE CONVERT(VARCHAR,RESULT_ETA,106)
		END RESULT_ETA,COMMENTS 
		FROM COLLEGE_APPLICATION_DEADLINE_DETAILS CADD
		WHERE ISDELETED=0 AND APPLICATIONID=$applicationid";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['APPLCATIONDETID'] = (int) $row['APPLCATIONDETID'];
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
/* ========== GET COLLEGE APPLICATION DEADLINES DETAILS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $APPLCATIONDETID = ($_POST['APPLCATIONDETID'] == 'undefined' || $_POST['APPLCATIONDETID'] == '') ? 0 : $_POST['APPLCATIONDETID'];  
			if($APPLCATIONDETID == 0)throw new Exception('APPLCATIONDETID Not Found.');
			$delQuery = "EXEC [COLLEGE_APPLICATION_DEADLINE_DETAILS_SP] 3,$APPLCATIONDETID,0,0,'','','','','',$userid";
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

/* ========== GET APPS NAMES =========== */
function getAppNames($mysqli){
	try
	{
		$data = array();
		$query = "SELECT APPID AS id,APPNAME AS label
		FROM APP_MASTER WHERE ISDELETED=0 ORDER BY label";
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
/* ========== GET APPS NAMES =========== */





/* ========== GET RECOMMENDATIONS =========== */
function getRecommendations($mysqli){
	try
	{
		$data = array();
		$query = "SELECT RECOMMENDID AS id,RECOMMENDTYPE AS label
		FROM RECOMMENDATION_TYPE_MASTER WHERE ISDELETED=0 ORDER BY RECOMMENDTYPE";
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
/* ========== GET RECOMMENDATIONS =========== */

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







