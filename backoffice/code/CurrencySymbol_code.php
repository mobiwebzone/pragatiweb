<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['USERID']))
{$userid=$_SESSION['USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "saveCurrencySymbol":saveCurrencySymbol($conn);break;
        case "getCurrencySymbols":getCurrencySymbols($conn);break;
        case "deleteCurrencySymbol":deleteCurrencySymbol($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveCurrencySymbol($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $csid  = ($_POST['csid'] == 'undefined' || $_POST['csid'] == '') ? 0 : $_POST['csid'];
        $txtCurrencyClass  = $_POST['txtCurrencyClass'] == 'undefined' ? '' : $_POST['txtCurrencyClass'];
        $txtCurrencyUnicode  = $_POST['txtCurrencyUnicode'] == 'undefined' ? '' : $_POST['txtCurrencyUnicode'];
		
		$actionid = $csid == 0 ? 1 : 2;

		if($txtCurrencyClass == '')
		{throw new Exception("Enter Currency Class.");}
		if($txtCurrencyUnicode == '')
		{throw new Exception("Enter Currency Code.");}

			$sql = "SELECT * FROM CURRENCY_SYMBOL_MASTER WHERE SYMBOLCLASS='$txtCurrencyClass' AND SYMBOLCODE='$txtCurrencyUnicode' AND CSID!=$csid AND ISDELETED=0";
				$row_count = unique($sql);
		
				$data = array();
				if($row_count == 0)
				{
					$query="EXEC [CURRENCY_SYMBOL_MASTER_SP] $actionid,$csid,'$txtCurrencyClass',N'$txtCurrencyUnicode',$userid";
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
						if(!empty($csid))$data['message'] = 'Record successfully updated';
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
     catch(Exception $e)
     {
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
function deleteCurrencySymbol($mysqli){
	try{   
			global $userid;
			$data = array();     
            $csid = ($_POST['csid'] == 'undefined' || $_POST['csid'] == '') ? 0 : $_POST['csid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [CURRENCY_SYMBOL_MASTER_SP] 3,$csid,'','',$userid");
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







