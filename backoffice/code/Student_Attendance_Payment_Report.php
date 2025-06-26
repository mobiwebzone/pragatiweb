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


/*============ Get Report =============*/ 
 function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		
		// $data['REGIDS123'] = in_array(1332,array(1045, 202, 1044, 1332));
		$data['REGIDS'] = $REGIDS_ARRAY;
		// echo json_encode($data);exit;
		// if($ddlPlan<=0) throw new Exception('Plan Not Found.');

		$MAIN_DATA = array();
		$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

		$query = "EXEC [RPT_STUDENT_ATTENDANCE_PAYMENT] '$txtFromDT','$txtToDT'";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);

		// GET WEEKS
		$WEEK_ARRAY = array();
		set_time_limit(0);
		while ($rowWeeks = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$WEEK_ARRAY[] = $rowWeeks['WEEKNO'];
			$data['WEEKS'][]=$rowWeeks;
		}


		$REGID1=$REGID2=0;
		$IDX=0;
		sqlsrv_next_result($result);
		set_time_limit(0);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['PAYMENTREMARKS']= rtrim($row['PAYMENTREMARKS'],', ');
			$row['PAYMENTREMARKS']=  str_replace(',',' / ',$row['PAYMENTREMARKS']);
			$REGID1=(int)$row['REGID'];
			// $CHK = in_array($REGID1,$REGIDS_ARRAY);
			// $data['CHK'][] = $CHK;
			// $data['R'][] = $row['REGID'];
			// $data['$REGID1'][] =(string)$row['REGID'];
		
			if(in_array($row['REGID'],$REGIDS_ARRAY)){
				if($REGID1!=$REGID2){
					$IDX++;
					$data['data'][$IDX][] = $row;
				}else{
					$data['data'][$IDX][] = $row;
				}
			}
			
			$REGID2=$REGID1;
			// for($i=0;$i<count($WEEK_ARRAY);$i++){
			// 	$WEEK = $WEEK_ARRAY[$i];
			// 	if($WEEK == $row['WEEKNO']){
			// 		$data['data'][$WEEK][] = $row;
			// 	}
			// }


			// $data['data'][]=$row;
		}


		
		

		// $data['MAIN_DATA']=$MAIN_DATA;
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







