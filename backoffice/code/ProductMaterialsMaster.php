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
        case "getProductMaterials":getProductMaterials($conn);break;
        case "getProduct":getProduct($conn);break;
        case "getProductPlans":getProductPlans($conn);break;
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


function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$matid = ($_POST['matid'] == 'undefined' || $_POST['matid'] == '') ? 0 : $_POST['matid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$ddlMaterialType = $_POST['ddlMaterialType'] == 'undefined' ? '' : $_POST['ddlMaterialType'];
		$txtPublishDT = $_POST['txtPublishDT'] == 'undefined' ? '' : $_POST['txtPublishDT'];
		$txtTitle = $_POST['txtTitle'] == 'undefined' ? '' : $_POST['txtTitle'];
		$txtBuyLink = $_POST['txtBuyLink'] == 'undefined' ? '' : $_POST['txtBuyLink'];
		$txtSEQNO = ($_POST['txtSEQNO'] == 'undefined' || $_POST['txtSEQNO'] == '') ? 0 : $_POST['txtSEQNO'];
		// === IMAGE
		$existingPictureUpload  = $_POST['existingPictureUpload'] == 'undefined' ? '' : $_POST['existingPictureUpload'];
		$fileSize = $ddlMaterialType=='VIDEO' ? 20971520 : ($ddlMaterialType=='PDF' ? 2097152 : 1048576);
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0 && $_FILES['pictureUpload']['size'] > $fileSize) throw new Exception('File size too large.');

		$pictureUpload = '';
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0){
			$ext = pathinfo($_FILES['pictureUpload']['name'],PATHINFO_EXTENSION);
			$pictureUpload .= strtolower($ddlMaterialType.'_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$pictureUpload=$existingPictureUpload;
		}
		// === IMAGE
		
		$actionid = $matid == 0 ? 1 : 2;

		if($ddlLocation == 0)throw new Exception("Select Location Name.");
		if($ddlProduct == 0)throw new Exception("Select Product Name.");
		if($ddlPlan == 0)throw new Exception("Select Product Name.");
		if($ddlMaterialType == '')throw new Exception("Select Material Type.");
		if($txtPublishDT == '')throw new Exception("Select Publish Date.");
		if($txtTitle == '')throw new Exception("Enter Title.");
		if($txtBuyLink == '')throw new Exception("Enter Buy Link.");

		$sql = "SELECT * FROM PRODUCT_MATERIAL_DISPLAY WHERE LOCID=$ddlLocation AND MATTYPE='$ddlMaterialType' AND PUBDATE='$txtPublishDT' AND PDMID=$ddlProduct AND PLANID=$ddlPlan
				AND TITLE='$txtTitle' AND MATID!=$matid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [PRODUCT_MATERIAL_DISPLAY_SP] $actionid,$matid,$ddlLocation,$ddlProduct,$ddlPlan,'$ddlMaterialType','$txtPublishDT','$txtTitle',
					'$pictureUpload','$txtBuyLink',$txtSEQNO,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				// $data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				//========== IMAGE
				if($pictureUpload != ''){

					if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/product_materials/'.$pictureUpload);
					}
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/product_materials/'.$existingPictureUpload))
						{
							unlink('../images/product_materials/'.$existingPictureUpload);
						}
					}
				}
				//========== IMAGE

				// $data['query'] = $query;
				$data['success'] = true;
				if(!empty($matid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
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


/*============ Get Data =============*/ 
function getProductMaterials($mysqli){
	try
	{
		$data = array();
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		if($ddlProduct == 0)throw new Exception('Error : PDMID Missing, Please Select Product Name.');
		if($ddlLocation == 0)throw new Exception('Error : LOCID Missing, Please Select Location Name.');
		
		$query = "SELECT MATID,PDMID,(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PM.PDMID)PRODUCT,
		PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=PM.PLANID)PLANNAME,
		LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PM.LOCID)[LOCATION],MATTYPE,
		CONVERT(VARCHAR,PUBDATE,106)PUBDATE,TITLE,MATIMG,BUYLINK,SEQNO
		FROM PRODUCT_MATERIAL_DISPLAY PM WHERE ISDELETED=0 AND PDMID=$ddlProduct AND LOCID=$ddlLocation";
		if($ddlPlan>0) $query .=" AND PLANID=$ddlPlan";
		$query .=" ORDER BY SEQNO";
		$data['query'] = $query;
		// echo json_encode($data);exit;
		$CNT = unique($query);
		if($CNT>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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



/*============ Get Product =============*/ 
function getProduct($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PDMID,DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE ISDELETED=0 ORDER BY [ORDER]";
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}
		$data['success'] = true;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}



/*============ Get Product Plans =============*/ 
function getProductPlans($mysqli){
	try
	{
		$data = array();
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		if($ddlProduct == 0) throw new Exception('Invalid ProductId.');
		$query = "SELECT PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)[PLAN] 
		FROM PRODUCT_DISPLAY_DETAIL PD WHERE PLANID>0 AND PDMID=$ddlProduct AND ISDELETED=0 AND 
		PLANID IN(SELECT PLANID FROM PLANS WHERE ISDELETED=0 AND CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,DISPLAYFROMDATE,105) AND CONVERT(DATE,DISPLAYTODATE,105))
		ORDER BY (SELECT [ORDER] FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PD.PDMID)";

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
		$MATID = ($_POST['MATID'] == 'undefined' || $_POST['MATID'] == '') ? 0 : $_POST['MATID'];
		if($MATID == 0)throw new Exception('Error : MATID Missing.');
		$delQuery = "EXEC [PRODUCT_MATERIAL_DISPLAY_SP] 3,$MATID,0,0,0,'','','','','',0,$userid";
		$stmt=sqlsrv_query($mysqli, $delQuery);
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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


