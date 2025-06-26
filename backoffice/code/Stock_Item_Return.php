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
        case "ReturnItemSave":ReturnItemSave($conn);break;
		case "getReturnData":getReturnData($conn);break;
		case "getTransForName":getTransForName($conn);break;
		case "getTransactionsByTransForID":getTransactionsByTransForID($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function ReturnItemSave($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtReturnDate  = ($_POST['txtReturnDate'] == 'undefined' || $_POST['txtReturnDate'] == '') ? '' : $_POST['txtReturnDate'];
        $RETURN_ITEM  = (isset($_POST['RETURN_ITEM']) && !empty($_POST['RETURN_ITEM'])) ?  json_decode($_POST['RETURN_ITEM'],true) : '';
		if($RETURN_ITEM == '') throw new Exception("Return Data Missing.");
		if($txtReturnDate == '') throw new Exception("Please Select Return Date.");
		if($ddlLocation == 0) throw new Exception("Please Select Location Name.");

		$data['$RETURN_ITEM'] = $RETURN_ITEM;
		
		$TRANSID = $RETURN_ITEM['TRANSID'];
		$STOCKDETID = $RETURN_ITEM['STOCKDETID'];
		$ITEMID = $RETURN_ITEM['ITEMID'];
		$AMOUNT = $RETURN_ITEM['AMOUNT'];
		$RATE = $RETURN_ITEM['RATE'];
		$DISCOUNT = $RETURN_ITEM['DISCOUNT'];
		$CAN_BE_RETURN = $RETURN_ITEM['CAN_BE_RETURN'];
		$QTY = $RETURN_ITEM['QTY'];
		$RETURN_QTY = $RETURN_ITEM['RETURN_QTY'];

		$FINAL_DISCOUNT =number_format((float)($DISCOUNT/$CAN_BE_RETURN)*$RETURN_QTY, 2, '.', '');
		$FINAL_AMOUNT = number_format((float)($RATE/$CAN_BE_RETURN)*$RETURN_QTY, 2, '.', '');
		$data['$FINAL_DISCOUNT']=$FINAL_DISCOUNT;
		$data['$FINAL_AMOUNT']=$FINAL_AMOUNT;
		// echo json_encode($data);exit;

       
		$query="EXEC [STOCK_ITEM_RETURNS_SP] 1,0,$ddlLocation,'$txtReturnDate',$TRANSID,$STOCKDETID,$ITEMID,$RETURN_QTY,$RATE,
				$FINAL_DISCOUNT,$FINAL_AMOUNT,'',$userid";
		// $data['$query'] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
			echo json_encode($data);exit;
		}
		else
		{
			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Record successfully inserted.';
		}
			
        echo json_encode($data);exit;
     }
     catch(Exception $e)
     {
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
     }
 }




/*============ GET RETURN DATA =============*/ 
function getReturnData($mysqli){
	try
	{
		$data = array();
		global $userid;
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT RETURNID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=SIR.LOCID)[LOCATION],
		CONVERT(VARCHAR,RETURNDATE,21)RETURNDATE,TRANSID,STOCKDETID,ITEMID,
		(SELECT ITEM FROM ITEM_MASTER WHERE ITEMID=SIR.ITEMID)ITEM,QTY,RATE,DISCOUNT,AMOUNT,
		(SELECT TRANSTYPE FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=SIR.TRANSID)TRANSTYPE,
		(SELECT TRANSFOR FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=SIR.TRANSID)TRANSFOR,
		(SELECT 
			CASE WHEN TRANSFOR='STUDENT'
				THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.TRANSFORID)
				WHEN TRANSFOR='STAFF'
				THEN (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=ST.TRANSFORID)
				ELSE 'OFFICE'
			END
		FROM STOCK_ITEM_TRANSACTIONS ST WHERE TRANSID=SIR.TRANSID)TRANSFOR_NAME
		FROM STOCK_ITEM_RETURNS SIR WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY RETURNID DESC";

		$data['query']=$query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
				$row['RATE'] = $row['RATE'];
				$row['DISCOUNT'] = $row['DISCOUNT'];
				$row['AMOUNT'] = $row['AMOUNT'];
				$date=date_create($row['RETURNDATE']);
				$row['RETURNDATE_ORDERBY'] = date_format($date,"dmyhis");
				$row['RETURNDATE'] = date_format($date,"d-m-y h:i A");
				$data['data'][]= $row;
			}
		   
		   	$data['success'] = true;
		}else{
			$data['success'] = false;
		}
	   	echo json_encode($data);exit;

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}
/*============ GET RETURN DATA =============*/ 




/*============ GET TRANSACTION FOR NAME =============*/ 
function getTransForName($mysqli){
	try
	{
	   $data = array();
	   global $userid;
      
	   $txtTRANSTYPE  = ($_POST['txtTRANSTYPE'] == 'undefined' || $_POST['txtTRANSTYPE'] == '' ||$_POST['txtTRANSTYPE'] == 'null') ? '' : $_POST['txtTRANSTYPE'];
	   $txtTransactionFor  = ($_POST['txtTransactionFor'] == 'undefined' || $_POST['txtTransactionFor'] == '' ||$_POST['txtTransactionFor'] == 'null') ? '' : $_POST['txtTransactionFor'];
	   $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '' ||$_POST['ddlLocation'] == 'null') ? 0 : $_POST['ddlLocation'];

	   if($txtTRANSTYPE == ''){throw new Exception("Please Select Transaction Type.");}
	   if($txtTransactionFor == ''){throw new Exception("Please Select Transaction For.");}

	   $query = "SELECT TRANSID,TRANSFORID,
	   CASE
		   WHEN TRANSFOR='STUDENT'
		   THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.TRANSFORID)
		   WHEN TRANSFOR='STAFF'
		   THEN (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=ST.TRANSFORID)
		   ELSE 'OFFICE'
	   END TRANSFOR_NAME
	   FROM STOCK_ITEM_TRANSACTIONS ST
	   WHERE ISDELETED=0 AND TRANSTYPE='$txtTRANSTYPE' AND TRANSFOR='$txtTransactionFor' AND LOCID=$ddlLocation";

	   $data['qry']=$query;
	   $count = unique($query);
	   if($count > 0){
		   $result = sqlsrv_query($mysqli, $query);
		   $row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		   $data['data'][]= $row;
		   
		   $data['success'] = true;
	   }else{
		   $data['success'] = false;
	   }
	   echo json_encode($data);exit;

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}
/*============ GET TRANSACTION FOR NAME =============*/ 





/* ========== GET TRANSACTIONS BY TRANSFORID =========== */
function getTransactionsByTransForID($mysqli){
	try
	{
		$data = array();
		global $userid;
		
		$ddlForTransName  = ($_POST['ddlForTransName'] == 'undefined' || $_POST['ddlForTransName'] == '' ||$_POST['ddlForTransName'] == 'null') ? 0 : $_POST['ddlForTransName'];
		$txtTransactionFor  = ($_POST['txtTransactionFor'] == 'undefined' || $_POST['txtTransactionFor'] == '' ||$_POST['txtTransactionFor'] == 'null') ? '' : $_POST['txtTransactionFor'];
		$txtTRANSTYPE  = ($_POST['txtTRANSTYPE'] == 'undefined' || $_POST['txtTRANSTYPE'] == '' ||$_POST['txtTRANSTYPE'] == 'null') ? '' : $_POST['txtTRANSTYPE'];

		if($txtTRANSTYPE == ''){throw new Exception("Please Select Transaction Type.");}
		if($txtTransactionFor == ''){throw new Exception("Please Select Transaction For.");}
		if($ddlForTransName == 0 && $txtTransactionFor != 'OFFICE'){throw new Exception("Please Select Staff/Student Name.");}

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
		TRANSDETID,TRANSID,STOCKDETID,ITEMID,
		(SELECT ITEM FROM ITEM_MASTER WHERE ITEMID=STD.ITEMID)ITEM,
		RATE,DISCOUNT,AMOUNT,QTY,NETAMOUNT,REMARKS AS TRANSDET_REMARK,
		(SELECT QTY-ISNULL((SELECT SUM(QTY) FROM STOCK_ITEM_RETURNS WHERE TRANSID=STD.TRANSID AND STOCKDETID=STD.STOCKDETID AND ITEMID=STD.ITEMID AND ISDELETED=0),0)
			FROM STOCK_ITEM_TRANSACTIONS_DETAILS WHERE TRANSDETID=STD.TRANSDETID)CAN_BE_RETURN
		FROM STOCK_ITEM_TRANSACTIONS_DETAILS STD
		WHERE ISDELETED=0 AND (SELECT TRANSTYPE FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSID=STD.TRANSID)='$txtTRANSTYPE'";
	   
		if($ddlForTransName > 0){
			$query .= " AND TRANSID IN (SELECT TRANSID FROM STOCK_ITEM_TRANSACTIONS WHERE TRANSFORID=$ddlForTransName)";
		}else{
			$query .= " AND (SELECT TRANSFOR FROM STOCK_ITEM_TRANSACTIONS WHERE ISDELETED=0 AND TRANSID=STD.TRANSID) = 'OFFICE'";
		}
		$query .= " ORDER BY TRANSDETID DESC";

		$data['qry']=$query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
				$row['RETURN_QTY'] = 0;
				$date = date_create($row['TRANSDATE']);
				$row['TRANSDATE_SHOW']= date_format($date,"d/m/Y, H:m: A");
				$data['data'][]= $row;
			}
			
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		echo json_encode($data);exit;

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}
/* ========== GET TRANSACTIONS BY TRANSFORID =========== */




/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RETURNID = ($_POST['RETURNID'] == 'undefined' || $_POST['RETURNID'] == '') ? 0 : $_POST['RETURNID'];  
			if($RETURNID == 0)throw new Exception("RETURNID Invalid.");
            $CANCEL_REASONE = ($_POST['CANCEL_REASONE'] == 'undefined' || $_POST['CANCEL_REASONE'] == '') ? '' : $_POST['CANCEL_REASONE'];  
			if($CANCEL_REASONE == "")throw new Exception("Please Enter Cancel Reasone.");
            $query = "EXEC [STOCK_ITEM_RETURNS_SP] 3,$RETURNID,0,'',0,0,0,0,0,0,0,'$CANCEL_REASONE',$userid";
						
            $data['$query']= $query;
			$stmt=sqlsrv_query($mysqli,$query );
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Return item cancel successfull.';
			}
		echo json_encode($data);exit;
	}catch (Exception $e){
		$data = array();
		$data['success'] = false . $query;
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







