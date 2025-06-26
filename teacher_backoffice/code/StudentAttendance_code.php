<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if(!empty($_SESSION['ROLE'])){
	$ROLE = $_SESSION['ROLE'];
	$LOC = $_SESSION['LOCATION'];}
else{$ROLE = '';
	$LOC = '';}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "SaveAttendace":SaveAttendace($conn);break;
        case "getAttendance":getAttendance($conn);break;
        case "getProduct":getProduct($conn);break;
        case "getPlans":getPlans($conn);break;
        case "getStudentData":getStudentData($conn);break;
        case "getLocations":getLocations($conn);break;
		
        case "saveCH_Work":saveCH_Work($conn);break;
        case "getCH_Work":getCH_Work($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function SaveAttendace($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$upd = $_POST['upd'] == 'undefined' ? '' : $_POST['upd'];
		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
		$ddlAttType = $_POST['ddlAttType'] == 'undefined' ? '' : $_POST['ddlAttType'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $Att = ($_POST['Att'] == 'undefined' || $_POST['Att'] == '') ? 0 : $_POST['Att'];
		$Att = explode (",", $Att);
        $RegID = ($_POST['RegID'] == 'undefined' || $_POST['RegID'] == '') ? 0 : $_POST['RegID'];
		$RegID = explode (",", $RegID);
		
		// if($ddlLocation == 0)
		// {throw new Exception("");}
		
		//Check Attendance
		$ChkAtt="SELECT * FROM STUDENT_ATTENDANCE WHERE ISDELETED=0 
		AND CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND (PLANID=$ddlPlan OR PRODUCTID=$ddlProduct) AND TEACHERID=$ddlTeacher";
		$data['ChkAtt']=$ChkAtt;
		$COUNT = unique($ChkAtt);
		$ChkAttstmt=sqlsrv_query($mysqli, $ChkAtt);
		if($COUNT > 0){
			$DelAtt="DELETE FROM STUDENT_ATTENDANCE WHERE CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND (PLANID=$ddlPlan OR PRODUCTID=$ddlProduct) AND TEACHERID=$ddlTeacher";
			$Delstmt=sqlsrv_query($mysqli, $DelAtt);
		}

	
		for($i=0; $i<COUNT($RegID); $i++){
			$ATTENDANCE = $Att[$i];
			$REGID = $RegID[$i];
			$query="EXEC [STUDENT_ATTENDANCE_SP] '$txtDate',$ddlTeacher,$ddlLocation,$REGID,$ddlPlan,$ddlProduct,$ATTENDANCE,'$ddlAttType',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
		}
	
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = true;
			$data['query'] = $query;
		}
		else
		{
			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Student Attendance Successfully Save.';
			// else $data['message'] = 'Student successfully revoke.';
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




/*============ Get Attendance =============*/ 
 function getAttendance($mysqli){
	try
	{
		global $userid;
		$data = array();
		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
		$ddlAttType=$_POST['ddlAttType'] == 'undefined' ? '' : $_POST['ddlAttType'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "SELECT ATTID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,
		TEACHERID,LOCID,REGID,PLANID,CONVERT(VARCHAR,PRESENT)PRESENT,ATTENDANCE_TYPE FROM STUDENT_ATTENDANCE WHERE ISDELETED=0
		AND CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND (PLANID=$ddlPlan OR PRODUCTID=$ddlProduct) AND ATTENDANCE_TYPE='$ddlAttType'";
		

		$data['query'] = $query;
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ATTID'] = (int) $row['ATTID'];
				$data['data'][] = $row;
				$data['Att'][]=$row['PRESENT'];
				$data['RegID'][]=$row['REGID'];
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



/*============ Get Plans =============*/ 
 function getPlans($mysqli){
	try
	{
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0 AND 
		PLANID IN (SELECT PLANID FROM TEACHER_PLAN WHERE TEACHERID=$ddlTeacher)";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
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


/*============ GET STUDENT FOR APPROVAL/APPROVED =============*/ 
 function getStudentData($mysqli){
	try
	{
		global $userid;
		$data = array();

		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
		$ddlAttType=($_POST['ddlAttType'] == 'undefined' || $_POST['ddlAttType'] == '') ? '' : $_POST['ddlAttType'];
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$ddlProduct=($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		if($ddlAttType == ''){
			throw new Exception('Select Attendance Type.');
		}

		$query = "EXEC [GET_STUDENT_ATTENDANCE]'$txtDate','$ddlAttType',$ddlLocation,$ddlPlan,$ddlProduct,$ddlTeacher";
		$count = unique($query);
		// $data['query']=$query;
		$RegID=array();
		$ATTEN=array();
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
				$data['RegID'][]=$row['REGID'];
				$data['ATTEN'][]=$row['ATTENDANCE'];
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


/*============ Get Locations =============*/ 
function getLocations($mysqli){
	try
	{
		global $userid,$ROLE,$LOC;
		$data = array();
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0";

		if($ROLE == 'TEACHER'){
			$query .=" AND LOC_ID IN (SELECT LOCID FROM TEACHER_LOCATION WHERE TEACHERID=$userid AND ISDELETED=0)";
		}
		else{
			if (strpos($LOC, 'HQ') !== false) {
				// $query .=" AND LOC_ID = (SELECT LOCID FROM USERS WHERE UID=$userid)";
			}
			else{
				// $query .=" AND LOC_ID = (SELECT LOCID FROM USERS WHERE UID=$userid AND USERROLE != 'ADMINISTRATOR')";
			}
		}
		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
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






// =================================================== Class/Home Work ===========================================

function saveCH_Work($mysqli){
	try
	{
		$data = array();
        global $userid;
    
        $hwid  = ($_POST['hwid'] == 'undefined' || $_POST['hwid'] == '') ? 0 : $_POST['hwid'];
        $txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
        $txtCW=$_POST['txtCW'] == 'undefined' ? '' : $_POST['txtCW'];
        $txtHW=$_POST['txtHW'] == 'undefined' ? '' : $_POST['txtHW'];
		
		$actionid = $hwid == 0 ? 1 : 2;

		if($txtDate == '')
		{throw new Exception("Please Enter Date.");}
		// if($ddlPlan == 0)
		// {throw new Exception("Please Select Plan Name.");}
		if($ddlProduct == 0)
		{throw new Exception("Please Select Product Name.");}

		// $sql = "SELECT * FROM CLASSWORKS_HOMEWORKS WHERE PRODUCT='$txtProduct' AND PRODUCT_ID!=$productid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [CLASSWORKS_HOMEWORKS_SP] $actionid,$hwid,'$txtDate',$userid,$ddlPlan,$ddlProduct,'$txtCW','$txtHW',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($hwid))$data['message'] = 'Record successfully updated';
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

/*============ Get C/H Work =============*/ 
function getCH_Work($mysqli){
	try
	{
		global $userid;
		$data = array();
		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];

		$query = "SELECT HWID,CLASSWORK,HOMEWORK FROM CLASSWORKS_HOMEWORKS WHERE ISDELETED=0
		AND CONVERT(DATE,HWDATE,105)='$txtDate' AND (PLANID=$ddlPlan OR PRODUCTID=$ddlProduct)";
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['HWID'] = (int) $row['HWID'];
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







