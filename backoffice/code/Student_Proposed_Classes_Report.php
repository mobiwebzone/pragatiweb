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


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getSTProposedClasses":getSTProposedClasses($conn);break;
        case "SendSMS":SendSMS($conn);break;
        // case "getStudents":getStudents($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ GET STUDENT PROPOSED CLASSES =============*/ 
 function getSTProposedClasses($mysqli){
	try
	{
		$data = array();
		$txtYear = ($_POST['txtYear'] == 'undefined' || $_POST['txtYear'] == '') ? 0 : $_POST['txtYear'];
		$ddlClassSubject = ($_POST['ddlClassSubject'] == 'undefined' || $_POST['ddlClassSubject'] == '') ? 0 : $_POST['ddlClassSubject'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT GSID,REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)STUDENTNAME,
		(SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)FIRSTNAME,
		(SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)LASTNAME,
		ISNULL((SELECT PHONE FROM REGISTRATIONS WHERE REGID=SGS.REGID),'')PHONE,
		ISNULL((SELECT P1_PHONE FROM REGISTRATIONS WHERE REGID=SGS.REGID),'')P1_PHONE,
		ISNULL((SELECT P2_PHONE FROM REGISTRATIONS WHERE REGID=SGS.REGID),'')P2_PHONE,
		ISNULL((SELECT P1_FIRSTNAME+' '+P1_LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID),'') PARENT1,
		ISNULL((SELECT P2_FIRSTNAME+' '+P2_LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID),'') PARENT2,
		ISNULL((SELECT CLASSOF FROM REGISTRATIONS WHERE REGID=SGS.REGID),'') CLASSOF,
		DRAFT,FINAL,GRYEAR
		FROM STUDENT_GRADE_SUBJECTS SGS
		WHERE ISDELETED=0 AND GRYEAR=$txtYear AND CSUBID=$ddlClassSubject AND
		REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) 
		ORDER BY (SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)";

		$data['$query ']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$row['FINAL_PHONE'] = '';

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));
			
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
/*============ GET STUDENT PROPOSED CLASSES =============*/





// =============== SEND SMS ==================
function SendSMS($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$txtSMS = ($_POST['txtSMS'] == 'undefined' || $_POST['txtSMS'] == '') ? '' : str_replace("'","''",$_POST['txtSMS']);
		$STUDENT_DATA = (!empty($_POST['STUDENT_DATA'])) ? $_POST['STUDENT_DATA'] : '';
		if($STUDENT_DATA == '')throw new Exception('Student Data Not Found.');
		$STUDENT_DATA =json_decode($STUDENT_DATA,true);
		$data['$STUDENT_DATA']=$STUDENT_DATA;
		// $data['message'] = 'Sms Send successfully.';
		// $data['success'] = true;
		// echo json_encode($data);exit;
		
		if($txtSMS == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($STUDENT_DATA) <= 0)throw new Exception('Select Students First.');

		for($i=0; $i<count($STUDENT_DATA); $i++){
			$REGID = $STUDENT_DATA[$i]['REGID'];
			$FIRSTNAME = $STUDENT_DATA[$i]['FIRSTNAME'];
			$LASTNAME = $STUDENT_DATA[$i]['LASTNAME'];
			$FINAL_PHONE = $STUDENT_DATA[$i]['FINAL_PHONE'];
			$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'Registered',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_PHONE','$txtSMS',$userid";
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
// =============== SEND SMS ==================



/*============ Get Student =============*/ 
//  function getStudents($mysqli){
// 	try
// 	{
// 		$data = array();
//         // $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
//         // $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
//         $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];

// 		$query = "SELECT DISTINCT REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENT 
// 		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0";

// 		if($ddlPlan > 0)$query .=" AND PLANID=$ddlPlan";
// 		$query .=" ORDER BY STUDENT";

// 		$data['$query ']=$query;

// 		$count = unique($query);

// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}
// 		else{
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }
/*============ Get Student =============*/




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







