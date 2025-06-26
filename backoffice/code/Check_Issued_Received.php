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
		case "getBankID":getBankID($conn);break;
        case "getBankAccID":getBankAccID($conn);break;
        case "getChequeDetails":getChequeDetails($conn);break;
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
		
		
        $chid  = ($_POST['chid'] == 'undefined' || $_POST['chid'] == '') ? 0 : $_POST['chid'];
        $txtChequetype  = ($_POST['txtChequetype'] == 'undefined' || $_POST['txtChequetype'] == '') ? '' : $_POST['txtChequetype'];
        $ddlBankID  = ($_POST['ddlBankID'] == 'undefined' || $_POST['ddlBankID'] == '') ? 0 : $_POST['ddlBankID'];
        $ddlBankAccNO  = ($_POST['ddlBankAccNO'] == 'undefined' || $_POST['ddlBankAccNO'] == '') ? 0: $_POST['ddlBankAccNO'];
		$TXTBankAccNO  = ($_POST['TXTBankAccNO'] == 'undefined' || $_POST['TXTBankAccNO'] == '') ? '': $_POST['TXTBankAccNO'];
        
        $ddlchequeNO  = ($_POST['ddlchequeNO'] == 'undefined' || $_POST['ddlchequeNO'] == '') ? '' : $_POST['ddlchequeNO'];
        $txtChequeDate  = ($_POST['txtChequeDate'] == 'undefined' || $_POST['txtChequeDate'] == '') ? '' : $_POST['txtChequeDate'];

        $txtChequeToFrom  = ($_POST['txtChequeToFrom'] == 'undefined' || $_POST['txtChequeToFrom'] == '') ? 0 : $_POST['txtChequeToFrom'];
        $txtAmmount  = ($_POST['txtAmmount'] == 'undefined' || $_POST['txtAmmount'] == '') ? 0 : $_POST['txtAmmount'];
        $txtChequeDateON  = ($_POST['txtChequeDateON'] == 'undefined' || $_POST['txtChequeDateON'] == '') ? '' : $_POST['txtChequeDateON'];
        $txtSignatureUPD  = ($_POST['txtSignatureUPD'] == 'undefined' || $_POST['txtSignatureUPD'] == '') ? '' : $_POST['txtSignatureUPD'];
       
        $txtSignature="";
		if(isset($_FILES['txtSignature']['name']) && $_FILES['txtSignature']['size'] > 0){
			$ext = pathinfo($_FILES['txtSignature']['name'],PATHINFO_EXTENSION);
			$txtSignature .= strtolower(time().'.'.$ext);
		}
        $txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		$actionid = $chid == 0 ? 1 : 2;
		
		
		if($txtChequetype == '')
		{throw new Exception("Please Enter STock   Name.");}
		
		
		// $sql = "SELECT * FROM ITEM_STOCK WHERE STOCKTYPE='$txtChequetype' AND chid=$chid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		
		if(isset($_FILES['txtSignature']['name']) && $_FILES['txtSignature']['size'] > 0)
		{
			$query="EXEC [CHEQUES_ISSUED_RECEIVED_SP] $actionid,$chid,'$txtChequetype',$ddlBankID,$ddlBankAccNO,'$TXTBankAccNO','$ddlchequeNO','$txtChequeDate'
            ,'$txtChequeToFrom',$txtAmmount,'$txtChequeDateON','$txtSignature','$txtRemark',$userid";
		}
		else{
			$query="EXEC [CHEQUES_ISSUED_RECEIVED_SP] $actionid,$chid,'$txtChequetype',$ddlBankID,$ddlBankAccNO,'$TXTBankAccNO','$ddlchequeNO','$txtChequeDate'
            ,'$txtChequeToFrom',$txtAmmount,'$txtChequeDateON','$txtSignatureUPD','$txtRemark',$userid";
		}

			
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			// throw new Exception($query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
                if(isset($_FILES['txtSignature']) && $_FILES['txtSignature']['size']>0)
                {
                    move_uploaded_file($_FILES["txtSignature"]["tmp_name"], '../upload/'.$txtSignature);
                

                }
				
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($chid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Item Category already exists.';
		
		// }
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

/*============ Get Bank Name =============*/ 
function getBankID($mysqli){
	try
	{
		$query = "SELECT BANKID,BANKNAME FROM BANKS WHERE ISDELETED=0 ORDER BY BANKNAME ";

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


/*============ Get Cheque Issued Received Details =============*/ 
function getChequeDetails($mysqli){
	try
	{
		$query = "SELECT CHID,CHTYPE,BANKID,(SELECT BANKNAME FROM BANKS WHERE  BANKID=ISD.BANKID)BANKNAME,
        BANKACCID,(SELECT ACCOUNTNO FROM BANK_ACCOUNTS WHERE  BANKACCID=ISD.BANKACCID)ACCOUNTNO_PAIDTO,
        ACCOUNTNO,CHNO,
        CASE WHEN (CHDATE='1900-01-01 00:00:00.000' OR CHDATE IS NULL OR CHDATE='')
        THEN '-'
        ELSE CONVERT(VARCHAR,CHDATE,105)
        END CHDATE, CONVERT(VARCHAR,CHDATE,107)CHDATE_ED,
        CHTOFROM,AMOUNT,
        CASE WHEN (ENCASHEDON='1900-01-01 00:00:00.000' OR ENCASHEDON IS NULL OR ENCASHEDON='')
        THEN '-'
        ELSE CONVERT(VARCHAR,ENCASHEDON,105)
        END ENCASHEDON, CONVERT(VARCHAR,ENCASHEDON,107)ENCASHEDON_ED,CHIMG,REMARKS 
        FROM CHEQUES_ISSUED_RECEIVED ISD WHERE ISDELETED=0 ORDER BY CHDATE DESC";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CHID'] = (int) $row['CHID'];
				
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





/*============ Get BANK account  =============*/ 
function getBankAccID($mysqli){
	try
	{
		$bankid  = ($_POST['bankid'] == 'undefined' || $_POST['bankid'] == '') ? 0 : $_POST['bankid'];
		
		$query = "SELECT BANKACCID,BANKID,ACCOUNTNO FROM BANK_ACCOUNTS WHERE  BANKID=$bankid AND ISDELETED=0 ORDER BY BANKACCID";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['BANKACCID'] = (int) $row['BANKACCID'];
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
            $chid = ($_POST['chid'] == 'undefined' || $_POST['chid'] == '') ? 0 : $_POST['chid'];  

            $query = "EXEC [CHEQUES_ISSUED_RECEIVED_SP] 3,$chid,'',0,0,'','','','',0,'','','',$userid";
			
						// UPDATE ITEM_STOCK_DETAILS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE CHID=$chid";
            $data['$query']= $query;
			$stmt=sqlsrv_query($mysqli,$query );
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







