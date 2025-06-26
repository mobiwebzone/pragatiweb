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
        case "getChannels":getChannels($conn);break;
        case "delete":delete($conn);break;
		
		// ============ GROUP ===========
        case "saveDataGroup":saveDataGroup($conn);break;
        case "getChannelGroups":getChannelGroups($conn);break;
        case "deleteGroup":deleteGroup($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$mchid  = ($_POST['mchid'] == 'undefined' || $_POST['mchid'] == '') ? 0 : $_POST['mchid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtChannel  = $_POST['txtChannel'] == 'undefined' ? '' : $_POST['txtChannel'];
	$txtChannel = str_replace("'","''",$txtChannel);
	$txtDesc  = $_POST['txtDesc'] == 'undefined' ? '' : $_POST['txtDesc'];
	$txtDesc = str_replace("'","''",$txtDesc);
	
	$actionid = $mchid == 0 ? 1 : 2;

	if($txtChannel == ''){throw new Exception("Please Enter 'Channel Name'.");}
	if($txtDesc == ''){throw new Exception("Please Enter 'Channel Description'.");}


	$sql = "SELECT * FROM MKT_CHANNELS WHERE LOCID=$ddlLocation AND CHANNEL='$txtChannel' AND MCHID!=$mchid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CHANNELS_SP] $actionid,$mchid,$ddlLocation,'$txtChannel','$txtDesc',$userid";
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
			$data['GET_MCHID'] = (int)$row['MCHID'];

			$data['success'] = true;
			if(!empty($mchid))$data['message'] = 'Channel successfully updated.';
			else $data['message'] = 'Channel successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Chennel already exists';
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






/*============ GET CHANNELS =============*/ 
function getChannels($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT MCHID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=MC.LOCID)[LOCATION],CHANNEL,DESCR,
		ISNULL((SELECT CHANNELGROUP + ' | ' FROM MKT_CHANNEL_GROUPS WHERE MCHID=MC.MCHID AND ISDELETED=0 FOR XML PATH('')),'')GROUPS 
		FROM MKT_CHANNELS MC WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY CHANNEL";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['GROUPS'] = rtrim($row['GROUPS'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Channels data not found.';
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
/*============ GET CHANNELS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $MCHID = ($_POST['MCHID'] == 'undefined' || $_POST['MCHID'] == '') ? 0 : $_POST['MCHID'];
			if($MCHID == 0){throw new Exception('MCHID Error.');}
			$delQuery = "EXEC [MKT_CHANNELS_SP] 3,$MCHID,0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Channel successfully deleted.';
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





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% GROUP SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE GROUP DATA ==================
function saveDataGroup($mysqli){
	try
	{
	global $userid;
	$data = array();

	$mchgid  = ($_POST['mchgid'] == 'undefined' || $_POST['mchgid'] == '') ? 0 : $_POST['mchgid'];
	$mchid  = ($_POST['mchid'] == 'undefined' || $_POST['mchid'] == '') ? 0 : $_POST['mchid'];
	$txtGroup  = $_POST['txtGroup'] == 'undefined' ? '' : $_POST['txtGroup'];
	$txtGroup = str_replace("'","''",$txtGroup);
	$txtGroupLink  = $_POST['txtGroupLink'] == 'undefined' ? '' : $_POST['txtGroupLink'];
	$txtGroupLink = str_replace("'","''",$txtGroupLink);
	
	$actionid = $mchgid == 0 ? 1 : 2;

	if($mchid == 0){throw new Exception("MCHID Not Found.");}
	if($txtGroup == ''){throw new Exception("Please Enter 'Group Name'.");}
	if($txtGroupLink == ''){throw new Exception("Please Enter 'Group Link'.");}


	$sql = "SELECT * FROM MKT_CHANNEL_GROUPS WHERE MCHID=$mchid AND CHANNELGROUP='$txtGroup' AND CHANNELLINK='$txtGroupLink' AND MCHGID!=$mchgid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_CHANNEL_GROUPS_SP] $actionid,$mchgid,$mchid,'$txtGroup','$txtGroupLink',$userid";
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
			if(!empty($mchgid))$data['message'] = 'Group successfully updated.';
			else $data['message'] = 'Group successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Group already exists';
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
 // =============== SAVE GROUP DATA ==============






/*============ GET CHANNEL GROUPS =============*/ 
function getChannelGroups($mysqli){
	try
	{
		$data = array();
		$mchid = ($_POST['mchid'] == 'undefined' || $_POST['mchid'] == '') ? 0 : $_POST['mchid'];
		if($mchid == 0) throw new Exception('MCHID Not Found.');

		$query = "SELECT MCHGID,CHANNELGROUP,CHANNELLINK FROM MKT_CHANNEL_GROUPS WHERE ISDELETED=0 AND MCHID=$mchid ORDER BY CHANNELGROUP";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Channel groups not found.';
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
/*============ GET CHANNEL GROUPS =============*/ 






/* =========== DELETE GROUP =========== */ 
function deleteGroup($mysqli){
	try{   
			global $userid;
			$data = array();     
            $MCHGID = ($_POST['MCHGID'] == 'undefined' || $_POST['MCHGID'] == '') ? 0 : $_POST['MCHGID'];
			if($MCHGID == 0){throw new Exception('MCHGID Error.');}
			$delQuery = "EXEC [MKT_CHANNEL_GROUPS_SP] 3,$MCHGID,0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Group successfully deleted.';
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
/* =========== DELETE GROUP =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







