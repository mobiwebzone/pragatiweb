<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['USER_LOCID']))
{$locid=$_SESSION['USER_LOCID'];}
else
{$locid=0;}

require __DIR__ . '../../../Twilio/autoload.php';
use Twilio\Rest\Client;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "saveContact":saveContact($conn);break;
        case "getContact":getContact($conn);break;
        case "getLocation":getLocation($conn);break;
        case "deleteContact":deleteContact($conn);break;

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





 /*============ Save Contacts =============*/ 
 function saveContact($mysqli){
     try
     {
		$data = array();
        global $userid;
        
        $cid  = ($_POST['cid'] == 'undefined' || $_POST['cid'] == '') ? 0 : $_POST['cid'];
        $ddlLocation  =  ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '')  ? 0 : $_POST['ddlLocation'];
        $txtname  =  $_POST['txtname'] == 'undefined'  ? '' : $_POST['txtname'];
        $txtemail  = $_POST['txtemail'] == 'undefined' ? '' : $_POST['txtemail'];
        $txtphone  = $_POST['txtphone'] == 'undefined' ? '' : $_POST['txtphone'];
        $txtsubject  = $_POST['txtsubject'] == 'undefined' ? '' : $_POST['txtsubject'];
		$txtsubject = str_replace("'","''",$txtsubject);
        $txtmessage  = $_POST['txtmessage'] == 'undefined' ? '' : $_POST['txtmessage'];        
		$txtmessage = str_replace("'","''",$txtmessage);
        $txtResponse  = $_POST['txtResponse'] == 'undefined' ? '' : $_POST['txtResponse'];        
		
		$actionid = $cid == 0 ? 1 : 2;

		if($ddlLocation == 0){throw new Exception("Select Your Location.");}
		if($txtname == ''){throw new Exception("Enter Your Full Name.");}
		if($txtemail == ''){throw new Exception("Enter Your Email.");}
		if($txtphone == ''){throw new Exception("Enter Your Phone Number.");}
		if($txtsubject == ''){throw new Exception("Enter Subject.");}
		if($txtmessage == ''){throw new Exception("Enter Your Message.");}
        $data = array();

			$query="EXEC [CONTACTUS_SP] $actionid,$cid, $ddlLocation, '$txtname','$txtemail','$txtphone','$txtsubject','$txtmessage'";
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
				if($actionid == 2){
					$txtResponse = str_replace("'","''",$txtResponse);
					$UpdResQuery="UPDATE CONTACTUS SET RESPONSE='$txtResponse' WHERE CID=$cid";
					$stmtUpdRes=sqlsrv_query($mysqli, $UpdResQuery);
				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($cid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				
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
 /*============ Save Contacts =============*/ 







/*============ Get Contacts =============*/ 
 function getContact($mysqli){
	try
	{
		$data = array();

		$ddlLocationSearch = ($_POST['ddlLocationSearch'] == 'undefined' || $_POST['ddlLocationSearch'] == '') ? 0 : $_POST['ddlLocationSearch'];

		$query = "SELECT CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE,CID,FULLNAME,
		ISNULL(CASE WHEN EMAILID='' OR EMAILID='null' OR EMAILID LIKE'%TBD%' OR EMAILID LIKE'%@na%' OR EMAILID IS NULL THEN '' ELSE EMAILID END,'') EMAILID,
		ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE,
		[SUBJECT], [MESSAGE], LOCATIONID, 
		(SELECT [LOCATION] from LOCATIONS WHERE ISDELETED=0 AND LOC_ID=C.LOCATIONID)[LOCATION], RESPONSE
		FROM CONTACTUS C";

		if($ddlLocationSearch>0){
			$query .=" WHERE LOCATIONID=$ddlLocationSearch";
		}else{
			if($_SESSION['USER_LOCID'] != '1')$query .=" WHERE LOCATIONID=".$_SESSION['USER_LOCID']."";
		}


		$query .=" ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";
		$data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['CID'] = (int) $row['CID'];

			// %%%%%%%% CHECK PHONE %%%%%%%%%%
			$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
			$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
			$row['PHONE'] = is_numeric($row['PHONE']) ? $row['PHONE'] : '';
			$row['PHONE'] = (strlen($row['PHONE']) > 0 && strlen($row['PHONE']) > 7 && strlen($row['PHONE']) < 14) ? $row['PHONE'] : '';
			// %%%%%%%% CHECK PHONE %%%%%%%%%%

			// %%%%%%%% CHECK EMAIL %%%%%%%%%%
			if (filter_var($row['EMAILID'], FILTER_VALIDATE_EMAIL)) {
				$row['EMAILID'] = (strlen($row['EMAILID']) > 0 && $row['EMAILID']!='NaN') ? $row['EMAILID'] : '';
			}else{
				$row['EMAILID'] = '';
			}
			// %%%%%%%% CHECK EMAIL %%%%%%%%%%
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
/*============ Get Contacts =============*/ 







/*============ Get Location =============*/ 
function getLocation($mysqli){
	try
	{
		$query = "SELECT LOC_ID,LOCATION,LOC_DESC,LOC_CONTACT,LOC_PERSON,LOC_EMAIL,LOC_ADDRESS_LINE1,LOC_ADDRESS_LINE2,
				LOC_CITY,LOC_STATE,LOC_COUNTRY,LOC_ZIPCODE,IS_ET,LOC_ET_DIFF,CURRENCY_ID,
				(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=LOCATIONS.CURRENCY_ID)CURRENCY_CODE,
				LOGIN_ID,DBO.GET_CLEAR_LOCATION_PASSWORD(LOC_ID) LOGIN_PWD,FLAG_ICON
				FROM LOCATIONS WHERE ISDELETED=0";

		if($_SESSION['USER_LOCID'] != '1'){
			$query .=" AND LOC_ID=".$_SESSION['USER_LOCID']."";
		}

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
			$row['CURRENCY_ID'] = (int) $row['CURRENCY_ID'];
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
/*============ Get Location =============*/ 







/* =========== Delete =========== */ 
function deleteContact($mysqli){
	try{   
			global $userid;
			$data = array();     
            $cid = ($_POST['cid'] == 'undefined' || $_POST['cid'] == '') ? 0 : $_POST['cid'];  
			if($cid == 0){throw new Exception('Cid Not Found.');}
			$stmt=sqlsrv_query($mysqli, "DELETE FROM CONTACTUS WHERE CID=$cid");
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
/* =========== Delete =========== */ 















// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SMS / EMAIL %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// =============== SAVE SMS DATA ==================
function saveDataSms($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		// $ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : $_POST['ddlStudentType'];

		$ContactData = (!empty($_POST['ContactData'])) ? $_POST['ContactData'] : '';
		if($ContactData == '')throw new Exception('Contact Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		// for($i=0; $i<count($ContactData); $i++){
		// 	$data['CID'][]=$ContactData[$i]['CID'];
		// }
		// echo json_encode($data);exit;	
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);
		
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Contact First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['PHONE'];
			if(!$p || $p=='') throw new Exception('Selected Contact Number Not Found.');
		}


		for($i=0; $i<count($ContactData); $i++){
			$CID = $ContactData[$i]['CID'];
			$FIRSTNAME = $ContactData[$i]['FULLNAME'];
			$FINAL_PHONE = $ContactData[$i]['PHONE'];

			if($FINAL_PHONE && $FINAL_PHONE!='' && strlen($FINAL_PHONE) > 0 && strlen($FINAL_PHONE) > 7 && strlen($FINAL_PHONE) < 15){
				$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'',$CID,'$FIRSTNAME','','$FINAL_PHONE','$txtMessage','CONTACTUS_SMS',$userid";
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
	
					
					$client = new Client($account_sid, $auth_token);
					try{
						$client->messages->create(
							// Where to send a text message (your cell phone?)
							// '+17035653342'
							$FINAL_PHONE,
							array(
								'from' => $twilio_number,
								'body' => $txtMessage
							)
						);

						// INSERT DETAILS
						$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
						VALUES($GET_MSGID,'$FINAL_PHONE','SUCCESS')";
						$stmt2=sqlsrv_query($mysqli, $query2);
						$data['query2'][] = $query2;
						$data['success'] = true;

					}catch (\Twilio\Exceptions\RestException $e) {
						$data['$e'] = $e;
						$error_msg=$e->getMessage();
						$error_msg = str_replace("'","''",$error_msg);
						// INSERT DETAILS
						$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
						VALUES($GET_MSGID,'$FINAL_PHONE','ERROR','$error_msg')";
						$stmt2=sqlsrv_query($mysqli, $query2);
						$data['query2'][] = $query2;

						// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
						$data['success'] = false;
						// $data['error'] = var_dump($e);
						$data['message'] = $e->getMessage();
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

		// $ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : $_POST['ddlStudentType'];

		$ContactData = (!empty($_POST['ContactData'])) ? $_POST['ContactData'] : '';
		if($ContactData == '')throw new Exception('Contact Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);

		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Contact First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['EMAILID'];
			if(!$p || $p=='') throw new Exception('Selected Contact Email Id Not Found.');
			if(!filter_var($p, FILTER_VALIDATE_EMAIL)) throw new Exception("Selected Contact Email Id Not Valid.");
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
			$CID = $ContactData[$i]['CID'];
			$FIRSTNAME = $ContactData[$i]['FULLNAME'];
			$FINAL_EMAIL = $ContactData[$i]['EMAILID'];
			if($FINAL_EMAIL!=''){
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'',$CID,'$FIRSTNAME','','$FINAL_EMAIL','$txtMessage','CONTACTUS_EMAIL',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
					// echo json_encode($data);exit;
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
							$FINAL_EMAIL => $FIRSTNAME,
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
		AND MSGTYPE='OUTGOING API' AND MSG_FROM='CONTACTUS_SMS' AND LOCID=$locid";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND INSERTID IN (SELECT [UID] FROM USERS WHERE LOCID=".$_SESSION['USER_LOCID'].")";
		// }
		$query .=" ORDER BY MSGID DESC";

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
		AND EMAILTYPE='OUTGOING API' AND EMAIL_FROM='CONTACTUS_EMAIL' AND LOCID=$locid";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND INSERTID IN (SELECT [UID] FROM USERS WHERE LOCID=".$_SESSION['USER_LOCID'].")";
		// }
		$query .=" ORDER BY EMID DESC";

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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







