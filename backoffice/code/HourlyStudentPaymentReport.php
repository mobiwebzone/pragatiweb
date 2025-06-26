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
		global $userid;
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$UID=($_POST['userid'] == 'undefined' || $_POST['userid'] == '') ? 0 : $_POST['userid'];
		$ddlPaidDue=($_POST['ddlPaidDue'] == 'undefined' || $_POST['ddlPaidDue'] == '') ? '' : $_POST['ddlPaidDue'];
		$sum_det=($_POST['sum_det'] == 'undefined' || $_POST['sum_det'] == '') ? 0 : $_POST['sum_det'];

		if($ddlLocation==0) throw new Exception('Error : Location Not Found.');
		if($ddlPaidDue=='') throw new Exception('Error : Paid/Due Not Found.');

		// $query = "EXEC [RPT_TUTORING_STUDENT_DUES] $ddlLocation,$userid,'$ddlPaidDue',$sum_det";
		$query = "EXEC [RPT_TUTORING_STUDENT_DUES] $ddlLocation,$UID,'$ddlPaidDue',$sum_det";
		$data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		
		if(sqlsrv_has_rows($result) !== false){
			$TOTAL_AMT = $TOTAL_PAID = $TOTAL_DUE = 0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['AMOUNT'] = (float) $row['AMOUNT'];
				$row['PAID'] = (float) $row['PAID'];
				$row['DUE'] = $row['AMOUNT']-$row['PAID'];
				$data['data'][] = $row;
				$TOTAL_AMT += $row['AMOUNT'];
				$TOTAL_PAID += $row['PAID'];
				$TOTAL_DUE += $row['DUE'];
			}
			$data['TOTALS'] = compact('TOTAL_AMT','TOTAL_PAID','TOTAL_DUE');
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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







