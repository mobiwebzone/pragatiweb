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
        case "getST_CLG_PROC_STEP_DET":getST_CLG_PROC_STEP_DET($conn);break;
        case "deleteData":deleteData($conn);break;
		

		//######## EXTRA DATA TABLE
		case "getUniversityByREGID":getUniversityByREGID($conn);break;
        case "getCollegeByUniversityID":getCollegeByUniversityID($conn);break;
		
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

		$scpsdid = ($_POST['scpsdid'] == 'undefined' || $_POST['scpsdid'] == '') ? 0 : $_POST['scpsdid'];
		$scpsid = ($_POST['scpsid'] == 'undefined' || $_POST['scpsid'] == '') ? 0 : $_POST['scpsid'];
		$stepid = ($_POST['stepid'] == 'undefined' || $_POST['stepid'] == '') ? 0 : $_POST['stepid'];
		$EDIT_ADD = $_POST['EDIT_ADD'] == 'false' ? false : true;
		$ddlCurrentOwner = $_POST['ddlCurrentOwner'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlCurrentOwner']);
		$txtComment = $_POST['txtComment'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComment']);
		$txtGDriveLink = $_POST['txtGDriveLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGDriveLink']);
		$ddlStatus = $_POST['ddlStatus'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlStatus']);
		$txtStudentETA_DT = $_POST['txtStudentETA_DT'] == 'undefined' ? '' : $_POST['txtStudentETA_DT'];
		$txtStudentCDT_DT = $_POST['txtStudentCDT_DT'] == 'undefined' ? '' : $_POST['txtStudentCDT_DT'];
		$txtMEP_ETA_DT = $_POST['txtMEP_ETA_DT'] == 'undefined' ? '' : $_POST['txtMEP_ETA_DT'];
		$txtMEP_CDT_DT = $_POST['txtMEP_CDT_DT'] == 'undefined' ? '' : $_POST['txtMEP_CDT_DT'];
		$txtRemarks = $_POST['txtRemarks'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks']);

		$actionid = !$EDIT_ADD ? 1 : 2;

		// $data['success'] = false;
		// $data['scpsdid'] = $actionid;
		// echo json_encode($data);exit;

		if($ddlCurrentOwner == '') throw new Exception("Select Current Owner.");
		if($ddlStatus == '') throw new Exception("Select Status.");
		if($txtStudentETA_DT == '') throw new Exception("Select Student ETA Date.");
		if($txtMEP_ETA_DT == '') throw new Exception("Select MEP ETA Date.");

		// $sql = "SELECT * FROM STUDENT_COLLEGE_PROCESS WHERE REGID=$ddlStudent AND UNIVERSITYID=$ddlUniversity AND 
		// CLID=$ddlCollege AND SCPID!=$scpid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			if($actionid == 1)	{
				$query="EXEC [STUDENT_COLLEGE_PROCESS_STEPS_DETAILS_SP] $actionid,0,$scpsid,$stepid,'$ddlCurrentOwner',
				'$txtComment','$txtGDriveLink','$ddlStatus','$txtStudentETA_DT','$txtStudentCDT_DT','$txtMEP_ETA_DT','$txtMEP_CDT_DT',
				'$txtRemarks',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt){
					// UPDATE REVIOUS STATUS
					$updquery = "UPDATE STUDENT_COLLEGE_PROCESS_STEPS_DETAILS SET STEP_STATUS='Close',MEP_CDT=GETDATE() WHERE SCPSDID=$scpsdid";
					sqlsrv_query($mysqli, $updquery);

				}
			}
			else if($actionid == 2){
				$query="EXEC [STUDENT_COLLEGE_PROCESS_STEPS_DETAILS_SP] $actionid,$scpsdid,$scpsid,$stepid,'$ddlCurrentOwner',
				'$txtComment','$txtGDriveLink','$ddlStatus','$txtStudentETA_DT','$txtStudentCDT_DT','$txtMEP_ETA_DT','$txtMEP_CDT_DT',
				'$txtRemarks',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
			}
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{

				

				// // #### GET SCPID
				// $row = sqlsrv_fetch_array($stmt);
				// $data['GET_SCPID'] = $row['SCPID'];
				// $GET_SCPID = $row['SCPID'];

				$data['query'] = $query;
				$data['success'] = true;
				if($actionid == 2)$data['message'] = 'Record successfully updated.';
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




/* ========== GET Student College Process Step Details =========== */
 function getST_CLG_PROC_STEP_DET($mysqli){
	try
	{
		$data = array();

		$SCPID = ($_POST['SCPID'] == 'undefined' || $_POST['SCPID'] == '') ? 0 : $_POST['SCPID'];

		$query = "SELECT SCPSDID,SCPSID,STEPID,
		(SELECT CONVERT(VARCHAR,STEPNO) +' - '+ STEPDESC FROM STEPS_MASTER WHERE STEPID=SCPSD.STEPID)STEPDESC,
		(SELECT STEPNO FROM STEPS_MASTER WHERE STEPID=SCPSD.STEPID)SEQNO,
		CURRENT_OWNER,COMMENTS,GDRIVE_LINK,STEP_STATUS,
		CASE WHEN (STUDENT_ETA='1900-01-01 00:00:00.000' OR STUDENT_ETA = null)THEN ''ELSE CONVERT(VARCHAR,STUDENT_ETA,106)END STUDENT_ETA,
		CASE WHEN (STUDENT_CDT='1900-01-01 00:00:00.000' OR STUDENT_CDT = null)THEN ''ELSE CONVERT(VARCHAR,STUDENT_CDT,106)END STUDENT_CDT,
		CASE WHEN (MEP_ETA='1900-01-01 00:00:00.000' OR MEP_ETA = null)THEN ''ELSE CONVERT(VARCHAR,MEP_ETA,106)END MEP_ETA,
		CASE WHEN (MEP_CDT='1900-01-01 00:00:00.000' OR MEP_CDT = null)THEN ''ELSE CONVERT(VARCHAR,MEP_CDT,106)END MEP_CDT,
		REMARKS,
		(SELECT REGID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS 
			WHERE REGID=(SELECT REGID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0))) STUDENTNAME,
		(SELECT UNIVERSITYID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER 
			WHERE UNIVERSITYID=(SELECT UNIVERSITYID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0))) UNIVERSITY,
		(SELECT CLID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER 
			WHERE CLID=(SELECT CLID FROM STUDENT_COLLEGE_PROCESS 
				WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0))) COLLEGE
				-- (SELECT SEQNO FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)SEQNO
		
		FROM STUDENT_COLLEGE_PROCESS_STEPS_DETAILS SCPSD
		WHERE ISDELETED=0 AND SCPSID IN (SELECT SCPSID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPID=$SCPID) 
		ORDER BY SEQNO";
		$count = unique($query);

		// if($ddlStudent > 0){
		// 	$query .= " AND (SELECT REGID FROM STUDENT_COLLEGE_PROCESS 
		// 					WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) = $ddlStudent";
		// }
		// if($ddlUniversity > 0){
		// 	$query .= " AND (SELECT UNIVERSITYID FROM STUDENT_COLLEGE_PROCESS 
		// 					WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) = $ddlUniversity";
		// }
		// if($ddlCollege > 0){
		// 	$query .= " AND (SELECT CLID FROM STUDENT_COLLEGE_PROCESS 
		// 					WHERE SCPID=(SELECT SCPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPSID=SCPSD.SCPSID AND ISDELETED=0)) = $ddlCollege";
		// }


		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SCPSDID'] = (int) $row['SCPSDID'];
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
/* ========== GET Student College Process Step Details =========== */




/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 








/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



/* ========== GET UNIVERSITY =========== */
function getUniversityByREGID($mysqli){
	try
	{
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception("REGID Error.");

		$query = "SELECT APPLDID,APPLID,REGID,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SAD.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SAD.CLID)COLLEGE
		FROM STUDENT_APPLICATION_DETAILS SAD
		WHERE ISDELETED=0 AND REGID=$REGID 
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
/* ========== GET UNIVERSITY =========== */



/* ========== GET COLLEGE =========== */
function getCollegeByUniversityID($mysqli){
	try
	{
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception("REGID Error.");
		$UNIVERSITYID = ($_POST['UNIVERSITYID'] == 'undefined' || $_POST['UNIVERSITYID'] == '') ? 0 : $_POST['UNIVERSITYID'];
		if($UNIVERSITYID == 0)throw new Exception("UNIVERSITYID Error.");

		$query = "SELECT APPLDID,APPLID,REGID,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SAD.CLID)COLLEGE
		FROM STUDENT_APPLICATION_DETAILS SAD
		WHERE ISDELETED=0 AND REGID=$REGID AND UNIVERSITYID=$UNIVERSITYID AND CLID>0
		ORDER BY COLLEGE";
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
/* ========== GET COLLEGE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







