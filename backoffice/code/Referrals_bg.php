<?php
session_start();
require_once '../../code/connection.php';

// require __DIR__ . '../../../Twilio/autoload.php';
// use Twilio\Rest\Client;

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getReferrals":getReferrals($conn);break;
        case "UpdateReferral":UpdateReferral($conn);break;
        case "ReferralPayment":ReferralPayment($conn);break;
		
		// ============ EXTRA ===========
        case "getPlans":getPlans($conn);break;
        case "getProductsByPlan":getProductsByPlan($conn);break;
        case "getStudentByPlanProduct":getStudentByPlanProduct($conn);break;
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


// =============== UPDATE REFERRAL ==================
function UpdateReferral($mysqli){
	try
	{
		global $userid;
		$data = array();

		$REFID = ($_POST['REFID'] == 'undefined' || $_POST['REFID'] == '') ? 0 : $_POST['REFID'];
		$ddlStatus = ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? '' : $_POST['ddlStatus'];
		$txtRemarkUser = ($_POST['txtRemarkUser'] == 'undefined' || $_POST['txtRemarkUser'] == '') ? '' : $_POST['txtRemarkUser'];
		$txtRemarkOffice = ($_POST['txtRemarkOffice'] == 'undefined' || $_POST['txtRemarkOffice'] == '') ? '' : $_POST['txtRemarkOffice'];
		$txtReferredAmtPaid = ($_POST['txtReferredAmtPaid'] == 'undefined' || $_POST['txtReferredAmtPaid'] == '') ? 0 : $_POST['txtReferredAmtPaid'];
		$txtReferredPaidOn = ($_POST['txtReferredPaidOn'] == 'undefined' || $_POST['txtReferredPaidOn'] == '') ? '' : $_POST['txtReferredPaidOn'];
		$txtReferralAmtPaid = ($_POST['txtReferralAmtPaid'] == 'undefined' || $_POST['txtReferralAmtPaid'] == '') ? 0 : $_POST['txtReferralAmtPaid'];
		$txtReferralPaidOn = ($_POST['txtReferralPaidOn'] == 'undefined' || $_POST['txtReferralPaidOn'] == '') ? '' : $_POST['txtReferralPaidOn'];

		if($REFID == 0)throw new Exception('REFID Not Found.');
		if($ddlStatus == '')throw new Exception('Select Status First.');
		
		// echo json_encode($data);exit;

		$query="UPDATE REFERRALS SET REF_STATUS='$ddlStatus',REF_REMARKS='$txtRemarkUser',REF_NOTES_OFFICE='$txtRemarkOffice',
				REFERRED_AMT=$txtReferredAmtPaid,REFERRED_ON='$txtReferredPaidOn',REFERRAL_AMT=$txtReferralAmtPaid,
				REFERRAL_ON='$txtReferralPaidOn',
				UPDATEID=$userid,UPDATEDATE=GETDATE() WHERE REFID=$REFID";
		$stmt=sqlsrv_query($mysqli, $query);
		$data['$query']=$query;
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{	

			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Referral successfully updated.';
		}
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
// =============== UPDATE REFERRAL ==================


// =============== REFERRAL PAYMENT ==================
// function ReferralPayment($mysqli){
// 	try
// 	{
// 		global $userid;
// 		$data = array();

// 		$REFID = ($_POST['REFID'] == 'undefined' || $_POST['REFID'] == '') ? 0 : $_POST['REFID'];
// 		$REFMID = ($_POST['REFMID'] == 'undefined' || $_POST['REFMID'] == '') ? 0 : $_POST['REFMID'];
// 		$txtReferredAmtPaid = ($_POST['txtReferredAmtPaid'] == 'undefined' || $_POST['txtReferredAmtPaid'] == '') ? 0 : $_POST['txtReferredAmtPaid'];
// 		$txtReferredPaidOn = ($_POST['txtReferredPaidOn'] == 'undefined' || $_POST['txtReferredPaidOn'] == '') ? '' : $_POST['txtReferredPaidOn'];
// 		$txtReferralAmtPaid = ($_POST['txtReferralAmtPaid'] == 'undefined' || $_POST['txtReferralAmtPaid'] == '') ? 0 : $_POST['txtReferralAmtPaid'];
// 		$txtReferralPaidOn = ($_POST['txtReferralPaidOn'] == 'undefined' || $_POST['txtReferralPaidOn'] == '') ? '' : $_POST['txtReferralPaidOn'];
// 		$txtRemark = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];

// 		if($REFID == 0)throw new Exception('REFID Not Found.');
// 		if($REFMID == 0)throw new Exception('REFMID Not Found.');
// 		if($txtReferredAmtPaid == 0)throw new Exception('Enter Referred Amount First.');
// 		if($txtReferredPaidOn == '')throw new Exception('Select Referred Date First.');
		
// 		// echo json_encode($data);exit;

// 		$query="INSERT INTO ";
// 		$stmt=sqlsrv_query($mysqli, $query);
// 		if($stmt === false)
// 		{
// 			// die( print_r( sqlsrv_errors(), true));
// 			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
// 			$data['success'] = false;
// 			$data['query'] = $query;
// 		}
// 		else
// 		{	

// 			$data['query'] = $query;
// 			$data['success'] = true;
// 			$data['message'] = 'Referral successfully updated.';
// 		}
// 		echo json_encode($data);exit;

// 	}
// 	catch(Exception $e)
// 	{
// 	$data = array();
// 	$data['success'] = false;
// 	$data['message'] = $e->getMessage();
// 	echo json_encode($data);
// 	exit;
// 	}
// }
// =============== REFERRAL PAYMENT ==================



/*============ GET REFERRALS =============*/ 
function getReferrals($mysqli){
	try
	{
		$data = array();

		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT REFID,CONVERT(VARCHAR,REFDATE,20)REFDATE,REFBY,REFBYID,
		CASE WHEN REFBY='STUDENT' THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=R.REFBYID)
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=R.REFBYID)
		END REFBY_NAME,REFMID,
		(SELECT REF_DESC FROM REFERRAL_MASTER WHERE REFMID=R.REFMID)REFERRAL_NAME,
		RELATION,COURSE,FIRSTNAME,LASTNAME,CONTACTNO,EMAILID,
		P1_FIRSTNAME,P1_LASTNAME,P1_CONTACTNO,P1_EMAILID,P2_FIRSTNAME,P2_LASTNAME,P2_CONTACTNO,P2_EMAILID,REF_DISCLOSE,
		REF_STATUS,REF_REMARKS,REF_NOTES_OFFICE,REFERRED_AMT,
		CASE WHEN REFERRED_ON IS NULL OR CONVERT(DATE,REFERRED_ON,105)='1900-01-01' THEN '' ELSE CONVERT(VARCHAR,REFERRED_ON,20) END REFERRED_ON,REFERRAL_AMT,
		CASE WHEN REFERRAL_ON IS NULL OR CONVERT(DATE,REFERRAL_ON,105)='1900-01-01' THEN '' ELSE CONVERT(VARCHAR,REFERRAL_ON,20) END REFERRAL_ON
		FROM REFERRALS R
		WHERE CONVERT(DATE,REFDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		ORDER BY CONVERT(DATE,REFDATE,105) DESC";

		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$date=date_create($row['REFDATE']);
				$row['REFDATE_SHOW']= date_format($date,"d-m-Y h:i:s a");
				if($row['REFERRED_ON'] && $row['REFERRED_ON']!=''){
					$date=date_create($row['REFERRED_ON']);
					$row['REFERRED_ON_SHOW']= date_format($date,"d-m-Y");
				}
				if($row['REFERRAL_ON'] && $row['REFERRAL_ON']!=''){
					$date=date_create($row['REFERRAL_ON']);
					$row['REFERRAL_ON_SHOW']= date_format($date,"d-m-Y");
				}

				// $row['STUDENT_TABLE']=$SS;
				if($row['REFBY'] === 'STUDENT'){
					$data['student'][] = $row;
				}else{
					$data['teacher'][] = $row;
				}
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
/*============ GET REFERRALS =============*/ 







// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA %%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

/*============ Get Plans =============*/ 
function getPlans($mysqli){
	try
	{
		$query = "SELECT PLANID AS id,PLANNAME AS label
		--,convert(varchar, STARTDATE, 106)STARTDATE,
		--convert(varchar, ENDDATE, 106)ENDDATE,PRICE,INST_AMOUNT,INST_FREQ,INST_NO,
		--convert(varchar, DISPLAYFROMDATE, 106)DISPLAYFROMDATE,
		--convert(varchar, DISPLAYTODATE, 106)DISPLAYTODATE,DISPLAYCOLOR,ACTIVE
		FROM PLANS WHERE ISDELETED=0 ORDER BY label";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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
/*============ Get Plans =============*/ 



/*============ Get Product By Plan =============*/ 
function getProductsByPlan($mysqli){
	try
	{
		
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$query = "SELECT DISTINCT PRODUCTID AS id,(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SCC.PRODUCTID)label 
		FROM STUDENT_COURSE_COVERAGE SCC WHERE ISDELETED=0 AND PLANID IN ($planid) ORDER BY label";
		// $data['success'] = false;
		// $data['planid']=$planid;
		// $data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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
/*============ Get Product By Plan =============*/ 



/*============ Get Student By Plan_Product =============*/ 
function getStudentByPlanProduct($mysqli){
	try
	{
		
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$data['$planid']=$planid;

		// $productid = (!empty($_POST['PRODUCTID'])) ? $_POST['PRODUCTID'] : '';
		// if($productid == '')throw new Exception('ProductID Not Found.');
		// $productid = implode(',',$productid);
		// $data['$productid']=$productid;

		// echo json_encode($data);exit;

		// $query = "SELECT DISTINCT REGID AS id, 
		// (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID) label,
		// ISNULL((SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')FIRSTNAME,
		// ISNULL((SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')LASTNAME,
		// ISNULL((SELECT PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PHONE,
		// ISNULL((SELECT P1_FIRSTNAME+' '+P1_LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PARENT1,
		// ISNULL((SELECT P1_PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')P1_PHONE,
		// ISNULL((SELECT P2_FIRSTNAME+' '+P2_LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PARENT2,
		// ISNULL((SELECT P2_PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')P2_PHONE
		// FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA
		// WHERE ISDELETED=0 AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=1 AND APPROVED=0) AND
		// SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE WHERE ISDELETED=0 AND PLANID IN ($planid) AND PRODUCTID IN ($productid))
		// ORDER BY label";

		$query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1,
		ISNULL(P1_PHONE,'')P1_PHONE,ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,ISNULL(P2_PHONE,'')P2_PHONE
		FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))
		ORDER BY label";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
				$row['FINAL_PHONE'] = '';

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));

				// $row['FINAL_PHONE'] = ($row['PHONE'] && $row['PHONE'] != '') ? $row['PHONE'] : (($row['P1_PHONE'] && $row['P1_PHONE'] != '') ? $row['P1_PHONE'] : (($row['P2_PHONE'] && $row['P2_PHONE'] != '') ? $row['P2_PHONE'] : ''));
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
/*============ Get Student By Plan_Product =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







