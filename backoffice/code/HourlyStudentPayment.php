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
		$studentListDetails = (!isset($_POST['studentListDetails']) || $_POST['studentListDetails'] == 'undefined' || $_POST['studentListDetails'] == '') ? array() : json_decode($_POST['studentListDetails']);
		$REQID = ($_POST['REQID'] == 'undefined' || $_POST['REQID'] == '') ? 0 : $_POST['REQID'];
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$txtTotalAmount = ($_POST['txtTotalAmount'] == 'undefined' || $_POST['txtTotalAmount'] == '') ? 0 : $_POST['txtTotalAmount'];
		$ddlPaymode = ($_POST['ddlPaymode'] == 'undefined' || $_POST['ddlPaymode'] == '') ? 0 : $_POST['ddlPaymode'];
		$txtRefeNo = ($_POST['txtRefeNo'] == 'undefined' || $_POST['txtRefeNo'] == '') ? '' : $_POST['txtRefeNo'];  
		$txtRefDate = ($_POST['txtRefDate'] == 'undefined' || $_POST['txtRefDate'] == '') ? '' : $_POST['txtRefDate'];  
		
		$actionid = $paymentid == 0 ? 1 : 2;

		if($txtPaymentDate == '') throw new Exception("Please Select Payment Date.");
		if(count($studentListDetails) == 0 && $actionid==1) throw new Exception("Student Not Found.");
		if($REQID == 0) throw new Exception("Error : REQID Not Found.");
		if($REGID == 0) throw new Exception("Error : REGID Not Found.");
		if($txtTotalAmount == 0) throw new Exception("Error : Total Amount 0. Please Enter Amount First.");
		if($ddlPaymode == 0) throw new Exception("Please Select Payment Mode.");


		// INSERT AMOUNT
		$queryMain="EXEC [STUDENT_TUTORING_PAYMENT_SP] $actionid,$paymentid,'$txtPaymentDate',$REQID,$REGID,$txtTotalAmount,$ddlPaymode,
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
	
				for($i=0; $i< count($studentListDetails); $i++)
				{
					// INSERT DETAILS
					$ATTID=$studentListDetails[$i]->ATTID;
					$PAY=$studentListDetails[$i]->PAY;	
		
					// $sql = "SELECT * FROM STUDENT_TUTORING_ATTENDANCE WHERE REQID=$REQID AND TEACHERID=$ddlTeacher AND CSUBID=$ddlSubject AND REGID=$REGID
					// AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' AND ATTID!=$attid AND ISDELETED=0";
					// $row_count = unique($sql);
					// if($row_count == 0)
					// {
						if($PAY>0){
							$query="INSERT INTO STUDENT_TUTORING_PAYMENT_DETAILS (PAYMENTID,ATTID,AMOUNT,INSERTID)
									VALUES($PAYMENTID,$ATTID,$PAY,$userid)";
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
		$ddlStudent=($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];		

		$query = "SELECT PAYMENTID,CONVERT(VARCHAR,PAYMENTDATE,106)PAYMENTDATE,REQID,REGID,AMOUNT,
		PMID,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=ST.PMID)PAYMENTMODE,REFNO,
		CASE WHEN CONVERT(VARCHAR,REFDATE,105)='01-01-1900' OR REFDATE IS NULL THEN '' ELSE CONVERT(VARCHAR,REFDATE,106) END REFDATE
		FROM STUDENT_TUTORING_PAYMENT ST
		WHERE ISDELETED=0 AND REGID=$ddlStudent
		ORDER BY CONVERT(DATE,PAYMENTDATE,105)";
		$data['query']=$query;
		// echo json_encode($data);exit;

		$RCOUNT = unique($query);
		
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
		ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)PAID,
		REMARKS
		FROM STUDENT_TUTORING_ATTENDANCE ST WHERE ISDELETED=0 AND REGID>0 AND TEACHERID=$ddlTeacher
		--AND AMOUNT>ISNULL((SELECT SUM(AMOUNT) FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0),0)
		AND ATTID NOT IN (SELECT ATTID FROM STUDENT_TUTORING_PAYMENT_DETAILS WHERE ATTID=ST.ATTID AND ISDELETED=0)
		";
		if($REGID>0) $query.=" AND REGID=$REGID";
		$query.=" ORDER BY STUDENTNAME,CONVERT(DATE, ATTDATE,105)";
		
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			$old_regid=$new_regid=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$new_regid = $row['REGID'];
				if($new_regid!=$old_regid){
					$data['STUDENT_LIST'][] = ['REGID'=>$row['REGID'],'STUDENTNAME'=>$row['STUDENTNAME']];
				}
				$old_regid=$new_regid;

				$row['AMOUNT'] = (float) $row['AMOUNT'];
				$row['PAID'] = (float) $row['PAID'];
				$row['PAY'] = ($row['AMOUNT']-$row['PAID']);
				$row['CLEAR_AMT'] = ($row['AMOUNT']==$row['PAID']) ? 1 : 0;
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['query'] = $query;
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

		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TUTORING_PAYMENT_SP] 3,$PAYMENTID,'',0,0,0,0,'','',$userid");
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







