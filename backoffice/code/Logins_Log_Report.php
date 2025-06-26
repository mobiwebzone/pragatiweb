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
        case "getTeacherAttExpenses":getTeacherAttExpenses($conn);break;
        case "getUserbyType":getUserbyType($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 /*============ Get Users By Types =============*/ 
function getUserbyType($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtFromDate = ($_POST['txtFromDate'] == 'undefined' || $_POST['txtFromDate'] == '') ? '' : $_POST['txtFromDate'];
		$txtToDate = ($_POST['txtToDate'] == 'undefined' || $_POST['txtToDate'] == '') ? '' : $_POST['txtToDate'];
		$ddlLoginType = ($_POST['ddlLoginType'] == 'undefined' || $_POST['ddlLoginType'] == '') ? 0 : $_POST['ddlLoginType'];

		if($ddlLoginType == "BACKOFFICE"){
			$query = "SELECT LOGINTYPE,USERID,
			(SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE UID=L.USERID)USERNAME,
			CONVERT(VARCHAR,LOGINDT,20)LOGINDT,IPADDRESS,LOGINSTATUS,CONVERT(VARCHAR,LOGOUTDT,20)LOGOUTDT 
			FROM LOGINS_LOG L WHERE LOGINTYPE='BACKOFFICE' 
			AND USERID IN (SELECT UID FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE NOT IN ('TEACHER','VOLUNTEER')) 
			AND CONVERT(DATE,LOGINDT,105) BETWEEN '$txtFromDate' AND '$txtToDate'
			ORDER BY LOGINTYPE,USERNAME,CONVERT(DATE,LOGINDT,105)";

		}
		else if($ddlLoginType == "TEACHER"){
			$query = "SELECT LOGINTYPE,USERID,
			(SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE UID=L.USERID)USERNAME,
			CONVERT(VARCHAR,LOGINDT,20)LOGINDT,IPADDRESS,LOGINSTATUS,CONVERT(VARCHAR,LOGOUTDT,20)LOGOUTDT 
			FROM LOGINS_LOG L WHERE LOGINTYPE='TEACHER' 
			AND USERID IN (SELECT UID FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER')) 
			AND CONVERT(DATE,LOGINDT,105) BETWEEN '$txtFromDate' AND '$txtToDate'
			ORDER BY LOGINTYPE,USERNAME,CONVERT(DATE,LOGINDT,105)";
		}
		else if($ddlLoginType == "STUDENT"){
			$query = "SELECT LOGINTYPE,USERID,
			(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=L.USERID)USERNAME,
			CONVERT(VARCHAR,LOGINDT,20)LOGINDT,IPADDRESS,LOGINSTATUS,CONVERT(VARCHAR,LOGOUTDT,20)LOGOUTDT 
			FROM LOGINS_LOG L WHERE LOGINTYPE='STUDENT' 
			AND USERID IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation)
			AND CONVERT(DATE,LOGINDT,105) BETWEEN '$txtFromDate' AND '$txtToDate'
			ORDER BY USERNAME";
		}
		else{
			throw new Exception("Error : Invalid Login Type.");
		}
		$data['query'] = $query;
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$date = date_create($row['LOGINDT']);
				$row['LOGINDT_SHOW'] = date_format($date,"d M Y h:m A");
				$row['LOGINDT_ORDERBY'] = date_format($date,"ymdhms");

				if(isset($row['LOGOUTDT'])){
					$date = date_create($row['LOGOUTDT']);
					$row['LOGOUTDT_SHOW'] = date_format($date,"d M Y h:m A");
					$row['LOGOUTDT_ORDERBY'] = date_format($date,"ymdhms");
				}else{
					$row['LOGOUTDT_SHOW'] = '—';
					$row['LOGOUTDT_ORDERBY'] = '—';
				}

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

/*============ GET Teacher Attendance Expenses =============*/ 
 function getTeacherAttExpenses($mysqli){
	try
	{
		$txtFromDate = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "EXEC [RPT_TEACHER_ATT_EXPENSES_DETAILS] '$txtFromDate','$txtToDate',$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();

		$TOTAL_CY=0;
		while ($row = sqlsrv_fetch_array($result)) {
			$TOTAL_CY = $TOTAL_CY+$row['NO_OF_HOURS_DECIMAL'];
			$row['NO_OF_HOURS_DECIMAL'] = (float)$row['NO_OF_HOURS_DECIMAL'];
			$data['Current_Year'][] = $row;
		}
		
		$TOTAL_PY=0;
		sqlsrv_next_result($result);
		while ($row1 = sqlsrv_fetch_array($result)) {
			$TOTAL_PY = $TOTAL_PY+$row1['NO_OF_HOURS_DECIMAL'];
			$row1['NO_OF_HOURS_DECIMAL'] = (float)$row1['NO_OF_HOURS_DECIMAL'];
			$data['Previous_Year'][] = $row1;
		}
		
		$TOTAL_PM=0;
		sqlsrv_next_result($result);
		while ($row2 = sqlsrv_fetch_array($result)) {
			$TOTAL_PM = $TOTAL_PM+$row2['NO_OF_HOURS_DECIMAL'];
			$row2['NO_OF_HOURS_DECIMAL'] = (float)$row2['NO_OF_HOURS_DECIMAL'];
			$data['Previous_Month'][] = $row2;
		}

		$data['$query']=$query;
		$data['TOTAL_CY']=number_format($TOTAL_CY, 2);
		$data['TOTAL_PY']=number_format($TOTAL_PY, 2);
		$data['TOTAL_PM']=number_format($TOTAL_PM, 2);
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







