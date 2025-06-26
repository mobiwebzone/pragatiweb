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
        case "getStudentCollegeProcess":getStudentCollegeProcess($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getStepsAttachments":getStepsAttachments($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE
        case "getUniversityByREGID":getUniversityByREGID($conn);break;
        case "getCollegeByUniversityID":getCollegeByUniversityID($conn);break;
        case "getSelectedSteps":getSelectedSteps($conn);break;

		
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

		$scpid = ($_POST['scpid'] == 'undefined' || $_POST['scpid'] == '') ? 0 : $_POST['scpid'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];

		$actionid = $scpid == 0 ? 1 : 2;

		if($ddlStudent == 0) throw new Exception("Select Student Name.");
		if($ddlUniversity == 0) throw new Exception("Select University Name.");


		$sql = "SELECT * FROM STUDENT_COLLEGE_PROCESS WHERE REGID=$ddlStudent AND UNIVERSITYID=$ddlUniversity AND 
		CLID=$ddlCollege AND SCPID!=$scpid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [STUDENT_COLLEGE_PROCESS_SP] $actionid,$scpid,$ddlStudent,$ddlUniversity,$ddlCollege,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET SCPID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_SCPID'] = $row['SCPID'];
				$GET_SCPID = $row['SCPID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($scpid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET Student College Process =========== */
 function getStudentCollegeProcess($mysqli){
	try
	{
		$data = array();

		$FROM = $_POST['FROM'];
		$UID = $_POST['UID'];

		$query = "SELECT SCPID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCP.REGID)STUDENTNAME,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=SCP.REGID)LOCID,
		(SELECT (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SCP.REGID)[LOCATION],UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SCP.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SCP.CLID) COLLEGE,
		ISNULL((SELECT (SELECT CONVERT(VARCHAR, STEPNO) FROM STEPS_MASTER WHERE STEPID=SC.STEPID) + ' | '
			FROM STUDENT_COLLEGE_PROCESS_STEPS SC WHERE ISDELETED=0 AND SCPID=SCP.SCPID ORDER BY SEQNO
			FOR XML PATH('')),'')STEPS
		FROM STUDENT_COLLEGE_PROCESS SCP
		WHERE ISDELETED=0";

		if($FROM == 'ST' && $UID > 0){
			$query .= " AND REGID=$UID";
		}
		$query .= " ORDER BY STUDENTNAME";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SCPID'] = (int) $row['SCPID'];
				$row['STEPS'] = rtrim($row['STEPS'],' | ');
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
/* ========== GET Student College Process =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCPID = ($_POST['SCPID'] == 'undefined' || $_POST['SCPID'] == '') ? 0 : $_POST['SCPID'];  
			if($SCPID == 0)throw new Exception('SCPID Not Found.');
			$delQuery = "EXEC [STUDENT_COLLEGE_PROCESS_SP] 3,$SCPID,0,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				// die( print_r( sqlsrv_errors(), true));
				// throw new Exception( $mysqli->sqlstate );
				$data['$delQuery']=$delQuery;
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














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataDET($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $scpid = ($_POST['scpid'] == 'undefined' || $_POST['scpid'] == '') ? 0 : $_POST['scpid'];
	   $chkSelectedSteps = !empty($_POST['chkSelectedSteps']) ? explode(',',$_POST['chkSelectedSteps']) : [];
	   $txtSEQNO = !empty($_POST['txtSEQNO']) ? explode(',',$_POST['txtSEQNO']) : [];
	   $LINKS = !empty($_POST['LINKS']) ? explode(',',$_POST['LINKS']) : [];

	   if($scpid == 0) throw new Exception("SCPID Error.");
	   if(count($chkSelectedSteps)==0)throw new Exception("Select Steps.");
	//    if(count($txtSEQNO)==0)throw new Exception("Select Steps.");

	//    $data['chkSelectedSteps'] = $chkSelectedSteps;
	//    $data['chkSelectedSteps_COUNT'] = COUNT($chkSelectedSteps);
	//    $data['txtSEQNO'] = $txtSEQNO;
	//    $data['$LINKS'] = $LINKS;
	//    $data['txtSEQNO_COUNT'] = COUNT($txtSEQNO);
	//    echo json_encode($data);exit;

			//#### CHECK DATA EXIST AND DELETE
			$chkData = "DELETE FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE SCPID=$scpid AND ISDELETED=0";
			sqlsrv_query($mysqli, $chkData);


			//#### INSERT STEPS
			$s=1;
			for($i=0;$i<count($chkSelectedSteps);$i++){
				$STEP = (int)$chkSelectedSteps[$i];
				$SEQNO = (int)$txtSEQNO[$i];
				$LINK = $LINKS[$i];

				if($STEP>0){
					$query1="EXEC [STUDENT_COLLEGE_PROCESS_STEPS_SP] 1,0,$scpid,$STEP,$SEQNO,$userid";
				 			$stmt1=sqlsrv_query($mysqli, $query1);
							if($stmt1){
								$row1 = sqlsrv_fetch_array($stmt1);	 
								$SCPSID = (int)$row1['SCPSID'];

								if($SCPSID > 0){
									$query2="EXEC [STUDENT_COLLEGE_PROCESS_STEPS_DETAILS_SP] 1,0,$SCPSID,$STEP,'MEP','','$LINK','Open','','','','','',$userid";
											 sqlsrv_query($mysqli, $query2);
											$data['query2'] = $query2;
								}
							}
							
				 	$s++;
					$data['query1'] = $query1;
				}
			}

			
			$data['success'] = true;
			$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














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





/* ========== GET SELECTED STEPS =========== */
function getSelectedSteps($mysqli){
	try
	{
		$data = array();

		$SCPID = ($_POST['SCPID'] == 'undefined' || $_POST['SCPID'] == '') ? 0 : $_POST['SCPID'];
		if($SCPID == 0) throw new Exception('SCPID Error.');

		$query = "SELECT 
		CASE WHEN STEPID=(SELECT STEPID FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE STEPID=SM.STEPID AND SCPID=$SCPID AND ISDELETED=0)
		   THEN STEPID
		   ELSE 0
		END STEPID,
		ISNULL((SELECT SEQNO FROM STUDENT_COLLEGE_PROCESS_STEPS WHERE STEPID=SM.STEPID AND SCPID=6 AND ISDELETED=0),0)SEQNO
		FROM STEPS_MASTER SM
		WHERE ISDELETED=0
		ORDER BY STEPNO";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['STEPID'] = (string) $row['STEPID'];
				$data['data'][] = $row['STEPID'];
				$data['ALL_SEQNO'][] = (int)$row['SEQNO'];
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
/* ========== GET SELECTED STEPS =========== */

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







