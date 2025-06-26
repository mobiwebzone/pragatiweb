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
        case "getItemmaster":getItemmaster($conn);break;
        case "getPubliManu":getPubliManu($conn);break;
		case "getItemStorageMaster":getItemStorageMaster($conn);break;
		case "getItemcategory":getItemcategory($conn);break;
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


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
        $itemid  = ($_POST['itemid'] == 'undefined' || $_POST['itemid'] == '') ? 0 : $_POST['itemid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlItemCategory = ($_POST['ddlItemCategory'] == 'undefined' || $_POST['ddlItemCategory'] == '') ? 0 : $_POST['ddlItemCategory'];
		$txtItem  = ($_POST['txtItem'] == 'undefined' || $_POST['txtItem'] == '') ? '' : $_POST['txtItem'];
        $txtDesc  = ($_POST['txtDesc'] == 'undefined' || $_POST['txtDesc'] == '') ? '' : $_POST['txtDesc'];
		$ddlPubliManu  = ($_POST['ddlPubliManu'] == 'undefined' || $_POST['ddlPubliManu'] == '') ? 0 : $_POST['ddlPubliManu'];
		$txtIsbn_Model  = ($_POST['txtIsbn_Model'] == 'undefined' || $_POST['txtIsbn_Model'] == '') ? '' : $_POST['txtIsbn_Model'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		$ddlItemMasterStorage  = ($_POST['ddlItemMasterStorage'] == 'undefined' || $_POST['ddlItemMasterStorage'] == '') ? 0 : $_POST['ddlItemMasterStorage'];
		$actionid = $itemid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtItem == '')throw new Exception("Please Enter Item  Name.");
	
		
		$sql = "SELECT * FROM ITEM_MASTER WHERE LOCID=$ddlLocation AND ITEM='$txtItem' AND ITEMID !=$itemid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [ITEM_MASTER_SP] $actionid,$itemid,$ddlLocation,$ddlItemCategory,'$txtItem','$txtDesc',$ddlPubliManu,'$txtIsbn_Model','$txtRemark',$ddlItemMasterStorage,$userid";
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
				if(!empty($itemid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Item already exists.';
			echo json_encode($data);exit;
		}

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
 


/*============ Get Item Master =============*/ 
function getItemmaster($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT ITEMID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=IM.LOCID)[LOCATION],
		ICATID,STMID,(SELECT STORAGE FROM ITEM_STORAGE_MASTER WHERE STMID=IM.STMID )STORAGE,
		ISNULL((SELECT ITEMCATEGORY FROM ITEM_CATEGORIES WHERE ICATID=IM.ICATID),'')ITEMCATEGORY,
		ITEM,ITEMDESC,PUBMANID,
		ISNULL((SELECT PUBLISHER_MANUFACTURER FROM PUBLISHER_MANUFACTURER_MASTER WHERE PUBMANID=IM.PUBMANID),'')PUBLISHER_MANUFACTURER,
		ISBN_MODEL_NO,REMARKS  
		FROM ITEM_MASTER IM WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY LOCATION,ITEM";

		$data = array();
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




 
/*============ Get Item Stock Master STMID =============*/ 
function getItemStorageMaster($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT STMID,STORAGE FROM ITEM_STORAGE_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY STMID ";

		$data = array();
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
 function getPubliManu($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT PUBMANID,PUBLISHER_MANUFACTURER FROM PUBLISHER_MANUFACTURER_MASTER WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY PUBLISHER_MANUFACTURER ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PUBMANID'] = (int) $row['PUBMANID'];
				
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




/*============ Get Item category =============*/ 
function getItemcategory($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT ICATID,ITEMCATEGORY  FROM ITEM_CATEGORIES WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY ITEMCATEGORY";

		$data = array();
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $itemid = ($_POST['itemid'] == 'undefined' || $_POST['itemid'] == '') ? 0 : $_POST['itemid'];  
			  
			$stmt=sqlsrv_query($mysqli, "EXEC [ITEM_MASTER_SP] 3,$itemid,'' ,'','', '','','','',$userid");
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







