<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['ROLE']))
{$role=$_SESSION['ROLE'];}
else
{$role=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "Save":Save($conn);break;
        case "getPayment":getPayment($conn);break;
        case "getTeacher":getTeacher($conn);break;
        case "getTeacherProduct":getTeacherProduct($conn);break;
        case "chkAttDT":chkAttDT($conn);break;
        case "delete":delete($conn);break;

		case "getStudentByTeacher":getStudentByTeacher($conn);break;		

		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



 /*========== SAVE DATA =========*/
 function Save($mysqli){
     try
     {
		$data = array();
        global $userid;

		$paymentid = ($_POST['paymentid'] == 'undefined' || $_POST['paymentid'] == '') ? 0 : $_POST['paymentid'];
		$txtPaymentDate = ($_POST['txtPaymentDate'] == 'undefined' || $_POST['txtPaymentDate'] == '') ? '' : $_POST['txtPaymentDate'];  
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$txtTotalAmount = ($_POST['txtTotalAmount'] == 'undefined' || $_POST['txtTotalAmount'] == '') ? 0 : $_POST['txtTotalAmount'];
		$ddlPaymode = ($_POST['ddlPaymode'] == 'undefined' || $_POST['ddlPaymode'] == '') ? 0 : $_POST['ddlPaymode'];
		$txtRefeNo = ($_POST['txtRefeNo'] == 'undefined' || $_POST['txtRefeNo'] == '') ? '' : $_POST['txtRefeNo'];  
		$txtRefDate = ($_POST['txtRefDate'] == 'undefined' || $_POST['txtRefDate'] == '') ? '' : $_POST['txtRefDate'];  
		$paymentDetails = (!isset($_POST['paymentDetails']) || $_POST['paymentDetails'] == 'undefined' || $_POST['paymentDetails'] == '') ? array() : json_decode($_POST['paymentDetails']);

		
		$actionid = $paymentid == 0 ? 1 : 2;

		if($txtPaymentDate == '') throw new Exception("Please Select Payment Date.");
		if($ddlTeacher == 0) throw new Exception("Error : TEACHERID Not Found.");
		if($txtTotalAmount == 0) throw new Exception("Error : Total Amount 0. Please Enter Amount First.");
		if($ddlPaymode == 0) throw new Exception("Please Select Payment Mode.");
		if(count($paymentDetails) == 0 && $actionid==1) throw new Exception("Error : Payment Details Not Found.");


		// INSERT AMOUNT
		$queryMain="EXEC [STUDENT_TUTORING_TEACHER_PAYMENT_SP] $actionid,$paymentid,'$txtPaymentDate',$ddlTeacher,$txtTotalAmount,$ddlPaymode,
				'$txtRefeNo','$txtRefDate',$userid";
		$data['queryMain'] = $queryMain;
		// echo json_encode($data);exit;	
		$stmt=sqlsrv_query($mysqli, $queryMain);
		if($stmt === false)
		{
			$data['success'] = false;
		}
		else
		{
			if($actionid == 1){
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$PAYMENTID = $row['PAYMENTID'];
	
				for($i=0; $i< count($paymentDetails); $i++)
				{
					// INSERT DETAILS
					$ATTID=$paymentDetails[$i]->ATTID;
					$TOTALAMOUNT=$paymentDetails[$i]->AMOUNT;	
					$TEACHER_PERC=$paymentDetails[$i]->TEACHER_PERC;	
					$PAY=$paymentDetails[$i]->PAY;	
		
					// $sql = "SELECT * FROM STUDENT_TUTORING_ATTENDANCE WHERE REQID=$REQID AND TEACHERID=$ddlTeacher AND CSUBID=$ddlSubject AND REGID=$REGID
					// AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' AND ATTID!=$attid AND ISDELETED=0";
					// $row_count = unique($sql);
					// if($row_count == 0)
					// {
						if($PAY>0){
							$query="INSERT INTO STUDENT_TUTORING_TEACHER_PAYMENT_DETAILS (PAYMENTID,ATTID,TOTALAMT,SHAREPERC,SHAREAMT,INSERTID)
									VALUES($PAYMENTID,$ATTID,$TOTALAMOUNT,$TEACHER_PERC,$PAY,$userid)";
							$stmt=sqlsrv_query($mysqli, $query);
						}
					// }
					// else{
					// 	$STUDENTNAME=$selectedStudent[$i]->STUDENTNAME;
					// 	throw new Exception('Record Already Exist. '.$STUDENTNAME.' ('.$txtTimeIN.'-'.$txtTimeOUT.')');
					// }
					
				}
			}

			$data['success'] = true;
			if(!empty($paymentid))$data['message'] = 'Record successfully updated';
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
 /*========== SAVE DATA =========*/




/*============ Get Payment =============*/ 
 function getPayment($mysqli){
	try
	{
		global $userid;
		$data = array();
		// $ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// $ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];		
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];		

		$query = "SELECT PAYMENTID,CONVERT(VARCHAR,PAYMENTDATE,106)PAYMENTDATE,TEACHERID,AMOUNT,
		PMID,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=ST.PMID)PAYMENTMODE,REFNO,
		CASE WHEN CONVERT(VARCHAR,REFDATE,105)='01-01-1900' OR REFDATE IS NULL THEN '' ELSE CONVERT(VARCHAR,REFDATE,106) END REFDATE,
		(SELECT FIRSTNAME+' '+LASTNAME+' | '  FROM REGISTRATIONS WHERE REGID IN (SELECT REGID FROM STUDENT_TUTORING_ATTENDANCE WHERE ATTID IN (SELECT ATTID FROM STUDENT_TUTORING_TEACHER_PAYMENT_DETAILS WHERE ISDELETED=0 AND PAYMENTID=ST.PAYMENTID)) FOR XML PATH(''))STUDENT_NAME
		FROM STUDENT_TUTORING_TEACHER_PAYMENT ST
		WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher
		ORDER BY CONVERT(DATE,PAYMENTDATE,105)";
		$data['query']=$query;
		// echo json_encode($data);exit;

		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['STUDENT_NAME'] = rtrim($row['STUDENT_NAME'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ Get Att =============*/ 




/*============ Get Teacher =============*/ 
function getTeacher($mysqli){
	try
	{
		global $role;
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$userrole=($_POST['userrole'] == 'undefined' || $_POST['userrole'] == '') ? 0 : $_POST['userrole'];
		$query = "SELECT UID,FIRSTNAME+' '+LASTNAME AS FULLNAME FROM USERS WHERE LOCID=$ddlLocation AND USERROLE='TEACHER' AND ISDELETED=0 ORDER BY FULLNAME";
	
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Teacher =============*/ 



/*============ Get Teacher Product =============*/ 
function getStudentByTeacher($mysqli){
	try
	{
		global $role;
		$data = array();
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];

		$query = "SELECT ATTID, REQID, REGID, (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENTNAME,
		CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=ST.CSUBID)SUBJECT,
		CONVERT(VARCHAR, ATTDATE,106)ATTDATE,
		(SELECT DBO.GET_CURRENCYCODE_BY_USER(ST.TEACHERID)) CURRENCY,AMOUNT,
		ISNULL((SELECT TEACHER_SHARE FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE ISDELETED=0 AND REQID=ST.REQID AND TEACHERID=ST.TEACHERID),0)TEACHER_PERC,
		--CONVERT(DECIMAL(10,2),((AMOUNT*ISNULL((SELECT TEACHER_SHARE FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE ISDELETED=0 AND REQID=ST.REQID AND TEACHERID=ST.TEACHERID),0))/100))TEACHER_AMOUNT,
		CONVERT(DECIMAL(10,2),((ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)*ISNULL((SELECT TEACHER_SHARE FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE ISDELETED=0 AND REQID=ST.REQID AND TEACHERID=ST.TEACHERID),0))/100))TEACHER_AMOUNT,
		ISNULL((SELECT SUM(SHAREAMT) FROM STUDENT_TUTORING_TEACHER_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)TEACHER_PAID,
		ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)STUDENT_PAID,
		REMARKS
		FROM STUDENT_TUTORING_ATTENDANCE ST WHERE ISDELETED=0 AND REGID>0 AND TEACHERID=$ddlTeacher 
		-- AND CONVERT(DECIMAL(10,2),((AMOUNT*ISNULL((SELECT TEACHER_SHARE FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE ISDELETED=0 AND REQID=ST.REQID AND TEACHERID=ST.TEACHERID),0))/100))>ISNULL((SELECT SUM(SHAREAMT) FROM STUDENT_TUTORING_TEACHER_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)
		AND CONVERT(DECIMAL(10,2),((ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)*ISNULL((SELECT TEACHER_SHARE FROM STUDENT_TUTORING_REQUEST_PROCESS WHERE ISDELETED=0 AND REQID=ST.REQID AND TEACHERID=ST.TEACHERID),0))/100))>ISNULL((SELECT SUM(SHAREAMT) FROM STUDENT_TUTORING_TEACHER_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)
		ORDER BY STUDENTNAME,CONVERT(DATE,ATTDATE,105)";
		$data['query'] = $query;
		
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['AMOUNT'] = (float) $row['AMOUNT'];
				$row['TEACHER_PERC'] = (float) $row['TEACHER_PERC'];
				$row['TEACHER_AMOUNT'] = (float) $row['TEACHER_AMOUNT'];
				$row['TEACHER_PAID'] = (float) $row['TEACHER_PAID'];
				$row['STUDENT_PAID'] = (float) $row['STUDENT_PAID'];
				$row['PAY'] = ($row['TEACHER_AMOUNT']-$row['TEACHER_PAID']);
				$row['CLEAR_AMT'] = ($row['TEACHER_AMOUNT']==$row['TEACHER_PAID']) ? 1 : 0;
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
/*============ Get Teacher Product =============*/ 



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$PAYMENTID = ($_POST['PAYMENTID'] == 'undefined' || $_POST['PAYMENTID'] == '') ? 0 : $_POST['PAYMENTID'];  
		if($PAYMENTID == 0)throw new Exception("Error : PAYMENTID Not Found.");

		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_TEACHER_PAYMENT_SP] 3,$PAYMENTID,'',0,0,0,'','',$userid");
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







