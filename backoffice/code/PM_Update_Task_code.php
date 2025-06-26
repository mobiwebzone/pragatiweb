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
        case "save":save($conn);break;
		case "getAssignedToUser":getAssignedToUser($conn);break;
		case "getOrganization":getOrganization($conn);break;
		case "getProjects":getProjects($conn);break;
		case "getQuery":getQuery($conn);break;
        // case "delete":delete($conn);break;
		case "getTaskMainCategory":getTaskMainCategory($conn);break;
		case "getTaskstatus":getTaskstatus($conn);break;
		case "sendMail":sendMail($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		$pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        $TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? 0 : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID  = ($_POST['TEXT_PROJECT_ID'] == 'undefined' || $_POST['TEXT_PROJECT_ID'] == '') ? 0 : $_POST['TEXT_PROJECT_ID'];
        
	    $TEXT_ASSIGNED_TO_ID  = ($_POST['TEXT_ASSIGNED_TO_ID'] == 'undefined' || $_POST['TEXT_ASSIGNED_TO_ID'] == '') ? '' : $_POST['TEXT_ASSIGNED_TO_ID'];
		$txtStartDT  = ($_POST['txtStartDT'] == 'undefined' || $_POST['txtStartDT'] == '') ? '' : $_POST['txtStartDT'];
        $txtEndDT  = ($_POST['txtEndDT'] == 'undefined' || $_POST['txtEndDT'] == '') ? '' : $_POST['txtEndDT'];
	    $remarks = ($_POST['remarks'] == 'undefined' || $_POST['remarks'] == '') ? '' : $_POST['remarks'];

		$actionid =  2;
	
		if($TEXT_ASSIGNED_TO_ID == 0)throw new Exception("Please Select User.");
		
		if ($txtStartDT=='' && $txtEndDT != '')
		{
			throw new Exception("Please Enter the Actual Date first!");
		}

	
			$query="EXEC [UPDATE_TASKS_SP] $actionid,$pmid,$TEXT_ASSIGNED_TO_ID,'$remarks','$txtStartDT','$txtEndDT',$userid ";
		   
			// echo json_encode($query);exit;
		
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($pmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
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

 
 function sendMail($mysqli){
	
	$mail = new PHPMailer(true);  // Passing `true` enables exceptions

	try {
		$data = array();
		
		$TEXT_ASSIGNED_TO_ID = ($_POST['TEXT_ASSIGNED_TO_ID'] == 'undefined' || $_POST['TEXT_ASSIGNED_TO_ID'] == '') ? 0 : $_POST['TEXT_ASSIGNED_TO_ID'];
        $TEXT_PROJECT_ID = (isset($_POST['TEXT_PROJECT_ID']) && $_POST['TEXT_PROJECT_ID'] != 'undefined' && $_POST['TEXT_PROJECT_ID'] != '') ? $_POST['TEXT_PROJECT_ID'] : 0;

		echo $TEXT_PROJECT_ID;
		
		var_dump($_POST); 
	        
		$query1 = "SELECT PROJECT_NAME FROM PROJECTS WHERE PROJECT_ID = $TEXT_PROJECT_ID";
		
		$params1 = array($TEXT_PROJECT_ID);
		
		$stmt1 = sqlsrv_query($mysqli, $query1, $params1);
		
		while ($row = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {

			$projectName = $row['PROJECT_NAME'];  // Extract the email from the row
		}

		
		$query = "SELECT email FROM USERS WHERE uid = $TEXT_ASSIGNED_TO_ID";
		$params = array($TEXT_ASSIGNED_TO_ID);  // Bind the pmid parameter

		// Using prepared statement with sqlsrv_query
		$stmt = sqlsrv_query($mysqli, $query, $params);

		$data = array();
		if ($stmt === false) {
			// Handle query error
			echo "Query failed: " . print_r(sqlsrv_errors(), true);
			$data['message'] = 'Error ocurred.';
			echo json_encode($data);exit;
			
		}

		// Fetching the email
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$userEmail = $row['email'];  // Extract the email from the row
		}
		
		if (!isset($userEmail)) {
			echo "No user found with uid: $TEXT_ASSIGNED_TO_ID";
			$data['message'] = 'No user found.';
			echo json_encode($data);exit;
			
		}

		//Server settings
		$mail->isSMTP();  // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through (example: Gmail)
		$mail->SMTPAuth = true;  // Enable SMTP authentication
		$mail->Username = "info.mobiwebzone@gmail.com";  // SMTP username (your email address)
		$mail->Password = "ught lkob etho quwk";  // SMTP password (use app password if 2FA enabled)
		$mail->SMTPSecure = 'tls';  // Enable TLS encryption
		$mail->Port = 587;  // TCP port to connect to (587 is common for TLS)
	
		//Recipients
		$mail->setFrom('info.mobiwebzone@gmail.com', 'Mobiwebzone');  // Sender email and name
		$mail->addAddress($userEmail, 'Test');  // Recipient's email and name
	
		//Content
		$mail->isHTML(true);  // Set email format to HTML
		$mail->Subject = 'Task Updated';  // Subject line
		$mail->Body =  $TEXT_PROJECT_ID.'<p>The task has been updated by you</p>'; // HTML message body
 
		$mail->AltBody = 'This is a test email sent via PHPMailer!';  // Plain text body for non-HTML email clients
	
		// Send the email
		$mail->send();
		echo 'Email has been sent successfully!';
	} catch (Exception $e) {
		echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}

}

 
 function getQuery($mysqli){
	try
	{
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
		$TEXT_TASK_CAT_ID_S  = ($_POST['TEXT_TASK_CAT_ID_S'] == 'undefined' || $_POST['TEXT_TASK_CAT_ID_S'] == '') ? '' : $_POST['TEXT_TASK_CAT_ID_S'];
		$TEXT_TASK_STATUS_CD_S = ($_POST['TEXT_TASK_STATUS_CD_S'] =='undefined' || $_POST['TEXT_TASK_STATUS_CD_S'] =='') ? 0 : $_POST['TEXT_TASK_STATUS_CD_S'];	

       $query =    " SELECT 
						TASK_ID,
						ORG_ID,
						ORG_NAME,
						PROJECT_ID,
						PROJECT_NAME,
						TASK_CAT_ID,
						TASK_CAT,
						TASK_SUB_CAT_ID,
						TASK_SUB_CAT,
						ASSIGNED_TO_ID,
						ASSIGNED_TO,
						CASE 
							WHEN NULLIF(STARTDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, STARTDATE, 106)
						END AS STARTDATE,
						CASE 
							WHEN NULLIF(ENDDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, ENDDATE, 106)
						END AS ENDDATE,
						TASK_DESC,
						TASK_STATUS_CD,
						TASK_STATUS,
						REMARKS,
						CASE 
							WHEN NULLIF(ACT_STARTDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, ACT_STARTDATE, 106)
						END AS ACT_STARTDATE,
						CASE 
							WHEN NULLIF(ACT_ENDDATE, '1900-01-01') IS NULL THEN ' '  
							ELSE CONVERT(VARCHAR, ACT_ENDDATE, 106)
						END AS ACT_ENDDATE
					FROM 
						TASK_USERS  
					WHERE 
						ISDELETED = 0

					AND   ORG_ID = $TEXT_ORG_ID
					AND   PROJECT_ID = $TEXT_PROJECT_ID ";
					  
        
		if ($TEXT_TASK_CAT_ID_S != '') {
			$query .= " AND TASK_CAT_ID = $TEXT_TASK_CAT_ID_S "; 

			}
	     	
		
		if ($TEXT_TASK_STATUS_CD_S != '') {
					$query .= " AND TASK_STATUS_CD = $TEXT_TASK_STATUS_CD_S "; 
		
					}	
			
		$query  .= " ORDER BY TASK_CAT_ID, TASK_ID ";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TASK_ID'] = (int) $row['TASK_ID'];
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

function getTaskMainCategory($mysqli){
	try
	{
		$data = array();
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
		$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
		$TEXT_TASK_CAT_ID_S = ($_POST['TEXT_TASK_CAT_ID_S'] =='undefined' || $_POST['TEXT_TASK_CAT_ID_S'] =='') ? 0 : $_POST['TEXT_TASK_CAT_ID_S'];

		$query = "SELECT 
		          TASK_CAT_ID
				 ,TASK_CAT 
				FROM TASK_MAIN_CATEGORIES  
				WHERE ISDELETED=0 
				AND   ORG_ID = $TEXT_ORG_ID
				AND   PROJECT_ID = $TEXT_PROJECT_ID ";
		
		if ($TEXT_TASK_CAT_ID_S != '') {
			$query .= " AND TASK_CAT_ID = $TEXT_TASK_CAT_ID_S "; 
			}
	    $query  .= " ORDER BY TASK_CAT_ID ";



		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASK_CAT_ID'] = (int) $row['TASK_CAT_ID'];
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
/*============ Get Task Main Category Master=============*/ 

function getTaskstatus($mysqli){
		try
		{
			
	        $query = "SELECT CODE_DETAIL_ID, CODE_DETAIL_DESC  FROM MEP_CODE_DETAILS WHERE ISDELETED=0  AND CODE_ID=9 ORDER BY CODE_DETAIL_ID";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
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
	


 function getOrganization($mysqli){
	try
	{
		global $userid;

        $query = "SELECT ORG_ID, ORG_NAME  FROM ORGANIZATION
                  WHERE ISDELETED =0 AND ORG_ID IN (SELECT ORG_ID FROM ORGANIZATION_PROJECT_USER WHERE USER_ID = $userid AND ISDELETED=0) ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ORG_ID'] = (int) $row['ORG_ID'];
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

function getProjects($mysqli){
	try
	{
		global $userid;
        $TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];
        $query =   "SELECT PROJECT_ID, PROJECT_NAME  FROM PROJECTS
					WHERE ISDELETED =0 
					AND PROJECT_ID IN (SELECT PROJECT_ID FROM ORGANIZATION_PROJECT_USER 
				    WHERE USER_ID = $userid
				    AND ORG_ID = $TEXT_ORG_ID
				    AND ISDELETED=0) ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PROJECT_ID'] = (int) $row['PROJECT_ID'];
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


function getAssignedToUser($mysqli){
	try
	{
	
	$data = array();
	$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? '' : $_POST['TEXT_ORG_ID'];	
	$TEXT_PROJECT_ID = ($_POST['TEXT_PROJECT_ID'] =='undefined' || $_POST['TEXT_PROJECT_ID'] =='') ? 0 : $_POST['TEXT_PROJECT_ID'];
	
	$query =    "SELECT A.UID, (A.FIRSTNAME+' '+A.LASTNAME) USERNAME 
				FROM  USERS A ,ORGANIZATION_PROJECT_USER B
				WHERE A.UID = B.USER_ID
				AND   B.ORG_ID = $TEXT_ORG_ID
				AND   B.PROJECT_ID = $TEXT_PROJECT_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['UID'] = (int) $row['UID'];
				
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







