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
        case "getReport":getReport($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Student Discontinue Report =============*/ 
 function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// if($ddlPlan<=0) throw new Exception('Plan Not Found.');

		$MAIN_DATA = array();
		$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

		$query = "EXEC [RPT_DAY_WISE_STUDENTLIST] '$txtFromDT','$txtToDT', $ddlLocation";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {

			// $data['data'][] = $row;
			$row['Sunday'] = rtrim($row['Sunday'],' | ');
			$row['Monday'] = rtrim($row['Monday'],' | ');
			$row['Tuesday'] = rtrim($row['Tuesday'],' | ');
			$row['Wednesday'] = rtrim($row['Wednesday'],' | ');
			$row['Thursday'] = rtrim($row['Thursday'],' | ');
			$row['Friday'] = rtrim($row['Friday'],' | ');
			$row['Saturday'] = rtrim($row['Saturday'],' | ');
			// BOLD
			$row['Sunday'] = str_replace('#b', "<b>", $row['Sunday']);
			$row['Sunday'] = str_replace('b#', "</b>", $row['Sunday']);
			$row['Monday'] = str_replace('#b', "<b>", $row['Monday']);
			$row['Monday'] = str_replace('b#', "</b>", $row['Monday']);
			$row['Tuesday'] = str_replace('#b', "<b>", $row['Tuesday']);
			$row['Tuesday'] = str_replace('b#', "</b>", $row['Tuesday']);
			$row['Wednesday'] = str_replace('#b', "<b>", $row['Wednesday']);
			$row['Wednesday'] = str_replace('b#', "</b>", $row['Wednesday']);
			$row['Thursday'] = str_replace('#b', "<b>", $row['Thursday']);
			$row['Thursday'] = str_replace('b#', "</b>", $row['Thursday']);
			$row['Friday'] = str_replace('#b', "<b>", $row['Friday']);
			$row['Friday'] = str_replace('b#', "</b>", $row['Friday']);
			$row['Saturday'] = str_replace('#b', "<b>", $row['Saturday']);
			$row['Saturday'] = str_replace('b#', "</b>", $row['Saturday']);
			// BOLD
			
			$row['Sunday'] = str_replace(' | ', ",\n\n", $row['Sunday']);
			$row['Monday'] = str_replace(' | ', ",\n\n", $row['Monday']);
			$row['Tuesday'] = str_replace(' | ', ",\n\n", $row['Tuesday']);
			$row['Wednesday'] = str_replace(' | ', ",\n\n", $row['Wednesday']);
			$row['Thursday'] = str_replace(' | ', ",\n\n", $row['Thursday']);
			$row['Friday'] = str_replace(' | ', ",\n\n", $row['Friday']);
			$row['Saturday'] = str_replace(' | ', ",\n\n", $row['Saturday']);


			$data['data'][]=$row;
		}

		$data['MAIN_DATA']=$MAIN_DATA;
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







