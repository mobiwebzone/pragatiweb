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
        case "save":save($conn);break;
        case "getStudentBatchesData":getStudentBatchesData($conn);break;
        case "delete":delete($conn);break;
		
		// ADD STUDENTS
        case "getStudents":getStudents($conn);break;
        case "saveDataDET":saveDataDET($conn);break;
        case "getBatchStudentsData":getBatchStudentsData($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		// ADD TEACHERS
        case "getTeachers":getTeachers($conn);break;
        case "saveDataDET_TH":saveDataDET_TH($conn);break;
        case "getBatchTeachersData":getBatchTeachersData($conn);break;
        case "deleteDET_TH":deleteDET_TH($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
        $batchid  = ($_POST['batchid'] == 'undefined' || $_POST['batchid'] == '') ? 0 : $_POST['batchid'];
        // $studentIdList  = (empty($_POST['studentIdList']) ||  $_POST['studentIdList'] == 'undefined' || $_POST['studentIdList'] == '') ? array() : explode(',',$_POST['studentIdList']);
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtBatchName  = ($_POST['txtBatchName'] == 'undefined' || $_POST['txtBatchName'] == '') ? '' : $_POST['txtBatchName'];
        $txtBatchDesc  = ($_POST['txtBatchDesc'] == 'undefined' || $_POST['txtBatchDesc'] == '') ? '' : $_POST['txtBatchDesc'];

		// $data['ddlSubject']=$ddlSubject;
		// echo json_encode($data);exit;
		
		$actionid = $batchid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location.");
		if($txtBatchName == '')throw new Exception("Please Enter Batch Name.");
		
		$sql = "SELECT * FROM LA_STUDENT_BATCHES WHERE LOCID=$ddlLocation AND BATCHNAME='$txtBatchName' AND BATCHID!=$batchid AND ISDELETED=0";
		$row_count = unique($sql);
		
		if($row_count == 0)
		{	
			$query="EXEC [LA_STUDENT_BATCHES_SP] $actionid,$batchid,$ddlLocation,'$txtBatchName','$txtBatchDesc',$userid";
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
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['GET_BATCHID']=$row['BATCHID'];

				$data['query'] = $query;
				$data['success'] = true;
			}	

			if(!empty($batchid))$data['message'] = 'Record successfully updated';
			else $data['message'] = 'Record successfully inserted.';
			echo json_encode($data);exit;
					
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Subject already exists.';
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


/*============ GET STUDENT BATCHES DATA =============*/ 
 function getStudentBatchesData($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT BATCHID,LOCID,BATCHNAME,BATCHDESC,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=SB.LOCID)[LOCATION],
		(SELECT (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=BS.REGID)+', ' FROM LA_BATCH_STUDENTS BS 
			WHERE ISDELETED=0 AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1) AND BATCHID=SB.BATCHID FOR XML PATH(''))STUDENTS,
		(SELECT (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=BT.TEACHERID)+', ' FROM LA_BATCH_TEACHERS BT 
			WHERE ISDELETED=0 AND TEACHERID NOT IN (SELECT [UID] FROM USERS WHERE ARCHIVED=1) AND BATCHID=SB.BATCHID FOR XML PATH(''))TEACHERS			
		FROM LA_STUDENT_BATCHES SB
		WHERE ISDELETED=0 AND LOCID=$ddlLocation
		ORDER BY [LOCATION]";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['BATCHID'] = (int) $row['BATCHID'];
				$row['STUDENTS'] = rtrim($row['STUDENTS'],', ');
				$row['TEACHERS'] = rtrim($row['TEACHERS'],', ');
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
/*============ GET STUDENT BATCHES DATA =============*/ 



/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BATCHID = ($_POST['BATCHID'] == 'undefined' || $_POST['BATCHID'] == '') ? 0 : $_POST['BATCHID'];  
			if($BATCHID==0) throw new Exception('Error : BATCHID Not Found.');
			$query = "EXEC [LA_STUDENT_BATCHES_SP] 3,$BATCHID,0,'','',$userid";
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
/* =========== DELETE =========== */ 


// ####################################################################################
//                                      ADD STUDENT START
// ####################################################################################
/*============ Get Student =============*/ 
function getStudents($mysqli){
	try
	{
		$data = array();
        // $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        // $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $batchid  = ($_POST['batchid'] == 'undefined' || $_POST['batchid'] == '') ? 0 : $_POST['batchid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT REGID,FIRSTNAME+' '+LASTNAME STUDENT,
		P1_FIRSTNAME+' '+P1_LASTNAME PARENT,
		CASE WHEN (SELECT COUNT(*) FROM LA_BATCH_STUDENTS WHERE ISDELETED=0 AND BATCHID=$batchid AND REGID=SR.REGID)>0
			THEN REGID ELSE 0 
		END EXIST
		FROM REGISTRATIONS SR WHERE ARCHIVED=0 AND ISDELETED=0 AND LOCATIONID=$ddlLocation
		AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND PLANID=$ddlPlan)
		ORDER BY STUDENT";

		// if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
		// $query .=" ORDER BY STUDENT";

		$data['$query ']=$query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['EXIST']=(string)$row['EXIST'];
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

function saveDataDET($mysqli){
	try
	{
		$data = array();
		global $userid;
		
		$bsid  = ($_POST['bsid'] == 'undefined' || $_POST['bsid'] == '') ? 0 : $_POST['bsid'];
		$batchid  = ($_POST['batchid'] == 'undefined' || $_POST['batchid'] == '') ? 0 : $_POST['batchid'];
		$studentIdList  = (empty($_POST['studentIdList']) ||  $_POST['studentIdList'] == 'undefined' || $_POST['studentIdList'] == '') ? array() : explode(',',$_POST['studentIdList']);

		// $data['ddlSubject']=$ddlSubject;
		// echo json_encode($data);exit;
	   
	   	$actionid = $bsid == 0 ? 1 : 2;
	   
	   
		if($batchid == 0)throw new Exception("Error : BATCHID Not Found.");
		if(count($studentIdList) == 0)throw new Exception("Please Select Student.");

		if(count($studentIdList)>0){
			foreach($studentIdList as $val){
				$REGID = $val;
				if($REGID > 0){
					$sql = "SELECT * FROM LA_BATCH_STUDENTS WHERE BATCHID=$batchid AND REGID=$REGID AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count==0){
						$query="EXEC [LA_BATCH_STUDENTS_SP] $actionid,$bsid,$batchid,$REGID,$userid";
						$data['$query'] = $query;
						// echo json_encode($data);exit;
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
						}
					}
				}
			}
		}
	   
		if(!empty($batchid))$data['message'] = 'Record successfully updated';
		else $data['message'] = 'Record successfully inserted.';
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

/*============ GET BATCH STUDENTS DATA =============*/ 
function getBatchStudentsData($mysqli){
	try
	{
		$data = array();
		$batchid = ($_POST['batchid'] =='undefined' || $_POST['batchid'] =='') ? 0 : $_POST['batchid'];

		$query = "SELECT BSID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=BS.REGID)STUDENT, 
		(SELECT P1_FIRSTNAME+' '+P1_LASTNAME PARENT FROM REGISTRATIONS WHERE REGID=BS.REGID)PARENT
		FROM LA_BATCH_STUDENTS BS WHERE ISDELETED=0 
		AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1)
		AND BATCHID=$batchid ORDER BY STUDENT";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['BSID'] = (int) $row['BSID'];
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
/*============ GET BATCH STUDENTS  DATA =============*/ 


/* =========== DELETE =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BSID = ($_POST['BSID'] == 'undefined' || $_POST['BSID'] == '') ? 0 : $_POST['BSID'];  
			if($BSID==0) throw new Exception('Error : BSID Not Found.');
			$query = "EXEC [LA_BATCH_STUDENTS_SP] 3,$BSID,0,0,$userid";
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
/* =========== DELETE =========== */ 
// ####################################################################################
//                                      ADD STUDENT END
// ####################################################################################




// ####################################################################################
//                                      ADD TEACHER START
// ####################################################################################
/*============ Get Teachers =============*/ 
function getTeachers($mysqli){
	try
	{
		$data = array();
        // $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        // $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $batchid  = ($_POST['batchid'] == 'undefined' || $_POST['batchid'] == '') ? 0 : $_POST['batchid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT [UID],FIRSTNAME+' '+LASTNAME TEACHER,USERROLE,
		CASE WHEN (SELECT COUNT(*) FROM LA_BATCH_TEACHERS WHERE ISDELETED=0 AND BATCHID=$batchid AND TEACHERID=TR.UID)>0
			THEN [UID] ELSE 0 
		END EXIST
		FROM USERS TR WHERE ARCHIVED=0 AND ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER')
		--AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND PLANID=$ddlPlan)
		ORDER BY USERROLE,TEACHER";

		// if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
		// $query .=" ORDER BY STUDENT";

		$data['$query ']=$query;
		// echo json_encode($data);exit;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['EXIST']=(string)$row['EXIST'];
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
/*============ Get Teachers =============*/

function saveDataDET_TH($mysqli){
	try
	{
		$data = array();
		global $userid;
		
		$btid  = ($_POST['btid'] == 'undefined' || $_POST['btid'] == '') ? 0 : $_POST['btid'];
		$batchid  = ($_POST['batchid'] == 'undefined' || $_POST['batchid'] == '') ? 0 : $_POST['batchid'];
		$teacherIdList  = (empty($_POST['teacherIdList']) ||  $_POST['teacherIdList'] == 'undefined' || $_POST['teacherIdList'] == '') ? array() : explode(',',$_POST['teacherIdList']);

		// $data['ddlSubject']=$ddlSubject;
		// echo json_encode($data);exit;
	   
	   	$actionid = $btid == 0 ? 1 : 2;
	   
	   
		if($batchid == 0)throw new Exception("Error : BATCHID Not Found.");
		if(count($teacherIdList) == 0)throw new Exception("Please Select Teachers.");

		if(count($teacherIdList)>0){
			foreach($teacherIdList as $val){
				$TEACHERID = $val;
				if($TEACHERID > 0){
					$sql = "SELECT * FROM LA_BATCH_TEACHERS WHERE BATCHID=$batchid AND TEACHERID=$TEACHERID AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count==0){
						$query="EXEC [LA_BATCH_TEACHERS_SP] $actionid,$btid,$batchid,$TEACHERID,$userid";
						$data['$query'] = $query;
						// echo json_encode($data);exit;
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
						}
					}
				}
			}
		}
	   
		if(!empty($batchid))$data['message'] = 'Record successfully updated';
		else $data['message'] = 'Record successfully inserted.';
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

/*============ GET BATCH TEACHERS DATA =============*/ 
function getBatchTeachersData($mysqli){
	try
	{
		$data = array();
		$batchid = ($_POST['batchid'] =='undefined' || $_POST['batchid'] =='') ? 0 : $_POST['batchid'];

		$query = "SELECT BTID,TEACHERID,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=BT.TEACHERID)TEACHER, 
		(SELECT USERROLE FROM USERS WHERE [UID]=BT.TEACHERID)USERROLE
		FROM LA_BATCH_TEACHERS BT WHERE ISDELETED=0 
		AND TEACHERID NOT IN (SELECT [UID] FROM USERS WHERE ARCHIVED=1)
		AND BATCHID=$batchid ORDER BY TEACHER";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['BTID'] = (int) $row['BTID'];
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
/*============ GET BATCH TEACHERS  DATA =============*/ 


/* =========== DELETE =========== */ 
function deleteDET_TH($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BTID = ($_POST['BTID'] == 'undefined' || $_POST['BTID'] == '') ? 0 : $_POST['BTID'];  
			if($BTID==0) throw new Exception('Error : BTID Not Found.');
			$query = "EXEC [LA_BATCH_TEACHERS_SP] 3,$BTID,0,0,$userid";
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
// ####################################################################################
//                                      ADD TEACHER END
// ####################################################################################




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







