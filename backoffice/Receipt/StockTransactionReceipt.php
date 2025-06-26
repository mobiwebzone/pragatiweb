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
        case "getTransaction":getTransaction($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ GET TRANSACTION =============*/ 
function getTransaction($mysqli){
	try
	{
		$data = array();
		$LOCID=0;

		$transid=($_POST['transid'] == 'undefined' || $_POST['transid'] == '') ? 0 : $_POST['transid'];
		if($transid == 0){throw new Exception ("TransId Invalid.");}

		$query = "SELECT (SELECT CONVERT(VARCHAR,TRANSDATE,21) FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=STD.TRANSID)TRANSDATE,
		(SELECT TRANSTYPE FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=STD.TRANSID)TRANSTYPE,
		(SELECT TRANSFOR FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=STD.TRANSID)TRANSFOR,
		(SELECT 
			CASE WHEN TRANSFOR='STUDENT'
				THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.TRANSFORID)
				WHEN TRANSFOR='STAFF'
				THEN (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=ST.TRANSFORID)
				ELSE 'OFFICE'
			END
		FROM STOCK_ITEM_TRANSACTIONS ST WHERE TRANSID=STD.TRANSID)TRANSFOR_NAME,
		(SELECT REMARKS FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=STD.TRANSID)TRANS_REMARK,
		TRANSDETID,STOCKDETID,ITEMID,
		(SELECT ITEM FROM ITEM_MASTER WHERE ITEMID=STD.ITEMID)ITEM,
		RATE,DISCOUNT,AMOUNT,QTY,NETAMOUNT,REMARKS AS TRANSDET_REMARK
		FROM STOCK_ITEM_TRANSACTIONS_DETAILS STD
		WHERE ISDELETED=0 AND TRANSID=$transid
		ORDER BY TRANSDETID DESC";

		$cnt = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($cnt > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['NETAMOUNT'] = (float)$row['NETAMOUNT'];
				$data['data'][] = $row;
				$data['TRANSTYPE'] = $row['TRANSTYPE'];
				$data['TRANSFOR_NAME']= $row['TRANSFOR_NAME'];
				$date = date_create($row['TRANSDATE']);
				// $data['TRANS_DATE']= date_format($date,"d/m/Y, H:m: A");
				$data['TRANS_DATE']= date_format($date,"d/m/Y");
				// $LOCID = $row['LOCATIONID'];
			}

			// Get Location Data
			// if($LOCID > 0){
			// 	$locQry = "SELECT LOC_ID,[LOCATION],LOC_DESC,LOC_CONTACT,LOC_PERSON,LOC_EMAIL,
			// 	COMPANY_NAME,TAXID,
			// 	LOC_ADDRESS_LINE1+', ' AS LOC_ADDRESS_LINE1,
			// 	LOC_ADDRESS_LINE2+', ' AS LOC_ADDRESS_LINE2,
			// 	LOC_CITY+', ' AS LOC_CITY,
			// 	LOC_STATE+', ' AS LOC_STATE,
			// 	LOC_COUNTRY,LOC_ZIPCODE,IS_ET,LOC_ET_DIFF,CURRENCY_ID
			// 	FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=$LOCID";
			// 	$LOCresult = sqlsrv_query($mysqli, $locQry);
			// 	$LOCrow = sqlsrv_fetch_array($LOCresult);
			// 	$data['LOC_DETAIL'] = $LOCrow;
			// }
			$data['success'] = true;
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






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







