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
        case "saveDataSms":saveDataSms($conn);break;
        case "saveDataEmail":saveDataEmail($conn);break;
        case "getMSGHistory":getMSGHistory($conn);break;
        case "getEMAILHistory":getEMAILHistory($conn);break;
		
		// ============ EXTRA ===========
        case "getPlans":getPlans($conn);break;
        case "getClassof":getClassof($conn);break;
        case "getSubject":getSubject($conn);break;
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


// =============== SAVE SMS DATA ==================
function saveDataSms($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : $_POST['ddlStudentType'];

		$studentid = (!empty($_POST['STUDENTID'])) ? $_POST['STUDENTID'] : '';
		if($studentid == '')throw new Exception('STUDENTID Not Found.');
		$studentid =json_decode($studentid,true);
		$data['$studentid']=$studentid;
		
		$txtFirstName = ($_POST['txtFirstName'] == 'undefined' || $_POST['txtFirstName'] == '') ? '' : str_replace("'","''",$_POST['txtFirstName']);
		$txtLastName = ($_POST['txtLastName'] == 'undefined' || $_POST['txtLastName'] == '') ? '' : str_replace("'","''",$_POST['txtLastName']);
		$txtMobile = ($_POST['txtMobile'] == 'undefined' || $_POST['txtMobile'] == '') ? '' : str_replace("'","''",$_POST['txtMobile']);
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);
		
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if($ddlStudentType == ''){throw new Exception("Please Select 'Student Type'.");}
		
		
		if($ddlStudentType == 'Registered'){
			if(count($studentid) <= 0)throw new Exception('Select Students First.');

			for($i=0; $i<count($studentid); $i++){
				$REGID = $studentid[$i]['id'];
				$FIRSTNAME = $studentid[$i]['FIRSTNAME'];
				$LASTNAME = $studentid[$i]['LASTNAME'];
				$FINAL_PHONE = $studentid[$i]['FINAL_PHONE'];
				$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'$ddlStudentType',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_PHONE','$txtMessage','STUDENT_SMS',$userid";
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

					$data['$FINAL_PHONE'][] = $FINAL_PHONE;
					$NUM = explode(", ",$FINAL_PHONE);

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
		else{
			// 	$data['$txtMobile']=$txtMobile;
			// echo json_encode($data);exit;
			if($txtMobile == '') throw new Exception("Please Enter 'Mobile No.'");
			// $valid = strpos("+17035653342","+1");
			$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'$ddlStudentType',0,'$txtFirstName','$txtLastName','$txtMobile','$txtMessage','STUDENT_SMS',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			// $stmt=true;
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['queryFail'][] = $query;
				echo json_encode($data);exit;
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

				$client = new Client($account_sid, $auth_token);
				try{
					$client->messages->create(
						// Where to send a text message (your cell phone?)
						$txtMobile,
						array(
							'from' => $twilio_number,
							'body' => $txtMessage
						)
					);

					// INSERT DETAILS
					$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
					VALUES($GET_MSGID,'$txtMobile','SUCCESS')";
					$stmt2=sqlsrv_query($mysqli, $query2);
					$data['query2'] = $query2;
					
					$data['success'] = true;
					$data['message'] = 'Sms Send successfully.';
				}catch (\Twilio\Exceptions\RestException $e) {
					// INSERT DETAILS
					$error_msg=$e->getMessage();
					$error_msg = str_replace("'","''",$error_msg);
					$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
					VALUES($GET_MSGID,'$txtMobile','ERROR','$error_msg')";
					$stmt2=sqlsrv_query($mysqli, $query2);
					$data['query2'] = $query2;

					// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
					$data['success'] = false;
					// $data['error'] = var_dump($e);
					$data['message'] = $e->getMessage();
				}

				// $data['$client']=print($message->sid);
				
				$data['querySuccess'][] = $query;
	
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

		$ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : $_POST['ddlStudentType'];

		$studentid = (!empty($_POST['STUDENTID'])) ? $_POST['STUDENTID'] : '';
		if($studentid == '')throw new Exception('STUDENTID Not Found.');
		$studentid =json_decode($studentid,true);
		$data['$studentid']=$studentid;
		
		$txtFirstName = ($_POST['txtFirstName'] == 'undefined' || $_POST['txtFirstName'] == '') ? '' : str_replace("'","''",$_POST['txtFirstName']);
		$txtLastName = ($_POST['txtLastName'] == 'undefined' || $_POST['txtLastName'] == '') ? '' : str_replace("'","''",$_POST['txtLastName']);
		$txtEmail = ($_POST['txtEmail'] == 'undefined' || $_POST['txtEmail'] == '') ? '' : str_replace("'","''",$_POST['txtEmail']);
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);

		

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
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if($ddlStudentType == ''){throw new Exception("Please Select 'Student Type'.");}
		
		
		if($ddlStudentType == 'Registered'){
			if(count($studentid) <= 0)throw new Exception('Select Students First.');

			for($i=0; $i<count($studentid); $i++){
				$REGID = $studentid[$i]['id'];
				$FIRSTNAME = $studentid[$i]['FIRSTNAME'];
				$LASTNAME = $studentid[$i]['LASTNAME'];
				$FINAL_EMAIL = $studentid[$i]['FINAL_EMAIL'];
				// if(!filter_var($FINAL_EMAIL, FILTER_VALIDATE_EMAIL)) throw new Exception("Please Enter Valid 'Email-ID'.");
				
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'$ddlStudentType',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$txtMessage','STUDENT_EMAIL',$userid";
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
					

					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$MAIL = explode(", ",$FINAL_EMAIL);

					$STmails = array();
					foreach($MAIL as $value){
						// EMAIL
						$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
						$data['mail'][] = $value;
					}
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
						if($txtAttachment && $txtAttachment!='')$mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
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
	
				}
			}
		}
		else{
			// if($txtEmail == '') throw new Exception("Please Enter 'Email-ID'.");
			// if(!filter_var($txtEmail, FILTER_VALIDATE_EMAIL)) throw new Exception("Please Enter Valid 'Email-ID'.");
			$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'$ddlStudentType',0,'$txtFirstName','$txtLastName','$txtEmail','$txtMessage','STUDENT_EMAIL',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['queryFail'][] = $query;
				echo json_encode($data);exit;
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


					$STmails = array();
					$STmails = array(
						$txtEmail => $txtFirstName,
					);

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
							$data['message'] = 'Sms Send successfully.';
							$data['success'] = true;
						}
					}

						

				
				$data['querySuccess'][] = $query;
	
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
 // =============== SAVE EMAIL DATA ==============
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
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND MSGTYPE='OUTGOING API' AND MSG_FROM='STUDENT_SMS' AND LOCID=$locid
		ORDER BY MSGID DESC";

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
		AND EMAILTYPE='OUTGOING API' AND EMAIL_FROM='STUDENT_EMAIL'
		AND LOCID=$locid
		ORDER BY EMID DESC";

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

/*============ Get Class Of =============*/ 
function getClassof($mysqli){
	try
	{
		// $query = "SELECT PLANID AS id,PLANNAME AS label
		// FROM PLANS WHERE ISDELETED=0 ORDER BY label";
		$query = "SELECT DISTINCT TOP 30 CLASSOF id,CLASSOF label FROM REGISTRATIONS WHERE ISDELETED=0 AND LEN(CLASSOF)=4 ORDER BY label DESC";
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
/*============ Get Class Of =============*/ 

/*============ Get Subject =============*/ 
function getSubject($mysqli){
	try
	{
		$query = "SELECT DISTINCT CSUBID id,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SPC.CSUBID)label 
		FROM STUDENT_PROPOSED_COURSES SPC WHERE ISDELETED=0 ORDER BY label";
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
/*============ Get Subject =============*/ 



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
		global $locid;
		$data = array();
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$data['$planid']=$planid;
		$classof = (!empty($_POST['CLASSOF'])) ? $_POST['CLASSOF'] : '';
		// if($classof == '')throw new Exception('Class Of Not Found.');
		$classof = $classof !== '' ? implode(',',$classof) : '';
		$data['$classof']=$classof;
		$subject = (!empty($_POST['SUBJECT'])) ? $_POST['SUBJECT'] : '';
		// if($subject == '')throw new Exception('Subject Not Found.');
		$subject = $subject !== '' ? implode(',',$subject) : '';
		$data['$subject']=$subject;

		$ddlLocation = ($_POST['ddlLocation']=='undefined' || $_POST['ddlLocation']=='') ? 0 : $_POST['ddlLocation'];
		$LID = $ddlLocation>0?$ddlLocation:$locid;

		// $query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(EMAIL,'')EMAIL,
		// ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, ISNULL(P1_PHONE,'')P1_PHONE,ISNULL(P1_EMAIL,'')P1_EMAIL,
		// ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,ISNULL(P2_PHONE,'')P2_PHONE,ISNULL(P2_EMAIL,'')P2_EMAIL
		// FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))
		// ORDER BY label";

		$query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,
		ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE,
		ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL,
		ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, 
		ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE,
		ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL,
		ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,
		ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE,
		ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$LID AND APPROVED=1 AND
		REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))";
		if($classof!='') $query .= " AND CLASSOF IN ($classof)";
		if($subject!='') $query .= " AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE CSUBID IN ($subject))";
		$query .= " ORDER BY label";

		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		$data['COUNT'] = $count;
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







