<?php
session_start();
require_once '../code/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "Save":Save($conn);break;
        case "getRFV":getRFV($conn);break;
        case "getStudentProduct":getStudentProduct($conn);break;
        case "delete":delete($conn);break;

        case "AddProduct":AddProduct($conn);break;
        case "getRFVD":getRFVD($conn);break;
        case "CancelPeoduct":CancelPeoduct($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$vrid = ($_POST['vrid'] == 'undefined' || $_POST['vrid'] == '') ? 0 : $_POST['vrid'];
		$txtFromDate=$_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
		$txtToDate=$_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		$txtRemark=$_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
		// $ddlProduct=($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
    
		
		$actionid = $vrid == 0 ? 1 : 2;

		if($txtFromDate == '')
		{throw new Exception("Please select From date.");}
		if($txtToDate == '')
		{throw new Exception("Please select To date.");}
		if($txtRemark == '')
		{throw new Exception("Please enter Remark.");}
		
		$sql = "SELECT * FROM VOLUNTEER_REQUESTS WHERE FROMDT='$txtFromDate' AND TODT='$txtToDate' AND REGID=$REGID AND VRID!=$vrid AND CANCELLED=0";

		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [VOLUNTEER_REQUESTS_SP] $actionid,$vrid,$REGID,'$txtFromDate','$txtToDate','$txtRemark',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$row = sqlsrv_fetch_array($stmt);
				$data['VRID'] = (int) $row['VRID'];
				//Get INFO
				$queryInfo="SELECT (SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)[LOCATION],
				(SELECT LOC_EMAIL FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)EMAIL,
				FIRSTNAME+' '+LASTNAME AS FULLNAME,GRADE
				FROM REGISTRATIONS R WHERE ISDELETED=0 AND REGID=1";
				$resultInfo=sqlsrv_query($mysqli, $queryInfo);
				$rowInfo=sqlsrv_fetch_array($resultInfo);
				$LOCEMAIL = $rowInfo['EMAIL'];
				$LOCATION = $rowInfo['LOCATION'];
				$FULLNAME = $rowInfo['FULLNAME'];

				// sendMail($LOCEMAIL,$LOCATION,$FULLNAME);

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($vrid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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


/*================ MAIL =================*/  
function sendMail($LOCEMAIL,$LOCATION,$FULLNAME){

	$txtFromDate=$_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
	$txtToDate=$_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
	$txtRemark=$_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
	$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];


		

	////////// FOR MULTIPLE EMAIL ///////////////
		$data = array();
		$STmails = array();
		

		// $LOCEMAIL => $LOCATION,
		$STmails = array(
			'clboy1997@gmail.com' => 'SHUBHAM',
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
		

	////////// FOR MULTIPLE EMAIL ///////////////


		
      //////////// MAGS //////////////////////////
		$msg = "";

		$msg .= "<u>Request For Volunteer</u> <br><br>
				NAME : <b>$FULLNAME</b>
				PRODUCT : <b>$FULLNAME</b>
				REMARK : <b>$txtRemark</b>";
	

	//////////// MAGS //////////////////////////


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




/*============ Get RFV =============*/ 
 function getRFV($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$GET_FOR=($_POST['GET_FOR'] == 'undefined' || $_POST['GET_FOR'] == '') ? '' : $_POST['GET_FOR'];
		if($REGID == 0)throw new Exception('REGID Invalid.');
		if($GET_FOR == '')throw new Exception('Get For Missing.');

		$query = "SELECT VRID,CONVERT(VARCHAR,REQDATE,106)REQDATE,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=VR.REGID)STUDENT_NAME,
		(SELECT (SELECT TOP 1 PLANID FROM REGISTRATION_DETAILS WHERE REGID=R.REGID AND CANCELLED=0) FROM REGISTRATIONS R WHERE REGID=VR.REGID)PLANID,
		CONVERT(VARCHAR,FROMDT,20)FROMDT,CONVERT(VARCHAR,TODT,20)TODT,
		REMARKS,APPROVED,APPROVEDBY,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=VR.APPROVEDBY AND ISDELETED=0)APPROVEDBY,
		APPR_REMARKS,
		CANCELLED,(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=VR.CANCELID AND ISDELETED=0)CANCELLEDBY,
		CANCELREMARKS,
		ISNULL((SELECT (SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=VRD.PRODUCTID) + ', ' FROM VOLUNTEER_REQUEST_DETAILS VRD WHERE ISDELETED=0 AND VRID=VR.VRID
        FOR XML PATH ('')),'-')PRODUCTS
		FROM VOLUNTEER_REQUESTS VR WHERE REGID=$REGID";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['VRID'] = (int) $row['VRID'];
				$row['PRODUCTS'] = rtrim($row['PRODUCTS'], ", ");
				$date = date_create($row['FROMDT']);
				$row['FROMDT'] = date_format($date,"d M Y");
				$row['FROMDT_ORDERBY'] = date_format($date,"mdYhm");
				$date = date_create($row['TODT']);
				$row['TODT'] = date_format($date,"d M Y");
				$row['TODT_ORDERBY'] = date_format($date,"mdYhm");
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


/*============ Get Student Product =============*/ 
 function getStudentProduct($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

		$query = "SELECT PRODUCTID,(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)PRODUCT 
				FROM PLAN_PRODUCTS PP WHERE ISDELETED=0 AND PLANID IN ((SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$REGID))";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PRODUCTID'] = (int) $row['PRODUCTID'];
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
            $vrid = ($_POST['vrid'] == 'undefined' || $_POST['vrid'] == '') ? 0 : $_POST['vrid'];  
            $txtCancelRemark = $_POST['txtCancelRemark'] == 'undefined' ? '' : $_POST['txtCancelRemark'];  
			$stmt=sqlsrv_query($mysqli, "DELETE FROM VOLUNTEER_REQUESTS WHERE VRID=$vrid;
										DELETE FROM VOLUNTEER_REQUEST_DETAILS WHERE VRID=$vrid");
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

/*============ Get Locations =============*/ 
function getLocations($mysqli){
	try
	{

		global $userid;
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0
		AND LOC_ID IN (SELECT LOCID FROM TEACHER_LOCATION WHERE TEACHERID=$userid AND ISDELETED=0)";

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





// ========================================= SAVE PRODUCT ==========================================
function AddProduct($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $vrdid = ($_POST['vrdid'] == 'undefined' || $_POST['vrdid'] == '') ? 0 : $_POST['vrdid'];
	   $GET_VRID = ($_POST['GET_VRID'] == 'undefined' || $_POST['GET_VRID'] == '') ? 0 : $_POST['GET_VRID'];
	   $ddlProduct=($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
   


	   if($ddlProduct == 0)
	   {throw new Exception("Please select Produuct.");}
	   if($GET_VRID == 0)
	   {throw new Exception("VRID ERROR.");}
	   
	   $sql = "SELECT * FROM VOLUNTEER_REQUEST_DETAILS WHERE VRID=$GET_VRID AND PRODUCTID=$ddlProduct AND VRDID!=$vrdid AND ISDELETED=0";

	   $row_count = unique($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="INSERT INTO VOLUNTEER_REQUEST_DETAILS (VRID,PRODUCTID) VALUES($GET_VRID,$ddlProduct)";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = true;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'Product already exists';
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

// get RFVD
function getRFVD($mysqli){
	try
	{
		$data = array();
		$GET_VRID=($_POST['GET_VRID'] == 'undefined' || $_POST['GET_VRID'] == '') ? 0 : $_POST['GET_VRID'];

		$query = "SELECT VRDID,PRODUCTID,
		(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=VRD.PRODUCTID)PEODUCT
		FROM VOLUNTEER_REQUEST_DETAILS VRD WHERE ISDELETED=0 AND VRID=$GET_VRID";
		
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){

			$Att = array();
			$RegID = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['VRDID'] = (int) $row['VRDID'];
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


/* =========== Cancel Peoduct =========== */ 
function CancelPeoduct($mysqli){
	try{   
			global $userid;
			$data = array();     
            $vrdid = ($_POST['vrdid'] == 'undefined' || $_POST['vrdid'] == '') ? 0 : $_POST['vrdid'];  

			$del="UPDATE VOLUNTEER_REQUEST_DETAILS SET ISDELETED=1 WHERE VRDID=$vrdid";
		
			$stmt=sqlsrv_query($mysqli, $del);
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







