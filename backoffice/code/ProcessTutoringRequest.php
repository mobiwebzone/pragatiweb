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
        case "save":save($conn);break;
        case "getTutoringRequests":getTutoringRequests($conn);break;
        case "getPendingRequests":getPendingRequests($conn);break;
        case "getSubjectTeacher":getSubjectTeacher($conn);break;
        case "delete":delete($conn);break;

		case "savehourly":savehourly($conn);break;
		// case "getTutoringRequests":getTutoringRequests($conn);break;
        case "deletehourly":deletehourly($conn);break;
        case "saveDet":saveDet($conn);break;
        case "getTutoringReqDetails":getTutoringReqDetails($conn);break;
        case "deleteDet":deleteDet($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


function save($mysqli){
	try
	{
	$data = array();
	global $userid,$locid;
	
	$reqpid  = ($_POST['reqpid'] == 'undefined' || $_POST['reqpid'] == '') ? 0 : $_POST['reqpid'];
	$GET_REQID = ($_POST['GET_REQID'] == 'undefined' || $_POST['GET_REQID'] == '') ? 0 : $_POST['GET_REQID'];
	$GET_REGID = ($_POST['GET_REGID'] == 'undefined' || $_POST['GET_REGID'] == '') ? 0 : $_POST['GET_REGID'];
	$ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
	$txtRatePerHour  = ($_POST['txtRatePerHour'] == 'undefined' || $_POST['txtRatePerHour'] == '') ? 0 : $_POST['txtRatePerHour'];
	$txtTeacherShare  = ($_POST['txtTeacherShare'] == 'undefined' || $_POST['txtTeacherShare'] == '') ? 0 : $_POST['txtTeacherShare'];
	$txtMEPShare  = ($_POST['txtMEPShare'] == 'undefined' || $_POST['txtMEPShare'] == '') ? 0 : $_POST['txtMEPShare'];
	$txtStartDate  = ($_POST['txtStartDate'] == 'undefined' || $_POST['txtStartDate'] == '') ? '' : $_POST['txtStartDate'];
	$txtEndDate  = ($_POST['txtEndDate'] == 'undefined' || $_POST['txtEndDate'] == '') ? '' : $_POST['txtEndDate'];
	$STUBJECT_NAME  = ($_POST['STUBJECT_NAME'] == 'undefined' || $_POST['STUBJECT_NAME'] == '') ? '_SUBJECT_' : $_POST['STUBJECT_NAME'];
	$STARTDT_EMAIL  = ($_POST['STARTDT_EMAIL'] == 'undefined' || $_POST['STARTDT_EMAIL'] == '') ? '_SUBJECT_' : $_POST['STARTDT_EMAIL'];
	
	$actionid = $reqpid == 0 ? 1 : 2;
	
	if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
	if($txtStartDate == '')throw new Exception("Please Select Start Date.");
	if($txtEndDate == '')throw new Exception("Please Select End Date.");
	
	$sql = "SELECT * FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE REQID=$GET_REQID AND REGID=$GET_REGID AND TEACHERID=$ddlTeacher AND RATE_PER_HOUR=$txtRatePerHour AND TEACHER_SHARE=$txtTeacherShare
	AND MEP_SHARE=$txtMEPShare AND CONVERT(DATE,STARTDATE,105)='$txtStartDate' AND CONVERT(DATE,ENDDATE,105)='$txtEndDate' AND REQPID!=$reqpid AND ISDELETED=0";
	$row_count = unique($sql);
	
	if($row_count == 0)
	{
		$query="EXEC [STUDENT_TUTORING_REQUEST_PROCESS_SP] $actionid,$reqpid,$GET_REQID,$GET_REGID,$ddlTeacher,$txtRatePerHour,$txtTeacherShare,$txtMEPShare,'$txtStartDate','$txtEndDate',$userid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}else{

			// #######################
			// EMAIL START
			// #######################
			if($actionid == 1){
				// GET STUDENT_DETAILS
				$QUERY_ST = "SELECT LOCATIONID,
				(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOCATION,
				FIRSTNAME,LASTNAME,
				ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE_F,
				ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL_F,
				P1_PHONE,ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE_F,
				P1_EMAIL,ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL_F,
				P2_PHONE,ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE_F,
				P2_EMAIL,ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL_F,
				ISNULL((SELECT FIRSTNAME FROM USERS WHERE UID=$ddlTeacher),'') T_FIRSTNAME,
				ISNULL((SELECT CASE WHEN MOBILE='' OR MOBILE='null' OR MOBILE LIKE'%TBD%' OR MOBILE IS NULL THEN '' ELSE MOBILE END FROM USERS WHERE UID=$ddlTeacher),'') T_PHONE_F,
				ISNULL((SELECT CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END FROM USERS WHERE UID=$ddlTeacher),'') T_EMAIL_F
				FROM REGISTRATIONS R WHERE REGID=$GET_REGID";
				$data['QUERY_ST']=$QUERY_ST;
				
				
				$res_ST = sqlsrv_query($mysqli, $QUERY_ST);
				$row_ST = sqlsrv_fetch_array($res_ST);
				$FIRSTNAME = $row_ST['FIRSTNAME'];
				$LASTNAME = $row_ST['LASTNAME'];
				$T_FIRSTNAME = $row_ST['T_FIRSTNAME'];

				$FINAL_EMAIL = '';
				$FINAL_EMAIL_T = '';
				$FINAL_PHONE = '';
				$FINAL_PHONE_T = '';

				// STUDENT
				$row_ST['PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row_ST['PHONE_F']);
				$row_ST['PHONE_F'] = preg_match('/^[0-9]+$/', $row_ST['PHONE_F']) ? $row_ST['PHONE_F'] : '';
				$row_ST['PHONE_F'] = is_numeric($row_ST['PHONE_F']) ? $row_ST['PHONE_F'] : '';
				if(strlen($row_ST['PHONE_F']) > 0) $FINAL_PHONE .= $row_ST['PHONE_F'].', ';
				if (filter_var($row_ST['EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row_ST['EMAIL_F']) > 0 && $row_ST['EMAIL_F']!='NaN') $FINAL_EMAIL .= $row_ST['EMAIL_F'].', ';
				}
				// P1
				$row_ST['P1_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row_ST['P1_PHONE_F']);
				$row_ST['P1_PHONE_F'] = preg_match('/^[0-9]+$/', $row_ST['P1_PHONE_F']) ? $row_ST['P1_PHONE_F'] : '';
				$row_ST['P1_PHONE_F'] = is_numeric($row_ST['P1_PHONE_F']) ? $row_ST['P1_PHONE_F'] : '';
				if(strlen($row_ST['P1_PHONE_F']) > 0) $FINAL_PHONE .= $row_ST['P1_PHONE_F'].', ';
				if (filter_var($row_ST['P1_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row_ST['P1_EMAIL_F']) > 0 && $row_ST['P1_EMAIL_F']!='NaN') $FINAL_EMAIL .= $row_ST['P1_EMAIL_F'].', ';
				}
				// P2
				$row_ST['P2_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row_ST['P2_PHONE_F']);
				$row_ST['P2_PHONE_F'] = preg_match('/^[0-9]+$/', $row_ST['P2_PHONE_F']) ? $row_ST['P2_PHONE_F'] : '';
				$row_ST['P2_PHONE_F'] = is_numeric($row_ST['P2_PHONE_F']) ? $row_ST['P2_PHONE_F'] : '';
				if(strlen($row_ST['P2_PHONE_F']) > 0) $FINAL_PHONE .= $row_ST['P2_PHONE_F'].', ';
				if (filter_var($row_ST['P2_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row_ST['P2_EMAIL_F']) > 0 && $row_ST['P2_EMAIL_F']!='NaN') $FINAL_EMAIL .= $row_ST['P2_EMAIL_F'].', ';
				}
				// TEACHER
				$row_ST['T_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row_ST['T_PHONE_F']);
				$row_ST['T_PHONE_F'] = preg_match('/^[0-9]+$/', $row_ST['T_PHONE_F']) ? $row_ST['T_PHONE_F'] : '';
				$row_ST['T_PHONE_F'] = is_numeric($row_ST['T_PHONE_F']) ? $row_ST['T_PHONE_F'] : '';
				if(strlen($row_ST['T_PHONE_F']) > 0) $FINAL_PHONE_T .= $row_ST['T_PHONE_F'].', ';
				if (filter_var($row_ST['T_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row_ST['T_EMAIL_F']) > 0 && $row_ST['T_EMAIL_F']!='NaN') $FINAL_EMAIL_T = $row_ST['T_EMAIL_F'];
				}
				$FINAL_PHONE = rtrim($FINAL_PHONE,', ');
				$FINAL_PHONE = implode(", ",array_unique(explode(", ",$FINAL_PHONE)));
				$FINAL_PHONE = str_replace(', ', ",\n", $FINAL_PHONE);
				$FINAL_PHONE_T = str_replace(', ', ",\n", $FINAL_PHONE_T);

				$FINAL_EMAIL = rtrim($FINAL_EMAIL,', ');
				$FINAL_EMAIL = implode(", ",array_unique(explode(", ",$FINAL_EMAIL)));
				$FINAL_EMAIL = str_replace(', ', ",\n", $FINAL_EMAIL);
				$FINAL_EMAIL_T = str_replace(', ', ",\n", $FINAL_EMAIL_T);
				
				// ###### STUDENT
				if($FINAL_EMAIL!=''){
					$ALL_MAILS = $FINAL_EMAIL;
					$mail_data = compact('ALL_MAILS','STUBJECT_NAME','STARTDT_EMAIL','locid','GET_REGID','FIRSTNAME','LASTNAME','userid');
					$data['SEND_MAIL_ST'] = sendMail($mysqli,'STUDENT',$mail_data);
				}
				if($FINAL_PHONE!=''){
					$ALL_PHONES = $FINAL_PHONE;
					$sms_data = compact('ALL_PHONES','STUBJECT_NAME','STARTDT_EMAIL','locid','GET_REGID','FIRSTNAME','LASTNAME','userid');
					$data['SEND_SMS_ST'] = sendSMS($mysqli,'STUDENT',$sms_data);
				}
				
				// ###### TEACHER
				if($FINAL_EMAIL_T!=''){
					$ALL_MAILS = $FINAL_EMAIL_T;
					$FIRSTNAME = $T_FIRSTNAME;
					$mail_data = compact('ALL_MAILS','STUBJECT_NAME','STARTDT_EMAIL','locid','GET_REGID','FIRSTNAME','LASTNAME','userid');
					$data['SEND_MAIL_TH'] = sendMail($mysqli,'TEACHER',$mail_data);
				}
				if($FINAL_PHONE_T!=''){
					$ALL_PHONES = $FINAL_PHONE_T;
					$FIRSTNAME = $T_FIRSTNAME;
					$sms_data = compact('ALL_PHONES','STUBJECT_NAME','STARTDT_EMAIL','locid','GET_REGID','FIRSTNAME','LASTNAME','userid');
					$data['SEND_SMS_TH'] = sendSMS($mysqli,'TEACHER',$sms_data);
				}
			}
			// #######################
			// EMAIL END
			// #######################

			$data['success'] = true;
			if(!empty($reqpid))$data['message'] = 'Record successfully updated.';
			else $data['message'] = 'Record successfully inserted.';
			
		}
		
		
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Record already exists.';
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
 
// SEND MAIL
function sendMail($mysqli,$ST_TE,$DET){
	// return $DET['ALL_MAILS'];
	$Data_ST = array();
	$Data_ST['DET']=$DET;
	$ALL_MAILS = $DET['ALL_MAILS'];
	$STUBJECT_NAME = $DET['STUBJECT_NAME'];
	$STARTDT_EMAIL = $DET['STARTDT_EMAIL'];
	$locid = $DET['locid'];
	$GET_REGID = $DET['GET_REGID'];
	$FIRSTNAME = $DET['FIRSTNAME'];
	$LASTNAME = $DET['LASTNAME'];
	$userid = $DET['userid'];

	// ##### STUDENT
	if($ST_TE=='STUDENT'){
		$MSG = "Congratulations, you have been assigned an $STUBJECT_NAME tutor starting $STARTDT_EMAIL.";
		$Data_ST['$MSG']=$MSG;
		$querySV="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'Registered',$GET_REGID,'$FIRSTNAME','$LASTNAME','$ALL_MAILS','$MSG','TUTORING_PROCESS_EMAIL',$userid";
		$stmtSV=sqlsrv_query($mysqli, $querySV);
		
		if($stmtSV === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$Data_ST['success'] = false;
			$Data_ST['querySV'] = $querySV;
		}
		else
		{
			$rowSV = sqlsrv_fetch_array($stmtSV,SQLSRV_FETCH_ASSOC);
			$GET_EMID = (int)$rowSV['EMID'];

			// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%
			$ALL_MAILS = str_replace("\n","",$ALL_MAILS);
			$MAIL = explode(",",$ALL_MAILS);
			// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%

			$STmails = array();
			// $STmails = array(
			// 	$ALL_MAILS => $FIRSTNAME,
			// );
			foreach($MAIL as $value){
				// EMAIL
				$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
				$Data_ST['mail_checking'][] = $value;
			}
			// echo json_encode($data);exit;
			

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
				$mail->msgHTML($MSG); 
				//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
				$mail->AltBody = 'HTML messaging not supported';
				// $mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
				// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
				
				if(!$mail->send()){
					// INSERT DETAILS
					$error_msg=$mail->ErrorInfo;
					$error_msg = str_replace("'","''",$error_msg);
					$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
					VALUES($GET_EMID,'$email','ERROR','$error_msg')";
					sqlsrv_query($mysqli, $query2);
					$Data_ST['query_saveMDet_ST'] = $query2;
					// echo "Mailer Error: " . $mail->ErrorInfo;
					$Data_ST['Mail_ST'] = $mail->ErrorInfo;
					$Data_ST['success_mail'] = false;
					$Data_ST['message_mail'] = 'Sms Send Failed.';
				}
				else{
					// INSERT DETAILS
					$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
					VALUES($GET_EMID,'$email','SUCCESS','')";
					sqlsrv_query($mysqli, $query2);
					$Data_ST['query_saveMDet_ST'] = $query2;
					// echo "Message sent!";
					$Data_ST['Mail_ST'] = "Message sent!";
					$Data_ST['message_mail'] = 'Sms Send successfully.';
					$Data_ST['success_mail'] = true;
				}
			}
		}
	}
	else if($ST_TE=='TEACHER'){
		$MSG = "Congratulations, you have been assigned an $STUBJECT_NAME student starting $STARTDT_EMAIL.";
		$Data_ST['$MSG']=$MSG;
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
		$mail->addAddress($ALL_MAILS, $FIRSTNAME);
		$mail->Subject = 'myexamsprep:Alert';
		$mail->msgHTML($MSG); 
		//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
		$mail->AltBody = 'HTML messaging not supported';
		// $mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
		// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
		
		if(!$mail->send()){
			// INSERT DETAILS
			$error_msg=$mail->ErrorInfo;
			$error_msg = str_replace("'","''",$error_msg);
			$Data_ST['Mail_ST_T'] = $mail->ErrorInfo;
			$Data_ST['success_mail_T'] = false;
			$Data_ST['message_mail_T'] = 'Sms Send Failed.';
		}
	}else{
		$Data_ST['success'] = false;
		$Data_ST['message'] = 'Email For Student or Teacher Not Found.';
	}

	return $Data_ST;
}

// SEND SMS
function sendSMS($mysqli,$ST_TE,$DET){
	$Data_ST = array();
	$Data_ST['DET']=$DET;
	$ALL_PHONES = $DET['ALL_PHONES'];
	$STUBJECT_NAME = $DET['STUBJECT_NAME'];
	$STARTDT_EMAIL = $DET['STARTDT_EMAIL'];
	$locid = $DET['locid'];
	$GET_REGID = $DET['GET_REGID'];
	$FIRSTNAME = $DET['FIRSTNAME'];
	$LASTNAME = $DET['LASTNAME'];
	$userid = $DET['userid'];
	
	// ##### STUDENT
	if($ST_TE=='STUDENT'){
		$MSG = "Congratulations, you have been assigned an $STUBJECT_NAME tutor starting $STARTDT_EMAIL.";
		$Data_ST['$MSG']=$MSG;
		
		if($ALL_PHONES && $ALL_PHONES!='' && strlen($ALL_PHONES) > 0){
			// $query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '',$CID,'$FIRSTNAME','','$FINAL_PHONE','$txtMessage','REGISTRATION_SMS',$userid";
			$querySS="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'Registered',$GET_REGID,'$FIRSTNAME','$LASTNAME','$ALL_PHONES','$MSG','TUTORING_PROCESS_SMS',$userid";
			$stmt=sqlsrv_query($mysqli, $querySS);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$Data_ST['success'] = false;
				$Data_ST['querySS'][] = $querySS;
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
				$ALL_PHONES = str_replace("\n","",$ALL_PHONES);
				$Data_ST['$ALL_PHONES'][] = $ALL_PHONES;
				$NUM = explode(",",$ALL_PHONES);

				forEach($NUM as $value){
					$Data_ST['num'][] = $value;
					
					if($value!='' && $value){
						$client = new Client($account_sid, $auth_token);
						try{
							$client->messages->create(
								// Where to send a text message (your cell phone?)
								// '+17035653342'
								$value,
								array(
									'from' => $twilio_number,
									'body' => $MSG
								)
							);

							// INSERT DETAILS
							$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
							VALUES($GET_MSGID,'$value','SUCCESS')";
							$stmt2=sqlsrv_query($mysqli, $query2);
							$Data_ST['query_sms'][] = $query2;
							$Data_ST['success_sms'] = true;
							$data['message_sms'] = 'Save.';

						}catch (\Twilio\Exceptions\RestException $e) {
							$error_msg=$e->getMessage();
							$error_msg = str_replace("'","''",$error_msg);
							// INSERT DETAILS
							$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
							VALUES($GET_MSGID,'$value','ERROR','$error_msg')";
							$stmt2=sqlsrv_query($mysqli, $query2);
							$data['query_sms'][] = $query2;
							// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
							$data['success_sms'] = false;
							// $data['error'] = var_dump($e);
							$data['message_sms'] = $e->getMessage();
						}
					}
				}
			}
		}else{
			$Data_ST['success'] = false;
			$Data_ST['message'] = 'Mobile No. Not Found.';
		}
	}
	else if($ST_TE=='TEACHER'){
		$MSG = "Congratulations, you have been assigned an $STUBJECT_NAME student starting $STARTDT_EMAIL.";
		$Data_ST['$MSG']=$MSG;
		// MESSAGE
		$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
		$auth_token = 'ad245122998781f49da657a66141cad6';
		$twilio_number = "+17039910242";
		

		// EXPLODE PHONE NUMBER
		$ALL_PHONES = str_replace("\n","",$ALL_PHONES);
		$Data_ST['$ALL_PHONES'][] = $ALL_PHONES;

		$Data_ST['num'][] = $ALL_PHONES;
		
		$client = new Client($account_sid, $auth_token);
		try{
			$client->messages->create(
				// Where to send a text message (your cell phone?)
				// '+17035653342'
				$ALL_PHONES,
				array(
					'from' => $twilio_number,
					'body' => $MSG
				)
			);
			$data['success_sms'] = true;
			$data['message_sms'] = 'Sms Sent';

		}catch (\Twilio\Exceptions\RestException $e) {
			$error_msg=$e->getMessage();
			$error_msg = str_replace("'","''",$error_msg);
			
			$data['success_sms'] = false;
			// $data['error'] = var_dump($e);
			$data['message_sms'] = $e->getMessage();
		}
		
		
	}else{
		$Data_ST['success'] = false;
		$Data_ST['message'] = 'Sms For Student or Teacher Not Found.';
	}
	
	return $Data_ST;
}

function array_push_assoc($array, $key, $value){
$array[$key] = $value;
return $array;
}

/*============ GET TUTORING REQUESTS =============*/ 
function getTutoringRequests($mysqli){
	try
	{
		$data = array();
		$REQID = ($_POST['REQID'] == 'undefined' || $_POST['REQID'] == '') ? 0 : $_POST['REQID'];
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		
		if($REQID==0) throw new Exception("Error : Invalid REQID.");
		
		$query = "SELECT REQPID,REQID,REGID,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=ST.TEACHERID)TEACHERNAME,
		(SELECT DBO.GET_CURRENCYCODE_BY_USER(ST.TEACHERID)) CURRENCY,
		RATE_PER_HOUR,TEACHER_SHARE,MEP_SHARE,
		CONVERT(VARCHAR,STARTDATE,106)STARTDATE,CONVERT(VARCHAR,ENDDATE,106)ENDDATE
		FROM STUDENT_TUTORING_REQUEST_PROCESS ST
		WHERE ISDELETED=0 AND REQID=$REQID";
		if($ddlTeacher>0) $query.=" AND TEACHERID = $ddlTeacher";
		$query .=" ORDER BY TEACHERNAME";

		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


/*============ GET PENDING REQUESTS =============*/ 
function getPendingRequests($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];

		if($ddlLocation==0) throw new Exception('Invalid Location.');

		$query = "SELECT REQID, FORMAT(REQDATE,'yyyyMMddmh')REQDATE_ORDERBY,
		CONVERT(VARCHAR,REQDATE,106)REQDATE,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=ST.REGID)ST_LOCID,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)[SUBJECT],CSUBID,
		CASE REQPRIORITY WHEN 0 THEN 'Low' WHEN 1 THEN 'Medium' WHEN 2 THEN 'Urgent' ELSE '-' END REQPRIORITY_SHOW,REQPRIORITY,REQSTATUS,
		CONVERT(VARCHAR,STARTDATE,106)STARTDATE,FORMAT(STARTDATE,'yyyyMMddmh')STARTDATE_ORDERBY,
		CASE WHEN FORMAT(ENDDATE,'yyyy')='1900' THEN '' ELSE CONVERT(VARCHAR,ENDDATE,106) END ENDDATE,		
		FORMAT(ENDDATE,'yyyyMMddmh')ENDDATE_ORDERBY,ISNULL(RATEFROM,0)RATEFROM,ISNULL(RATETO,0)RATETO,COMMENTS,
		(SELECT DYNAME+' : '+CAST(DYHOURS AS VARCHAR)+' hour ('+(CASE FROMTIME WHEN '00:00:00.0000000' THEN '-' ELSE CONVERT(VARCHAR,FROMTIME,100) END)
			+' / '+(CASE TOTIME WHEN '00:00:00.0000000' THEN '-' ELSE CONVERT(VARCHAR,TOTIME,100) END)+'), ' 
			FROM STUDENT_TUTORING_REQUEST_DETAILS WHERE ISDELETED=0 AND REQID=ST.REQID FOR XML PATH('')
		)PREFERENCE,
		(SELECT DISTINCT (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=STRP.TEACHERID)+', ' FROM STUDENT_TUTORING_REQUEST_PROCESS STRP WHERE ISDELETED=0 AND REQID=ST.REQID FOR XML PATH(''))ASSIGN_TEACHERS
		FROM STUDENT_TUTORING_REQUEST ST
		WHERE ISDELETED=0 AND REQSTATUS='OPEN' 
		AND REGID IN(SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";
		if($txtFromDT!='' && $txtToDT!='') $query .= " AND CONVERT(DATE,REQDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";
		$query .= " ORDER BY CONVERT(DATE,REQDATE,106) ASC,STUDENTNAME";

		$data['$query'] = $query;
		// echo json_encode($data);exit();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PREFERENCE'] = rtrim($row['PREFERENCE'],', ');
				$row['PREFERENCE'] = str_replace(', ', ",\n", $row['PREFERENCE']);
				$row['ASSIGN_TEACHERS'] = rtrim($row['ASSIGN_TEACHERS'],', ');
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


/*============ GET SUBJECT TEACHER =============*/ 
function getSubjectTeacher($mysqli){
	try
	{
		$data = array();
		$CSUBID = ($_POST['CSUBID'] == 'undefined' || $_POST['CSUBID'] == '') ? 0 : $_POST['CSUBID'];
		$ST_LOCID = ($_POST['ST_LOCID'] == 'undefined' || $_POST['ST_LOCID'] == '') ? 0 : $_POST['ST_LOCID'];
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		

		// $query ="SELECT TEACHERID,
		// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TS.TEACHERID) TEACHERNAME,
		// CSUBID, RATE_PER_HOUR, TEACHER_SHARE, MEP_SHARE,
		// ISNULL((SELECT 'RATING:'+CAST(RATING AS VARCHAR) + (CASE WHEN COMMENT<>'' THEN ' / '+ COMMENT ELSE '' END) FROM TEACHER_FEEDBACK WHERE TEACHERID=TS.TEACHERID),'-')FEEDBACK
		// FROM TEACHER_SUBJECTS TS 
		// WHERE ISDELETED=0 AND CSUBID=$CSUBID AND  TEACHERID IN ( SELECT UID FROM USERS WHERE USERROLE='TEACHER' AND LOCID=$ST_LOCID)
		// ORDER BY CASE WHEN (SELECT COUNT(*) FROM STUDENT_RECEIPTS WHERE ISDELETED=0 AND REGID=$REGID)>0
		// 	  THEN (SELECT RATING FROM TEACHER_FEEDBACK WHERE TEACHERID=TS.TEACHERID)
		// 	  ELSE MEP_SHARE+' '+(SELECT RATING FROM TEACHER_FEEDBACK WHERE TEACHERID=TS.TEACHERID)
		// END";
		//--ORDER BY MEP_SHARE DESC,TEACHERNAME";
	
		$query="EXEC [GET_SUBJECT_TEACHERS] $ST_LOCID,$CSUBID,$REGID";

		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false)
		{
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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





/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$REQPID = ($_POST['REQPID'] == 'undefined' || $_POST['REQPID'] == '') ? 0 : $_POST['REQPID']; 
		if($REQPID==0)throw new Exception('REQPID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_REQUEST_PROCESS_SP] 3,$REQPID,0,0,0,0,0,0,'','',$userid");
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




function savehourly($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $reqid  = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];
	   $ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
	   $txtReqDate  = ($_POST['txtReqDate'] == 'undefined' || $_POST['txtReqDate'] == '') ? '' : $_POST['txtReqDate'];
	   $subjectIDs  = ($_POST['subjectIDs'] == 'undefined' || $_POST['subjectIDs'] == '') ? array() : explode(',',$_POST['subjectIDs']);
	   $ddlPriority  = ($_POST['ddlPriority'] == 'undefined' || $_POST['ddlPriority'] == '') ? 1 : $_POST['ddlPriority'];
	   $txtStartDate  = ($_POST['txtStartDate'] == 'undefined' || $_POST['txtStartDate'] == '') ? '' : $_POST['txtStartDate'];
	   $txtEndDate  = ($_POST['txtEndDate'] == 'undefined' || $_POST['txtEndDate'] == '') ? '' : $_POST['txtEndDate'];
	   $txtExpectedFrom  = ($_POST['txtExpectedFrom'] == 'undefined' || $_POST['txtExpectedFrom'] == '') ? 0 : $_POST['txtExpectedFrom'];
	   $txtExpectedTo  = ($_POST['txtExpectedTo'] == 'undefined' || $_POST['txtExpectedTo'] == '') ? 0 : $_POST['txtExpectedTo'];
	   $txtComments  = ($_POST['txtComments'] == 'undefined' || $_POST['txtComments'] == '') ? '' : $_POST['txtComments'];
	   $ddlStatus  = ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? '' : $_POST['ddlStatus'];
	   
	   $data['subjectIDs'] = $subjectIDs;
	   // echo json_encode($data);exit;
	   
	   $actionid = $reqid == 0 ? 1 : 2;
	   
	   if($ddlStudent == 0 && $reqid == 0)throw new Exception("Please Select Student Name.");
	   if($txtReqDate == '')throw new Exception("Please Select Request Date.");
	   if(count($subjectIDs) == 0 && $actionid==1)throw new Exception("Please Select Subject Name.");
	   if($txtStartDate == '')throw new Exception("Please Select Start Date.");
	   if($ddlStatus == '')throw new Exception("Please Select Status.");
	   
	   // $sql = "SELECT * FROM STUDENT_TUTORING_REQUEST WHERE REGID=$ddlStudent AND CONVERT(DATE,REQDATE,105)='$txtReqDate' AND REQPRIORITY=$ddlPriority AND REQID!=$reqid AND ISDELETED=0";
	   // $row_count = unique($sql);
	   
	   // if($row_count == 0)
	   // {
		   if($actionid==1){
			   for($i=0;$i<count($subjectIDs);$i++){
				   $CSUBID = $subjectIDs[$i];
				   $sql = "SELECT * FROM STUDENT_TUTORING_REQUEST WHERE REGID=$ddlStudent AND CONVERT(DATE,REQDATE,105)='$txtReqDate' AND CSUBID=$CSUBID AND 
						   REQPRIORITY=$ddlPriority AND REQSTATUS='$ddlStatus' AND REQID!=$reqid AND ISDELETED=0";
				   $row_count = unique($sql);
				   if($row_count==0){
					   if($CSUBID>0){
						   $query="EXEC [STUDENT_TUTORING_REQUEST_SP] $actionid,$reqid,$ddlStudent,'$txtReqDate',$CSUBID,$ddlPriority,'$txtStartDate','$txtEndDate',$txtExpectedFrom, $txtExpectedTo,'$txtComments','$ddlStatus',$userid";
						   $data['query'][] = $query;
						   $stmt=sqlsrv_query($mysqli, $query);
						   if($stmt === false)
						   {
							   // die( print_r( sqlsrv_errors(), true));
							   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
							   $data['success'] = false;
							   echo json_encode($data);exit;
						   }else{
							   $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
							   $data['REQID'] = $row['REQID'];
							   
						   }
					   }
				   }
	   
			   }
		   }else{
			   $query="EXEC [STUDENT_TUTORING_REQUEST_SP] $actionid,$reqid,$ddlStudent,'$txtReqDate',0,$ddlPriority,'$txtStartDate','$txtEndDate',$txtExpectedFrom, $txtExpectedTo,'$txtComments','$ddlStatus',$userid";
			   $data['query'] = $query;
			   $stmt=sqlsrv_query($mysqli, $query);
			   if($stmt === false)
			   {
				   // die( print_r( sqlsrv_errors(), true));
				   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				   $data['success'] = false;
				   echo json_encode($data);exit;
			   }else{
				   $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				   $data['REQID'] = $row['REQID'];
				   
			   }
		   }


		   $data['success'] = true;
		   if(!empty($reqid))$data['message'] = 'Record successfully updated.';
		   else $data['message'] = 'Record successfully inserted.';
		   
		   
	   // }
	   // else
	   // {
	   // 	$data['success'] = false;
	   // 	$data['message'] = 'Record already exists.';
	   // }
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




/*============ GET TUTORING REQUESTS =============*/ 
// function getTutoringRequests($mysqli){
// 	try
// 	{
// 		$data = array();
// 		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];

// 		$query = "SELECT REQID,REGID,
// 		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
// 		FORMAT(REQDATE,'MMddyyyyhm')REQDATE_ORDERBY,
// 		CONVERT(VARCHAR,REQDATE,106)REQDATE,CSUBID,
// 		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)[SUBJECT],
// 		REQPRIORITY,
// 		CONVERT(VARCHAR,STARTDATE,106)STARTDATE,FORMAT(STARTDATE,'MMddyyyyhm')STARTDATE_ORDERBY,
// 		CASE WHEN FORMAT(ENDDATE,'yyyy')='1900' THEN '' ELSE CONVERT(VARCHAR,ENDDATE,106) END ENDDATE,
// 		FORMAT(ENDDATE,'MMddyyyyhm')ENDDATE_ORDERBY,
// 		COMMENTS,REQSTATUS 
// 		FROM STUDENT_TUTORING_REQUEST ST
// 		WHERE ISDELETED=0
// 		AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation)
// 		ORDER BY CONVERT(DATE,REQDATE,105)";
// 		$data['$query'] = $query;
// 		$count = unique($query);
// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}else{
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }



/* =========== Delete =========== */ 
function deletehourly($mysqli){
	try{   
		global $userid;
		$data = array();     
		$REQID = ($_POST['REQID'] == 'undefined' || $_POST['REQID'] == '') ? 0 : $_POST['REQID']; 
		if($REQID==0)throw new Exception('REQID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_REQUEST_SP] 3,$REQID,0,'',0,0,'','',0,0,'','',$userid");
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted';
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







 function saveDet($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		
        $reqdetid  = ($_POST['reqdetid'] == 'undefined' || $_POST['reqdetid'] == '') ? 0 : $_POST['reqdetid'];
        $reqid  = ($_POST['reqid'] == 'undefined' || $_POST['reqid'] == '') ? 0 : $_POST['reqid'];
        $ddlDay  = ($_POST['ddlDay'] == 'undefined' || $_POST['ddlDay'] == '') ? '' : $_POST['ddlDay'];
        $txtDayHours  = ($_POST['txtDayHours'] == 'undefined' || $_POST['txtDayHours'] == '') ? 0 : $_POST['txtDayHours'];
        $txtFromTime  = ($_POST['txtFromTime'] == 'undefined' || $_POST['txtFromTime'] == '') ? '' : $_POST['txtFromTime'];
        $txtToTime  = ($_POST['txtToTime'] == 'undefined' || $_POST['txtToTime'] == '') ? '' : $_POST['txtToTime'];
		
		$actionid = $reqdetid == 0 ? 1 : 2;
		
		if($reqid == 0)throw new Exception("Error : Invalid REQID.");
		if($ddlDay == '')throw new Exception("Please Select Day Name.");
		if($txtDayHours == 0)throw new Exception("Please Select Hours.");
		
		$sql = "SELECT * FROM STUDENT_TUTORING_REQUEST_DETAILS WHERE REQID=$reqid AND DYNAME='$ddlDay' AND DYHOURS=$txtDayHours AND REQDETID!=$reqdetid AND ISDELETED=0";
		$row_count = unique($sql);
		if($row_count == 0)
		{
			$query="EXEC [STUDENT_TUTORING_REQUEST_DETAILS_SP] $actionid,$reqdetid,$reqid,'$ddlDay',$txtDayHours,'$txtFromTime','$txtToTime',$userid";
			$data['query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				echo json_encode($data);exit;
			}else{
				$data['success'] = true;
				if(!empty($reqdetid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
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


/*============ GET TUTORING REQUEST DETAILS =============*/ 
 function getTutoringReqDetails($mysqli){
	try
	{
		$data = array();
		$reqid = ($_POST['reqid'] =='undefined' || $_POST['reqid'] =='') ? 0 : $_POST['reqid'];
		$query = "SELECT REQDETID,REQID,DYNAME,DYHOURS,
		CASE WHEN FROMTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,FROMTIME,100) END FROMTIME,
		CONVERT(VARCHAR,FROMTIME,21)FROMTIME_SET,
		CASE WHEN TOTIME='00:00:00.0000000' THEN '' ELSE CONVERT(VARCHAR,TOTIME,100) END TOTIME,
		CONVERT(VARCHAR,TOTIME,21)TOTIME_SET
		FROM STUDENT_TUTORING_REQUEST_DETAILS ST WHERE ISDELETED=0 AND REQID=$reqid ORDER BY DYNAME";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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



/* =========== Delete =========== */ 
function deleteDet($mysqli){
	try{   
		global $userid;
		$data = array();     
		$REQDETID = ($_POST['REQDETID'] == 'undefined' || $_POST['REQDETID'] == '') ? 0 : $_POST['REQDETID']; 
		if($REQDETID==0)throw new Exception('REQDETID Error.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_REQUEST_DETAILS_SP] 3,$REQDETID,0,'',0,'','',$userid");
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







