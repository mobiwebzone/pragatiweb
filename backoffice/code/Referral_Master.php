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
        case "save":save($conn);break;
        case "getReferralMaster":getReferralMaster($conn);break;
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


 function save($mysqli){
     try
     {
		 global $userid;
		$data = array();
    
        $refmid  = ($_POST['refmid'] == 'undefined' || $_POST['refmid'] == '') ? 0 : $_POST['refmid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtRefDesc  = $_POST['txtRefDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRefDesc']);
		$txtAmount =($_POST['txtAmount'] == 'undefined' || $_POST['txtAmount'] == '') ? 0 : $_POST['txtAmount'];
		$txtValidFrom = ($_POST['txtValidFrom'] == 'undefined' || $_POST['txtValidFrom'] == '') ? '' : $_POST['txtValidFrom'];
		$txtValidTo = ($_POST['txtValidTo'] == 'undefined' || $_POST['txtValidTo'] == '') ? '' : $_POST['txtValidTo'];
		$txtTermsConditions = $_POST['txtTermsConditions'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTermsConditions']);
		$txtRemarks = $_POST['txtRemarks'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks']);
		
		$actionid = $refmid == 0 ? 1 : 2;

		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtRefDesc == '')throw new Exception("Please Enter Referral Desc.");
		if($txtAmount == 0)throw new Exception("Please Enter Referral Amount.");
		if($txtValidFrom == '')throw new Exception("Please Enter Valid From.");
		if($txtValidTo == '')throw new Exception("Please Enter Valid Upto.");
		if($txtTermsConditions == '')throw new Exception("Please Enter Terms & Conditions.");

		$sql = "SELECT * FROM REFERRAL_MASTER WHERE LOCID=$ddlLocation AND REF_DESC='$txtRefDesc' AND REF_AMOUNT=$txtAmount AND 
				CONVERT(DATE,VALID_FROM,105)='$txtValidFrom' AND CONVERT(DATE,VALID_UPTO,105)='$txtValidTo' AND 
				REFMID!=$refmid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [REFERRAL_MASTER_SP] $actionid,$refmid,$ddlLocation,'$txtRefDesc',$txtAmount,'$txtValidFrom','$txtValidTo',
											'$txtTermsConditions','$txtRemarks',$userid";
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
				if(!empty($refmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Referral already exists.';
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


/*============ GET REFERRAL MASTER =============*/ 
 function getReferralMaster($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		if($LOCID==0) throw new Exception('Invalid LOCID.');
		$query = "SELECT REFMID,REF_DESC,REF_AMOUNT,
				CONVERT(VARCHAR,VALID_FROM,106)VALID_FROM,CONVERT(VARCHAR,VALID_UPTO,106)VALID_UPTO,
				TERMSCONDITIONS,REMARKS FROM REFERRAL_MASTER WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY REF_DESC";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REFMID'] = (int) $row['REFMID'];
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
/*============ GET REFERRAL MASTER =============*/ 


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $REFMID = ($_POST['REFMID'] == 'undefined' || $_POST['REFMID'] == '') ? 0 : $_POST['REFMID'];  
			if($REFMID == 0)throw new Exception('REFMID Error.');
			$del = "EXEC [REFERRAL_MASTER_SP] 3,$REFMID,0,'',0,'','','','',$userid";
			$stmt=sqlsrv_query($mysqli, $del);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted.';
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







