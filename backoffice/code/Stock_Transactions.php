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
        case "save":save($conn);break;
		case "saveStockDetails":saveStockDetails($conn);break;
        case "getStudentID":getStudentID($conn);break;
        case "getStaffID":getStaffID($conn);break;
       
		case "getStockItemTransactions":getStockItemTransactions($conn);break;
        case "delete":delete($conn);break;
		case "delete1":delete1($conn);break;
		
		case "getItem_Categories":getItem_Categories($conn);break;
		case "getItemIDName":getItemIDName($conn);break;
		case "getStockItemTransactionDetails":getStockItemTransactionDetails($conn);break;
		case "GetCpUnit":GetCpUnit($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		
        $transid  = ($_POST['transid'] == 'undefined' || $_POST['transid'] == '') ? 0 : $_POST['transid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtBillDate  = ($_POST['txtBillDate'] == 'undefined' || $_POST['txtBillDate'] == '') ? '' : $_POST['txtBillDate'];
        $txtTRANSTYPE  = ($_POST['txtTRANSTYPE'] == 'undefined' || $_POST['txtTRANSTYPE'] == '') ? '' : $_POST['txtTRANSTYPE'];
        $txtTransactionFor  = ($_POST['txtTransactionFor'] == 'undefined' || $_POST['txtTransactionFor'] == '') ? '' : $_POST['txtTransactionFor'];
        $tranforid  = ($_POST['tranforid'] == 'undefined' || $_POST['tranforid'] == '') ? 0 : $_POST['tranforid'];
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
       
		
		$actionid = $transid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtTRANSTYPE == '')throw new Exception("Please Select Transaction Type.");
		
		
		// $sql = "SELECT * FROM ITEM_STOCK WHERE STOCKTYPE='$txtStype' AND transid=$transid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [STOCK_ITEM_TRANSACTIONS_SP] $actionid,$transid,$ddlLocation,'$txtBillDate','$txtTRANSTYPE','$txtTransactionFor','$tranforid','$txtRemark',$userid";
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			// throw new Exception($query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$row=sqlsrv_fetch_array($stmt);
				$data['TRANSID']=(int)$row['TRANSID'];
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($stockid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Item Category already exists.';
		
		// }
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



// save function 2 for Item Stock Details 

 function saveStockDetails($mysqli){
	try
	{
	   $data = array();
	   global $userid;
      
	   $transdetid  = ($_POST['transdetid'] == 'undefined' || $_POST['transdetid'] == '' ||$_POST['transdetid'] == 'null') ? 0 : $_POST['transdetid'];
	   $transid  = ($_POST['transid'] == 'undefined' || $_POST['transid'] == ''||$_POST['transid'] == 'null') ? 0 : $_POST['transid'];
	   $STOCKDETID  = ($_POST['STOCKDETID'] == 'undefined' || $_POST['STOCKDETID'] == '' || $_POST['STOCKDETID'] == 'null') ? 0 : $_POST['STOCKDETID'];
	   $itemID  = ($_POST['itemID'] == 'undefined' || $_POST['itemID'] == '' || $_POST['itemID'] == 'null') ? 0 : $_POST['itemID'];
	   $txtRate  = ($_POST['txtRate'] == 'undefined' || $_POST['txtRate'] == ''||$_POST['txtRate'] == 'null') ? 0 : $_POST['txtRate'];
	   $txtDiscount  = ($_POST['txtDiscount'] == 'undefined' || $_POST['txtDiscount'] == ''||$_POST['txtDiscount'] == 'null') ? 0 : $_POST['txtDiscount'];
	   $txtAmmount  = ($_POST['txtAmmount'] == 'undefined' || $_POST['txtAmmount'] == ''||$_POST['txtAmmount'] == 'null') ? 0 : $_POST['txtAmmount'];
	   $txtFreeQty  = ($_POST['txtFreeQty'] == 'undefined' || $_POST['txtFreeQty'] == ''||$_POST['txtFreeQty'] == 'null') ? 0 : $_POST['txtFreeQty'];
	   $txtNetAmmount  = ($_POST['txtNetAmmount'] == 'undefined' || $_POST['txtNetAmmount'] == ''||$_POST['txtNetAmmount'] == 'null') ? 0 : $_POST['txtNetAmmount'];
	   $txtRemarks  = ($_POST['txtRemarks'] == 'undefined' || $_POST['txtRemarks'] == ''||$_POST['txtRemarks'] == 'null') ? '' : $_POST['txtRemarks'];
	  
	   $actionid = $transdetid == 0 ? 1 : 2;

	   if($STOCKDETID == 0){throw new Exception("STOCKDETID Invalid.");}
	   if($itemID == 0){throw new Exception("ITEMID Invalid.");}
	   if($transid == 0){throw new Exception("TRANSID Invalid.");}
	   
	   
	   // $sql = "SELECT * FROM ITEM_STOCK WHERE STOCKTYPE='$txtStype' AND STOCKID=$stockid AND ISDELETED=0";
	   // $row_count = unique($sql);
	   
	   
	   // if($row_count == 0)
	   // {
		   $query="EXEC [STOCK_ITEM_TRANSACTIONS_DETAILS_SP] $actionid, $transdetid ,$transid,$STOCKDETID,$itemID,$txtRate,$txtDiscount,$txtAmmount,$txtFreeQty,$txtNetAmmount,'$txtRemarks',$userid";
		   // $data['$query'] = $query;
		   // echo json_encode($data);exit;
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['query'] = $query;
		   }
		   else
		   {
			  
			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($transdetid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   // }
	   // else
	   // {
		   
	   // 	$data['success'] = false;
	   // 	$data['message'] = 'Item Category already exists.';
	   
	   // }
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



/*============ Get STOCK_ITEM_TRANSACTIONS =============*/ 
function getStockItemTransactions($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT TRANSID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=I.LOCID)[LOCATION],
		CASE WHEN (TRANSDATE='1900-01-01 00:00:00.000' OR TRANSDATE IS NULL OR TRANSDATE='')
			THEN '-'
			ELSE CONVERT(VARCHAR,TRANSDATE,105)
		END TRANSDATE, CONVERT(VARCHAR,TRANSDATE,107)TRANSDATE_ED 
		,TRANSTYPE,TRANSFOR,TRANSFORID,
		CASE WHEN TRANSFOR = 'STAFF' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME  FROM USERS WHERE [UID]=I.TRANSFORID)
			WHEN TRANSFOR ='STUDENT'
			THEN (SELECT FIRSTNAME+' '+LASTNAME  FROM REGISTRATIONS WHERE REGID=I.TRANSFORID)
			ELSE 'For Office Use'
		END TRANSACTION_FOR,REMARKS
		FROM STOCK_ITEM_TRANSACTIONS I WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY TRANSDATE DESC";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TRANSID'] = (int) $row['TRANSID'];
				
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
/*============ Get STOCK_ITEM_TRANSACTIONS =============*/ 






/*============ Get STOCK_ITEM_TRANSACTIONS Details =============*/ 
function getStockItemTransactionDetails($mysqli){
	try
	{
		$transid  = ($_POST['transid'] == 'undefined' || $_POST['transid'] == '') ? 0 : $_POST['transid'];
		$query = "SELECT TRANSDETID,TRANSID,(SELECT TRANSFOR FROM STOCK_ITEM_TRANSACTIONS WHERE  TRANSID=ISD.TRANSID)TRANSFOR,
		(SELECT ICATID FROM ITEM_MASTER WHERE  ITEMID=ISD.ITEMID)ICATID,
		ITEMID,(SELECT ITEM FROM ITEM_MASTER WHERE  ITEMID=ISD.ITEMID)ITEM,
		RATE,DISCOUNT,AMOUNT,QTY,NETAMOUNT ,REMARKS
		FROM STOCK_ITEM_TRANSACTIONS_DETAILS ISD where TRANSID=$transid AND isdeleted=0 order by TRANSDETID DESC";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TRANSDETID'] = (int) $row['TRANSDETID'];
				$row['ICATID'] = (string) $row['ICATID'];
				
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
/*============ Get STOCK_ITEM_TRANSACTIONS Details =============*/ 





/*============ Get TRANSACTION_FOR ID Name =============*/ 
function GetCpUnit($mysqli){
	try
	{
		$data = array();
		$TRANSTYPE  = ($_POST['TRANSTYPE'] == 'undefined' || $_POST['TRANSTYPE'] == '' ||$_POST['TRANSTYPE'] == 'null') ? '' : $_POST['TRANSTYPE'];
		$STOCKDETID  = ($_POST['STOCKDETID'] == 'undefined' || $_POST['STOCKDETID'] == '' ||$_POST['STOCKDETID'] == 'null') ? 0 : $_POST['STOCKDETID'];
		if($STOCKDETID ==0) throw new Exception('Stockid Invalid.');
		$query = "EXEC [GET_ITEM_PRICE_FOR_TRANSACTION] '$TRANSTYPE', $STOCKDETID";

		$data['qry']=$query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$row['RATE'] = (float) $row['RATE'];
			$data['data'] = $row;
			
			$data['success'] = true;
		}else{
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


/*============ Get STAFF NAME ID  =============*/ 
function getStaffID($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT UID,FIRSTNAME,LASTNAME ,USERROLE FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY FIRSTNAME ";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['UID'] = (int) $row['UID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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



/*============ Get Item ID NAME  =============*/ 
function getItemIDName($mysqli){
	try
	{
		$data = array();
		$Item_Categories  = ($_POST['Item_Categories'] == 'undefined' || $_POST['Item_Categories'] == ''||$_POST['Item_Categories'] == 'null') ? 0 : $_POST['Item_Categories'];
		if($Item_Categories == 0) throw new Exception('Item Category ID Invalid.');
				
		$query="EXEC [GET_ITEMS_FOR_TRANSACTION] $Item_Categories";
		$data['query']=$query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STOCKDETID'] = (int) $row['STOCKDETID'];
				
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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


/*============ Get get Item_Categories  =============*/ 
function getItem_Categories($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT ICATID,ITEMCATEGORY FROM ITEM_CATEGORIES WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY ITEMCATEGORY";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ICATID'] = (int) $row['ICATID'];
				
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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


/*============ Get STAFF NAME ID  =============*/ 
function getStudentID($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT REGID,FIRSTNAME,LASTNAME FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND LOCATIONID=$ddlLocation ORDER BY FIRSTNAME";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
				
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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










/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $transid = ($_POST['transid'] == 'undefined' || $_POST['transid'] == '') ? 0 : $_POST['transid'];  
            $query = "EXEC [STOCK_ITEM_TRANSACTIONS_SP] 3,$transid,0,'','','',0,'',$userid;
			UPDATE STOCK_ITEM_TRANSACTIONS_DETAILS SET ISDELETED=1, DELETEID=$userid,DELETEDATE=GETDATE() WHERE TRANSID= $transid";
						
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
				$data['message'] = 'Record successfully deleted';
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



/* =========== Delete ITEM_STOCK_DETAILS =========== */ 
function delete1($mysqli){
	try{   
			global $userid;
			$data = array();     
            $transdetid = ($_POST['transdetid'] == 'undefined' || $_POST['transdetid'] == '') ? 0 : $_POST['transdetid'];  
			if($transdetid==0) throw new Exception('TRANSDETID Invalid.');
            $query = "EXEC [STOCK_ITEM_TRANSACTIONS_DETAILS_SP] 3,$transdetid,0,0,0,0,0,0,0,0,'',$userid";
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
				$data['message'] = 'Record successfully deleted';
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







