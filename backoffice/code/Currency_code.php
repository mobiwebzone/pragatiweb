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
        case "saveCurrency":saveCurrency($conn);break;
        case "getCurrency":getCurrency($conn);break;
        case "getCurrencySymbols":getCurrencySymbols($conn);break;
        case "deleteCurrency":deleteCurrency($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveCurrency($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $currencyid  = ($_POST['currencyid'] == 'undefined' || $_POST['currencyid'] == '') ? 0 : $_POST['currencyid'];
        $txtCurrencyCode  = $_POST['txtCurrencyCode'] == 'undefined' ? '' : $_POST['txtCurrencyCode'];
        $txtCurrencySymbolClass  = $_POST['txtCurrencySymbolClass'] == 'undefined' ? '' : $_POST['txtCurrencySymbolClass'];
        // $Multiply  = $_POST['Multiply'] == 'undefined' ? 0 : $_POST['Multiply'];
        // $Divide  = $_POST['Divide'] == 'undefined' ? 0 : $_POST['Divide'];
		$Multiply = $_POST['MultiplyDIV'] == "Multiply" ? 1 : 0;
		$Divide = $_POST['MultiplyDIV'] == "Divide" ? 1 : 0;
        $txtFactor  = ($_POST['txtFactor'] == 'undefined' || $_POST['txtFactor'] == '') ? 0 : $_POST['txtFactor'];
        $IsmainCurrency  = $_POST['IsmainCurrency'] == 'undefined' ? "false" : $_POST['IsmainCurrency'];
		
		$actionid = $currencyid == 0 ? 1 : 2;

		if($txtCurrencyCode == '')
		{throw new Exception("Enter Currency Code.");}

		
		if($IsmainCurrency == "true"){

			$chkISMain="SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE IS_MAIN=1 AND ISDELETED=0";
			$ISMain_count = unique($chkISMain);
			$ISMain_stmt=sqlsrv_query($mysqli, $chkISMain);
	
			if($ISMain_count <= 0){
				
				$sql = "SELECT * FROM CURRENCY_MASTER WHERE CURRENCY_CODE='$txtCurrencyCode' AND CURRENCY_ID!=$currencyid AND ISDELETED=0";
				$row_count = unique($sql);
		
				$data = array();
				if($row_count == 0)
				{
					$query="EXEC [CURRENCY_MASTER_SP] $actionid,$currencyid,'$txtCurrencyCode','$txtCurrencySymbolClass',$Multiply,$Divide,$txtFactor,$IsmainCurrency,$userid";
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
						if(!empty($currencyid))$data['message'] = 'Record successfully updated';
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
	
			}else{
				$ISMain_row = sqlsrv_fetch_array($ISMain_stmt);
				$Ismain = $ISMain_row['CURRENCY_CODE'];
	
				$data['success'] = false;
				$data['IsmainCurrency'] = $IsmainCurrency;
				$data['message'] = 'Main Currency Already Set. <b>('. $ISMain_row['CURRENCY_CODE'].')</b>';
				echo json_encode($data);exit;
			}
		}
		else{
			$sql = "SELECT * FROM CURRENCY_MASTER WHERE CURRENCY_CODE='$txtCurrencyCode' AND CURRENCY_ID!=$currencyid AND ISDELETED=0";
				$row_count = unique($sql);
		
				$data = array();
				if($row_count == 0)
				{
					$query="EXEC [CURRENCY_MASTER_SP] $actionid,$currencyid,'$txtCurrencyCode','$txtCurrencySymbolClass',$Multiply,$Divide,$txtFactor,$IsmainCurrency,$userid";
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
						if(!empty($currencyid))$data['message'] = 'Record successfully updated';
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


/*============ Get Currency =============*/ 
 function getCurrency($mysqli){
	try
	{
		$query = "SELECT CURRENCY_ID,CURRENCY_CODE,CURRENCY_CLASS,MULTIPLY,DIVIDE,FACTOR,IS_MAIN FROM CURRENCY_MASTER WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CURRENCY_ID'] = (int) $row['CURRENCY_ID'];
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



/*============ Get Currency Symbol =============*/ 
function getCurrencySymbols($mysqli){
	try
	{
		$query = "SELECT CSID,SYMBOLCLASS,SYMBOLCODE FROM CURRENCY_SYMBOL_MASTER WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CSID'] = (int) $row['CSID'];
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
function deleteCurrency($mysqli){
	try{   
			global $userid;
			$data = array();     
            $currencyid = $_POST['currencyid'] == 'undefined' ? 0 : $_POST['currencyid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [CURRENCY_MASTER_SP] 3,$currencyid,'','',0,0,0,0,$userid");
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







