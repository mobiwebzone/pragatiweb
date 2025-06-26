<?php
session_start();
require_once '../../code/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "CheckUserID":CheckUserID($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function CheckUserID($mysqli){
	try{
	    $data = array();
        $txtLoginId = $_POST['txtLoginId'] == 'undefined' ? '' : $_POST['txtLoginId'];
        
        if($txtLoginId == "")
        {
            throw new Exception( "Enter Your User ID.");
        }

        
        $queryUser = "SELECT REGID FROM REGISTRATIONS WHERE LOGINID='$txtLoginId' AND ISDELETED=0";
        $row_count=unique($queryUser);

        if($row_count > 0)
        {

            $resultUser = sqlsrv_query($mysqli, $queryUser);
            $rowUser = sqlsrv_fetch_array($resultUser);
            $ret=(int) $rowUser['REGID'];
        

        
            if($ret > 0)
            {
                $queryEmail = "SELECT FIRSTNAME,LASTNAME,DBO.GET_CLEAR_STUDENT_PASSWORD($ret) AS PASS,
                CASE WHEN (EMAIL != '' OR EMAIL != null) THEN EMAIL 
                WHEN (P1_EMAIL != '' OR P1_EMAIL != null) THEN P1_EMAIL
                WHEN (P2_EMAIL != '' OR P2_EMAIL != null) THEN P2_EMAIL
                END EMAIL
                FROM REGISTRATIONS WHERE REGID=$ret";
                $resultEmail = sqlsrv_query($mysqli, $queryEmail);
                $rowEmail = sqlsrv_fetch_array($resultEmail);

                $Email = $rowEmail['EMAIL'];
                $SEND_EMAIL = $rowEmail['EMAIL'];
                $ST_NAME = $rowEmail['FIRSTNAME'];
                $PASS = $rowEmail['PASS'];


                $ChkApprove="SELECT APPROVED FROM REGISTRATIONS WHERE REGID=$ret AND ISDELETED=0";
                $stmtApprove = sqlsrv_query($mysqli, $ChkApprove);
                $rowApprove = sqlsrv_fetch_array($stmtApprove);

                if($rowApprove['APPROVED'] == 1){

                    $stars = 4; // Min Stars to use
                    $at = strpos($Email,'@');
                    if($at - 2 > $stars) $stars = $at - 2;
                    $Email = substr($Email,0,3) . str_repeat('*',$stars) . substr($Email,$at - 1);
                    // $Email = str_replace(substr($Email,1,6),str_repeat('*',6),$Email);



                    sendMail($SEND_EMAIL,$ST_NAME,$PASS);
                    $data['success'] = true;
                    $data['EMAIL'] = $Email;
                    $data['message'] = 'Password has been emailed to your registered email ID '. $Email .' Pleaase check your email box.';
                }
                else{
                    $data['success'] = false;
                    $data['message'] = 'You are not Approved for login.';
                }
                    

                
                
            }
        }
        else
        {
            $data['userid'] = $queryUser;
            $data['success'] = false;
            $data['message'] = 'Wrong User ID.';
            // $data['message'] = 'We could not verify your credentials. Please double-check and try again.';
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


//  SEND EMAIL
function sendMail($SEND_EMAIL,$ST_NAME,$PASS){

    $data = array();
    $STmails = array();

    $STmails = array(
        $SEND_EMAIL => $ST_NAME
    );

    $msg = "";

            $msg .="
                <div style='border:1px solid #DCEAEB; background:#fffff5;  font-family: sans-serif;'>
                    <h1 style='text-align: left; font-family:Arial; font-size:17px; font-weight:normal; padding7px 0px; padding-left:15px; margin:0px; background:#ffce43; color: #a95d00'>
                        FORGOT PASSWORD
                    </h1>

                    <h2 style='color: #fdaf00; text-transform: uppercase; word-spacing: 10px; letter-spacing: 3px; padding: 15px; padding-left: 40px;'>
                        Hello $ST_NAME,
                    </h2>

                    <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>Your current password is <b><u>$PASS</u></b> </p>

                    <p style='padding-left: 2.8rem; padding-top: 10px; border-top: 1px solid #e6d4ad;color: #bbbbbb; font-size: 11px; letter-spacing: 1px;'> 
                        www.myexamsprep.us
                    </p>

                </div>";


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
            $mail->Subject = 'myexamsprep:Forgot Password';
            $mail->msgHTML($msg); 
            //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
            $mail->AltBody = 'HTML messaging not supported';
            // $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


            foreach($STmails as $email => $name){
                $mail->AddCC($email, $name);
            }

            if(!$mail->send()){
                // echo "Mailer Error: " . $mail->ErrorInfo;
                $data['Mail_ST'] = "Message sent!";
            }
            else{
                // echo "Message sent!";
                $data['Mail_ST'] = $mail->ErrorInfo;
            }
}


function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['MEP_USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
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







