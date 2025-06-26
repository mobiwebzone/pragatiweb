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
        case "getInventoryChapters":getInventoryChapters($conn);break;
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

		$chapid = ($_POST['chapid'] == 'undefined' || $_POST['chapid'] == '') ? 0 : $_POST['chapid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$ddlInventory = ($_POST['ddlInventory'] == 'undefined' || $_POST['ddlInventory'] == '') ? 0 : $_POST['ddlInventory'];
		$txtDescription = $_POST['txtDescription'] == 'undefined' ? '' : $_POST['txtDescription'];
		$txtChapterNo = ($_POST['txtChapterNo'] == 'undefined' || $_POST['txtChapterNo'] == '') ? 0 : $_POST['txtChapterNo'];
		$txtChapter = $_POST['txtChapter'] == 'undefined' ? '' : $_POST['txtChapter'];
    
		$actionid = $chapid == 0 ? 1 : 2;


		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlProduct == 0){throw new Exception("Select Product Name.");}
		if($txtChapterNo == 0){throw new Exception("Enter Chapter No.");}
		if($txtChapter == ''){throw new Exception("Enter Your Chapter Name.");}

		$sql = "SELECT * FROM INV_CHAPTERS WHERE LOCID=$ddlLocation AND PRODUCTID=$ddlProduct AND CHAPNO=$txtChapterNo AND
			CHAPTER='$txtChapter' AND CHAPID!=$chapid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [INV_CHAPTERS_SP]$actionid,$chapid,$ddlLocation,$ddlProduct,$ddlInventory,'$txtDescription',$txtChapterNo,'$txtChapter',$userid";
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
				if(!empty($chapid))$data['message'] = 'Record successfully updated';
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




/* ========== GET INVENTORY CHAPTERS =========== */
 function getInventoryChapters($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT CHAPID,
		LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=IC.LOCID)[LOCATION],
		PRODUCTID,(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=IC.PRODUCTID)PRODUCT,INVID,
		ISNULL((SELECT TITLE FROM INVENTORY WHERE INVID=IC.INVID),'')INVENTORY,DESCR,CHAPNO,CHAPTER 
		FROM INV_CHAPTERS IC WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY CHAPTER";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CHAPID'] = (int) $row['CHAPID'];
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
/* ========== GET INVENTORY CHAPTERS =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CHAPID = ($_POST['CHAPID'] == 'undefined' || $_POST['CHAPID'] == '') ? 0 : $_POST['CHAPID'];  
			if($CHAPID == 0){throw new Exception('CHAPID NOT FOUND.');}
			$delQuery = "EXEC [INV_CHAPTERS_SP]3,$CHAPID,0,0,0,'',0,'',$userid";
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
		$data['success'] = false . $query;
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







