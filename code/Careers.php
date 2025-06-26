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


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "saveCareers":saveCareers($conn);break;
        case "getLocation":getLocation($conn);break;
        case "getPlans":getPlans($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveCareers($mysqli){
     try
     {
		$data = array();
        global $userid;
        
        $cid  = ($_POST['cid'] == 'undefined' || $_POST['cid'] == '') ? 0 : $_POST['cid'];        
        $ddlLocation =  ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '')  ? 0 : $_POST['ddlLocation'];
        $txtname     =  $_POST['txtname'] == 'undefined'  ? '' : $_POST['txtname'];
        $txtemail    =  $_POST['txtemail'] == 'undefined'  ? '' : $_POST['txtemail'];   
        $txtphone    =  $_POST['txtphone'] == 'undefined'  ? '' : $_POST['txtphone'];
        $txtmaddress =  $_POST['txtmaddress'] == 'undefined'  ? '' : $_POST['txtmaddress'];
        $ddlplan     =  $_POST['ddlplan'] == 'undefined'  ? '' : $_POST['ddlplan'];
        $ddlplan     =  ltrim($ddlplan," , ");
        $txtedubackground =  $_POST['txtedubackground'] == 'undefined'  ? '' : $_POST['txtedubackground'];
        $txtworkexp =  $_POST['txtworkexp'] == 'undefined'  ? '' : $_POST['txtworkexp'];
        $txtaddinfo =  $_POST['txtaddinfo'] == 'undefined'  ? '' : $_POST['txtaddinfo'];        

		//Get LOCATION NAME
		$queryLNAME="SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0";
		$resultLNAME=sqlsrv_query($mysqli, $queryLNAME);
		$rowLNAME=sqlsrv_fetch_array($resultLNAME);
		$LNAME = $rowLNAME['LOCATION'];

		//Get LOCATION EMAIL
		$LOCEMAIL='';
		if($ddlLocation > 1){
			$queryLOCEMAIL="SELECT LOC_EMAIL FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=$ddlLocation";
			$resultLOCEMAIL=sqlsrv_query($mysqli, $queryLOCEMAIL);
			$rowLOCEMAIL=sqlsrv_fetch_array($resultLOCEMAIL);
			$LOCEMAIL = $rowLOCEMAIL['LOC_EMAIL'];

		}
           
		
		$actionid = $cid == 0 ? 1 : 2;

		if($txtname == '')
		{throw new Exception("Enter Full Name.");}

		if($txtphone == '')
		{throw new Exception("Enter Phone.");}
        $data = array();


		

		$query="EXEC [CAREERS_SP] $actionid,$cid, $ddlLocation,'$txtname','$txtemail','$txtphone','$txtmaddress','$ddlplan','$txtedubackground','$txtworkexp','$txtaddinfo',$userid";

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
				sendMail('PERSON',$LNAME,$LOCEMAIL);
				sendMail('MYEXAMPREP',$LNAME,$LOCEMAIL);

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($cid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Thank you for Submitting your information MyExamsPrep. Team will contact you Shortly.';
				
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
function sendMail($for,$LNAME,$LOCEMAIL){

		$ddlLocation =  ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '')  ? 0 : $_POST['ddlLocation'];
        $txtname     =  $_POST['txtname'] == 'undefined'  ? '' : $_POST['txtname'];
        $txtemail    =  $_POST['txtemail'] == 'undefined'  ? '' : $_POST['txtemail'];   
        $txtphone    =  $_POST['txtphone'] == 'undefined'  ? '' : $_POST['txtphone'];
        $txtmaddress =  $_POST['txtmaddress'] == 'undefined'  ? '' : $_POST['txtmaddress'];
        $ddlplan     =  $_POST['ddlplan'] == 'undefined'  ? '' : $_POST['ddlplan'];
        $ddlplan     =  ltrim($ddlplan," , ");
        $txtedubackground =  $_POST['txtedubackground'] == 'undefined'  ? '' : $_POST['txtedubackground'];
        $txtworkexp =  $_POST['txtworkexp'] == 'undefined'  ? '' : $_POST['txtworkexp'];
        $txtaddinfo =  $_POST['txtaddinfo'] == 'undefined'  ? '' : $_POST['txtaddinfo'];    

	$data = array();
	$STmails = array();

	if($for == 'PERSON'){
		$STmails = array(
			$txtemail => $txtname,
		);
	}else{
		
		if($ddlLocation > 1){
			$STmails = array(
				'info@myexamsprep.com' => 'HQ',
				$LOCEMAIL => $LNAME,
			);
		}else{
			$STmails = array(
				'info@myexamsprep.com' => 'HQ',
			);
		}
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
					<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#D8ECF5; color: #628fa2'>CAREER</h1>
				
					<table style='font-family:Arial; margin: 25px 40px; width: 90%;'>
						<tr>
							<td style='width:100px;'>Location</td><td style='width:10px'>:</td><td>$LNAME</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
						<tr>
							<td style='width:100px;'>Name</td><td style='width:10px'>:</td><td>$txtname</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
						<tr>
						<td style='width:100px;'>Email</td><td style='width:10px'>:</td><td><a href='mailto:".$txtemail."' style='color:#118bf2; text-decoration:none'>$txtemail</a></td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
						<tr>
							<td style='width:100px;'>Phone</td><td style='width:10px'>:</td><td>$txtphone</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
						<tr>
							<td style='width:100px;'>Mailing Address</td><td style='width:10px'>:</td><td>$txtmaddress</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
				

						
						
						<tr>
							<td style='width:100px;'>Education Background</td><td style='width:10px'>:</td><td>$txtedubackground</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
					
						<tr>
							<td style='width:100px;'>Work Experience</td><td style='width:10px'>:</td><td>$txtworkexp</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
						<tr>
							<td style='width:100px;'>Additional Information</td><td style='width:10px'>:</td><td>$txtaddinfo</td>
						</tr>
						<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>


					</table>
					
				</div>
		";

	// 	<tr>
	// 	<td style='width:100px;'>Plan</td><td style='width:10px'>:</td><td>$ddlplan</td>
	// </tr>
	// <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
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




/*============ Get Location =============*/ 
function getLocation($mysqli){
	try
	{
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
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

/*============ Get getPlans =============*/ 
function getPlans($mysqli){
	try
	{
		$query = "SELECT PRODUCT_ID, PRODUCT,LEN(PRODUCT)CC FROM PRODUCTS WHERE ISDELETED=0 ORDER BY CC,PRODUCT";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PRODUCT_ID'] = (int) $row['PRODUCT_ID'];
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







