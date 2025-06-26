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
		$DAYS =['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		$DAYS_NUM =[1, 2, 3, 4, 5, 6, 7];
		for($d=0;$d<count($DAYS_NUM);$d++){
			$DATES=array();
			$TOTALS=array();
			$AVEGS=array();
			$DAY = $d+1;
			$query = "EXEC [GET_DAYWAISE_ATT_AVG_GRAPH] '$txtFromDT','$txtToDT',$DAY,$ddlLocation";
			$data['$query'][]=$query;
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['AVEG']=(float)$row['AVEG'];
				$DATES[] = $row['ATTDATE'];
				$TOTALS[] = $row['TOTAL'];
				$AVEGS[] = $row['AVEG'];
				// $data['data'][]=$row;
			}
			$MAIN_DATA[$DAYS_NUM[$d]] = ['DATE'=>$DATES,'TOTAL'=>$TOTALS,'AVEG'=>$AVEGS];
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







