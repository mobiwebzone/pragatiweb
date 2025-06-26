<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

// if($_SESSION['ROLE'] != 'SUPERADMIN')
// {
// 	if(!empty($_SESSION['CLID']))
// 	{$userclid=$_SESSION['CLID'];}
// 	else
// 	{$userclid=0;}
// }
// else
// {
// 	$userclid=0;
// }

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "saveFranchise":saveFranchise($conn);break;
        case "getFranchise":getFranchise($conn);break;        
        case "deleteFranchise":deleteContact($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveFranchise($mysqli){
     try
     {
		$data = array();
        global $userid;
        
        $faid  = ($_POST['faid'] == 'undefined' || $_POST['faid'] == '') ? 0 : $_POST['faid'];        
        $txtfirstname  =  $_POST['txtfirstname'] == 'undefined'  ? '' : $_POST['txtfirstname'];
        $txtmiddlename  = $_POST['txtmiddlename'] == 'undefined' ? '' : $_POST['txtmiddlename'];
        $txtlastname  = $_POST['txtlastname'] == 'undefined' ? '' : $_POST['txtlastname'];
        $txtdob  = $_POST['txtdob'] == 'undefined' ? '' : $_POST['txtdob'];
        $txtcellphone  = $_POST['txtcellphone'] == 'undefined' ? '' : $_POST['txtcellphone'];
        $txtemail  =  $_POST['txtemail'] == 'undefined'  ? '' : $_POST['txtemail'];
        $txtaddress1  = $_POST['txtaddress1'] == 'undefined' ? '' : $_POST['txtaddress1'];
        $txtaddress2  = $_POST['txtaddress2'] == 'undefined' ? '' : $_POST['txtaddress2'];
        $txtcity  = $_POST['txtcity'] == 'undefined' ? '' : $_POST['txtcity'];
        $txtstate  = $_POST['txtstate'] == 'undefined' ? '' : $_POST['txtstate'];
        $txtzip  =  $_POST['txtzip'] == 'undefined'  ? '' : $_POST['txtzip'];
        $txtcitizen  = $_POST['txtcitizen'] == 'undefined' ? '' : $_POST['txtcitizen'];
        $txteducatBack  = $_POST['txteducatBack'] == 'undefined' ? '' : $_POST['txteducatBack'];
        $txtjobexp  = $_POST['txtjobexp'] == 'undefined' ? '' : $_POST['txtjobexp'];
        $txtbusiness  = $_POST['txtbusiness'] == 'undefined' ? '' : $_POST['txtbusiness'];
        $txttutoringexp  =  $_POST['txttutoringexp'] == 'undefined'  ? '' : $_POST['txttutoringexp'];
        $txtliquidfin  = $_POST['txtliquidfin'] == 'undefined' ? '' : $_POST['txtliquidfin'];
        $txtlistallfel  = $_POST['txtlistallfel'] == 'undefined' ? '' : $_POST['txtlistallfel'];
        $txtlistallpast  = $_POST['txtlistallpast'] == 'undefined' ? '' : $_POST['txtlistallpast'];        
		
		$actionid = $faid == 0 ? 1 : 2;

		if($txtfirstname == '')
		{throw new Exception("Enter Full Name.");}

		if($txtcellphone == '')
		{throw new Exception("Enter Phone.");}
        $data = array();

		
			$query="EXEC [FRANCHISE_APPLICATION_SP] $actionid,$faid, '$txtfirstname', '$txtmiddlename','$txtlastname','$txtdob','$txtcellphone','$txtemail','$txtaddress1','$txtaddress2','$txtcity','$txtstate','$txtzip','$txtcitizen','$txteducatBack','$txtjobexp','$txtbusiness','$txttutoringexp','$txtliquidfin','$txtlistallfel','$txtlistallpast'";
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
				sendMail('PERSON');
				sendMail('MYEXAMPREP');

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($faid))$data['message'] = 'Record successfully updated';
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



 //  SEND EMAIL
function sendMail($for){

		$txtfirstname  =  $_POST['txtfirstname'] == 'undefined'  ? '' : $_POST['txtfirstname'];
        $txtmiddlename  = $_POST['txtmiddlename'] == 'undefined' ? '' : $_POST['txtmiddlename'];
        $txtlastname  = $_POST['txtlastname'] == 'undefined' ? '' : $_POST['txtlastname'];
        $txtdob  = $_POST['txtdob'] == 'undefined' ? '' : $_POST['txtdob'];
        $txtcellphone  = $_POST['txtcellphone'] == 'undefined' ? '' : $_POST['txtcellphone'];
        $txtemail  =  $_POST['txtemail'] == 'undefined'  ? '' : $_POST['txtemail'];
        $txtaddress1  = $_POST['txtaddress1'] == 'undefined' ? '' : $_POST['txtaddress1'];
        $txtaddress2  = $_POST['txtaddress2'] == 'undefined' ? '' : $_POST['txtaddress2'];
        $txtcity  = $_POST['txtcity'] == 'undefined' ? '' : $_POST['txtcity'];
        $txtstate  = $_POST['txtstate'] == 'undefined' ? '' : $_POST['txtstate'];
        $txtzip  =  $_POST['txtzip'] == 'undefined'  ? '' : $_POST['txtzip'];
        $txtcitizen  = $_POST['txtcitizen'] == 'undefined' ? '' : $_POST['txtcitizen'];
        $txteducatBack  = $_POST['txteducatBack'] == 'undefined' ? '' : $_POST['txteducatBack'];
        $txtjobexp  = $_POST['txtjobexp'] == 'undefined' ? '' : $_POST['txtjobexp'];
        $txtbusiness  = $_POST['txtbusiness'] == 'undefined' ? '' : $_POST['txtbusiness'];
        $txttutoringexp  =  $_POST['txttutoringexp'] == 'undefined'  ? '' : $_POST['txttutoringexp'];
        $txtliquidfin  = $_POST['txtliquidfin'] == 'undefined' ? '' : $_POST['txtliquidfin'];
        $txtlistallfel  = $_POST['txtlistallfel'] == 'undefined' ? '' : $_POST['txtlistallfel'];
        $txtlistallpast  = $_POST['txtlistallpast'] == 'undefined' ? '' : $_POST['txtlistallpast'];

		$data = array();
		$STmails = array();

		if($for == 'PERSON'){
			$STmails = array(
				$txtemail => $txtfirstname,
			);
		}else{
			
			$STmails = array(
				'info@myexamsprep.com' => 'HQ',
			);
			// if($ddlLocation > 1){
			// 	$STmails = array(
			// 		'info@myexamsprep.com' => 'HQ',
			// 		$LOCEMAIL => $LNAME,
			// 	);
			// }else{
			// 	$STmails = array(
			// 		'info@myexamsprep.com' => 'HQ',
			// 		'clboy1997@gmail.com' => 'SHUBHAM',
			// 	);
			// }
		}





		$msg = "";

		if($for == 'PERSON'){
			$msg .= "Hello,<br/>
					We’re writing to confirm that we’ve received your registration form. Our team is working on this and will be in with you touch soon! <br/>
							
					Thanks <br/>
					MyExamsPrep";
		}
		else{

			$msg .="
						<div style='border:1px solid #DCEAEB'>
						<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#D8ECF5; color: #628fa2'>FRANCHISE</h1>
					
						<table style='font-family:Arial; margin: 25px 40px; width: 90%;'>
							<tr>
								<td style='width:100px;'>First Name</td><td style='width:10px'>:</td><td>$txtfirstname</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Middle Name</td><td style='width:10px'>:</td><td>$txtmiddlename</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Last Name</td><td style='width:10px'>:</td><td>$txtlastname</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Date of Birth</td><td style='width:10px'>:</td><td>$txtdob</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Cell Phone</td><td style='width:10px'>:</td><td>$txtcellphone</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
					
							<tr>
								<td style='width:100px;'>Email</td><td style='width:10px'>:</td><td><a href='mailto:".$txtemail."' style='color:#118bf2; text-decoration:none'>$txtemail</a></td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Address1</td><td style='width:10px'>:</td><td>$txtaddress1</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							
							<tr>
								<td style='width:100px;'>Address2</td><td style='width:10px'>:</td><td>$txtaddress2</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
							<tr>
								<td style='width:100px;'>City/State</td><td style='width:10px'>:</td><td>".$txtcity." / ".$txtstate."</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Zip</td><td style='width:10px'>:</td><td>$txtzip</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Citizenship Country</td><td style='width:10px'>:</td><td>$txtcitizen</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							

							<tr>
								<td style='width:100px;'>Educational Background</td><td style='width:10px'>:</td><td>$txteducatBack</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Job Experience</td><td style='width:10px'>:</td><td>$txtjobexp</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Business Experience</td><td style='width:10px'>:</td><td>$txtbusiness</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>


							<tr>
								<td style='width:100px;'>Tutoring Experience</td><td style='width:10px'>:</td><td>$txttutoringexp</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>Liquid Financial</td><td style='width:10px'>:</td><td>$txtliquidfin</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							<tr>
								<td style='width:100px;'>List all felony</td><td style='width:10px'>:</td><td>$txtlistallfel</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
							
							<tr>
								<td style='width:150px;'>personal/business filings</td><td style='width:10px'>:</td><td>$txtlistallpast</td>
							</tr>
							<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>


						</table>
						
					</div>
			";
		}


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
			$mail->Subject = 'myexamsprep:Registration';
			$mail->msgHTML($msg); 
			//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
			$mail->AltBody = 'HTML messaging not supported';
			// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


			//USE AddCC When use foreach loop
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

/*============ Get Countries =============*/ 
 function getFranchise($mysqli){
	try
	{

		$query = "SELECT FAID, FIRSTNAME,MIDDLENAME,LASTNAME, BIRTHDATE,PHONE,EMAILID,ADDRESS1,ADDRESS2,CITY,STATE,ZIPCODE,CITIZEN,
EDUCATION,JOBEXP,BUSIEXP,TUTEXP,LIQFINRESOURCE,FELONY,PASTPERSONAL from FRANCHISE_APPLICATION";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['FAID'] = (int) $row['FAID'];
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



/* =========== Delete =========== */ 
function deleteFranchise($mysqli){
	try{   
			global $userid;
			$data = array();     
            $faid = ($_POST['faid'] == 'undefined' || $_POST['faid'] == '') ? 0 : $_POST['faid'];  
			$stmt=sqlsrv_query($mysqli, "DELETE FROM FRANCHISE_APPLICATION WHERE FAID=$faid");
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
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







