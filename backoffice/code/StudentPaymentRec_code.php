<?php
session_start();
require_once '../../code/connection.php';


require __DIR__ . '../../../Twilio/autoload.php';
use Twilio\Rest\Client;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';


if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['USER_LOCID']))
{$locid=$_SESSION['USER_LOCID'];}
else
{$locid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "Pay":Pay($conn);break;
        case "PayOther":PayOther($conn);break;
        case "getStudent":getStudent($conn);break;
        case "getPaymentsShedule":getPaymentsShedule($conn);break;
        case "getReceipts":getReceipts($conn);break;
        case "delete":delete($conn);break;
		case "saveExcelFile":saveExcelFile($conn);break;

		// EMAIL / SMS
		case "saveDataSms":saveDataSms($conn);break;
        case "saveDataEmail":saveDataEmail($conn);break;
		case "getMSGHistory":getMSGHistory($conn);break;
        case "getEMAILHistory":getEMAILHistory($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function Pay($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $recid  = ($_POST['recid'] == 'undefined' || $_POST['recid'] == '') ? 0 : $_POST['recid'];
		$txtDate=($_POST['txtDate'] == 'undefined' || $_POST['txtDate'] == '') ? '' : $_POST['txtDate'];
        $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
        $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
        $instalment  = ($_POST['instalment'] == 'undefined' || $_POST['instalment'] == '') ? 0 : $_POST['instalment'];
        $txtAmt  = ($_POST['txtAmt'] == 'undefined' || $_POST['txtAmt'] == '') ? 0 : $_POST['txtAmt'];
        $ddlPaymode  = ($_POST['ddlPaymode'] == 'undefined' || $_POST['ddlPaymode'] == '') ? 0 : $_POST['ddlPaymode'];
		$txtRefno=$_POST['txtRefno'] == 'undefined' ? '-' : $_POST['txtRefno'];
		$txtRemark=$_POST['txtRemark'] == 'undefined' ? '-' : $_POST['txtRemark'];

		
		if($txtDate == ''){throw new Exception("Select Date First.");}
		if($ddlPaymode == 0){throw new Exception("Please Select PayMode First.");}
		if($regid == 0){throw new Exception("REGID ERROR");}
		if($planid == 0){throw new Exception("PLANID ERROR");}
		// if($txtAmt == 0){throw new Exception("Please Enter Amount First.");}
		
		// !!!!!!!!!!! CHECK REFNO !!!!!!!!!!!
		if($txtRefno != '' && $recid == 0){
			$chkRef = "SELECT * FROM STUDENT_RECEIPTS WHERE REFNO='$txtRefno'";
			$stmtRef=sqlsrv_query($mysqli, $chkRef);
			$refcnt=unique($chkRef);
			if($refcnt > 0){throw new Exception("Ref. no. already exist.");}
		}else if($txtRefno != '' && $recid > 0){
			$chkRef = "SELECT * FROM STUDENT_RECEIPTS WHERE REFNO='$txtRefno' AND RECID!=$recid";
			$stmtRef=sqlsrv_query($mysqli, $chkRef);
			$refcnt=unique($chkRef);
			if($refcnt > 0){throw new Exception("Ref. no. already exist.");}
		}


		$actionid = $recid == 0 ? 1 : 2;


		$sql = "SELECT * FROM STUDENT_RECEIPTS WHERE REGID=$regid AND PLANID=$planid AND INSTALLMENT=$instalment AND 
				RECID!=$recid AND ISDELETED=0";
				sqlsrv_query($mysqli, $sql);

		// throw new Exception($sql);
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [STUDENT_RECEIPTS_SP] $actionid,$recid,'$txtDate',$regid,$planid,$instalment,$txtAmt,$ddlPaymode,'$txtRefno','$txtRemark',$userid";
	
	
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($recid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
		}
		else{
			$data['message'] = '<b>Payment already paid.</b>';
			$data['success'] = false;
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
 



// =============== SAVE EXCEL DATA ==================
function saveExcelFile($mysqli){
	try
	{
		$data = array();
		global $userid;
	
		// $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		// if($regid==0) throw new Exception("REGID Not Found.");
		$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];
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
					$date=DateTime::createFromFormat('d-m-Y', $emapData[1]);
					$emapData[1]=$date->format('Y-m-d');
					$data['$emapData'][]=$emapData;

					// SAVE REGISTRATION
					$query="EXEC [STUDENT_RECEIPTS_SP] 1,0,'$emapData[1]',$emapData[0],0,0,$emapData[2],$emapData[3],'$emapData[4]','$emapData[5]',$userid";
					sqlsrv_query($mysqli, $query);
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
		$data['tmp_name']=$_FILES["txtUploadExcelData"]["tmp_name"];
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
// =============== SAVE EXCEL DATA ==================

 
 
//  ========= Pay Other Payment ===========
 function PayOther($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $recid  = ($_POST['recid'] == 'undefined' || $_POST['recid'] == '') ? 0 : $_POST['recid'];
		$txtDate_Oth=($_POST['txtDate_Oth'] == 'undefined' || $_POST['txtDate_Oth'] == '') ? '' : $_POST['txtDate_Oth'];
        $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
        $txtAmt_Oth  = ($_POST['txtAmt_Oth'] == 'undefined' || $_POST['txtAmt_Oth'] == '') ? 0 : $_POST['txtAmt_Oth'];
        $ddlPaymode_Oth  = ($_POST['ddlPaymode_Oth'] == 'undefined' || $_POST['ddlPaymode_Oth'] == '') ? 0 : $_POST['ddlPaymode_Oth'];
		$txtRefno_Oth=$_POST['txtRefno_Oth'] == 'undefined' ? '-' : $_POST['txtRefno_Oth'];
		$txtRemark_Oth=$_POST['txtRemark_Oth'] == 'undefined' ? '-' : $_POST['txtRemark_Oth'];

		
		if($txtDate_Oth == ''){throw new Exception("Select Date First.");}
		if($ddlPaymode_Oth == 0){throw new Exception("Please Select PayMode First.");}
		if($regid == 0){throw new Exception("REGID ERROR");}
		// if($txtAmt_Oth == 0){throw new Exception("Please Enter Amount First.");}
		
		// !!!!!!!!!!! CHECK REFNO !!!!!!!!!!!
		if($txtRefno_Oth != '' && $recid == 0){
			$chkRef = "SELECT * FROM STUDENT_RECEIPTS WHERE REFNO='$txtRefno_Oth'";
			$stmtRef=sqlsrv_query($mysqli, $chkRef);
			$refcnt=unique($chkRef);
			if($refcnt > 0){throw new Exception("Ref. no. already exist.");}
		}else if($txtRefno_Oth != '' && $recid > 0){
			$chkRef = "SELECT * FROM STUDENT_RECEIPTS WHERE REFNO='$txtRefno_Oth' AND RECID!=$recid";
			$stmtRef=sqlsrv_query($mysqli, $chkRef);
			$refcnt=unique($chkRef);
			if($refcnt > 0){throw new Exception("Ref. no. already exist.");}
		}


		$actionid = $recid == 0 ? 1 : 2;


		// $sql = "SELECT * FROM STUDENT_RECEIPTS WHERE REGID=$regid AND PLANID=0 AND INSTALLMENT=0 AND 
		// 		RECID!=$recid AND ISDELETED=0";
		// 		sqlsrv_query($mysqli, $sql);


		// $row_count = unique($sql);

		// if($row_count == 0)
		// {

			$query="EXEC [STUDENT_RECEIPTS_SP] $actionid,$recid,'$txtDate_Oth',$regid,0,0,$txtAmt_Oth,$ddlPaymode_Oth,'$txtRefno_Oth','$txtRemark_Oth',$userid";
	
	
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($recid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
		// }
		// else{
		// 	$data['message'] = '<b>Payment already paid.</b>';
		// 	$data['success'] = false;
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


/*============ GET STUDENT =============*/ 
function getStudent($mysqli){
	try
	{
		$data = array();

		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// $ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];

		if($ddlLocation == 0){throw new Exception ("Select Location First.");}
		// if($ddlPlan == 0){throw new Exception ("Select Plan First.");}

		$query = "SELECT REGID,LOCATIONID,MODE,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)[LOCATION],
		FIRSTNAME,LASTNAME,
		PHONE,ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE_F,
		EMAIL,ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL_F,
		GRADE,
		ADDRESSLINE1,ADDRESSLINE2,CITY,STATE,ZIPCODE,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=R.COUNTRYID)COUNTRY,
		ALLERGIES,P1_FIRSTNAME,P1_LASTNAME,
		P1_PHONE,ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE_F,
		P1_EMAIL,ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL_F,
		P2_FIRSTNAME,P2_LASTNAME,
		P2_PHONE,ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE_F,
		P2_EMAIL,ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL_F,
		CLASSOF
		FROM REGISTRATIONS R WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation";

		// $query = "SELECT REGID,FIRSTNAME,LASTNAME,GRADE
		//  FROM REGISTRATIONS R WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation ORDER BY REGID DESC";

		// $data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['FINAL_PHONE'] = '';
			$row['FINAL_EMAIL'] = '';


			// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
			$row['PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE_F']);
			$row['PHONE_F'] = preg_match('/^[0-9]+$/', $row['PHONE_F']) ? $row['PHONE_F'] : '';
			$row['PHONE_F'] = is_numeric($row['PHONE_F']) ? $row['PHONE_F'] : '';
			if(strlen($row['PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['PHONE_F'].', ';

			if (filter_var($row['EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['EMAIL_F']) > 0 && $row['EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL_F'].', ';
			}
			
			// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
			
			// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
			$row['P1_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE_F']);
			$row['P1_PHONE_F'] = preg_match('/^[0-9]+$/', $row['P1_PHONE_F']) ? $row['P1_PHONE_F'] : '';
			$row['P1_PHONE_F'] = is_numeric($row['P1_PHONE_F']) ? $row['P1_PHONE_F'] : '';
			if(strlen($row['P1_PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE_F'].', ';

			if (filter_var($row['P1_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['P1_EMAIL_F']) > 0 && $row['P1_EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL_F'].', ';
			}
			// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
			
			// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
			$row['P2_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE_F']);
			$row['P2_PHONE_F'] = preg_match('/^[0-9]+$/', $row['P2_PHONE_F']) ? $row['P2_PHONE_F'] : '';
			$row['P2_PHONE_F'] = is_numeric($row['P2_PHONE_F']) ? $row['P2_PHONE_F'] : '';
			if(strlen($row['P2_PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE_F'].', ';

			if (filter_var($row['P2_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['P2_EMAIL_F']) > 0 && $row['P2_EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['P2_EMAIL_F'].', ';
			}
			// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

			$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
			$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));
			$row['FINAL_PHONE'] = str_replace(', ', ",\n", $row['FINAL_PHONE']);

			$row['FINAL_EMAIL'] = rtrim($row['FINAL_EMAIL'],', ');
			$row['FINAL_EMAIL'] = implode(", ",array_unique(explode(", ",$row['FINAL_EMAIL'])));
			$row['FINAL_EMAIL'] = str_replace(', ', ",\n", $row['FINAL_EMAIL']);

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





/*============ GET PAYMENTS =============*/ 
 function getPaymentsShedule($mysqli){
	try
	{
		$data = array();

		$regid=($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		if($regid == 0){throw new Exception ("regID Error.");}

		$query = "SELECT CONVERT(VARCHAR,INSERTDATE,106)[DATE],PSID,REGID,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=PS.REGID)STUDENT_NAME,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=PS.REGID)LOCATIONID,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=PS.PLANID)PLANNAME,
		ACTPRICE,ACTINST_AMOUNT,ACTINSTALLMENTS,PAYPLAN,AMOUNT,INSTALLMENTS,REMARKS,
		(SELECT (SELECT (SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=PS.REGID)CURRENCY_CLASS,
		(SELECT (SELECT (SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=PS.REGID)CURRENCY_CODE
		FROM PAYMENT_SCHEDULE PS WHERE ISDELETED=0 AND REGID=$regid";

		// $query = "SELECT CONVERT(VARCHAR,INSERTDATE,106)[DATE],PSID,REGID,
		// (SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=PS.REGID)STUDENT_NAME,
		// (SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=PS.REGID)LOCATIONID,PLANID,
		// (SELECT PLANNAME FROM PLANS WHERE PLANID=PS.PLANID)PLANNAME,
		// ACTPRICE,ACTINST_AMOUNT,ACTINSTALLMENTS,PAYPLAN,AMOUNT,INSTALLMENTS,REMARKS,
		// CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0)) =1 THEN
		// (SELECT PRICE FROM PLANS WHERE PLANID=PS.PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
		// ELSE 
		// (SELECT PRICE FROM PLANS WHERE PLANID=PS.PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
		// END COST
		// FROM PAYMENT_SCHEDULE PS WHERE ISDELETED=0 AND REGID=$regid";


		$cnt = unique($query);

		$result = sqlsrv_query($mysqli, $query);

		if($cnt > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else {
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



/*============ GET RECEIPTS =============*/ 
 function getReceipts($mysqli){
	try
	{
		$data = array();

		$regid=($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];

		if($regid == 0){throw new Exception ("regID Error.");}

		$query = "SELECT RECID,CONVERT(VARCHAR,RECDATE,20)RECDATE,RECNO,RECNOFULL,REGID,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID)[PLAN],
		INSTALLMENT,AMOUNT,PMID,REFNO,
		(SELECT INSTALLMENTS FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)TOTAL_INST,
		(SELECT PAYPLAN FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)PAYPLAN,REMARK,
		(SELECT (SELECT (SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SR.REGID)CURRENCY_CLASS,
		(SELECT (SELECT (SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=SR.REGID)CURRENCY_CODE
		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=$regid";


		$cnt = unique($query);

		$result = sqlsrv_query($mysqli, $query);

		if($cnt > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$date = date_create($row['RECDATE']);
				$row['RECDATE'] = date_format($date,"d M Y");
				$row['RECDATE_ORDERBY'] = date_format($date,"ymd");
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else {
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
            $RECID = ($_POST['RECID'] == 'undefined' || $_POST['RECID'] == '') ? 0 : $_POST['RECID'];  
			if($RECID == 0){throw new Exception('RECID Error.');}			

			$delQuery = "EXEC [STUDENT_RECEIPTS_SP] 3,$RECID,'',0,0,0,0,0,'','',$userid";

			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['delQuery'] = $delQuery;
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









// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SMS / EMAIL %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// =============== SAVE SMS DATA ==================
function saveDataSms($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$ContactData = (!empty($_POST['StudentData'])) ? $_POST['StudentData'] : '';
		if($ContactData == '')throw new Exception('Student Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		if(!$ContactData || count($ContactData)==0)throw new Exception('Select Student First.');
		for($i=0; $i<count($ContactData); $i++){
			$data['FINAL'][]= str_replace("\n","",$ContactData[$i]['FINAL_PHONE']);
			// $ContactData = str_replace("'","''",$_POST['txtMessage']);
		}
		// echo json_encode($data);exit;	
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);
		
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Student First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['FINAL_PHONE'];
			if(!$p || $p=='') throw new Exception('Selected Student Number Not Found.');
		}


		for($i=0; $i<count($ContactData); $i++){
			$REGID = $ContactData[$i]['REGID'];
			$FIRSTNAME = $ContactData[$i]['FIRSTNAME'];
			$LASTNAME = $ContactData[$i]['LASTNAME'];
			$FINAL_PHONE = $ContactData[$i]['FINAL_PHONE'];

			if($FINAL_PHONE && $FINAL_PHONE!='' && strlen($FINAL_PHONE) > 0){
				// $query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '',$CID,'$FIRSTNAME','','$FINAL_PHONE','$txtMessage','REGISTRATION_SMS',$userid";
				$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE]$locid,'Registered',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_PHONE','$txtMessage','STUDENT_PAYMENT_SMS',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					// GET MSGID
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$GET_MSGID = (int) $row['MSGID'];
	
	
					// MESSAGE
					$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
					$auth_token = 'ad245122998781f49da657a66141cad6';
					$twilio_number = "+17039910242";
					

					// EXPLODE PHONE NUMBER
					$FINAL_PHONE = str_replace("\n","",$FINAL_PHONE);
					$data['$FINAL_PHONE'][] = $FINAL_PHONE;
					$NUM = explode(",",$FINAL_PHONE);

					forEach($NUM as $value){
						$data['num'][] = $value;
						
						if($value!='' && $value){
							$client = new Client($account_sid, $auth_token);
							try{
								$client->messages->create(
									// Where to send a text message (your cell phone?)
									// '+17035653342'
									$value,
									array(
										'from' => $twilio_number,
										'body' => $txtMessage
									)
								);

								// INSERT DETAILS
								$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
								VALUES($GET_MSGID,'$value','SUCCESS')";
								$stmt2=sqlsrv_query($mysqli, $query2);
								$data['query2'][] = $query2;
								$data['success'] = true;

							}catch (\Twilio\Exceptions\RestException $e) {
								$error_msg=$e->getMessage();
								$error_msg = str_replace("'","''",$error_msg);
								// INSERT DETAILS
								$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
								VALUES($GET_MSGID,'$value','ERROR','$error_msg')";
								$stmt2=sqlsrv_query($mysqli, $query2);
								$data['query2'][] = $query2;

								// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
								$data['success'] = false;
								// $data['error'] = var_dump($e);
								$data['message'] = $e->getMessage();
							}
						}
					}
					

					$data['querySuccess'][] = $query;
	
				}
			}
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
 // =============== SAVE SMS DATA ==============



// =============== SAVE EMAIL DATA ==================
function saveDataEmail($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$ContactData = (!empty($_POST['StudentData'])) ? $_POST['StudentData'] : '';
		if($ContactData == '')throw new Exception('Contact Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		if(!$ContactData || count($ContactData)==0)throw new Exception('Select Student First.');
		for($i=0; $i<count($ContactData); $i++){
			$data['FINAL'][]= str_replace("\n","",$ContactData[$i]['FINAL_EMAIL']);
			// $ContactData = str_replace("'","''",$_POST['txtMessage']);
		}
		// echo json_encode($data);exit;
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);

		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Student First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['FINAL_EMAIL'];
			if(!$p || $p=='') throw new Exception('Selected Student Email Id Not Found.');
			// if(!filter_var($p, FILTER_VALIDATE_EMAIL)) throw new Exception("Selected Student Email Id Not Valid.");
		}

		$txtAttachment="";
		$data['$_FILES']=$_FILES;
		// $data['$_FILES_25']=formatSizeUnits(26214400); // 25 MB
		
		if(isset($_FILES['txtAttachment']['name']) && $_FILES['txtAttachment']['size'] > 0){

			$data['$_FILES_size']=formatSizeUnits($_FILES['txtAttachment']['size']);
			if($_FILES['txtAttachment']['size'] > 26214400) throw new Exception('File size limit of 25MB.');

			$ext = pathinfo($_FILES['txtAttachment']['name'],PATHINFO_EXTENSION);
			$txtAttachment .= strtolower(time().'.'.$ext);
		}
		else
		{
			$txtAttachment="";
		}
		$data['$txtAttachment']=$txtAttachment;
		// echo json_encode($data);exit;



		$msg = "";
		$msg .="
				<div style='border: 1px solid #DCEAEB; background: #ffd82b24;'>
					<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#ffd82b; color: #615136; font-weight: 800;'>MyExamsPrep</h1>
						
					<p style='padding: 0px 20px; font-family: system-ui;'>$txtMessage</p>			
				</div>
		";
		
		// echo json_encode($data);exit;
		
		for($i=0; $i<count($ContactData); $i++){
			$REGID = $ContactData[$i]['REGID'];
			$FIRSTNAME = $ContactData[$i]['FIRSTNAME'];
			$LASTNAME = $ContactData[$i]['LASTNAME'];
			$FINAL_EMAIL = $ContactData[$i]['FINAL_EMAIL'];
			if($FINAL_EMAIL!=''){
				// $query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] '',$CID,'$FIRSTNAME','','$FINAL_EMAIL','$txtMessage','CONTACTUS_EMAIL',$userid";
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'Registered',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$txtMessage','STUDENT_PAYMENT_EMAIL',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					// ########### SAVE IMAGE IN FOLDER ###########
						$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
						$GET_EMID = (int)$row['EMID'];
		
						if($txtAttachment != ''){
		
							if($GET_EMID > 0)
							{
								$insertimage = "UPDATE TEXT_EMAIL SET ATTACHMENT='$txtAttachment' WHERE EMID=$GET_EMID";
								sqlsrv_query($mysqli,$insertimage);
							}
			
			
							if(isset($_FILES['txtAttachment']['name']) && $_FILES['txtAttachment']['size'] > 0)
							{
								move_uploaded_file($_FILES["txtAttachment"]["tmp_name"], '../mail_attachment_images/'.$txtAttachment);
							}
		
							// UPDATE SECTION
		
							// if(isset($_FILES['txtAttachment']['name']) && $existingCatImage != '')
							// {
							// 	if (file_exists('../gallery_images/'.$existingCatImage))
							// 	{
							// 		unlink('../gallery_images/'.$existingCatImage);
							// 	}
							// }
						
		
						}
					// ########### SAVE IMAGE IN FOLDER ###########
		
					// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%
					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$FINAL_EMAIL = str_replace("\n","",$FINAL_EMAIL);
					$MAIL = explode(",",$FINAL_EMAIL);
					// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%

						$STmails = array();
						// $STmails = array(
						// 	$FINAL_EMAIL => $FIRSTNAME,
						// );
						foreach($MAIL as $value){
							// EMAIL
							$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
							$data['mail'][] = $value;
						}
						

						foreach($STmails as $email => $name){
							// MAIL
							$mail = new PHPMailer;
							$mail->isSMTP(); 
							$mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
							$mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
							$mail->Port = 587; // TLS only 587
							$mail->SMTPSecure = 'tls'; // ssl is depracated
							$mail->SMTPAuth = true;
							$mail->Username = "no.reply.myexamsprep@gmail.com";
							$mail->Password = "xagdmidhhtzijcgt";
							$mail->setFrom("no.reply.myexamsprep@gmail.com", "MyExamsPrep");
							$mail->addAddress($email, $name);
							$mail->Subject = 'myexamsprep:Alert';
							$mail->msgHTML($msg); 
							//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
							$mail->AltBody = 'HTML messaging not supported';
							if($txtAttachment && $txtAttachment!='')$mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
							// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
							
							//USE AddCC When use foreach loop
							// foreach($STmails as $email => $name){
							// 	$EM = $email;
							// 	$NM = $name;
							// 	$mail->AddCC($email, $name); 
							// }
							
							if(!$mail->send()){
								// INSERT DETAILS
								$error_msg=$mail->ErrorInfo;
								$error_msg = str_replace("'","''",$error_msg);
								$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
								VALUES($GET_EMID,'$email','ERROR','$error_msg')";
								sqlsrv_query($mysqli, $query2);
								$data['query2'] = $query2;
								// echo "Mailer Error: " . $mail->ErrorInfo;
								$data['Mail_ST'] = $mail->ErrorInfo;
								$data['sss'][] = $mail;
								$data['success'] = false;
								$data['message'] = 'Sms Send Failed.';
							}
							else{
								// INSERT DETAILS
								$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
								VALUES($GET_EMID,'$email','SUCCESS','')";
								sqlsrv_query($mysqli, $query2);
								$data['query2'] = $query2;
								// echo "Message sent!";
								$data['sss'][] = $mail;
								$data['Mail_ST'] = "Message sent!";
								$data['message'] = 'Sms Send successfully.';
								$data['success'] = true;
							}
						}
		
							
		
					
					$data['querySuccess'][] = $query;
		
				}
			}
			
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
// =============== SAVE EMAIL DATA ==================
function array_push_assoc($array, $key, $value){
	$array[$key] = $value;
	return $array;
 }


/*============ Get MSG History =============*/ 
function getMSGHistory($mysqli){
	try
	{
		global $locid;
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,MOBILENO,TEXTMESSAGE,
		-- ISNULL((SELECT MOBILENO+' ('+MSG_STATUS+'), ' FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID FOR XML PATH('')),'')MSG_STATUS
		CASE WHEN (SELECT COUNT(*) FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID)>0
			THEN ISNULL((SELECT MOBILENO+'_('+MSG_STATUS+'), ' FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID FOR XML PATH('')),'')
			ELSE MOBILENO
		END MSG_STATUS
		FROM TEXT_MESSAGES TM
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT' AND LOCID=$locid
		AND MSGTYPE='OUTGOING API' AND MSG_FROM='STUDENT_PAYMENT_SMS'";
		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND INSERTID IN (SELECT [UID] FROM USERS WHERE LOCID=".$_SESSION['USER_LOCID'].")";
		// }
		$query .= " ORDER BY MSGID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['MSG_STATUS'] = $row['MSG_STATUS'] && $row['MSG_STATUS']!='' ? rtrim($row['MSG_STATUS'],', ') : '';
				$row['MSG_STATUS'] = str_replace(', ', "\n", $row['MSG_STATUS']);
				$MSGDATE = $row['MSGDATE'];
				$MSGDATE=date_create($MSGDATE);
				$row['MSGDATE']= date_format($MSGDATE,"d-m-Y || h:i:s a");

				$row['MSGID'] = (int) $row['MSGID'];
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
/*============ Get MSG History =============*/ 


/*============ Get EMAIL History =============*/ 
function getEMAILHistory($mysqli){
	try
	{
		global $locid;
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT EMID,CONVERT(VARCHAR,EMAILDATE,20)EMAILDATE,EMAILTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,EMAILID,TEXTEMAIL,ATTACHMENT
		FROM TEXT_EMAIL 
		WHERE CONVERT(DATE,EMAILDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND EMAILTYPE='OUTGOING API' AND EMAIL_FROM='STUDENT_PAYMENT_EMAIL' AND LOCID=$locid";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND INSERTID IN (SELECT [UID] FROM USERS WHERE LOCID=".$_SESSION['USER_LOCID'].")";
		// }

		$query .= " ORDER BY EMID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				
				$EMAILDATE = $row['EMAILDATE'];
				$EMAILDATE=date_create($EMAILDATE);
				$row['EMAILDATE']= date_format($EMAILDATE,"d-m-Y || h:i:s a");

				$row['EMID'] = (int) $row['EMID'];
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
/*============ Get EMAIL History =============*/ 



/*============ CHECK/CONVERT FILE SIZE =============*/ 
function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
}
/*============ CHECK/CONVERT FILE SIZE =============*/
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SMS / EMAIL %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







