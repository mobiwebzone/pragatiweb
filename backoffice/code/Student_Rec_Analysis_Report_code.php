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
        case "getSTRecAnalysisDetail":getSTRecAnalysisDetail($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET Student Rec Analysis Details =============*/ 
 function getSTRecAnalysisDetail($mysqli){
	try
	{
		$txtFromDate_SRA = $_POST['txtFromDate_SRA'] == 'undefined' ? '' : $_POST['txtFromDate_SRA'];
		$txtToDate_SRA = $_POST['txtToDate_SRA'] == 'undefined' ? '' : $_POST['txtToDate_SRA'];
		$query = "EXEC [RPT_STUDENT_RECEIPTS_ANALYSIS_DETAILS] '$txtFromDate_SRA','$txtToDate_SRA'";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();

		$TOTAL_CY=0;
		while ($row = sqlsrv_fetch_array($result)) {
			$data['Current_Year'][] = $row;
			$TOTAL_CY = $TOTAL_CY+$row['AMOUNT'];
		}

		$TOTAL_PY=0;
		sqlsrv_next_result($result);
		while ($row1 = sqlsrv_fetch_array($result)) {
			$data['Previous_Year'][] = $row1;
			$TOTAL_PY = $TOTAL_PY+$row1['AMOUNT'];
		}
		
		$TOTAL_PM=0;
		sqlsrv_next_result($result);
		while ($row2 = sqlsrv_fetch_array($result)) {
			$data['Previous_Month'][] = $row2;
			$TOTAL_PM = $TOTAL_PM+$row2['AMOUNT'];
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







