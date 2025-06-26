<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "Pay":Pay($conn);break;
        case "getStudent":getStudent($conn);break;
        case "getStudentPlan":getStudentPlan($conn);break;
        case "getSelectedPlan_Record":getSelectedPlan_Record($conn);break;
        case "getPayments":getPayments($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function Pay($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $psid  = ($_POST['psid'] == 'undefined' || $_POST['psid'] == '') ? 0 : $_POST['psid'];
        $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
        $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
        $ActPrice  = ($_POST['ActPrice'] == 'undefined' || $_POST['ActPrice'] == '') ? 0 : $_POST['ActPrice'];
        $ActInstPrice  = ($_POST['ActInstPrice'] == 'undefined' || $_POST['ActInstPrice'] == '') ? 0 : $_POST['ActInstPrice'];
        $ActInst  = ($_POST['ActInst'] == 'undefined' || $_POST['ActInst'] == '') ? 0 : $_POST['ActInst'];
		$ddlPayType=$_POST['ddlPayType'] == 'undefined' ? '' : $_POST['ddlPayType'];
        $txtAmt  = ($_POST['txtAmt'] == 'undefined' || $_POST['txtAmt'] == '') ? 0 : $_POST['txtAmt'];
        $Installment  = ($_POST['Installment'] == 'undefined' || $_POST['Installment'] == '') ? 0 : $_POST['Installment'];
		$txtRemark=($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '-' : $_POST['txtRemark'];
		
		if($regid == 0){throw new Exception("REGID ERROR");}
		if($planid == 0){throw new Exception("PLANID ERROR");}
		if($ddlPayType == ''){throw new Exception("Please Select Payment Type.");}
		if($txtAmt == 0){throw new Exception("Please Enter Amount First.");}

		$actionid = $psid == 0 ? 1 : 2;



			$query="EXEC [PAYMENT_SCHEDULE_SP] $actionid,$psid,$regid,$planid,$ActPrice,$ActInstPrice,$ActInst,'$ddlPayType',$txtAmt,$Installment,'$txtRemark',$userid";


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
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($psid))$data['message'] = 'Record successfully updated';
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

 


/*============ GET STUDENT =============*/ 
function getStudent($mysqli){
	try
	{
		$data = array();

		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// $ddlPlan=($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];

		if($ddlLocation == 0){throw new Exception ("Select Location First.");}
		// if($ddlPlan == 0){throw new Exception ("Select Plan First.");}

		$query = "SELECT REGID,FIRSTNAME,LASTNAME,GRADE
		 FROM REGISTRATIONS R WHERE ISDELETED=0 AND LOCATIONID=$ddlLocation";

		// $data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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




/*============ Get Student Plans =============*/ 
 function getStudentPlan($mysqli){
	try
	{
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT PLANID,PLANNAME,CONVERT(VARCHAR,STARTDATE,105)STARTDATE,
		CONVERT(VARCHAR,ENDDATE,105)ENDDATE,INST_FREQ +' ('+ CONVERT(VARCHAR,INST_NO) +')' INST,INST_NO,
		(SELECT (SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=$ddlLocation)CURRENCY_CODE,
		(SELECT (SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=$ddlLocation)CURRENCY,
		CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0)) =1 THEN
			(SELECT PRICE FROM PLANS WHERE PLANID=P.PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
			ELSE 
			(SELECT PRICE FROM PLANS WHERE PLANID=P.PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
		END PRICE,
		CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0)) =1 THEN
			(SELECT INST_AMOUNT FROM PLANS WHERE PLANID=P.PLANID)*(SELECT FACTOR FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
			ELSE 
			(SELECT INST_AMOUNT FROM PLANS WHERE PLANID=P.PLANID)/(SELECT FACTOR FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0))	
		END INST_AMOUNT
		FROM PLANS P WHERE ISDELETED=0 AND 
		PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$REGID AND CANCELLED=0)";

		$result = sqlsrv_query($mysqli, $query);

		$CNT = unique($query);
		$data = array();

		if($CNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANID'] = (int) $row['PLANID'];
				$row['PRICE'] = (int) $row['PRICE'];
				$row['INST_AMOUNT'] = (int) $row['INST_AMOUNT'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['$query'] = $query;
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




/*============ GET Selected Plan Payment Record =============*/ 
 function getSelectedPlan_Record($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		$planid = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];

		if($ddlLocation == 0){throw new Exception ('LoCID Error.');}
		if($regid == 0){throw new Exception ('RegID Error.');}
		if($planid == 0){throw new Exception ('PlanID Error.');}

		$query = "SELECT PSID,PAYPLAN,AMOUNT,INSTALLMENTS,REMARKS 
		FROM PAYMENT_SCHEDULE WHERE ISDELETED=0 AND REGID=$regid AND PLANID=$planid";
		$result = sqlsrv_query($mysqli, $query);
		$cnt = unique($query);
		$data = array();

		if($cnt > 0){
			$row = sqlsrv_fetch_array($result) ;
			$row['PSID'] = (int) $row['PSID'];
			$data['data'] = $row;
			
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







/*============ GET PAYMENTS =============*/ 
 function getPayments($mysqli){
	try
	{
		$data = array();

		$TopRecord=($_POST['TopRecord'] == 'undefined' || $_POST['TopRecord'] == '') ? 100 : $_POST['TopRecord'];
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		// if($ddlLocation == 0){throw new Exception ("Select Location First.");}
		$query = "SELECT TOP $TopRecord CONVERT(VARCHAR,INSERTDATE,20)[DATE],PSID,REGID,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=PS.REGID)STUDENT_NAME,
		(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=PS.REGID)LOCATIONID,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=PS.PLANID)PLANNAME,
		ACTPRICE,ACTINST_AMOUNT,ACTINSTALLMENTS,PAYPLAN,AMOUNT,INSTALLMENTS,REMARKS,
		(SELECT (SELECT (SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=PS.REGID)CURRENCY_CLASS,
		(SELECT (SELECT (SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=L.CURRENCY_ID) FROM LOCATIONS L WHERE LOC_ID=R.LOCATIONID) FROM REGISTRATIONS R WHERE REGID=PS.REGID)CURRENCY_CODE
		FROM PAYMENT_SCHEDULE PS WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID']."";
		// }

		// $data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$date = date_create($row['DATE']);
			$row['DATE'] = date_format($date,"d M Y");
			$row['DATE_ORDERBY'] = date_format($date,"mdYhm");
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
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $PSID = ($_POST['PSID'] == 'undefined' || $_POST['PSID'] == '') ? 0 : $_POST['PSID'];  
			if($PSID == 0){throw new Exception('PSID Error.');}			

			$delQuery = "EXEC [PAYMENT_SCHEDULE_SP] 3,$PSID,0,0,0,0,0,'',0,0,'',$userid";

			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['delQuery'] = $delQuery;
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







