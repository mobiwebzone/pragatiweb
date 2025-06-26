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
		case "saveExcelFile":saveExcelFile($conn);break;
        case "saveData":saveData($conn);break;
        case "getStatistics":getStatistics($conn);break;
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


 // =============== SAVE EXCEL DATA ==================
 function saveExcelFile($mysqli){
	try
	{
		$data = array();
		global $userid;
	
		// $ehid  = ($_POST['ehid'] == 'undefined' || $_POST['ehid'] == '') ? 0 : $_POST['ehid'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];

		// if($ehid == 0){throw new Exception("EHID Error.");}
		if($ddlLocation == 0){throw new Exception("Locid Error.");}
		if($txtUploadExcel == ''){throw new Exception("Please Select Excel File.");}

		
		$filename=$_FILES["txtUploadExcelData"]["tmp_name"];
		if($_FILES["txtUploadExcelData"]["size"] > 0)
		{
			$file = fopen($filename, "r");
			$count = 0;
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
			{ 
				$count++;
				if($count>1){
					$data['$emapData'][]=$emapData;

					$D = date_create($emapData[0]);
					$DATE = date_format($D,"Y-m-d");
					// $DATE = $emapData[0];
					$SHID = $emapData[1];
					$AMOUNT = $emapData[2];
					$PMID = $emapData[3];
					$NARRATION = $emapData[4];
					$CAPITALIZE = $emapData[5] == 'N' ? 0 : 1;
					$ACCRUED = $emapData[6] == 'N' ? 0 : 1;

					// GET SSHID
					$SSHID=0;
					$GET_SSHID = "SELECT TOP 1 SSHID FROM STATISTICS_SUB_HEADS WHERE ISDELETED=0 AND SHID=$SHID";
					$count = unique($GET_SSHID);
					if($count > 0){
						$stmt_SSHID=sqlsrv_query($mysqli, $GET_SSHID);
						$row_SSHID = sqlsrv_fetch_array($stmt_SSHID, SQLSRV_FETCH_ASSOC);
						$SSHID = $row_SSHID['SSHID'];
					}

					$queryExp="EXEC [STATISTICS_SP] 1,0,'$DATE',$ddlLocation,$SHID,$SSHID,$AMOUNT,$PMID,'$NARRATION',$CAPITALIZE,$ACCRUED,$userid";
					$stmt=sqlsrv_query($mysqli, $queryExp);
					
					if($stmt === false)
					{
						// die( print_r( sqlsrv_errors(), true));
						// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
						$data['success'] = false;
						$data['query'] = $queryExp;
					}
					else
					{


						// $data['success'] = true;
						// if(!empty($ehid))$data['message'] = 'Expense Head successfully updated.';
						// else $data['message'] = 'Expense Head successfully inserted.';
					}


					
				}  
			}
			fclose($file);
			$data['message'] = "<i class='fa fa-check'> Data successfully uploaded.";
			$data['success'] = true;
			echo json_encode($data);exit;
		}
		else {
			$data['success'] = false;
			$data['message'] = 'Upload error';
		}
		$data['message'] = 'failed Outside';
		// $data['tmp_name']=$_FILES["txtUploadExcelData"]["tmp_name"];
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
// =============== SAVE EXCEL DATA ==============


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$staid = ($_POST['staid'] == 'undefined' || $_POST['staid'] == '') ? 0 : $_POST['staid'];
	$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtDate = $_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
	$ddlStaHead = ($_POST['ddlStaHead'] == 'undefined' || $_POST['ddlStaHead'] == '') ? 0 : $_POST['ddlStaHead'];
	$ddlStaSubHead = ($_POST['ddlStaSubHead'] == 'undefined' || $_POST['ddlStaSubHead'] == '') ? 0 : $_POST['ddlStaSubHead'];
	$txtAmount = ($_POST['txtAmount'] == 'undefined' || $_POST['txtAmount'] == '') ? 0 : $_POST['txtAmount'];
	$ddlPaymentSource = ($_POST['ddlPaymentSource'] == 'undefined' || $_POST['ddlPaymentSource'] == '') ? 0 : $_POST['ddlPaymentSource'];
	$txtNarration = $_POST['txtNarration'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtNarration']);
	$chkCapitalize = ($_POST['chkCapitalize'] == 'undefined' || $_POST['chkCapitalize'] == '' || $_POST['chkCapitalize'] == '0') ? 0 : 1;
	$chkAccrued = ($_POST['chkAccrued'] == 'undefined' || $_POST['chkAccrued'] == '' || $_POST['chkAccrued'] == '0') ? 0 : 1;
	
	$actionid = $staid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($ddlStaHead == 0){throw new Exception("Please Select 'Statistics Head Name'.");}
	if($ddlStaSubHead == 0){throw new Exception("Please Select 'Statistics Sub Head Name'.");}
	if($txtAmount == 0){throw new Exception("Please Enter 'Amount'.");}
	if($ddlPaymentSource == 0){throw new Exception("Please Select 'Payment Source'.");}


	// $sql = "SELECT * FROM STATISTICS WHERE LOC_ID=$ddlLocation AND SHID=$ddlStaHead AND SSHID=$ddlStaSubHead AND AMOUNT AND STAID!=$staid AND ISDELETED=0";
	// $row_count = unique($sql);
	// if($row_count == 0)
	// {
		$query="EXEC [STATISTICS_SP] $actionid,$staid,'$txtDate',$ddlLocation,$ddlStaHead,$ddlStaSubHead,$txtAmount,
				$ddlPaymentSource,'$txtNarration',$chkCapitalize,$chkAccrued,$userid";
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
			if(!empty($staid))$data['message'] = 'Statistics successfully updated.';
			else $data['message'] = 'Statistics successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Statistics Head already exists';
	// 	echo json_encode($data);exit;
	// }

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






/*============ GET STATISTICS =============*/ 
function getStatistics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT STAID,CONVERT(VARCHAR,STADATE,106)STADATE,LOC_ID,SHID,
		(SELECT STATISTICS_HEAD FROM STATISTICS_HEADS WHERE SHID=E.SHID)STATISTICS_HEAD,SSHID,
		(SELECT STATISTICS_SUB_HEAD FROM STATISTICS_SUB_HEADS WHERE SSHID=E.SSHID AND ISDELETED=0)STATISTICS_SUB_HEAD,AMOUNT,STAFROM,
		(SELECT (SELECT BANKNAME FROM BANKS WHERE BANKID=BA.BANKID)+' ('+ACCOUNTNO+')' FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.STAFROM)STAFROM,
		(SELECT BANKACCID FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.STAFROM)STAFROM_BANKACCID,
		NARRATION,CAPITALIZE,ACCRUED
		FROM [STATISTICS] E WHERE ISDELETED=0 AND LOC_ID=$ddlLocation
		ORDER BY CONVERT(DATE,STADATE,105) DESC,STATISTICS_HEAD ASC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Statistics Not Found.';
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
/*============ GET STATISTICS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STAID = ($_POST['STAID'] == 'undefined' || $_POST['STAID'] == '') ? 0 : $_POST['STAID'];
			if($STAID == 0){throw new Exception('STAID Error.');}
			$delQuery = "EXEC [STATISTICS_SP] 3,$STAID,'',0,0,0,0,'','',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Statistics successfully deleted.';
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


?>




