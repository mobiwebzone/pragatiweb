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



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "saveDataSms":saveDataSms($conn);break;
        case "saveDataEmail":saveDataEmail($conn);break;
        case "getMSGHistory":getMSGHistory($conn);break;
		
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


// =============== SAVE SMS DATA ==================
function saveDataSms($mysqli){
	try
	{
		global $userid;
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
		
		// echo json_encode($data);exit;
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if($ddlStudentType == ''){throw new Exception("Please Select 'Student Type'.");}
		
		
		if($ddlStudentType == 'Registered'){
			if(count($studentid) <= 0)throw new Exception('Select Students First.');

			for($i=0; $i<count($studentid); $i++){
				$REGID = $studentid[$i]['id'];
				$FIRSTNAME = $studentid[$i]['FIRSTNAME'];
				$LASTNAME = $studentid[$i]['LASTNAME'];
				$FINAL_PHONE = $studentid[$i]['FINAL_PHONE'];
				$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '$ddlStudentType',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_PHONE','$txtMessage',$userid";
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
					// MESSAGE
					$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
					$auth_token = 'ad245122998781f49da657a66141cad6';
					$twilio_number = "+17039910242";

					$data['$FINAL_PHONE'][] = $FINAL_PHONE;
					$NUM = explode(", ",$FINAL_PHONE);

					forEach($NUM as $value){
						$data['num'][] = $value;
						
						$client = new Client($account_sid, $auth_token);
						$client->messages->create(
							// Where to send a text message (your cell phone?)
							// '+17035653342'
							$value,
							array(
								'from' => $twilio_number,
								'body' => $txtMessage
							)
						);
					}



					$data['querySuccess'][] = $query;
	
				}
			}
		}
		else{
			if($txtMobile == '') throw new Exception("Please Enter 'Mobile No.'");
			$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '$ddlStudentType',0,'$txtFirstName','$txtLastName','$txtMobile','$txtMessage',$userid";
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
				// MESSAGE
				$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
				$auth_token = 'ad245122998781f49da657a66141cad6';
				$twilio_number = "+17039910242";

				$client = new Client($account_sid, $auth_token);
				$client->messages->create(
					// Where to send a text message (your cell phone?)
					$txtMobile,
					array(
						'from' => $twilio_number,
						'body' => $txtMessage
					)
				);
				
				$data['querySuccess'][] = $query;
	
			}

		}
		$data['success'] = true;
		$data['message'] = 'Sms Send successfully.';

		
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
		// echo json_encode($data);exit;

		$txtFirstName = ($_POST['txtFirstName'] == 'undefined' || $_POST['txtFirstName'] == '') ? '' : str_replace("'","''",$_POST['txtFirstName']);
		$txtLastName = ($_POST['txtLastName'] == 'undefined' || $_POST['txtLastName'] == '') ? '' : str_replace("'","''",$_POST['txtLastName']);
		$txtEmail = ($_POST['txtEmail'] == 'undefined' || $_POST['txtEmail'] == '') ? '' : str_replace("'","''",$_POST['txtEmail']);
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);

		$msg = "";
		$msg .="
				<div style='border: 1px solid #DCEAEB; background: #ffd82b24;'>
					<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#ffd82b; color: #615136; font-weight: 800;'>MyExamPrep</h1>
						
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
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] '$ddlStudentType',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$txtMessage',$userid";
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

					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$MAIL = explode(", ",$FINAL_EMAIL);

					$STmails = array();
					foreach($MAIL as $value){
						// EMAIL
						$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);

						$data['mail'][] = $value;
					}
					// $STmails = array($dd);
					$data['$STmails'][] = $STmails;

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
						// $mail->addAddress($Email, $FirstName);
						$mail->Subject = 'myexamsprep:Alert';
						$mail->msgHTML($msg); 
						//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
						$mail->AltBody = 'HTML messaging not supported';
						$mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


						//USE AddCC When use foreach loop
						foreach($STmails as $email => $name){
							$mail->AddCC($email, $name); 
						}

						if(!$mail->send()){
							// echo "Mailer Error: " . $mail->ErrorInfo;
							$data['Mail_ST'] = $mail->ErrorInfo;
						}
						else{
							// echo "Message sent!";
							$data['Mail_ST'] = "Message sent!";
						}
					



					$data['querySuccess'][] = $query;
	
				}
			}
		}
		else{
			if($txtEmail == '') throw new Exception("Please Enter 'Email-ID'.");
			$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '$ddlStudentType',0,'$txtFirstName','$txtLastName','$txtMobile','$txtMessage',$userid";
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
				// MESSAGE
				$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
				$auth_token = 'ad245122998781f49da657a66141cad6';
				$twilio_number = "+17039910242";

				$client = new Client($account_sid, $auth_token);
				$client->messages->create(
					// Where to send a text message (your cell phone?)
					$txtMobile,
					array(
						'from' => $twilio_number,
						'body' => $txtMessage
					)
				);
				
				$data['querySuccess'][] = $query;
	
			}

		}
		$data['success'] = true;
		$data['message'] = 'Sms Send successfully.';

		
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
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,MOBILENO,TEXTMESSAGE
		FROM TEXT_MESSAGES 
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND MSGTYPE='OUTGOING API'
		ORDER BY MSGID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				
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
		
		$data = array();
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

		$query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(EMAIL,'')EMAIL,
		ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, ISNULL(P1_PHONE,'')P1_PHONE,ISNULL(P1_EMAIL,'')P1_EMAIL,
		ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,ISNULL(P2_PHONE,'')P2_PHONE,ISNULL(P2_EMAIL,'')P2_EMAIL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))
		ORDER BY label";

		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
				$row['FINAL_PHONE'] = '';
				$row['FINAL_EMAIL'] = '';

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';

				if (filter_var($row['EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['EMAIL']) > 0 && $row['EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL'].', ';
				}
				
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';

				if (filter_var($row['P1_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P1_EMAIL']) > 0 && $row['P1_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







