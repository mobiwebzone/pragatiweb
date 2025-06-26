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
		case "login":login($conn);break;
        case "SaveAttendace":SaveAttendace($conn);break;
        case "getAttendance":getAttendance($conn);break;
        case "getProduct":getProduct($conn);break;
        case "getPlans":getPlans($conn);break;
        case "getStudentData":getStudentData($conn);break;
        case "getLocations":getLocations($conn);break;
        case "deleteProduct":deleteProduct($conn);break;
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
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $Att = ($_POST['Att'] == 'undefined' || $_POST['Att'] == '') ? 0 : $_POST['Att'];
		$Att = explode (",", $Att);
        $RegID = ($_POST['RegID'] == 'undefined' || $_POST['RegID'] == '') ? 0 : $_POST['RegID'];
		$RegID = explode (",", $RegID);
		
		// if($ddlLocation == 0)
		// {throw new Exception("");}
		
		//Check Attendance
		$ChkAtt="SELECT * FROM STUDENT_ATTENDANCE WHERE ISDELETED=0 
		AND CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND PLANID=$ddlPlan AND TEACHERID=$ddlTeacher";
		// $data['ChkAtt']=$ChkAtt;
		$COUNT = unique($ChkAtt);
		$ChkAttstmt=sqlsrv_query($mysqli, $ChkAtt);
		if($COUNT > 0){
			$DelAtt="DELETE FROM STUDENT_ATTENDANCE WHERE CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND PLANID=$ddlPlan AND TEACHERID=$ddlTeacher";
			$Delstmt=sqlsrv_query($mysqli, $DelAtt);
		}

	
		for($i=0; $i<COUNT($RegID); $i++){
			$ATTENDANCE = $Att[$i];
			$REGID = $RegID[$i];
			$query="EXEC [STUDENT_ATTENDANCE_SP] '$txtDate',$ddlTeacher,$ddlLocation,$REGID,$ddlPlan,$ATTENDANCE,$userid";
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
		$data = array();
		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "SELECT ATTID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,
		TEACHERID,LOCID,REGID,PLANID,CONVERT(VARCHAR,PRESENT)PRESENT FROM STUDENT_ATTENDANCE WHERE ISDELETED=0
		AND CONVERT(DATE,ATTDATE,105)='$txtDate' AND LOCID=$ddlLocation AND PLANID=$ddlPlan AND TEACHERID=$ddlTeacher";
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
		$data = array();

		$txtDate=$_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		$query = "EXEC [GET_STUDENT_ATTENDANCE]'$txtDate',$ddlLocation,$ddlPlan,$ddlTeacher";
		
		// $data['query']=$query;
		$RegID=array();
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
			$data['RegID'][]=$row['REGID'];
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



/* =========== Delete =========== */ 
function deleteProduct($mysqli){
	try{   
			global $userid;
			$data = array();     
            $productid = ($_POST['productid'] == 'undefined' || $_POST['productid'] == '') ? 0 : $_POST['productid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [PRODUCTS_SP] 3,$productid,'','',0,'',$userid");
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

/*============ Get Locations =============*/ 
function getLocations($mysqli){
	try
	{
	// 	if($_SESSION['USER_LOCID'] == '1')
	// {
	// 	$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0";
	// }
	// else{
	// 	$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=".$_SESSION['USER_LOCID']."";
	// }
		global $userid;
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0
		AND LOC_ID IN (SELECT LOCID FROM TEACHER_LOCATION WHERE TEACHERID=$userid AND ISDELETED=0)";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







