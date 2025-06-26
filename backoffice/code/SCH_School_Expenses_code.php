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
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		case "getmonth": getmonth($conn);break;
		case "getExpenseHead": getExpenseHead($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getPaymentMode": getPaymentMode($conn);break;
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
    
        $expenseid  = ($_POST['expenseid'] == 'undefined' || $_POST['expenseid'] == '') ? 0 : $_POST['expenseid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
	    $TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
		$TEXT_EXPENSE_CD  = $_POST['TEXT_EXPENSE_CD'] == 'undefined' ? 0 : $_POST['TEXT_EXPENSE_CD'];
		$TEXT_PAYMENT_MODE_CD  = $_POST['TEXT_PAYMENT_MODE_CD'] == 'undefined' ? 0 : $_POST['TEXT_PAYMENT_MODE_CD'];
		$TEXT_AMOUNT  = $_POST['TEXT_AMOUNT'] == 'undefined' ? 0 : $_POST['TEXT_AMOUNT'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$TEXT_VOUCHER_NO  = $_POST['TEXT_VOUCHER_NO'] == 'undefined' ? 0 : $_POST['TEXT_VOUCHER_NO'];
		$TEXT_INSTRUMENT_NO  = $_POST['TEXT_INSTRUMENT_NO'] == 'undefined' ? '' : $_POST['TEXT_INSTRUMENT_NO'];
		$actionid = $expenseid == 0 ? 1 : 2;

			
				$sql = "SELECT * FROM SCHOOL_EXPENSES
						WHERE EXPENSE_ID!  = $expenseid
						AND   SCHOOL_ID    = $TEXT_SCHOOL_ID
						AND   EXPENSE_CD   = $TEXT_EXPENSE_CD
						AND   FY_YEAR_CD   = $TEXT_FY_YEAR_CD
						AND   PAYMENT_DATE = '$TEXT_PAYMENT_DATE'
						AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	
	  if($row_count == 0)
	   {
	   
		$query="EXEC [SCHOOL_EXPENSES_SP] $actionid,$expenseid,$TEXT_SCHOOL_ID,$TEXT_FY_YEAR_CD,'$TEXT_PAYMENT_DATE',$TEXT_PAYMENT_MODE_CD,$TEXT_EXPENSE_CD,$TEXT_AMOUNT,$userid,'$txtremarks','$TEXT_VOUCHER_NO','$TEXT_INSTRUMENT_NO' ";
        
		

		$data['$sql'] = $query;
		
		

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
				if(!empty($expenseid))
				$data['message'] = 'Record successfully updated';
				else 
				$data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
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




 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];

		
		$fromdate = $_POST['fromdate'] == 'undefined' ? '' : $_POST['fromdate'];
		$todate  = $_POST['todate'] == 'undefined' ? '' : $_POST['todate'];
		$expmonth = $_POST['expmonth'] == 'undefined' ? '' : $_POST['expmonth'];
		
		    
		$cleanedDateFrom = preg_replace('/\s*\([^)]*\)/', '', $fromdate);
    
		// Convert the cleaned date string to a DateTime object
		$dateObjectFrom = new DateTime($cleanedDateFrom);
		
		// Format to 'Y-m-d' to get only the date (or 'Y-m-d H:i:s' for full datetime)
		$formattedDateFrom = $dateObjectFrom->format('Y-m-d');
	
	   
		$cleanedDateTo = preg_replace('/\s*\([^)]*\)/', '', $todate);
		
		// Convert the cleaned date string to a DateTime object
		$dateObjectTo = new DateTime($cleanedDateTo);
		
		// Format to 'Y-m-d' to get only the date (or 'Y-m-d H:i:s' for full datetime)
		$formattedDateTo = $dateObjectTo->format('Y-m-d');
	
       		
       $query =     "SELECT EXPENSE_ID
							,SCHOOL_ID
							,SCHOOL_NAME
							,FY_YEAR_CD
							,FY_YEAR
							,MONTH_CD
							,MONTH
							,CONVERT(VARCHAR,PAYMENT_DATE,106)PAYMENT_DATE
							,EXPENSE_CD
							,EXPENSE_DESC
							,PAYMENT_MODE_CD
							,PAYMENT_MODE
							,AMOUNT
							,REMARKS
							,VOUCHER_NO
                            ,INSTRUMENT_NO
							FROM SCHOOL_EXPENSES 
							WHERE ISDELETED=0 
							AND SCHOOL_ID  = $TEXT_SCHOOL_ID
							AND FY_YEAR_CD = $TEXT_FY_YEAR_CD 
							 ";
		
	    
	
	
		if ($fromdate != '' AND $todate != '') {
			$query .= " AND CAST(PAYMENT_DATE AS DATE) between '$formattedDateFrom' and '$formattedDateTo' "; 
		}

		if ($expmonth != '') {
			$query .= " AND MONTH_CD = $expmonth "; 
		}
	
		// $query .= " order by MONTH_CD "; 

      

        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['EXPENSE_ID'] = (int) $row['EXPENSE_ID'];
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



function getmonth($mysqli){
	try
	{
		
	$query = "SELECT DISTINCT  MONTH_CD,MONTH FROM SCHOOL_EXPENSES where isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MONTH_CD'] = (int) $row['MONTH_CD'];
				
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




function getPaymentMode($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=50 and isdeleted=0";

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



function getExpenseHead($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=48 and isdeleted=0 and code_detail_id not in (621)";

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



function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0";

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


function getschoolname($mysqli){
	try
	{
		global $userid;
		$query = "select SCHOOL_ID,SCHOOL_NAME FROM SCHOOL WHERE ISDELETED=0 
		AND SCHOOL_ID IN (SELECT SCHOOL_ID FROM SCHOOL_USER WHERE USER_ID= $userid AND ISDELETED=0)
		ORDER BY SCHOOL_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SCHOOL_ID'] = (int) $row['SCHOOL_ID'];
				
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


function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
           		
			$expenseid = ($_POST['expenseid'] == 'undefined' || $_POST['expenseid'] == '') ? 0 : $_POST['expenseid'];  

			if($expenseid == 0){
				throw new Exception('EXPENSE_ID Error.');
			}

	
	
	$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_EXPENSES_SP] 3,$expenseid,'','','','','',NULL,$userid,'','','' ");
	
	
	
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







