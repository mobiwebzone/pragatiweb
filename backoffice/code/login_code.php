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
		case "login":login($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function login($mysqli){
	try{
	    $data = array();
        // $ipadd = getUserIP();
        $txtLoginId = $_POST['txtLoginId'] == 'undefined' ? '' : $_POST['txtLoginId'];
        $txtPWD = $_POST['txtPWD'] == 'undefined' ? '' : $_POST['txtPWD'];   
        $datetime = $_POST['datetime'] == 'undefined' ? '-' : $_POST['datetime'];   
        $IP = ($_POST['IP'] == 'undefined' || $_POST['IP'] == '') ? 'unknown' : $_POST['IP'];   
        $TIMEZONE = $_POST['TIMEZONE'] == 'undefined' ? '' : $_POST['TIMEZONE'];   
        $COUNTRYNAME = $_POST['COUNTRYNAME'] == 'undefined' ? 'unknown' : $_POST['COUNTRYNAME'];   
        
        if($txtLoginId =="")
        {
            throw new Exception( "Enter Correct User ID");
        }
        if($txtPWD =="")
        {
            $data['success'] = false;
            $data['message'] = 'Enter Correct Password.';
        }
        
        $queryUser = "SELECT UID FROM USERS WHERE LOGINID='$txtLoginId' AND ISDELETED=0 AND USERROLE NOT IN ('TEACHER','VOLUNTEER')";
        $row_count=unique($queryUser);

        
        if($row_count > 0)
        {
            
            
            $resultUser = sqlsrv_query($mysqli, $queryUser);
            $rowUser = sqlsrv_fetch_array($resultUser);
            $ret=$rowUser['UID'];
            
            
            if($ret > 0)
            {
                
                $queryPwd = "SELECT DBO.GET_CLEAR_USER_PASSWORD($ret) PWD";
                $resultPwd = sqlsrv_query($mysqli, $queryPwd);
                $rowPwd = sqlsrv_fetch_array($resultPwd);
                
                
                if($rowPwd['PWD'] == $txtPWD)
                {

                    $query="SELECT UID,FIRSTNAME,LASTNAME,MOBILE,EMAIL,USERROLE,LOCID,
                        (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)[LOCATION],
                        (SELECT IS_ET FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)IS_ET
                         FROM USERS WHERE UID=$ret AND ISDELETED=0";
                    $stmt = sqlsrv_query($mysqli, $query);
                    while($row = sqlsrv_fetch_array($stmt))
                    {
                        $row['UID'] = (int) $row['UID'];
                        $UID = (int) $row['UID'];
                        $EMAIL=$row['EMAIL'];
                        $NAME=$row['FIRSTNAME'];
                        $LOCATION=$row['LOCATION'];

                        $_SESSION['MEP_USERID']=$row['UID'];
                        $_SESSION['FNAME']=$row['FIRSTNAME'];
                        $_SESSION['LNAME']=$row['LASTNAME'];
                        $_SESSION['ROLE']=$row['USERROLE'];
                        $_SESSION['USER_LOCID']=$row['LOCID'];
                        $_SESSION['LOCATION']=$row['LOCATION'];
                        $_SESSION['IS_ET']=$row['IS_ET'];



                        $role=$row['USERROLE'];
                        
                        $data['data'][] = $row;
                        
                    }
                    // $data['IP'] = getUserIP();
                    // UPDATE LOG
                    $logQuery = "INSERT INTO LOGINS_LOG (LOGINTYPE,USERID,LOGINDT,IPADDRESS,LOGINSTATUS)
                                VALUES ('BACKOFFICE',$ret,GETDATE(),'$IP','SUCCESS')";
                    sqlsrv_query($mysqli, $logQuery);
                    
                    // $data['TIMEZONE'] = $TIMEZONE;
                    if($TIMEZONE || $TIMEZONE!=''){
                        sendMail($EMAIL,$NAME,$LOCATION,$datetime,$IP,$TIMEZONE,$COUNTRYNAME);
                    }
                        

                    $data['role'] = $role;
                    // $data['query'] = $query;
                    $data['success'] = true;
                    $data['message'] = 'Signing In...';
                }
                else {    
                    // UPDATE LOG
                    $logQuery = "INSERT INTO LOGINS_LOG (LOGINTYPE,USERID,LOGINDT,IPADDRESS,LOGINSTATUS)
                                VALUES ('BACKOFFICE',$ret,GETDATE(),'$IP','FAILED')";
                    sqlsrv_query($mysqli, $logQuery);                                
                    
                    $data['success'] = false;
                    $data['message'] = 'Wrong password';
                }
                
                
            }
            
        }
        else
        {
            // $data['userid'] = $queryUser;
            $data['success'] = false;
            $data['message'] = 'Wrong Login ID.';
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
function sendMail($SEND_EMAIL,$NAME,$LOCATION,$datetime,$ip,$timezone,$COUNTRYNAME){
    date_default_timezone_set($timezone);
    $date = date('h:i A | d-M-Y');

    $data = array();
    $STmails = array();

    $STmails = array(
        $SEND_EMAIL => $NAME
    );

    $msg = "";

            $msg .="
                <div style='border:1px solid #DCEAEB; background:#fffff5;  font-family: sans-serif;'>
                    <h1 style='text-align: left; font-family:Arial; font-size:17px; font-weight:normal; padding:7px 0px; padding-left:15px; margin:0px; background:#ffce43; color: #a95d00'>
                        Login Alert
                    </h1>

                    <h2 style='color: #fdaf00; text-transform: uppercase; word-spacing: 10px; letter-spacing: 3px; padding: 15px; padding-left: 40px;'>
                        Hi $NAME,
                    </h2>



                    
                    <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>
                        IP : $ip
                    </p>
                    <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>
                        Country : $COUNTRYNAME
                    </p>
                    <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>
                        Time : $date
                    </p>
                    <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>
                        Time Zone : $timezone
                    </p>


                    <p style='padding-left: 2.8rem; padding-top: 10px; border-top: 1px solid #e6d4ad;color: #bbbbbb; font-size: 11px; letter-spacing: 1px;'> 
                        www.myexamsprep.us
                    </p>

                </div>";
                // <p style='margin:0px; padding-left: 45px; padding-bottom: 15px;'>
                //         Login Location : $LOCATION
                //     </p>


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
            $mail->Subject = 'myexamsprep:Login';
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




// Function to get the client IP address
function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







