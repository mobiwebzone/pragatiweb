<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Start output buffering
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
		case "getNetSalary": getNetSalary($conn);break;
		case "getEmployeeName": getEmployeeName($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getPaymentMode": getPaymentMode($conn);break;
		case "delete":delete($conn);break;
		
		case "getBank": getBank($conn);break;
		case "getMonth": getMonth($conn);break;

		
		
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
    
        $paymentid  = ($_POST['paymentid'] == 'undefined' || $_POST['paymentid'] == '') ? 0 : $_POST['paymentid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_MONTH_ID  = $_POST['TEXT_MONTH_ID'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_ID'];
        $TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		
		$TEXT_EMPLOYEE_ID  = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
		$TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
		
		$TEXT_SALARY_PROCESS_ID  = $_POST['TEXT_SALARY_PROCESS_ID'] == 'undefined' ? 0 : $_POST['TEXT_SALARY_PROCESS_ID'];
		$TEXT_PAID_AMOUNT  = $_POST['TEXT_PAID_AMOUNT'] == 'undefined' ? 0 : $_POST['TEXT_PAID_AMOUNT'];
		
		$TEXT_PAYMENT_MODE_CD  = $_POST['TEXT_PAYMENT_MODE_CD'] == 'undefined' ? 0 : $_POST['TEXT_PAYMENT_MODE_CD'];
	    
		
		$TEXT_CHEQUE_NO  = $_POST['TEXT_CHEQUE_NO'] == 'undefined' ? 0 : $_POST['TEXT_CHEQUE_NO'];
		$TEXT_CHEQUE_DATE  = $_POST['TEXT_CHEQUE_DATE'] == 'undefined' ? '' : $_POST['TEXT_CHEQUE_DATE'];
		$TEXT_BANK_CD  = $_POST['TEXT_BANK_CD'] == 'undefined' ? 0 : $_POST['TEXT_BANK_CD'];
		
		$TEXT_UPI_ID  = $_POST['TEXT_UPI_ID'] == 'undefined' ? 0 : $_POST['TEXT_UPI_ID'];
		$TEXT_UPI_PLATFORM  = $_POST['TEXT_UPI_PLATFORM'] == 'undefined' ? 0 : $_POST['TEXT_UPI_PLATFORM'];
        $TEXT_MOBILE_NO  = $_POST['TEXT_MOBILE_NO'] == 'undefined' ? 0 : $_POST['TEXT_MOBILE_NO'];

		$actionid = $paymentid == 0 ? 1 : 2;
        
	
		$query="EXEC [EMPLOYEE_SALARY_PAYMENT_SP]
												1
												,$paymentid
												,$TEXT_SCHOOL_ID
												,$TEXT_SALARY_PROCESS_ID
												,$TEXT_EMPLOYEE_ID
												,$TEXT_FY_YEAR_CD
												,$TEXT_MONTH_ID
												,'$TEXT_PAYMENT_DATE'
												,$TEXT_PAYMENT_MODE_CD
												,$TEXT_PAID_AMOUNT
												,$TEXT_CHEQUE_NO
												,'$TEXT_CHEQUE_DATE'
												,$TEXT_BANK_CD
												,'$TEXT_UPI_ID'
												,$TEXT_MOBILE_NO
												,'$TEXT_UPI_PLATFORM'
												,$userid ";

  
	

	    // echo json_encode($query);exit;
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
				if(!empty($paymentid))
				$data['message'] = 'Record successfully updated';
				else 
				$data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
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


 function getMonth($mysqli){
	try
	{
	$data = array();
	
	$query = "SELECT MONTH_ID, MONTH FROM MONTH   
	          ORDER BY MONTH_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MONTH_ID'] = (int) $row['MONTH_ID'];
				
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


function getBank($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
	$query = "SELECT BANKID, BANKNAME FROM BANKS WHERE ISDELETED = 0  
	          AND LOCID IN (SELECT LOC_ID FROM SCHOOL WHERE SCHOOL_ID = $TEXT_SCHOOL_ID)";

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


 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_MONTH_ID  = $_POST['TEXT_MONTH_ID'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_ID'];
       	$TEXT_EMPLOYEE_ID  = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
		
       $query =     " SELECT 
									A.PAYMENT_ID
									,A.SALARY_PROCESS_ID
									,A.EMPLOYEE_ID
									,A.SCHOOL_ID
									,A.FY_YEAR_CD
									,A.FY_YEAR
									,A.MONTH_ID
									,A.MONTH
									,CONVERT(VARCHAR,A.PAYMENT_DATE,106)PAYMENT_DATE
									,A.PAYMENT_MODE_CD
									,A.PAYMENT_MODE
									,A.PAID_AMOUNT
									,B.EMPLOYEE_NAME
									,C.NET_SALARY
									FROM EMPLOYEE_SALARY_PAYMENT A, EMPLOYEE_MASTER B , EMPLOYEE_SALARY_PROCESS C
									WHERE A.EMPLOYEE_ID = B.EMPLOYEE_ID
									AND   A.SALARY_PROCESS_ID = C.SALARY_PROCESS_ID
									AND   A.SCHOOL_ID  = $TEXT_SCHOOL_ID
									AND   A.FY_YEAR_CD = $TEXT_FY_YEAR_CD
									AND   A.ISDELETED  = 0
									AND   B.ISDELETED  = 0
									AND   C.ISDELETED  = 0
									";
		
								if ($TEXT_MONTH_ID != '') {
									$query .= " AND A.MONTH_ID = $TEXT_MONTH_ID"; 
								}

								if ($TEXT_EMPLOYEE_ID != '') {
									$query .= " AND A.EMPLOYEE_ID = $TEXT_EMPLOYEE_ID"; 
								}

        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['PAYMENT_ID'] = (int) $row['PAYMENT_ID'];
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






function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0 ";
	

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



function getEmployeeName($mysqli){
	

	try
	{
	$data = array();	
	
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
   	$query = "SELECT EMPLOYEE_ID, EMPLOYEE_NAME 
	         FROM EMPLOYEE_MASTER 
			 WHERE ISDELETED = 0 
			 AND SCHOOL_ID   = $TEXT_SCHOOL_ID 
			 ORDER BY EMPLOYEE_NAME 
			  ";

		$data['query'] = $query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['EMPLOYEE_ID'] = (int) $row['EMPLOYEE_ID'];
				
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


function getNetSalary($mysqli){
	try
	{
		
	$data = array();
	$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];	
	$TEXT_MONTH_ID  = $_POST['TEXT_MONTH_ID'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_ID'];
	$TEXT_EMPLOYEE_ID  = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
	 
	$query = "SELECT SALARY_PROCESS_ID, NET_SALARY FROM EMPLOYEE_SALARY_PROCESS 
				WHERE EMPLOYEE_ID = $TEXT_EMPLOYEE_ID
				AND   MONTH_ID    = $TEXT_MONTH_ID
				AND   IS_PROCESSED = 1
				AND   IS_PAID      = 0
				AND   FY_YEAR_CD   = $TEXT_FY_YEAR_CD
				AND   ISDELETED    = 0
				";
	
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SALARY_PROCESS_ID'] = (int) $row['SALARY_PROCESS_ID'];
				
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
            $schoolid = ($_POST['schoolid'] == 'undefined' || $_POST['schoolid'] == '') ? 0 : $_POST['schoolid'];
			$paymentid = ($_POST['paymentid'] == 'undefined' || $_POST['paymentid'] == '') ? 0 : $_POST['paymentid'];
			$monthid = ($_POST['monthid'] == 'undefined' || $_POST['monthid'] == '') ? 0 : $_POST['monthid'];
			$empid = ($_POST['empid'] == 'undefined' || $_POST['empid'] == '') ? 0 : $_POST['empid'];
            $salaryprocessid = ($_POST['salaryprocessid'] == 'undefined' || $_POST['salaryprocessid'] == '') ? 0 : $_POST['salaryprocessid'];
			  
			if($paymentid == 0){
				throw new Exception('PAYMENT_ID Error.');
			}

	
	
    $stmt=sqlsrv_query($mysqli, "EXEC [EMPLOYEE_SALARY_PAYMENT_SP] 3,$paymentid,$schoolid,$salaryprocessid,$empid,'',$monthid,'','','','','','','','','',$userid ");
	
	// echo json_encode($stmt);exit;
	
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







