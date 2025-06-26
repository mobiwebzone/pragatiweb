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
        case "getCampaign":getCampaign($conn);break;
        case "delete":delete($conn);break;
		
		// ============ GROUP ===========
        case "saveDataGeoLoc":saveDataGeoLoc($conn);break;
        case "getCampGeoLocation":getCampGeoLocation($conn);break;
        case "deleteGeoLoc":deleteGeoLoc($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CAMPAIGN SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$campid  = ($_POST['campid'] == 'undefined' || $_POST['campid'] == '') ? 0 : $_POST['campid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtCampaign  = $_POST['txtCampaign'] == 'undefined' ? '' : $_POST['txtCampaign'];
	$txtCampaign = str_replace("'","''",$txtCampaign);
	$txtCampDesc  = $_POST['txtCampDesc'] == 'undefined' ? '' : $_POST['txtCampDesc'];
	$txtCampDesc = str_replace("'","''",$txtCampDesc);
	$ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
	$txtStartDate  = $_POST['txtStartDate'] == 'undefined' ? '' : $_POST['txtStartDate'];
	$txtEndDate  = $_POST['txtEndDate'] == 'undefined' ? '' : $_POST['txtEndDate'];
	
	$actionid = $campid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
	if($txtCampaign == ''){throw new Exception("Please Enter 'Campaign Name'.");}
	if($txtCampDesc == ''){throw new Exception("Please Enter 'Campaign Description'.");}
	if($ddlProduct == 0){throw new Exception("Please Select 'Product Name'.");}
	if($txtStartDate == ''){throw new Exception("Please Enter 'Start Date'.");}
	if($txtEndDate == ''){throw new Exception("Please Enter 'Start Date'.");}


	$sql = "SELECT * FROM MKT_CAMPAIGN_MASTER WHERE LOCID=$ddlLocation AND CAMPAIGN='$txtCampaign' AND PRODUCTID=$ddlProduct 
		AND CONVERT(DATE, STARTDATE, 105)='$txtStartDate' AND CONVERT(DATE, ENDDATE, 105)='$txtEndDate' AND CAMPID!=$campid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CAMPAIGN_MASTER_SP] $actionid,$campid,$ddlLocation,'$txtCampaign','$txtCampDesc',$ddlProduct,'$txtStartDate','$txtEndDate',$userid";
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
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_CAMPID'] = (int)$row['CAMPID'];

			$data['success'] = true;
			if(!empty($campid))$data['message'] = 'Campaign successfully updated.';
			else $data['message'] = 'Campaign successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Campaign already exists';
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
 // =============== SAVE DATA ==============






/*============ GET CAMPAIGN =============*/ 
function getCampaign($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT CAMPID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=MCM.LOCID)[LOCATION],
		CAMPAIGN,CAMPAIGN_DESC,PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=MCM.PRODUCTID)PRODUCT,
		CONVERT(VARCHAR,STARTDATE,106)STARTDATE,
		CONVERT(VARCHAR,ENDDATE,106)ENDDATE,
		ISNULL((SELECT (SELECT GEOLOCATION FROM MKT_GEOLOCATIONS WHERE ISDELETED=0 AND GEOLOCID=MCL.GEOLOCID) + ' | ' FROM MKT_CAMPAIGN_LOCATIONS MCL WHERE ISDELETED=0 AND CAMPID=MCM.CAMPID FOR XML PATH('')),'')CAMP_LOCATION
		FROM MKT_CAMPAIGN_MASTER MCM
		WHERE ISDELETED=0 AND LOCID=$ddlLocation 
		ORDER BY CONVERT(DATE,STARTDATE,105)DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['CAMP_LOCATION'] = rtrim($row['CAMP_LOCATION'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Campaign data not found.';
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
/*============ GET CAMPAIGN =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CAMPID = ($_POST['CAMPID'] == 'undefined' || $_POST['CAMPID'] == '') ? 0 : $_POST['CAMPID'];
			if($CAMPID == 0){throw new Exception('CAMPID Error.');}
			$delQuery = "EXEC [MKT_CAMPAIGN_MASTER_SP] 3,$CAMPID,0,'','',0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Campagin successfully deleted.';
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
/* =========== DELETE =========== */ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CAP GEO LOCATION SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE DATA ==================
function saveDataGeoLoc($mysqli){
	try
	{
	global $userid;
	$data = array();

	$camplocid  = ($_POST['camplocid'] == 'undefined' || $_POST['camplocid'] == '') ? 0 : $_POST['camplocid'];
	$campid  = ($_POST['campid'] == 'undefined' || $_POST['campid'] == '') ? 0 : $_POST['campid'];
	$ddlGeoLocation  = ($_POST['ddlGeoLocation'] == 'undefined' || $_POST['ddlGeoLocation'] == '') ? 0 : $_POST['ddlGeoLocation'];

	$actionid = $camplocid == 0 ? 1 : 2;

	if($campid == 0){throw new Exception("CAMPID Not Found.");}
	if($ddlGeoLocation == 0){throw new Exception("Please Select 'Geographic Location'.");}


	$sql = "SELECT * FROM MKT_CAMPAIGN_LOCATIONS WHERE CAMPID=$campid AND GEOLOCID=$ddlGeoLocation AND CAMPLOCID!=$camplocid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CAMPAIGN_LOCATIONS_SP] $actionid,$camplocid,$campid,$ddlGeoLocation,$userid";
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
			$data['success'] = true;
			if(!empty($camplocid))$data['message'] = 'Campagin location successfully updated.';
			else $data['message'] = 'Campagin location successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Campagin location already exists';
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
 // =============== SAVE DATA ==============






/*============ GET CAMP GEO LOCATION =============*/ 
function getCampGeoLocation($mysqli){
	try
	{
		$data = array();
		$campid = ($_POST['campid'] == 'undefined' || $_POST['campid'] == '') ? 0 : $_POST['campid'];
		if($campid == 0) throw new Exception('CAMPID Not Found.');

		$query = "SELECT CAMPLOCID,GEOLOCID,
			(SELECT GEOLOCATION FROM MKT_GEOLOCATIONS WHERE GEOLOCID=MCL.GEOLOCID)GEOLOCATION 
			FROM MKT_CAMPAIGN_LOCATIONS MCL
			WHERE ISDELETED=0 AND CAMPID=$campid ORDER BY GEOLOCATION";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Camp Locations not found.';
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET CAMP GEO LOCATION =============*/ 






/* =========== DELETE =========== */ 
function deleteGeoLoc($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CAMPLOCID = ($_POST['CAMPLOCID'] == 'undefined' || $_POST['CAMPLOCID'] == '') ? 0 : $_POST['CAMPLOCID'];
			if($CAMPLOCID == 0){throw new Exception('CAMPLOCID Error.');}
			$delQuery = "EXEC [MKT_CAMPAIGN_LOCATIONS_SP] 3,$CAMPLOCID,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Campagin location successfully deleted.';
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
/* =========== DELETE =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







