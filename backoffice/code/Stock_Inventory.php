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
		case "save1":save1($conn);break;
        case "getVendorID":getVendorID($conn);break;
        case "getStockInventory":getStockInventory($conn);break;
		case "getStockID":getStockID($conn);break;
		case "getItemID":getItemID($conn);break;
		case "getItemStockDetail":getItemStockDetail($conn);break;
		case "getItemStorageMaster":getItemStorageMaster($conn);break;
        case "delete":delete($conn);break;
		case "delete1":delete1($conn);break;
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
		
		
        $stockid  = ($_POST['stockid'] == 'undefined' || $_POST['stockid'] == '') ? 0 : $_POST['stockid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtStype  = ($_POST['txtStype'] == 'undefined' || $_POST['txtStype'] == '') ? '' : $_POST['txtStype'];
        $txtDesc  = ($_POST['txtDesc'] == 'undefined' || $_POST['txtDesc'] == '') ? '' : $_POST['txtDesc'];
        $ddlVendorID  = ($_POST['ddlVendorID'] == 'undefined' || $_POST['ddlVendorID'] == '') ? 0 : $_POST['ddlVendorID'];
        $txtBillno  = ($_POST['txtBillno'] == 'undefined' || $_POST['txtBillno'] == '') ? '' : $_POST['txtBillno'];
        $txtBillDate  = ($_POST['txtBillDate'] == 'undefined' || $_POST['txtBillDate'] == '') ? '' : $_POST['txtBillDate'];
		
		$actionid = $stockid == 0 ? 1 : 2;
		
		
		if($txtStype == '')
		{throw new Exception("Please Enter STock   Name.");}
		
		
		// $sql = "SELECT * FROM ITEM_STOCK WHERE STOCKTYPE='$txtStype' AND STOCKID=$stockid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [ITEM_STOCK_SP] $actionid,$stockid,$ddlLocation,'$txtStype','$txtDesc','$ddlVendorID','$txtBillno','$txtBillDate',$userid";
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
				$data['STOCKID']=(int)$row['STOCKID'];
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

 function save1($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $stockdetailid  = ($_POST['stockdetailid'] == 'undefined' || $_POST['stockdetailid'] == '' || $_POST['stockdetailid'] == 'null') ? 0 : $_POST['stockdetailid'];
	   $stockid  = ($_POST['stockid'] == 'undefined' || $_POST['stockid'] == '' || $_POST['stockid'] == 'null') ? 0 : $_POST['stockid'];
	   $itemid  = ($_POST['itemid'] == 'undefined' || $_POST['itemid'] == '' || $_POST['itemid'] == 'null') ? 0 : $_POST['itemid'];
	   $txtUno  = ($_POST['txtUno'] == 'undefined' || $_POST['txtUno'] == '' || $_POST['txtUno'] == 'null') ? '' : $_POST['txtUno'];
	   $txtRemarks  = ($_POST['txtRemarks'] == 'undefined' || $_POST['txtRemarks'] == '' || $_POST['txtRemarks'] == 'null') ? '' : $_POST['txtRemarks'];
	   $txtQty  = ($_POST['txtQty'] == 'undefined' || $_POST['txtQty'] == '' || $_POST['txtQty'] == 'null') ? 0 : $_POST['txtQty'];
	   $txtFreeQty  = ($_POST['txtFreeQty'] == 'undefined' || $_POST['txtFreeQty'] == '' || $_POST['txtFreeQty'] == 'null') ? 0 : $_POST['txtFreeQty'];
	   $txtRate  = ($_POST['txtRate'] == 'undefined' || $_POST['txtRate'] == '' || $_POST['txtRate'] == 'null') ? 0 : $_POST['txtRate'];
	   $txtMrp  = ($_POST['txtMrp'] == 'undefined' || $_POST['txtMrp'] == '' || $_POST['txtMrp'] == 'null') ? 0 : $_POST['txtMrp'];
	   $ddlItemMasterStorage  = ($_POST['ddlItemMasterStorage'] == 'undefined' || $_POST['ddlItemMasterStorage'] == '' || $_POST['ddlItemMasterStorage'] == 'null') ? 0 : $_POST['ddlItemMasterStorage'];
	   
	   $actionid = $stockdetailid == 0 ? 1 : 2;
	   
	  

	   if($stockid == 0)
	   {throw new Exception("Stock ID Error.");}
	   
	   
	   // $sql = "SELECT * FROM ITEM_STOCK WHERE STOCKTYPE='$txtStype' AND STOCKID=$stockid AND ISDELETED=0";
	   // $row_count = unique($sql);
	   
	   
	   // if($row_count == 0)
	   // {
		   $query="EXEC [ITEM_STOCK_DETAILS_SP] $actionid, $stockdetailid ,$stockid,$itemid,'$txtUno','$txtRemarks',$txtQty,$txtFreeQty,$txtRate,$txtMrp,$ddlItemMasterStorage,$userid";
		   $data['$query'] = $query;
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
			  
			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($stockdetailid))$data['message'] = 'Record successfully updated';
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




/*============ Get Stock MAIN DATA =============*/ 
function getStockInventory($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT STOCKID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=I.LOCID)[LOCATION],
		STOCKDATE,STOCKTYPE,STOCKDESC,VENDORID,
		(SELECT VENDOR FROM VENDOR_MASTER WHERE VENDORID=I.VENDORID)VENDOR,BILLNO,
		CASE WHEN (BILLDATE='1900-01-01 00:00:00.000' OR BILLDATE IS NULL OR BILLDATE='')
			THEN '-'
			ELSE CONVERT(VARCHAR,BILLDATE,105)
 		END BILLDATE, CONVERT(VARCHAR,BILLDATE,107)BILLDATE_ED 
 		FROM ITEM_STOCK I WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY LOCATION,STOCKTYPE";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STOCKID'] = (int) $row['STOCKID'];
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
/*============ Get Stock MAIN DATA =============*/ 



/*============ Get Stock DETAILS =============*/ 
function getItemStockDetail($mysqli){
	try
	{
	    $stockid = ($_POST['stockid'] == 'undefined' || $_POST['stockid'] == '') ? 0 : $_POST['stockid'];
		$query = "SELECT STOCKDETID,STOCKID,ITEMID,(SELECT ITEM FROM ITEM_MASTER WHERE  ITEMID=ISD.ITEMID)ITEM,
		UNIQUENO,REMARKS,QTY,FREE_QTY,RATE,MRP,STMID,
		(SELECT STORAGE FROM ITEM_STORAGE_MASTER WHERE STMID=ISD.STMID)STORAGE
		FROM ITEM_STOCK_DETAILS ISD where isdeleted=0 AND STOCKID=$stockid order by ITEM";

		$data = array();
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
/*============ Get Stock DETAILS =============*/ 

 
/*============ Get Item Stock Master STMID =============*/ 
function getItemStorageMaster($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT STMID,STORAGE FROM ITEM_STORAGE_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY STMID";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STMID'] = (int) $row['STMID'];
				
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


/*============ Get Publisher Manufacturer LINKS =============*/ 
function getVendorID($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT VENDORID,VENDOR FROM VENDOR_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY VENDOR";
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['VENDORID'] = (int) $row['VENDORID'];
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



/*============ Get Stock ID =============*/ 
function getStockID($mysqli){
	try
	{
		$query = "SELECT STOCKID,STOCKDESC FROM ITEM_STOCK where isdeleted=0 order by STOCKDESC";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STOCKID'] = (int) $row['STOCKID'];
				
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





/*============ Get ITEM ID =============*/ 
function getItemID($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT ITEMID,ITEM,STMID  FROM ITEM_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY ITEM";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ITEMID'] = (int) $row['ITEMID'];
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
            $stockid = ($_POST['stockid'] == 'undefined' || $_POST['stockid'] == '') ? 0 : $_POST['stockid'];  
            $query = "EXEC [ITEM_STOCK_SP] 3,$stockid,0,'','','','','',$userid;
						UPDATE ITEM_STOCK_DETAILS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE STOCKID=$stockid";
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
            $stockdeid = ($_POST['stockdeid'] == 'undefined' || $_POST['stockdeid'] == '') ? 0 : $_POST['stockdeid'];  
            $query = "EXEC [ITEM_STOCK_DETAILS_SP] 3,$stockdeid,0,0,'','',0,0,0,0,0,$userid";
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







