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
        case "saveData":saveData($conn);break;
        case "getInventories":getInventories($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
		$invid = ($_POST['invid'] == 'undefined' || $_POST['invid'] == '') ? 0 : $_POST['invid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlMainCategory = ($_POST['ddlMainCategory'] == 'undefined' || $_POST['ddlMainCategory'] == '') ? 0 : $_POST['ddlMainCategory'];
		$ddlCategory = ($_POST['ddlCategory'] == 'undefined' || $_POST['ddlCategory'] == '') ? 0 : $_POST['ddlCategory'];
		$ddlSubCategory = ($_POST['ddlSubCategory'] == 'undefined' || $_POST['ddlSubCategory'] == '') ? 0 : $_POST['ddlSubCategory'];
		$ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$txtTitle = $_POST['txtTitle'] == 'undefined' ? '' : $_POST['txtTitle'];
		$txtDescription = $_POST['txtDescription'] == 'undefined' ? '' : $_POST['txtDescription'];
		$txtCost = ($_POST['txtCost'] == 'undefined' || $_POST['txtCost'] == '') ? 0 : $_POST['txtCost'];
		$ddlPublisher = ($_POST['ddlPublisher'] == 'undefined' || $_POST['ddlPublisher'] == '') ? 0 : $_POST['ddlPublisher'];
		$ddlCapitalExpense = $_POST['ddlCapitalExpense'] == 'undefined' ? '' : $_POST['ddlCapitalExpense'];
		$ddlInventoryType = ($_POST['ddlInventoryType'] == 'undefined' || $_POST['ddlInventoryType'] == '') ? 0 : $_POST['ddlInventoryType'];

		$actionid = $invid == 0 ? 1 : 2;


		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlMainCategory == 0){throw new Exception("Select Main Category.");}
		if($ddlCategory == 0){throw new Exception("Select Category.");}
		if($ddlSubCategory == 0){throw new Exception("Select Sub Category.");}
		if($ddlTopic == 0){throw new Exception("Select Topic Category.");}
		if($txtTitle == ''){throw new Exception("Enter Your Title.");}

		$sql = "SELECT * FROM INVENTORY WHERE LOCID=$ddlLocation AND SECID=$ddlMainCategory AND CATID=$ddlCategory AND SUBCATID=$ddlSubCategory AND TOPICID=$ddlTopic AND
		TITLE='$txtTitle' AND INVID!=$invid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [INVENTORY_SP]$actionid,$invid,$ddlLocation,$ddlInventoryType,$ddlMainCategory,$ddlCategory,$ddlSubCategory,
			$ddlTopic,$ddlProduct,'$txtTitle','$txtDescription',$txtCost,$ddlPublisher,'$ddlCapitalExpense',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($invid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/* ========== SAVE DATA =========== */




/* ========== GET INVENTORY =========== */
 function getInventories($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT INVID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=I.LOCID)[LOCATION],ITID,
		ISNULL((SELECT INVTYPE FROM INVENTORY_TYPES WHERE ITID=I.ITID),'')INVTYPE,SECID,
		(SELECT SECTION FROM SECTION_MASTER WHERE SECID=I.SECID)SECTION,CATID,
		(SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=I.CATID)CATEGORY,SUBCATID,
		(SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=I.SUBCATID)SUBCATEGORY,TOPICID,
		(SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=I.TOPICID)TOPIC,PRODUCTID,
		ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=I.PRODUCTID),'')PRODUCT,TITLE,DESCR,COST,PUBID,
		ISNULL((SELECT MAKE_PUB FROM MAKE_PUBLISHERS WHERE PUBID=I.PUBID),'')PUBLISHER,ITYPE 
		FROM INVENTORY I WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY TITLE";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['INVID'] = (int) $row['INVID'];
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
/* ========== GET INVENTORY =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $INVID = ($_POST['INVID'] == 'undefined' || $_POST['INVID'] == '') ? 0 : $_POST['INVID'];  
			if($INVID == 0){throw new Exception('INVID NOT FOUND.');}
			$delQuery = "EXEC [INVENTORY_SP]3,$INVID,0,0,0,0,0,0,0,'','',0,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







