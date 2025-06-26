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
        case "getExpenses":getExpenses($conn);break;
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
					$EHID = $emapData[1];
					$AMOUNT = $emapData[2];
					$PMID = $emapData[3];
					$NARRATION = $emapData[4];
					$CAPITALIZE = $emapData[5] == 'N' ? 0 : 1;
					$ACCRUED = $emapData[6] == 'N' ? 0 : 1;

					// GET ESHID
					$ESHID=0;
					$GET_ESHID = "SELECT TOP 1 ESHID FROM EXPENSE_SUB_HEADS WHERE ISDELETED=0 AND EHID=$EHID";
					$count = unique($GET_ESHID);
					if($count > 0){
						$stmt_ESHID=sqlsrv_query($mysqli, $GET_ESHID);
						$row_ESHID = sqlsrv_fetch_array($stmt_ESHID, SQLSRV_FETCH_ASSOC);
						$ESHID = $row_ESHID['ESHID'];
					}

					$queryExp="EXEC [EXPENSES_SP] 1,0,'$DATE',$ddlLocation,$EHID,$ESHID,$AMOUNT,$PMID,'$NARRATION',$CAPITALIZE,$ACCRUED,$userid";
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

	$expid = ($_POST['expid'] == 'undefined' || $_POST['expid'] == '') ? 0 : $_POST['expid'];
	$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtDate = $_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
	$ddlExpenseHead = ($_POST['ddlExpenseHead'] == 'undefined' || $_POST['ddlExpenseHead'] == '') ? 0 : $_POST['ddlExpenseHead'];
	$ddlExpenseSubHead = ($_POST['ddlExpenseSubHead'] == 'undefined' || $_POST['ddlExpenseSubHead'] == '') ? 0 : $_POST['ddlExpenseSubHead'];
	$txtAmount = ($_POST['txtAmount'] == 'undefined' || $_POST['txtAmount'] == '') ? 0 : $_POST['txtAmount'];
	$ddlPaymentSource = ($_POST['ddlPaymentSource'] == 'undefined' || $_POST['ddlPaymentSource'] == '') ? 0 : $_POST['ddlPaymentSource'];
	$txtNarration = $_POST['txtNarration'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtNarration']);
	$chkCapitalize = ($_POST['chkCapitalize'] == 'undefined' || $_POST['chkCapitalize'] == '' || $_POST['chkCapitalize'] == '0') ? 0 : 1;
	$chkAccrued = ($_POST['chkAccrued'] == 'undefined' || $_POST['chkAccrued'] == '' || $_POST['chkAccrued'] == '0') ? 0 : 1;
	
	$actionid = $expid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($ddlExpenseHead == 0){throw new Exception("Please Select 'Expense Head Name'.");}
	if($ddlExpenseSubHead == 0){throw new Exception("Please Select 'Expense Sub Head Name'.");}
	if($txtAmount == 0){throw new Exception("Please Enter 'Amount'.");}
	if($ddlPaymentSource == 0){throw new Exception("Please Select 'Payment Source'.");}


	// $sql = "SELECT * FROM EXPENSES WHERE LOC_ID=$ddlLocation AND EHID=$ddlExpenseHead AND ESHID=$ddlExpenseSubHead AND AMOUNT AND EHID!=$ehid AND ISDELETED=0";
	// $row_count = unique($sql);
	// if($row_count == 0)
	// {
		$query="EXEC [EXPENSES_SP] $actionid,$expid,'$txtDate',$ddlLocation,$ddlExpenseHead,$ddlExpenseSubHead,$txtAmount,
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
			if(!empty($ehid))$expid['message'] = 'Expense successfully updated.';
			else $data['message'] = 'Expense successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Expense Head already exists';
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






/*============ GET EXPENSES =============*/ 
function getExpenses($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT EXPID,CONVERT(VARCHAR,EXPDATE,106)EXPDATE,LOC_ID,EHID,
		(SELECT EXPENSE_HEAD FROM EXPENSE_HEADS WHERE EHID=E.EHID)EXPENSE_HEAD,ESHID,
		(SELECT EXPENSE_SUB_HEAD FROM EXPENSE_SUB_HEADS WHERE ESHID=E.ESHID AND ISDELETED=0)EXPENSE_SUB_HEAD,AMOUNT,EXPFROM,
		(SELECT (SELECT BANKNAME FROM BANKS WHERE BANKID=BA.BANKID)+' ('+ACCOUNTNO+')' FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.EXPFROM)EXPFROM,
		(SELECT BANKACCID FROM BANK_ACCOUNTS BA WHERE BANKACCID=E.EXPFROM)EXPFROM_BANKACCID,
		NARRATION,CAPITALIZE,ACCRUED
		FROM EXPENSES E WHERE ISDELETED=0 AND LOC_ID=$ddlLocation
		ORDER BY CONVERT(DATE,EXPDATE,105) DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Expenses Not Found.';
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
/*============ GET EXPENSES =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $EXPID = ($_POST['EXPID'] == 'undefined' || $_POST['EXPID'] == '') ? 0 : $_POST['EXPID'];
			if($EXPID == 0){throw new Exception('EXPID Error.');}
			$delQuery = "EXEC [EXPENSES_SP] 3,$EXPID,'',0,0,0,0,'','',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Expense successfully deleted.';
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







