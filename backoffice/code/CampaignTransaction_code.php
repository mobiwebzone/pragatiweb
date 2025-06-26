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
        case "getTransactions":getTransactions($conn);break;
        case "delete":delete($conn);break;
		
		// ============ GROUP ===========
        case "saveDataTransLog":saveDataTransLog($conn);break;
        case "getTransactionLogs":getTransactionLogs($conn);break;
        case "deleteTransLog":deleteTransLog($conn);break;
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

	$mktransid  = ($_POST['mktransid'] == 'undefined' || $_POST['mktransid'] == '') ? 0 : $_POST['mktransid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$ddlCampaign  = ($_POST['ddlCampaign'] == 'undefined' || $_POST['ddlCampaign'] == '') ? 0 : $_POST['ddlCampaign'];
	$ddlChannel  = ($_POST['ddlChannel'] == 'undefined' || $_POST['ddlChannel'] == '') ? 0 : $_POST['ddlChannel'];
	$ddlChannelGroup  = ($_POST['ddlChannelGroup'] == 'undefined' || $_POST['ddlChannelGroup'] == '') ? 0 : $_POST['ddlChannelGroup'];
	$txtTarget  = ($_POST['txtTarget'] == 'undefined' || $_POST['txtTarget'] == '') ? 0 : $_POST['txtTarget'];
	$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : str_replace("'","''",$_POST['txtRemark']);

	// $txtCampDesc  = $_POST['txtCampDesc'] == 'undefined' ? '' : $_POST['txtCampDesc'];
	// $txtCampDesc = str_replace("'","''",$txtCampDesc);
	$actionid = $mktransid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
	if($ddlCampaign == 0){throw new Exception("Please Select 'Capaign Name'.");}
	if($ddlChannel == 0){throw new Exception("Please Select 'Channel Name'.");}
	if($ddlChannelGroup == 0){throw new Exception("Please Select 'Channel group Name'.");}
	if($txtTarget == 0){throw new Exception("Please Enter 'Target' Minimum 1.");}


	$sql = "SELECT * FROM MKT_CAMPAIGN_TRANSACTIONS WHERE LOCID=$ddlLocation AND CAMPID=$ddlCampaign AND MCHID=$ddlChannel AND MCHGID=$ddlChannelGroup AND [TARGET]=$txtTarget
			AND MKTRANSID!=$mktransid AND ISDELETED=0";

// $data['sql'] = $sql;
// echo json_encode($data);exit;

	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CAMPAIGN_TRANSACTIONS_SP] $actionid,$mktransid,$ddlLocation,$ddlCampaign,$ddlChannel,$ddlChannelGroup,$txtTarget,'$txtRemark',$userid";
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
			$data['GET_MKTRANSID'] = (int)$row['MKTRANSID'];

			$data['success'] = true;
			if(!empty($mktransid))$data['message'] = 'Transaction successfully updated.';
			else $data['message'] = 'Transaction successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Transaction already exists';
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






/*============ GET TRANSACTION =============*/ 
function getTransactions($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT MKTRANSID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=MCT.LOCID)[LOCATION],CAMPID,
		(SELECT CAMPAIGN FROM MKT_CAMPAIGN_MASTER WHERE CAMPID=MCT.CAMPID)CAMPAIGN,MCHID,
		(SELECT CHANNEL FROM MKT_CHANNELS WHERE MCHID=MCT.MCHID)CHANNEL,MCHGID,
		(SELECT CHANNELGROUP FROM MKT_CHANNEL_GROUPS WHERE MCHGID=MCT.MCHGID)CHANNELGROUP,[TARGET],
		(SELECT COUNT(*) FROM MKT_CAMPAIGN_TRANSACTIONS_LOG WHERE MKTRANSID=MCT.MKTRANSID AND ISDELETED=0)TARGET_COMPLETE,
		ISNULL((SELECT ACTIVITY+' ('+CONVERT(VARCHAR,ACTIVITY_DATE,106)+'), ' FROM MKT_CAMPAIGN_TRANSACTIONS_LOG WHERE MKTRANSID=MCT.MKTRANSID AND ISDELETED=0 
				ORDER BY CONVERT(DATE,ACTIVITY_DATE,106) DESC FOR XML PATH('')),
		'')ACTIVIY,REMARKS
		FROM MKT_CAMPAIGN_TRANSACTIONS MCT WHERE ISDELETED=0 AND LOCID=$ddlLocation";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['ACTIVIY'] = rtrim($row['ACTIVIY'],', ');
				$row['ACTIVIY'] = str_replace(', ', ",\n", $row['ACTIVIY']);
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Transactions not found.';
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
/*============ GET TRANSACTION =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $MKTRANSID = ($_POST['MKTRANSID'] == 'undefined' || $_POST['MKTRANSID'] == '') ? 0 : $_POST['MKTRANSID'];
			if($MKTRANSID == 0){throw new Exception('MKTRANSID Error.');}
			$delQuery = "EXEC [MKT_CAMPAIGN_TRANSACTIONS_SP] 3,$MKTRANSID,0,0,0,0,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Transaction successfully deleted.';
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
/* =========== DELETE =========== */ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% TRANSACTION LOGS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE DATA ==================
function saveDataTransLog($mysqli){
	try
	{
	global $userid;
	$data = array();

	$mklogid  = ($_POST['mklogid'] == 'undefined' || $_POST['mklogid'] == '') ? 0 : $_POST['mklogid'];
	$mktransid  = ($_POST['mktransid'] == 'undefined' || $_POST['mktransid'] == '') ? 0 : $_POST['mktransid'];
	$ddlActivity  = ($_POST['ddlActivity'] == 'undefined' || $_POST['ddlActivity'] == '') ? '' : $_POST['ddlActivity'];
	$txtActivityDate  = $_POST['txtActivityDate'] == 'undefined' ? '' : $_POST['txtActivityDate'];
	$txtActivityCount  = ($_POST['txtActivityCount'] == 'undefined' || $_POST['txtActivityCount'] == '') ? 0 : $_POST['txtActivityCount'];
	$txtActivityRemark  = ($_POST['txtActivityRemark'] == 'undefined' || $_POST['txtActivityRemark'] == '') ? '': str_replace("'","''", $_POST['txtActivityRemark']);

	$actionid = $mklogid == 0 ? 1 : 2;

	if($mktransid == 0){throw new Exception("MKTRANSID Not Found.");}
	if($ddlActivity == ''){throw new Exception("Please Select 'Activity'.");}
	if($txtActivityDate == ''){throw new Exception("Please Select 'Activity Date'.");}


	$sql = "SELECT * FROM MKT_CAMPAIGN_TRANSACTIONS_LOG WHERE MKTRANSID=$mktransid AND ACTIVITY='$ddlActivity' AND ACTIVITY_DATE='$txtActivityDate' 
	AND ACTIVITY_COUNT=$txtActivityCount AND ACTIVITY_REMARK='$txtActivityRemark' AND MKLOGID!=$mklogid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CAMPAIGN_TRANSACTIONS_LOG_SP] $actionid,$mklogid,$mktransid,'$ddlActivity','$txtActivityDate',$txtActivityCount,'$txtActivityRemark',$userid";
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
			if(!empty($mklogid))$data['message'] = 'Transaction log successfully updated.';
			else $data['message'] = 'Transaction log successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Transaction log already exists';
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






/*============ GET TRANSACTION LOGS LOCATION =============*/ 
function getTransactionLogs($mysqli){
	try
	{
		$data = array();
		$mktransid = ($_POST['mktransid'] == 'undefined' || $_POST['mktransid'] == '') ? 0 : $_POST['mktransid'];
		if($mktransid == 0) throw new Exception('MKTRANSID Not Found.');

		$query = "SELECT MKLOGID,ACTIVITY,CONVERT(VARCHAR,ACTIVITY_DATE,106)ACTIVITY_DATE,ACTIVITY_COUNT,ACTIVITY_REMARK 
		FROM MKT_CAMPAIGN_TRANSACTIONS_LOG
		WHERE ISDELETED=0 AND MKTRANSID=$mktransid
		ORDER BY ACTIVITY";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Transaction log not found.';
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
/*============ GET TRANSACTION LOGS LOCATION =============*/ 






/* =========== DELETE =========== */ 
function deleteTransLog($mysqli){
	try{   
			global $userid;
			$data = array();     
            $MKLOGID = ($_POST['MKLOGID'] == 'undefined' || $_POST['MKLOGID'] == '') ? 0 : $_POST['MKLOGID'];
			if($MKLOGID == 0){throw new Exception('MKLOGID Error.');}
			$delQuery = "EXEC [MKT_CAMPAIGN_TRANSACTIONS_LOG_SP] 3,$MKLOGID,0,'','',0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Transaction log successfully deleted.';
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
/* =========== DELETE =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







