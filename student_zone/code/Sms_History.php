<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ GET PAYMENT TRANSACTION =============*/ 
//  function getPT($mysqli){
// 	try
// 	{
// 		$data = array();
// 		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

// 		$query = "SELECT RECID,CONVERT(VARCHAR,RECDATE,105)RECDATE,RECNO,RECNOFULL,PLANID,
// 		(SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID)PLANNAME,INSTALLMENT,AMOUNT,
// 		PMID,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=SR.PMID)PAYMODE,REFNO,
// 		(SELECT PAYPLAN FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)PAYPLAN,
// 		REMARK
// 		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=$REGID";
// 		$data['$query']=$query;
// 		$RCOUNT = unique($query);
// 		$result = sqlsrv_query($mysqli, $query);
		
// 		if($RCOUNT > 0){

// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['RECID'] = (int) $row['RECID'];
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}else{
			
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }
/*============ GET PAYMENT TRANSACTION =============*/ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







