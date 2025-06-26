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
        case "getRevenue":getRevenue($conn);break;
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
					$RHID = $emapData[1];
					$AMOUNT = $emapData[2];
					$PMID = $emapData[3];
					$NARRATION = $emapData[4];
					$CAPITALIZE = $emapData[5] == 'N' ? 0 : 1;
					$ACCRUED = $emapData[6] == 'N' ? 0 : 1;

					// GET RSHID
					$RSHID=0;
					$GET_RSHID = "SELECT TOP 1 RSHID FROM REVENUE_SUB_HEADS WHERE ISDELETED=0 AND RHID=$RHID";
					$count = unique($GET_RSHID);
					if($count > 0){
						$stmt_RSHID=sqlsrv_query($mysqli, $GET_RSHID);
						$row_RSHID = sqlsrv_fetch_array($stmt_RSHID, SQLSRV_FETCH_ASSOC);
						$RSHID = $row_RSHID['RSHID'];
					}

					$queryExp="EXEC [REVENUE_SP] 1,0,'$DATE',$ddlLocation,$RHID,$RSHID,$AMOUNT,$PMID,'$NARRATION',$CAPITALIZE,$ACCRUED,$userid";
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

	$revid = ($_POST['revid'] == 'undefined' || $_POST['revid'] == '') ? 0 : $_POST['revid'];
	$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtDate = $_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
	$ddlRevHead = ($_POST['ddlRevHead'] == 'undefined' || $_POST['ddlRevHead'] == '') ? 0 : $_POST['ddlRevHead'];
	$ddlRevSubHead = ($_POST['ddlRevSubHead'] == 'undefined' || $_POST['ddlRevSubHead'] == '') ? 0 : $_POST['ddlRevSubHead'];
	$txtAmount = ($_POST['txtAmount'] == 'undefined' || $_POST['txtAmount'] == '') ? 0 : $_POST['txtAmount'];
	$ddlPaymentSource = ($_POST['ddlPaymentSource'] == 'undefined' || $_POST['ddlPaymentSource'] == '') ? 0 : $_POST['ddlPaymentSource'];
	$txtNarration = $_POST['txtNarration'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtNarration']);
	$chkCapitalize = ($_POST['chkCapitalize'] == 'undefined' || $_POST['chkCapitalize'] == '' || $_POST['chkCapitalize'] == '0') ? 0 : 1;
	$chkAccrued = ($_POST['chkAccrued'] == 'undefined' || $_POST['chkAccrued'] == '' || $_POST['chkAccrued'] == '0') ? 0 : 1;
	
	$actionid = $revid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($ddlRevHead == 0){throw new Exception("Please Select 'Revenue Head Name'.");}
	if($ddlRevSubHead == 0){throw new Exception("Please Select 'Revenue Sub Head Name'.");}
	if($txtAmount == 0){throw new Exception("Please Enter 'Amount'.");}
	if($ddlPaymentSource == 0){throw new Exception("Please Select 'Payment Source'.");}


	// $sql = "SELECT * FROM REVENUE WHERE LOC_ID=$ddlLocation AND EHID=$ddlRevHead AND ESHID=$ddlRevSubHead AND AMOUNT AND EHID!=$ehid AND ISDELETED=0";
	// $row_count = unique($sql);
	// if($row_count == 0)
	// {
		$query="EXEC [REVENUE_SP] $actionid,$revid,'$txtDate',$ddlLocation,$ddlRevHead,$ddlRevSubHead,$txtAmount,
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
			if(!empty($revid))$data['message'] = 'Revenue successfully updated.';
			else $data['message'] = 'Revenue successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Revenue Head already exists';
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






/*============ GET REVENUE =============*/ 
function getRevenue($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT REVID,CONVERT(VARCHAR,REVDATE,106)REVDATE,LOC_ID,RHID,
		(SELECT REVENUE_HEAD FROM REVENUE_HEADS WHERE RHID=E.RHID)REVENUE_HEAD,RSHID,
		(SELECT REVENUE_SUB_HEAD FROM REVENUE_SUB_HEADS WHERE RSHID=E.RSHID AND ISDELETED=0)REVENUE_SUB_HEAD,AMOUNT,REVFROM,
		(SELECT (SELECT BANKNAME FROM BANKS WHERE BANKID=BA.BANKID)+' ('+ACCOUNTNO+')' FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.REVFROM)REVFROM,
		(SELECT BANKACCID FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.REVFROM)REVFROM_BANKACCID,
		NARRATION,CAPITALIZE,ACCRUED
		FROM REVENUE E WHERE ISDELETED=0 AND LOC_ID=$ddlLocation
		ORDER BY CONVERT(DATE,REVDATE,105) DESC,REVENUE_HEAD ASC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Revenue Not Found.';
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
/*============ GET REVENUE =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $REVID = ($_POST['REVID'] == 'undefined' || $_POST['REVID'] == '') ? 0 : $_POST['REVID'];
			if($REVID == 0){throw new Exception('REVID Error.');}
			$delQuery = "EXEC [REVENUE_SP] 3,$REVID,'',0,0,0,0,'','',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Revenue successfully deleted.';
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







