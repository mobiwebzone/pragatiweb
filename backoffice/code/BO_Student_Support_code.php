<?php
session_start();
require_once '../../code/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "UPDATE_STATUS":UPDATE_STATUS($conn);break;
		case "createTicket":createTicket($conn);break;
		case "deleteTicket":deleteTicket($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 
/*============ UPDATE STATUS ============*/
 function UPDATE_STATUS($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $TICKETID  = ($_POST['TICKETID'] == 'undefined' || $_POST['TICKETID'] == '') ? 0 : $_POST['TICKETID'];
        $ddlStatus  = ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? '' : $_POST['ddlStatus'];

		if($TICKETID == 0)
		{throw new Exception("TICKETID Error.");}
		if($ddlStatus == '')
		{throw new Exception("Please Select Status.");}

		$final_CLOSEDBY =  $ddlStatus=='CLOSED'?$userid:0;


			$query="UPDATE SUPPORT_TICKETS SET [STATUS]='$ddlStatus', CLOSEDBY=$final_CLOSEDBY WHERE TICKETID=$TICKETID";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				// GET REGID
				$queryRegid = "SELECT TOP 1 REGID FROM SUPPORT_TICKETS WHERE TICKETID=$TICKETID AND CANCELLED=0";
				$resultRegid = sqlsrv_query($mysqli,$queryRegid);
				$rowRegid = sqlsrv_fetch_array($resultRegid,SQLSRV_FETCH_ASSOC);
				$REGID = $rowRegid['REGID'];

				if($REGID>0){
					// SEND MAIL
					$sendSMS = sendText_Email($mysqli,$REGID,'updated');
					$data['sendSMS']=$sendSMS;
				}


				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Status successfully updated';
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
 /*============ UPDATE STATUS ============*/
 
 
 
 /*============ CREATE TICKET ============*/
 function createTicket($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $ddlPlanCT=($_POST['ddlPlanCT'] == 'undefined' || $_POST['ddlPlanCT'] == '') ? 0 : $_POST['ddlPlanCT'];
	   $ddlStudentCT=($_POST['ddlStudentCT'] == 'undefined' || $_POST['ddlStudentCT'] == '') ? 0 : $_POST['ddlStudentCT'];
	   $ddlPriorityCT=$_POST['ddlPriorityCT'] == 'undefined' ? '' : $_POST['ddlPriorityCT'];
	   $textSubjectCT=$_POST['textSubjectCT'] == 'undefined' ? '' : $_POST['textSubjectCT'];
	   $txtCommentCT=$_POST['txtCommentCT'] == 'undefined' ? '' : $_POST['txtCommentCT'];

	   if($ddlStudentCT == 0)throw new Exception("Please Select Student.");
	   if($ddlPriorityCT == '')throw new Exception("Please Select Priority.");
	   if($textSubjectCT == '')throw new Exception("Please Enter Subject.");
	   if($txtCommentCT == '')throw new Exception("Please Enter Comment.");
   
		   $query="EXEC [SUPPORT_TICKETS_SP] 0,$ddlStudentCT,'$textSubjectCT','$ddlPriorityCT','OPEN','ADMIN',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['ERROR_query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
			   $row = sqlsrv_fetch_array($stmt);
			   $data['TICKETID'] = (int) $row['TICKETID'];
			   $TICKETID = (int) $row['TICKETID'];


			   //Save Comment
			   $queryComment="EXEC [SUPPORT_TICKETS_DETAILS_SP] 0,$TICKETID,1,'$txtCommentCT','ADMIN',$userid,'COMMENT'";
			   $resultComment=sqlsrv_query($mysqli, $queryComment);

			   if($stmt === false)
			   {
				   // die( print_r( sqlsrv_errors(), true));
				   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				   $data['success'] = false;
				   $data['queryComment'] = $queryComment;
				   echo json_encode($data);exit;
			   }
			   else
			   {	
					// SEND MAIL
					$sendSMS = sendText_Email($mysqli,$ddlStudentCT,'created');
					$data['sendSMS']=$sendSMS;

					$data['query'] = $query;
					$data['success'] = true;
					$data['message'] = 'Record successfully inserted.';
			   }



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
 /*============ CREATE TICKET ============*/




/* =========== Delete TICKET =========== */ 
function deleteTicket($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TICKETID = ($_POST['TICKETID'] == 'undefined' || $_POST['TICKETID'] == '') ? 0 : $_POST['TICKETID'];  
            $txtReasoneTC = ($_POST['txtReasoneTC'] == 'undefined' || $_POST['txtReasoneTC'] == '') ? '' : $_POST['txtReasoneTC'];  
			$delQuery = "UPDATE SUPPORT_TICKETS SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE(),CANCEL_REASONE='$txtReasoneTC' 
						WHERE TICKETID=$TICKETID; 
						UPDATE SUPPORT_TICKETS_DETAILS SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE() WHERE TICKETID=$TICKETID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Ticket successfully cancelled.';
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
/* =========== Delete TICKET =========== */ 



/*============ SEND EMAIL =============*/ 
function sendText_Email($mysqli,$regid,$msgFor){
	global $userid;
	$data = array();

	$msg = "MyExamPrep's Support ticket has been ".$msgFor.". <br/><br/>
						
				Thanks <br/>
				MyExamsPrep";
	$msgdb = str_replace("'","''",$msg);

	$query = "SELECT REGID, ISNULL(FIRSTNAME+' '+LASTNAME,'') FULLNAME,FIRSTNAME,LASTNAME,LOCATIONID,
		ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE,
		ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL,
		ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, 
		ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE,
		ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL,
		ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,
		ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE,
		ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID=$regid ORDER BY FULLNAME";

		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		$data['COUNT'] = $count;
		if($count > 0){
			$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
				$row['REGID'] = (int) $row['REGID'];
				$row['FINAL_PHONE'] = '';
				$row['FINAL_EMAIL'] = '';

				

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				$row['PHONE'] = is_numeric($row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';

				if (filter_var($row['EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['EMAIL']) > 0 && $row['EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				$row['P1_PHONE'] = is_numeric($row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';

				if (filter_var($row['P1_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P1_EMAIL']) > 0 && $row['P1_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				$row['P2_PHONE'] = is_numeric($row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';

				if (filter_var($row['P2_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P2_EMAIL']) > 0 && $row['P2_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P2_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));

				$row['FINAL_EMAIL'] = rtrim($row['FINAL_EMAIL'],', ');
				$row['FINAL_EMAIL'] = implode(", ",array_unique(explode(", ",$row['FINAL_EMAIL'])));

				// $row['FINAL_PHONE'] = ($row['PHONE'] && $row['PHONE'] != '') ? $row['PHONE'] : (($row['P1_PHONE'] && $row['P1_PHONE'] != '') ? $row['P1_PHONE'] : (($row['P2_PHONE'] && $row['P2_PHONE'] != '') ? $row['P2_PHONE'] : ''));
				$data['data_STUDENT'][] = $row;

				$REGID = $row['REGID'];
				$LOCATIONID = $row['LOCATIONID'];
				$FIRSTNAME = $row['FIRSTNAME'];
				$LASTNAME = $row['LASTNAME'];
				$FINAL_EMAIL = $row['FINAL_EMAIL'];


				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& SEND EMAIL DATA &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $LOCATIONID,'Registered',$regid,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$msgdb','TICKET_EMAIL',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				// $stmt=true;
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$GET_EMID = (int)$row['EMID'];
					

					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$MAIL = explode(", ",$FINAL_EMAIL);
					
					// echo json_encode($data);exit;
					
					$STmails = array();
					foreach($MAIL as $value){
						// EMAIL
						$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
						$data['mail'][] = $value;
					}
					$STmails = array_push_assoc($STmails, 'info@myexamsprep.com', 'HQ');
					$data['$STmails'][] = $STmails;



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
						// $mail->addAddress("shubham07v@gmail.com", "Shubham");
						$mail->addAddress($email, $name);
						$mail->Subject = 'myexamsprep:Alert';
						$mail->msgHTML($msg); 
						//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
						$mail->AltBody = 'HTML messaging not supported';
						// if($txtAttachment && $txtAttachment!='')$mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
						// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


						//USE AddCC When use foreach loop
						// foreach($STmails as $email => $name){
						// 	$mail->AddCC($email, $name); 
						// }

						if(!$mail->send()){
							// INSERT DETAILS
							$error_msg=$mail->ErrorInfo;
							$error_msg = str_replace("'","''",$error_msg);
							$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
							VALUES($GET_EMID,'$email','ERROR','$error_msg')";
							sqlsrv_query($mysqli, $query2);
							$data['query2'][] = $query2;
							// echo "Mailer Error: " . $mail->ErrorInfo;
							$data['Mail_ST'][] = $mail->ErrorInfo;
							$data['sss'] = $mail;
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
							$data['sss'] = $mail;
							$data['Mail_ST'] = "Message sent!";
							$data['success'] = true;
							$data['message'] = 'Sms Send successfully.';
						}

					}

					$data['querySuccess'][] = $query;
					$data['email_message'] = 'Email Send Successfully.';
	
				}
				
			}
			else{
				$data['email_message'] = 'Student Not Found For Email.';
			}
			return $data;
}
/*============ SEND EMAIL =============*/ 
function array_push_assoc($array, $key, $value){
	$array[$key] = $value;
	return $array;
}
function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







