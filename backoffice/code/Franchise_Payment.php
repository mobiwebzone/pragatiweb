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
        case "getFranchisePayment":getFranchisePayment($conn);break;
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


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$fpid = ($_POST['fpid'] == 'undefined' || $_POST['fpid'] == '') ? 0 : $_POST['fpid'];
	$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$ddlCategory = ($_POST['ddlCategory'] == 'undefined' || $_POST['ddlCategory'] == '') ? 0 : $_POST['ddlCategory'];
	$ddlSubCategory = ($_POST['ddlSubCategory'] == 'undefined' || $_POST['ddlSubCategory'] == '') ? 0 : $_POST['ddlSubCategory'];
	$txtPB_ID = ($_POST['txtPB_ID'] == 'undefined' || $_POST['txtPB_ID'] == '') ? '' : $_POST['txtPB_ID'];
	$txtPBP_Ref = ($_POST['txtPBP_Ref'] == 'undefined' || $_POST['txtPBP_Ref'] == '') ? '' : $_POST['txtPBP_Ref'];
	$txtPB_Desc = ($_POST['txtPB_Desc'] == 'undefined' || $_POST['txtPB_Desc'] == '') ? '' : str_replace("'","''",$_POST['txtPB_Desc']);
	$txtPB_Period = ($_POST['txtPB_Period'] == 'undefined' || $_POST['txtPB_Period'] == '') ? '' : $_POST['txtPB_Period'];
	$txtPB_Amount = ($_POST['txtPB_Amount'] == 'undefined' || $_POST['txtPB_Amount'] == '') ? 0 : $_POST['txtPB_Amount'];
	$txtPB_Fraction = ($_POST['txtPB_Fraction'] == 'undefined' || $_POST['txtPB_Fraction'] == '') ? 0 : $_POST['txtPB_Fraction'];
	$txtNetPayAsh_HQ = ($_POST['txtNetPayAsh_HQ'] == 'undefined' || $_POST['txtNetPayAsh_HQ'] == '') ? 0 : $_POST['txtNetPayAsh_HQ'];
	$txtPaidAmt = ($_POST['txtPaidAmt'] == 'undefined' || $_POST['txtPaidAmt'] == '') ? 0 : $_POST['txtPaidAmt'];
	$txtPaidAmtDate = ($_POST['txtPaidAmtDate'] == 'undefined' || $_POST['txtPaidAmtDate'] == '') ? '' : $_POST['txtPaidAmtDate'];
	$txtPaidMethod = ($_POST['txtPaidMethod'] == 'undefined' || $_POST['txtPaidMethod'] == '') ? '' : $_POST['txtPaidMethod'];
	$txtRemarks = ($_POST['txtRemarks'] == 'undefined' || $_POST['txtRemarks'] == '') ? '' : str_replace("'","''",$_POST['txtRemarks']);
	$chkSettled = ($_POST['chkSettled'] == 'undefined' || $_POST['chkSettled'] == '' || $_POST['chkSettled'] == '0') ? 0 : 1;

	//==== IMAGE
	if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0 && $_FILES['DocsUpload']['size'] > 1048576) throw new Exception('File size too large.');
	$existingDocsUpload  = $_POST['existingDocsUpload'] == 'undefined' ? '' : $_POST['existingDocsUpload'];
	$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
	$DocsUpload = '';
	if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0){
		$ext = pathinfo($_FILES['DocsUpload']['name'],PATHINFO_EXTENSION);
		$DocsUpload .= strtolower(time().'.'.$ext);
	}
	else
	{
		$DocsUpload="";
	}
	//==== IMAGE
	
	$actionid = $fpid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($ddlCategory == 0){throw new Exception("Please Select 'Category'.");}
	if($ddlSubCategory == 0){throw new Exception("Please Select 'Sub Category'.");}
	if($txtPB_ID == ''){throw new Exception("Please Enter 'Payment Basis ID'.");}
	if($txtPBP_Ref == ''){throw new Exception("Please Enter 'Payment Basis Past Reference'.");}
	// if($txtPB_Amount == 0){throw new Exception("Please Enter 'Payment Basis Amount'.");}
	// if($txtPB_Fraction == 0){throw new Exception("Please Enter 'Payment Fraction Basis'.");}
	// if($txtPaidAmt == 0){throw new Exception("Please Enter 'Paid Amount'.");}
	// if($txtPaidAmtDate == ''){throw new Exception("Please Select 'Paid to Ashburn/HQ Date'.");}
	// if($txtPaidMethod == ''){throw new Exception("Please Select 'Paid Method'.");}


	$sql = "SELECT * FROM FRANCHISE_PAYMENT WHERE LOC_ID=$ddlLocation AND LMCID=$ddlCategory AND LSCID=$ddlSubCategory AND PID='$txtPB_ID' AND PREF='$txtPBP_Ref' AND FPID!=$fpid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [FRANCHISE_PAYMENT_SP] $actionid,$fpid,$ddlLocation,$ddlCategory,$ddlSubCategory,'$txtPB_ID','$txtPBP_Ref','$txtPB_Desc','$txtPB_Period',
											$txtPB_Amount,$txtPB_Fraction,$txtNetPayAsh_HQ,$txtPaidAmt,'$txtPaidAmtDate','$txtPaidMethod',
											'$txtRemarks',$chkSettled,$userid";
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
			$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$GET_FPID = (int)$row['FPID'];

			if($DocsUpload != ''){
				if($GET_FPID > 0)
				{
					$insertimage = "UPDATE FRANCHISE_PAYMENT SET DOC_FILE='$DocsUpload' WHERE FPID=$GET_FPID";
					sqlsrv_query($mysqli,$insertimage);
				}


				if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0)
				{
					move_uploaded_file($_FILES["DocsUpload"]["tmp_name"], '../images/franchise_payment/'.$DocsUpload);
				}

				
				if(isset($_FILES['DocsUpload']['name']) && $existingDocsUpload != '')
				{
					if (file_exists('../images/franchise_payment/'.$existingDocsUpload))
					{
						unlink('../images/franchise_payment/'.$existingDocsUpload);
					}
				}
			}

			if($chkRemoveImgOnUpdate>0 && $existingDocsUpload!='' && $DocsUpload==''){
				if($GET_FPID>0){
					$insertimage = "UPDATE FRANCHISE_PAYMENT SET DOC_FILE='' WHERE FPID=$GET_FPID";
						sqlsrv_query($mysqli,$insertimage);
				}
				if (file_exists('../images/franchise_payment/'.$existingDocsUpload))
				{
					unlink('../images/franchise_payment/'.$existingDocsUpload);
				}
			}

			$data['success'] = true;
			if(!empty($fpid))$data['message'] = 'Record successfully updated.';
			else $data['message'] = 'Record successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
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
 // =============== SAVE DATA ==============






/*============ GET FRANCHISE PAYMENT =============*/ 
function getFranchisePayment($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$chkSettledSearch = ($_POST['chkSettledSearch'] =='undefined' || $_POST['chkSettledSearch'] =='' || $_POST['chkSettledSearch'] =='0') ? 0 : 1;
		$query = "SELECT FPID,LOC_ID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=FP.LOC_ID)LOCATION,
		LMCID,(SELECT CATEGORY FROM LICENSE_MAIN_CATEGORIES WHERE LMCID=FP.LMCID)CATEGORY,
		LSCID,(SELECT SUBCATEGORY FROM LICENSE_SUB_CATEGORIES WHERE LSCID=FP.LSCID)SUBCATEGORY,
		PID,PREF,PDESC,PERIOD,AMOUNT,FRACTION,NETPAYABLE,AMOUNTPAID,
		CASE WHEN PAIDDATE IS NULL OR CONVERT(DATE,PAIDDATE,105) = '01-01-1900' THEN '' ELSE CONVERT(VARCHAR,PAIDDATE,106) END PAIDDATE,
		PAYMODE,REMARKS,SETTLED,DOC_FILE
		FROM FRANCHISE_PAYMENT FP
		WHERE ISDELETED=0 AND LOC_ID=$ddlLocation AND SETTLED=$chkSettledSearch
		ORDER BY PID DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['AMOUNT_SORT'] = (int)$row['AMOUNT'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Record not found.';
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
/*============ GET FRANCHISE PAYMENT =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $FPID = ($_POST['FPID'] == 'undefined' || $_POST['FPID'] == '') ? 0 : $_POST['FPID'];
			if($FPID == 0){throw new Exception('FPID Error.');}
			$delQuery = "EXEC [FRANCHISE_PAYMENT_SP] 3,$FPID,0,0,0,'','','','',0,0,0,0,'','','',0,$userid";
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
/* =========== DELETE =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







