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
		case "getTechStackData":getTechStackData($conn);break;
        case "getRecurrCurrency":getRecurrCurrency($conn);break;
		case "getFrequency":getFrequency($conn);break;
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



// Get Recurring Cost Currency
function getRecurrCurrency($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=15 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

// Get Frequency
function getFrequency($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=23 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		$TECHSTACKID  = ($_POST['TECHSTACKID'] == 'undefined' || $_POST['TECHSTACKID'] == '') ? 0 : $_POST['TECHSTACKID'];
        $txtSoftwarename  = ($_POST['txtSoftwarename'] == 'undefined' || $_POST['txtSoftwarename'] == '') ? '' : $_POST['txtSoftwarename'];
        $txtPurpose  = ($_POST['txtPurpose'] == 'undefined' || $_POST['txtPurpose'] == '') ? '' : $_POST['txtPurpose'];
        $txtVersion  = ($_POST['txtVersion'] == 'undefined' || $_POST['txtVersion'] == '') ? '' : $_POST['txtVersion'];
		$txtWeblink  = ($_POST['txtWeblink'] == 'undefined' || $_POST['txtWeblink'] == '') ? '' : $_POST['txtWeblink'];
		$NumOnetimeCost  = ($_POST['NumOnetimeCost'] == 'undefined' || $_POST['NumOnetimeCost'] == '') ? 0 : $_POST['NumOnetimeCost'];
        $NumRecurringcost  = ($_POST['NumRecurringcost'] == 'undefined' || $_POST['NumRecurringcost'] == '') ? 0 : $_POST['NumRecurringcost'];
		$ddlRecurrCurrency  = ($_POST['ddlRecurrCurrency'] == 'undefined' || $_POST['ddlRecurrCurrency'] == '') ? 0 : $_POST['ddlRecurrCurrency'];
		$ddlFrequency  = ($_POST['ddlFrequency'] == 'undefined' || $_POST['ddlFrequency'] == '') ? 0 : $_POST['ddlFrequency'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		$actionid = $TECHSTACKID == 0 ? 1 : 2;
		
		if($txtSoftwarename == '')
		{throw new Exception("Please Write Software Name.");}
	
		$querycount = "SELECT * FROM MEP_TECH_STACK WHERE SOFTWARE_NAME='$txtSoftwarename' AND PURPOSE='$txtPurpose' AND TECHSTACKID!=$TECHSTACKID AND ISDELETED=0";
		$row_count = unique($querycount);	


		if($row_count == 0)
		{
			$query="EXEC [IT_TECHSTACK_SP] $actionid,$TECHSTACKID,'$txtSoftwarename','$txtPurpose','$txtVersion','$txtWeblink',$NumOnetimeCost,
			$NumRecurringcost,$ddlRecurrCurrency,$ddlFrequency,$userid,'$txtRemark'";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				die( print_r( sqlsrv_errors(), true));
						throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($TECHSTACKID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
		 }
		 else
		 {
			
		 	$data['success'] = false;
		 	$data['message'] = 'Record already exists.';
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

/*============ GET SALES DATA =============*/ 
function getTechStackData($mysqli){
	try
	{
		$data = array();
		

		$query = "SELECT TECHSTACKID,SOFTWARE_NAME,PURPOSE,VERSION,WEBSITE_LINK,ONE_TIME_COST,
		RECURR_COST,RECURR_COST_CURRENCY_CD,RECURR_COST_CURRENCY,RECURR_COST_FREQ_CD,RECURR_COST_FREQ,REMARKS FROM MEP_TECH_STACK 
		WHERE ISDELETED=0 
		ORDER BY TECHSTACKID";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
            $TECHSTACKID = ($_POST['TECHSTACKID'] == 'undefined' || $_POST['TECHSTACKID'] == '') ? 0 : $_POST['TECHSTACKID'];
			if($TECHSTACKID == 0) throw new Exception('Invalid TECH STACK-ID.');
			$query = "EXEC [IT_TECHSTACK_SP] 3,$TECHSTACKID,'','','','',0,0,0,0,$userid,''";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}






