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
        case "getBankAccountsDetails":getBankAccountsDetails($conn);break;
        case "getBankID":getBankID($conn);break;
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
		$data = array();
        global $userid;
		
		$data = array();
        $bankaccid  = ($_POST['bankaccid'] == 'undefined' || $_POST['bankaccid'] == '') ? 0 : $_POST['bankaccid'];
        $ddlBankID  = ($_POST['ddlBankID'] == 'undefined' || $_POST['ddlBankID'] == '') ? 0 : $_POST['ddlBankID'];
        $ddlAcNO  = ($_POST['ddlAcNO'] == 'undefined' || $_POST['ddlAcNO'] == '') ? '' : $_POST['ddlAcNO'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		$TEXT_IFSC_CODE  = ($_POST['TEXT_IFSC_CODE'] == 'undefined' || $_POST['TEXT_IFSC_CODE'] == '') ? '' : $_POST['TEXT_IFSC_CODE'];
		
		$actionid = $bankaccid == 0 ? 1 : 2;
		
		
		if($ddlAcNO == '')
		{throw new Exception("Please Enter Bank Account No.");}
	
		
		$sql = "SELECT * FROM BANK_ACCOUNTS 
		        WHERE ACCOUNTNO='$ddlAcNO' 
				AND   BANKACCID!=$bankaccid 
				AND   BANKID = $ddlBankID 
				AND   ISDELETED=0";
		
		$row_count = unique($sql);
		
		// throw new Exception($query);
		
		if($row_count == 0)
		{
			$query="EXEC [BANK_ACCOUNTS_SP] $actionid,$bankaccid,$ddlBankID,'$ddlAcNO','$txtRemark',$userid,'$TEXT_IFSC_CODE' ";
			
			
			
			$stmt=sqlsrv_query($mysqli, $query);
			
			
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($bankacid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Item Category already exists.';
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





/*============ Get BANK ACCOUNT DETAILS =============*/ 
function getBankAccountsDetails($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		
		$query = "SELECT 
				 BANKACCID
				,BANKID
				,(SELECT LOCID FROM BANKS WHERE BANKID=BID.BANKID)LOCID
				,(SELECT BANKNAME FROM BANKS WHERE BANKID=BID.BANKID)BANKNAME
				,ACCOUNTNO
				,REMARKS
				,IFSC_CODE
				FROM BANK_ACCOUNTS BID 
				WHERE ISDELETED=0 
				AND BANKID IN (SELECT BANKID FROM BANKS WHERE LOCID=$ddlLocation AND ISDELETED=0) ORDER BY BANKACCID ";

		$data = array();
 			$result = sqlsrv_query($mysqli, $query);
			
			if ($result === false) {
 		   
    		die(print_r(sqlsrv_errors(), true)); 
			}


			while ($row = sqlsrv_fetch_array($result)) {
				$row['BANKACCID'] = (int) $row['BANKACCID'];
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






/*============ Get Bank Name =============*/ 
function getBankID($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT BANKID,BANKNAME FROM BANKS WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY BANKNAME ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['BANKID'] = (int) $row['BANKID'];
				
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
            $bankaccid = ($_POST['bankaccid'] == 'undefined' || $_POST['bankaccid'] == '') ? 0 : $_POST['bankaccid'];  
			$query = "EXEC [BANK_ACCOUNTS_SP] 3,$bankaccid,0,'','',$userid,'' ";
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







