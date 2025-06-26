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
        case "saveData":saveData($conn);break;
        case "getTrainingTransactions":getTrainingTransactions($conn);break;
        case "getTeacher_Students":getTeacher_Students($conn);break;
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



// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;


		$ttid = ($_POST['ttid'] == 'undefined' || $_POST['ttid'] == '') ? 0 : $_POST['ttid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlTransaction = $_POST['ddlTransaction'] == 'undefined' ? '' : $_POST['ddlTransaction'];
		$ddlTraining = ($_POST['ddlTraining'] == 'undefined' || $_POST['ddlTraining'] == '') ? 0 : $_POST['ddlTraining'];
		$txtCompletedDT = $_POST['txtCompletedDT'] == 'undefined' ? '' : $_POST['txtCompletedDT'];
		$ddlTeacher_Student = $_POST['ddlTeacher_Student'] == 'undefined' ? '' : $_POST['ddlTeacher_Student'];
		$ddlUser = ($_POST['ddlUser'] == 'undefined' || $_POST['ddlUser'] == '') ? 0 : $_POST['ddlUser'];
		$txtTRemark = $_POST['txtTRemark'] == 'undefined' ? '' : $_POST['txtTRemark'];
		$txtSRemark = $_POST['txtSRemark'] == 'undefined' ? '' : $_POST['txtSRemark'];
		
		$actionid = $ttid == 0 ? 1 : 2;

		if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
		if($ddlTraining == 0){throw new Exception("Please Select 'Training Name'.");}
		// if($txtCompletedDT == ''){throw new Exception("Please Select 'Complete Date'.");}

		$sql = "SELECT * FROM TRAINING_TRANSACTION WHERE LOCID=$ddlLocation AND [TRANSACTION]='$ddlTransaction' AND TMID=$ddlTraining AND 
		CONVERT(DATE,COMPLETED_DATE,105)='$txtCompletedDT' AND TTID!=$ttid AND ISDELETED=0";

		$data['$sql'] = $sql;
		// echo json_encode($data);
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TRAINING_TRANSACTION_SP] $actionid,$ttid,$ddlLocation,'$ddlTransaction',$ddlTraining,'$txtCompletedDT',
			'$ddlTeacher_Student',$ddlUser,'$txtTRemark','$txtSRemark',$userid";

			// $data['$query'] = $query;
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
				if(!empty($ttid))$data['message'] = 'Record successfully updated.';
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
 // =============== SAVE DATA ==============





/* ========== GET TRAINING TRANSACTIONS =========== */
 function getTrainingTransactions($mysqli){
	try
	{
		global $userid;
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];
		$ddlSearchSubCategory = ($_POST['ddlSearchSubCategory'] == 'undefined' || $_POST['ddlSearchSubCategory'] == '') ? 0 : $_POST['ddlSearchSubCategory'];
		$ddlSearchSSubCategory = ($_POST['ddlSearchSSubCategory'] == 'undefined' || $_POST['ddlSearchSSubCategory'] == '') ? 0 : $_POST['ddlSearchSSubCategory'];
		$UID = ($_POST['USERID'] == 'undefined' || $_POST['USERID'] == '') ? 0 : $_POST['USERID'];
		$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		if($FOR == '')throw new Exception('Error.');

		$query = "SELECT TTID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TT.LOCID)[LOCATION],
		[TRANSACTION],TMID,
		ISNULL((SELECT T_DESC FROM TRAINING_MASTER WHERE TMID=TT.TMID),'')TRAINING,
		ISNULL((SELECT T_LINK FROM TRAINING_MASTER WHERE TMID=TT.TMID),'')LINK,
		CASE WHEN (COMPLETED_DATE='1900-01-01 00:00:00.000' OR COMPLETED_DATE=NULL)
			THEN '-'
			ELSE CONVERT(VARCHAR,COMPLETED_DATE,106)
		END	COMPLETED_DATE,USER_TYPE,USERID,
		CASE WHEN USER_TYPE='Teacher' THEN (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=TT.USERID AND USERROLE='TEACHER')
			WHEN USER_TYPE='Admin' THEN (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=TT.USERID AND USERROLE='ADMINISTRATOR')
			WHEN USER_TYPE='Student' THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=TT.USERID)
			ELSE '-'
		END USERNAME,TEACHER_REMARK,SUPERVISIOR_REMARK 
		FROM TRAINING_TRANSACTION TT
		WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($FOR === 'TEACHER') $query .=" AND USER_TYPE='Teacher' AND USERID=$UID";
		if($FOR === 'STUDENT') $query .=" AND USER_TYPE='Student' AND USERID=$UID";

		if($ddlSearchCategory>0) $query .=" AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM TRAINING_MASTER WHERE ISDELETED=0 AND TMID=TT.TMID))) = $ddlSearchCategory";
		if($ddlSearchSubCategory>0) $query .=" AND (SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM TRAINING_MASTER WHERE ISDELETED=0 AND TMID=TT.TMID)) = $ddlSearchSubCategory";
		if($ddlSearchSSubCategory>0) $query .=" AND (SELECT TDSSUBCATID FROM TRAINING_MASTER WHERE ISDELETED=0 AND TMID=TT.TMID) = $ddlSearchSSubCategory";
		
		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";

		$data['$query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
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
/* ========== GET TRAINING TRANSACTIONS =========== */







/* ========== GET TEACHER/STUDENTS =========== */
function getTeacher_Students($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlTeacher_Student = $_POST['ddlTeacher_Student'] == 'undefined' ? '' : $_POST['ddlTeacher_Student'];


		if($ddlTeacher_Student === 'Admin'){
			$query = "SELECT [UID] AS ID,FIRSTNAME+' '+LASTNAME+' == ('+USERROLE+')' AS FULLNAME 
			FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE IN ('ADMINISTRATOR')
			ORDER BY FIRSTNAME";
		}
		else if($ddlTeacher_Student === 'Teacher'){
			$query = "SELECT [UID] AS ID,FIRSTNAME+' '+LASTNAME+' == ('+USERROLE+')' AS FULLNAME 
			FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER')
			ORDER BY FIRSTNAME";
		}
		else if($ddlTeacher_Student === 'Student'){

			$query = "SELECT REGID AS ID,FIRSTNAME+' '+LASTNAME AS FULLNAME 
			FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation AND APPROVED=1
			ORDER BY FIRSTNAME";
		}
		else{
			throw new Exception('Error.');
		}

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
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
/* ========== GET TEACHER/STUDENTS =========== */






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TTID = ($_POST['TTID'] == 'undefined' || $_POST['TTID'] == '') ? 0 : $_POST['TTID'];
			if($TTID == 0){throw new Exception('TTID Error.');}
			$delQuery = "EXEC [TRAINING_TRANSACTION_SP] 3,$TTID,0,'',0,'','',0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Data successfully deleted.';
			}
			// $data['$delQuery']=$delQuery;
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







