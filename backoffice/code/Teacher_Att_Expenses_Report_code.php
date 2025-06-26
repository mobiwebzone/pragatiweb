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
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


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







