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
        case "searchCourses":searchCourses($conn);break;
        case "getLocation":getLocation($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function searchCourses($mysqli){
     try
     {
		$data = array();
        global $userid;
        
        $searchIndex = $_POST['searchIndex'] == 'undefined'  ? '' : str_replace("'","''",$_POST['searchIndex']);
		$searchIndex = '%'.$searchIndex.'%';

		$queryP="SELECT PDMID,DISPLAY_PRODUCT,COLORCODE FROM PRODUCT_DISPLAY_MASTER 
		WHERE ISDELETED=0 AND DISPLAY_PRODUCT LIKE '$searchIndex' 
		ORDER BY [ORDER]";
		$data['queryP']=$queryP;
		$countP = unique($queryP);
		if($countP>0){
			$resultP=sqlsrv_query($mysqli, $queryP);
			while($rowP=sqlsrv_fetch_array($resultP,SQLSRV_FETCH_ASSOC)){
				$rowP['PTYPE'] = 'P';
				$data['dataP'][]=$rowP;
			}
			$data['successP'] = true;
		}else{
			$data['successP'] = false;
		}


		$queryR="SELECT ID,RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES 
		WHERE ISDELETED=0 AND RESOURCE_CATEGORY_TEXT LIKE '$searchIndex' AND RESOURCE_CATEGORY='Category' AND UNDER_ID=0 
		ORDER BY SEQNO";
		$countR = unique($queryR);
		if($countR>0){
			$resultR=sqlsrv_query($mysqli, $queryR);
			while($rowR=sqlsrv_fetch_array($resultR,SQLSRV_FETCH_ASSOC)){
				$data['dataR'][]=$rowR;
			}
			$data['successR'] = true;
		}else{
			$data['successR'] = false;
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






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







