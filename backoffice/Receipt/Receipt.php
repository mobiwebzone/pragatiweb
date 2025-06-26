<?php
session_start();
require_once '../../code/connection.php';

// require_once '../../dompdf/autoload.inc.php';
// use Dompdf\Dompdf;


if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "getReceipts":getReceipts($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



 /*============ GET RECEIPTS =============*/ 
 function getReceipts($mysqli){
	try
	{
		$data = array();
		$LOCID=0;

		$recid=($_POST['recid'] == 'undefined' || $_POST['recid'] == '') ? 0 : $_POST['recid'];

		if($recid == 0){throw new Exception ("recid Error.");}

		$query = "SELECT RECID,CONVERT(VARCHAR,RECDATE,106)RECDATE,RECNO,RECNOFULL,REGID,
		(SELECT FIRSTNAME +' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENT_NAME,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID)[PLAN],
		INSTALLMENT,AMOUNT,PMID,REFNO,
		(SELECT INSTALLMENTS FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)TOTAL_INST,
		(SELECT PAYPLAN FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)PAYPLAN,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=SR.REGID)LOCATIONID,
		(SELECT (SELECT (SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SR.REGID)CURRENCY_CLASS,
		(SELECT (SELECT (SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SR.REGID)CURRENCY_CODE
		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND RECID=$recid";


		$cnt = unique($query);

		$result = sqlsrv_query($mysqli, $query);

		if($cnt > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
				$LOCID = $row['LOCATIONID'];
			}

			// Get Location Data
			if($LOCID > 0){
				$locQry = "SELECT LOC_ID,[LOCATION],LOC_DESC,LOC_CONTACT,LOC_PERSON,LOC_EMAIL,
				COMPANY_NAME,TAXID,
				LOC_ADDRESS_LINE1+', ' AS LOC_ADDRESS_LINE1,
				LOC_ADDRESS_LINE2+', ' AS LOC_ADDRESS_LINE2,
				LOC_CITY+', ' AS LOC_CITY,
				LOC_STATE+', ' AS LOC_STATE,
				LOC_COUNTRY,LOC_ZIPCODE,IS_ET,LOC_ET_DIFF,CURRENCY_ID
				FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=$LOCID";
				$LOCresult = sqlsrv_query($mysqli, $locQry);
				$LOCrow = sqlsrv_fetch_array($LOCresult);
				$data['LOC_DETAIL'] = $LOCrow;
			}
			$data['success'] = true;
			// fopen('testPDF.php','r');
			// PDF();

			
			// header("Location: testPDF.php");
		}
		else {
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



/*================ PDF =================*/ 
function PDF(){
	$_SESSION['data'] = 'Hello Shubham';
}





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







