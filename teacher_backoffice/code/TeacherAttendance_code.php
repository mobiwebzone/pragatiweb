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
		case "login":login($conn);break;
        case "Save":Save($conn);break;
        case "getAtt":getAtt($conn);break;
        case "getTeacher":getTeacher($conn);break;
        case "getTeacherProduct":getTeacherProduct($conn);break;
        case "chkAttDT":chkAttDT($conn);break;
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



 /*========== SAVE DATA =========*/
 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;

		
		$taid = ($_POST['taid'] == 'undefined' || $_POST['taid'] == '') ? 0 : $_POST['taid'];
		$txtAttDate=$_POST['txtAttDate'] == 'undefined' ? '' : $_POST['txtAttDate'];
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$ddlProduct1 = ($_POST['ddlProduct1'] == 'undefined' || $_POST['ddlProduct1'] == '') ? 0 : $_POST['ddlProduct1'];
		$ddlProduct2 = ($_POST['ddlProduct2'] == 'undefined' || $_POST['ddlProduct2'] == '') ? 0 : $_POST['ddlProduct2'];
		$ddlProduct3 = ($_POST['ddlProduct3'] == 'undefined' || $_POST['ddlProduct3'] == '') ? 0 : $_POST['ddlProduct3'];
		$txtTimeIN=$_POST['txtTimeIN'] == 'undefined' ? '' : $_POST['txtTimeIN'];
		$txtTimeOUT=$_POST['txtTimeOUT'] == 'undefined' ? '' : $_POST['txtTimeOUT'];
		$txtRemark=$_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
		$txtLearnToday=$_POST['txtLearnToday'] == 'undefined' ? '' : $_POST['txtLearnToday'];
		$txtOtherWork=$_POST['txtOtherWork'] == 'undefined' ? '' : $_POST['txtOtherWork'];
		$txtSupervisorComm=$_POST['txtSupervisorComm'] == 'undefined' ? '' : $_POST['txtSupervisorComm'];
    
		
		$actionid = $taid == 0 ? 1 : 2;

		if($txtAttDate == '')
		{throw new Exception("Please select Attendance date.");}
		if($ddlTeacher == 0)
		{throw new Exception("Please select Teacher.");}
		// if($ddlPlan == 0)
		// {throw new Exception("Please select Plan.");}
		// if($ddlProduct == 0)
		// {throw new Exception("Please select Product.");}
		
		// $sql = "SELECT * FROM TEACHER_ATTENDANCE WHERE ATTDATE='$txtAttDate' AND TEACHERID=$ddlTeacher AND PLANID=$ddlPlan AND TAID!=$taid AND ISDELETED=0";

		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [TEACHER_ATTENDANCE_SP] $actionid,$taid,'$txtAttDate',$ddlTeacher,$ddlPlan,$ddlProduct1,$ddlProduct2,$ddlProduct3,'$txtTimeIN','$txtTimeOUT','$txtRemark','$txtLearnToday','$txtOtherWork','$txtSupervisorComm',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($taid))$data['message'] = 'Record successfully updated';
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
 /*========== SAVE DATA =========*/







/*============ Get Att =============*/ 
 function getAtt($mysqli){
	try
	{
		global $userid;
		$data = array();
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$txtAttDate=$_POST['txtAttDate'] == 'undefined' ? '' : $_POST['txtAttDate'];

		$query = "SELECT TAID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,TEACHERID,
			(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TA.TEACHERID)TEACHER,
			(SELECT LOCID FROM USERS WHERE UID=TA.TEACHERID)LOCID,PLANID,
			ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=TA.PLANID),'')PLANNAME,PRODUCTID1,PRODUCTID2,PRODUCTID3,
			ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TA.PRODUCTID1),'')PRODUCT1,
			ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TA.PRODUCTID2),'')PRODUCT2,
			ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TA.PRODUCTID3),'')PRODUCT3,
			CONVERT(VARCHAR, TIME_IN, 100)TIME_IN,
			CONVERT(VARCHAR,TIME_OUT,100)TIME_OUT,
			REMARKS,LEARNED_TODAY,OTHER_WORK,SUPERVISOR_COMMENT,
			CASE WHEN 
				ISNULL((SELECT CONVERT(DATE,CLOSED_DATE,105) FROM TEACHER_ATTENDANCE_SETTING WHERE ISDELETED=0 AND 
						LOCID=(SELECT LOCID FROM USERS WHERE UID=TA.TEACHERID)),'')<>'' AND 
				ISNULL((SELECT CONVERT(DATE,CLOSED_DATE,105) FROM TEACHER_ATTENDANCE_SETTING WHERE ISDELETED=0 AND 
						LOCID=(SELECT LOCID FROM USERS WHERE UID=TA.TEACHERID)),'')<>'01-01-1900' AND			 
				ISNULL((SELECT CONVERT(DATE,CLOSED_DATE,105) FROM TEACHER_ATTENDANCE_SETTING WHERE ISDELETED=0 AND 
						LOCID=(SELECT LOCID FROM USERS WHERE UID=TA.TEACHERID)),'')<CONVERT(DATE,ATTDATE,105)
				THEN 1 ELSE 0 
			END EDITABLE
			FROM TEACHER_ATTENDANCE TA WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher ORDER BY CONVERT(DATE,ATTDATE,105) DESC";
			//  AND CONVERT(DATE,ATTDATE,105)='$txtAttDate'
			
			$RCOUNT = unique($query);
			$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TAID'] = (int) $row['TAID'];
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
		if($role == 'TEACHER' || $role == 'VOLUNTEER'){
			$query = "SELECT UID,FIRSTNAME+' '+LASTNAME AS FULLNAME FROM USERS WHERE LOCID=$ddlLocation AND USERROLE='$userrole' AND ISDELETED=0";
		}else{
			$query = "SELECT UID,FIRSTNAME+' '+LASTNAME +' — '+' ('+USERROLE+')' AS FULLNAME FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0 ORDER BY USERROLE";
		}
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
		$query = "SELECT CONVERT(VARCHAR,CLOSED_DATE+1,23)CLOSED_DATE FROM TEACHER_ATTENDANCE_SETTING WHERE LOCID=$locid AND ISDELETED=0";
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




/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $taid = ($_POST['taid'] == 'undefined' || $_POST['taid'] == '') ? 0 : $_POST['taid'];  
			if($taid == 0){
				throw new Exception("taid Not Found.");
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_ATTENDANCE_SP] 3,$taid,'',0,0,0,0,0,'','','','','','',$userid");
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







