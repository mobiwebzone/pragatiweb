<?php
session_start();
require_once '../../code/connection.php';

require __DIR__ . '../../../Twilio/autoload.php';
use Twilio\Rest\Client;

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
        case "getMSGHistory":getMSGHistory($conn);break;
        case "sendSms":sendSms($conn);break;
        case "markReply":markReply($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get MSG History =============*/ 
function getMSGHistory($mysqli){
	try
	{
		global $locid;
		$data = array();
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

		$query = "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,LOCID AS LOCATIONID,MOBILENO,TEXTMESSAGE,MSG_FROM,REPLIED
		FROM TEXT_MESSAGES 
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND MSGTYPE='OUTGOING API'
		AND MSGID NOT IN (SELECT MSGID FROM TEXT_MESSAGES_DETAILS WHERE MSG_STATUS='ERROR' OR MSG_STATUS='UNDELIVERED')
		AND [STATUS]<>'ERROR' AND [STATUS]<>'UNDELIVERED'";
		if($locid>1){
			$query .= " AND LOCID=$locid";
		}
		$query .= " ORDER BY MSGID DESC;"; 

		// -----------
		// -- INCOMING
		// -----------
		$query .= "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,MOBILENO,TEXTMESSAGE,MSG_FROM,REPLIED,
		-- STUDENT NAME
		ISNULL((SELECT FIRSTNAME+' '+LASTNAME+' | ' FROM REGISTRATIONS WHERE
		PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') FOR XML PATH('')),'')STUDENT_NAME,
		-- FIRST NAME
		ISNULL((SELECT TOP 1 FIRSTNAME FROM REGISTRATIONS WHERE
		PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','')),'')FIRSTNAME,
		-- LAST NAME
		ISNULL((SELECT TOP 1 LASTNAME FROM REGISTRATIONS WHERE
		PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','')),'')LASTNAME,
		-- REGID
		ISNULL((SELECT TOP 1 REGID FROM REGISTRATIONS WHERE
		PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','')),0)REGID,
		-- LOCATIONID
		ISNULL((SELECT TOP 1 LOCATIONID FROM REGISTRATIONS WHERE
		PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','')),0)LOCATIONID,
		CASE WHEN DATEDIFF(DD,MSGDATE,GETDATE())>=3 AND REPLIED=0
			THEN 'NO REPLY' ELSE '' 
		END CHECK_REPLY
		FROM TEXT_MESSAGES TM
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND MSGTYPE='INCOMING'
		AND MSGID NOT IN (SELECT MSGID FROM TEXT_MESSAGES_DETAILS WHERE MSG_STATUS='ERROR' OR MSG_STATUS='UNDELIVERED')
		AND [STATUS]<>'ERROR' AND [STATUS]<>'UNDELIVERED'
		AND (REPLACE(TM.MOBILENO,'+1','') IN (SELECT PHONE FROM REGISTRATIONS WHERE LOCATIONID=$locid AND PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1',''))
		OR REPLACE(TM.MOBILENO,'+1','') IN (SELECT P1_PHONE FROM REGISTRATIONS WHERE LOCATIONID=$locid AND P1_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1',''))
		OR REPLACE(TM.MOBILENO,'+1','') IN (SELECT P2_PHONE FROM REGISTRATIONS WHERE LOCATIONID=$locid AND P2_PHONE LIKE REPLACE('%'+TM.MOBILENO+'%','+1','')))";
		// ORDER BY MSGID DESC;
		// if($locid>1){
		// 	$query .= " AND LOCID=$locid";
		// }
		$query .= " ORDER BY MSGID DESC;"; 
		
		
		// --------------------
		// -- FAILED/UNDELIVERED
		// --------------------
		// -- SELECT MSGID,REGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,MOBILENO,TEXTMESSAGE,REPLIED,
		// -- ISNULL((SELECT REMARK FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID),'-')REMARK,
		// -- ISNULL((SELECT MSG_STATUS FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID),'-')[STATUS]
		// -- FROM TEXT_MESSAGES TM
		// -- WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		// -- AND ([STATUS]='UNDELIVERED' OR [STATUS]='ERROR') AND
		// -- MSGID NOT IN (SELECT MSGID FROM TEXT_MESSAGES_DETAILS WHERE MSG_STATUS='SUCCESS' AND MSG_STATUS='')
		// -- ORDER BY MSGID DESC
		$query .= "SELECT MSGID,MOBILENO,MSG_STATUS,ISNULL(REMARK,'-')REMARK,
		(SELECT REGID FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)REGID,
		(SELECT CONVERT(VARCHAR,MSGDATE,20) FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)MSGDATE,
		(SELECT MSGTYPE FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)MSGTYPE,
		(SELECT STUDENTTYPE FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)STUDENTTYPE,
		ISNULL((SELECT TOP 1 FIRSTNAME+' '+LASTNAME+' | ' FROM REGISTRATIONS WHERE ISDELETED=0 AND
		PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') FOR XML PATH('')),'')STUDENT_NAME,
		ISNULL((SELECT TOP 1 FIRSTNAME FROM REGISTRATIONS WHERE ISDELETED=0 AND
		PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','')),'')FIRSTNAME,
		-- LAST NAME
		ISNULL((SELECT TOP 1 LASTNAME FROM REGISTRATIONS WHERE ISDELETED=0 AND
		PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','')),'')LASTNAME,
		-- LOCATIONID
		ISNULL((SELECT TOP 1 LOCATIONID FROM REGISTRATIONS WHERE ISDELETED=0 AND
		PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P1_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','') OR 
		P2_PHONE LIKE REPLACE('%'+TMD.MOBILENO+'%','+1','')),0)LOCATIONID,
		-- (SELECT FIRSTNAME FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)FIRSTNAME,
		-- (SELECT LASTNAME FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)LASTNAME,
		(SELECT TEXTMESSAGE FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID)TEXTMESSAGE,
		ISNULL((SELECT MSG_FROM FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID),'-')MSG_FROM
		FROM TEXT_MESSAGES_DETAILS TMD WHERE (MSG_STATUS='ERROR' OR MSG_STATUS='UNDELIVERED')
		AND (SELECT CONVERT(DATE,MSGDATE,105) FROM TEXT_MESSAGES WHERE MSGID=TMD.MSGID) BETWEEN '$txtFromDT' AND '$txtToDT'
		";
		
		$data['$queryINC'] = $query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			// OUTGOING
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				
				$MSGDATE = $row['MSGDATE'];
				$MSGDATE=date_create($MSGDATE);
				$row['MSGDATE']= date_format($MSGDATE,"d-m-Y || h:i:s a");
	
				$row['MSGID'] = (int) $row['MSGID'];
				$data['OUTGOING'][] = $row;
			}
	
			// INCOMING
			sqlsrv_next_result($result);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				// SET STUDENT TYPE
				$row['STUDENTTYPE'] = ((int) $row['REGID'] > 0 && $row['STUDENT_NAME'] != '') ? 'Registered' : 'Non-Registered';
				
				$row['STUDENT_NAME'] = rtrim($row['STUDENT_NAME'],' | ');
				$MSGDATE = $row['MSGDATE'];
				$MSGDATE=date_create($MSGDATE);
				$row['MSGDATE']= date_format($MSGDATE,"d-m-Y || h:i:s a");
	
				$row['MSGID'] = (int) $row['MSGID'];
				$data['INCOMING'][] = $row;
			}

			// FAILED/UNDELIVERED
			sqlsrv_next_result($result);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				// SET STUDENT TYPE
				$row['STUDENTTYPE'] = ((int) $row['REGID'] > 0) ? 'Registered' : 'Non-Registered';
				
				$row['STUDENT_NAME'] = rtrim($row['STUDENT_NAME'],' | ');
				$MSGDATE = $row['MSGDATE'];
				$MSGDATE=date_create($MSGDATE);
				$row['MSGDATE']= date_format($MSGDATE,"d-m-Y || h:i:s a");
	
				$row['MSGID'] = (int) $row['MSGID'];
				$data['FAILED_UNDELIVERED'][] = $row;
			}
	
			$data['success'] = true;
		}
		else{
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




/*============ SEND SMS =============*/ 
function sendSms($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$REPLY_NUMBER = $_POST['REPLY_NUMBER'] == 'undefined' ? '' : str_replace("'","''",$_POST['REPLY_NUMBER']);
		$ddlTextFrom = $_POST['ddlTextFrom'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlTextFrom']);
		$txtReplySms = $_POST['txtReplySms'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtReplySms']);
		$Reply_numData = (isset($_POST['Reply_numData']) && !empty($_POST['Reply_numData'])) ? json_decode($_POST['Reply_numData'], true) : '';
		if($Reply_numData == '')throw new Exception('User Data Invalid.');
		if($REPLY_NUMBER == '')throw new Exception('Student Number Invalid.');
		if($txtReplySms == '')throw new Exception('Please Enter Your Sms.');

		// $data['Reply_numData'] = $Reply_numData['STUDENTTYPE'];
		// echo json_encode($data);exit;

		$STUDENTTYPE = $Reply_numData['STUDENTTYPE'];
		$REGID = $Reply_numData['REGID'];
		$FIRSTNAME = $Reply_numData['FIRSTNAME'];
		$LASTNAME = $Reply_numData['LASTNAME'];
		$MOBILENO = $Reply_numData['MOBILENO'];
		$REPLY_MSGID = $Reply_numData['MSGID'];
		

		$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'$STUDENTTYPE',$REGID,'$FIRSTNAME','$LASTNAME','$MOBILENO','$txtReplySms','$ddlTextFrom',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query']= $query;
			echo json_encode($data);exit;
		}
		else
		{
			// UPDATE REPLIED
			$queryUpd = "UPDATE TEXT_MESSAGES SET REPLIED=1 WHERE MSGID=$REPLY_MSGID";
			sqlsrv_query($mysqli, $queryUpd);


			// GET MSGID
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$GET_MSGID = (int) $row['MSGID'];


			// MESSAGE
			$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
			$auth_token = 'ad245122998781f49da657a66141cad6';
			$twilio_number = "+17039910242";

			$client = new Client($account_sid, $auth_token);
			try{
				$client->messages->create(
					// Where to send a text message (your cell phone?)
					$MOBILENO,
					array(
						'from' => $twilio_number,
						'body' => $txtReplySms
					)
				);		
				
				// INSERT DETAILS
				$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
				VALUES($GET_MSGID,'$MOBILENO','SUCCESS')";
				sqlsrv_query($mysqli, $query2);
				$data['query2'] = $query2;
				$data['success'] = true;
				$data['message'] = 'Sms Send successfully.';
			}catch (\Twilio\Exceptions\RestException $e) {
				$msg=$e->getMessage();
				// INSERT DETAILS
				$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
				VALUES($GET_MSGID,'$MOBILENO','ERROR','$msg')";
				$stmt2=sqlsrv_query($mysqli, $query2);
				$data['query2'] = $query2;
				
				$data['success'] = false;
				$data['message'] = $e->getMessage();
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
/*============ SEND SMS =============*/ 




/*============ MARK TO REPLY =============*/ 
function markReply($mysqli){
	try
	{
		global $userid;
		$data = array();

		$MSGID = ($_POST['MSGID'] == 'undefined' || $_POST['MSGID'] == '') ? 0 :$_POST['MSGID'];
		if($MSGID == 0)throw new Exception('MSGID Invalid.');
		

		$query="UPDATE TEXT_MESSAGES SET REPLIED=1 WHERE MSGID=$MSGID";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query']= $query;
			$data['message'] = 'Sms marked reply failed.';
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Sms marked reply successfully.';
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
/*============ MARK TO REPLY =============*/ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







