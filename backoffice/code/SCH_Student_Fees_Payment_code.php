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
		case "getClass": getClass($conn);break;
		case "getStudent": getStudent($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "getFeesDue": getFeesDue($conn);break;
		case "getPaymentMode": getPaymentMode($conn);break;
		case "getOtherFees": getOtherFees($conn);break;
		case "getSum": getSum($conn);break;
		case "delete":delete($conn);break;
		case "getFeesHeads":getFeesHeads($conn);break;
		case "getStudentDetails":getStudentDetails($conn);break;
		case "getBank": getBank($conn);break;

		
		
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
    
        $feespaymentid  = ($_POST['feespaymentid'] == 'undefined' || $_POST['feespaymentid'] == '') ? 0 : $_POST['feespaymentid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
		$TEXT_FEES_PAID  = $_POST['TEXT_FEES_PAID'] == 'undefined' ? 0 : $_POST['TEXT_FEES_PAID'];
		$TEXT_PAYMENT_MODE_CD  = $_POST['TEXT_PAYMENT_MODE_CD'] == 'undefined' ? 0 : $_POST['TEXT_PAYMENT_MODE_CD'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		$TEXT_OTHER_FEES_TYPES_CD  = $_POST['TEXT_OTHER_FEES_TYPES_CD'] == 'undefined' ? 0 : $_POST['TEXT_OTHER_FEES_TYPES_CD'];
		$TEXT_OTHER_FEES_AMOUNT  = $_POST['TEXT_OTHER_FEES_AMOUNT'] == 'undefined' ? 0 : $_POST['TEXT_OTHER_FEES_AMOUNT'];
		$TEXT_RECEIPT_NO  = $_POST['TEXT_RECEIPT_NO'] == 'undefined' ? 0 : $_POST['TEXT_RECEIPT_NO'];
		
		$TEXT_CHEQUE_NO  = $_POST['TEXT_CHEQUE_NO'] == 'undefined' ? 0 : $_POST['TEXT_CHEQUE_NO'];
		$TEXT_CHEQUE_DATE  = $_POST['TEXT_CHEQUE_DATE'] == 'undefined' ? '' : $_POST['TEXT_CHEQUE_DATE'];
		$TEXT_BANK_CD  = $_POST['TEXT_BANK_CD'] == 'undefined' ? 0 : $_POST['TEXT_BANK_CD'];
		
		$TEXT_UPI_ID  = $_POST['TEXT_UPI_ID'] == 'undefined' ? 0 : $_POST['TEXT_UPI_ID'];
		$TEXT_MOBILE_NO  = $_POST['TEXT_MOBILE_NO'] == 'undefined' ? 0 : $_POST['TEXT_MOBILE_NO'];

		$actionid = $feespaymentid == 0 ? 1 : 2;
           
			
				$sql = "SELECT * FROM STUDENT_FEES_PAYMENT
		        WHERE FEES_PAYMENT_ID != $feespaymentid
				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
				AND   STUDENT_ID = $TEXT_STUDENT_ID
				AND   CLASS_CD   = $TEXT_CLASS_CD
			    AND   PAYMENT_DATE = '$TEXT_PAYMENT_DATE'
				AND   PAYMENT_MODE_CD = $TEXT_PAYMENT_MODE_CD
				AND   RECEIPT_NO  = $TEXT_RECEIPT_NO
				AND   ISDELETED = 0	";	
       
	// throw new Exception($sql);
	   $row_count = unique($sql);
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [INSERT_STUDENT_FEES_PAYMENT_SP]1, $TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_STUDENT_ID,$TEXT_FEES_FY_YEAR_CD,'$TEXT_PAYMENT_DATE',$TEXT_FEES_PAID,$userid,$TEXT_PAYMENT_MODE_CD,$TEXT_RECEIPT_NO,$TEXT_OTHER_FEES_AMOUNT,$TEXT_CHEQUE_NO,'$TEXT_CHEQUE_DATE',$TEXT_BANK_CD,$TEXT_UPI_ID,$TEXT_MOBILE_NO ";

    //    echo json_encode($query);exit;
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
				if(!empty($feespaymentid))
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
		ob_end_clean(); // Clear output buffer before sending JSON
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


function getStudentDetails($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
       	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		
       $query =     " SELECT 
							STUDENT_ID,
							FY_YEAR,
							(STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME,
							CLASS,
							FORMAT(GETDATE(), 'dd-MMM-yyyy') AS TODAYS_DATE
							FROM STUDENT
							WHERE SCHOOL_ID = $TEXT_SCHOOL_ID AND CLASS_CD = $TEXT_CLASS_CD AND STUDENT_ID = $TEXT_STUDENT_ID;	";
														
        
        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
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




function getFeesHeads($mysqli){
		try
	{
		$data = array();
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		
		
       $query =     " SELECT 
								CAST(DETAIL_ID AS VARCHAR) AS DETAIL_ID,
								CAST(FEES_ID AS VARCHAR) AS FEES_ID,
								CAST(STUDENT_ID AS VARCHAR) AS STUDENT_ID,
								CAST(FEES_YEAR_CD AS VARCHAR) AS FEES_YEAR_CD,
								CAST(FEES_HEAD_CD AS VARCHAR) AS FEES_HEAD_CD,
								FEES_HEAD,
								AMOUNT_DUE,
								REMARKS
								FROM STUDENT_FEES_DETAIL
								WHERE STUDENT_ID = $TEXT_STUDENT_ID AND FEES_YEAR_CD = $TEXT_FEES_FY_YEAR_CD AND ISDELETED = 0
								UNION ALL
								SELECT 
								'' AS DETAIL_ID,
								'' AS FEES_ID,
								'' AS STUDENT_ID,
								'' AS FEES_YEAR_CD,
								'' AS FEES_HEAD_CD,
								'Total' AS FEES_HEAD,
								SUM(AMOUNT_DUE) AS AMOUNT_DUE,
								'' AS REMARKS
								FROM STUDENT_FEES_DETAIL
								WHERE STUDENT_ID = $TEXT_STUDENT_ID AND FEES_YEAR_CD = $TEXT_FEES_FY_YEAR_CD AND ISDELETED = 0;
								";
														
        
        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['DETAIL_ID'] = (int) $row['DETAIL_ID'];
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


 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
       	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
		
		
       $query =     " SELECT A.FEES_PAYMENT_ID
							,A.SCHOOL_ID
							,A.FEES_ID
							,A.STUDENT_ID
							,CONVERT(VARCHAR,A.PAYMENT_DATE,106)PAYMENT_DATE
							,A.FEES_FY_YEAR_CD
							,A.FEES_FY_YEAR
							,A.REMARKS
							,A.CLASS_CD
							,A.CLASS 
							, (B.STUDENT_FIRST_NAME +' '+B.STUDENT_LAST_NAME) STUDENT_NAME 
							,A.PAYMENT_MODE_CD
							,A.PAYMENT_MODE
							,A.FEES_PAID
							,A.RECEIPT_NO
							FROM STUDENT_FEES_PAYMENT A, STUDENT B 
							WHERE A.ISDELETED=0 
							AND A.STUDENT_ID = B.STUDENT_ID
							AND A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
							AND A.SCHOOL_ID = $TEXT_SCHOOL_ID
							AND A.CLASS_CD  = $TEXT_CLASS_CD
							AND B.ISDELETED = 0 ";
														
         if ($TEXT_STUDENT_ID != '') 
		 {
		    $query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID ";
		 }

        $result = sqlsrv_query($mysqli, $query);

		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FEES_PAYMENT_ID'] = (int) $row['FEES_PAYMENT_ID'];
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


function getOtherFees($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=51 and code_detail_id=720 and isdeleted=0";

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




function getFeesDue($mysqli){
	

	try
	{
	$data = array();	
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
    $TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
    $TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
	
	$query = "SELECT FEES_ID, FEES_BALANCE 
	         FROM  STUDENT_FEES_MASTER 
			 where ISDELETED = 0
			 and   STUDENT_ID = $TEXT_STUDENT_ID
			 and   CLASS_CD = $TEXT_CLASS_CD 
			 and   FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
			 and   SCHOOL_ID = $TEXT_SCHOOL_ID ";

		$data['query'] = $query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['FEES_ID'] = (int) $row['FEES_ID'];
				
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



function getSum($mysqli){
	try {
		$data = array();
		
		$TEXT_FEES_DUE  = $_POST['TEXT_FEES_DUE'] == 'undefined' ? 0 : $_POST['TEXT_FEES_DUE'];
		$TEXT_OTHER_FEES_AMOUNT  = $_POST['TEXT_OTHER_FEES_AMOUNT'] == 'undefined' ? 0 : $_POST['TEXT_OTHER_FEES_AMOUNT'];
		
		$totalfees = $TEXT_FEES_DUE + $TEXT_OTHER_FEES_AMOUNT;
		
		$data['totalfees'] = $totalfees;
		$data['success'] = true;

		// Output JSON response
		echo json_encode($data);
		exit;
	} catch (Exception $e) {
		// Handle errors and return error message
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



function getStudent($mysqli){
	

	try
	{
	$data = array();	
	
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
    $TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
	
	$query = "SELECT 
	              A.STUDENT_ID
				  ,(B.STUDENT_FIRST_NAME+' '+B.STUDENT_LAST_NAME) STUDENT_NAME 
	         FROM  STUDENT_FEES_MASTER A, STUDENT B 
			 where A.ISDELETED = 0
			 AND   B.ISDELETED = 0
			 AND   B.ARCHIVED =  0
			 AND   A.STUDENT_ID = B.STUDENT_ID
			 and   A.CLASS_CD = $TEXT_CLASS_CD 
			 and   A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
			 and   A.SCHOOL_ID = $TEXT_SCHOOL_ID ";

		$data['query'] = $query;

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
				
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


function getClass($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
	 
	$query = "SELECT CLASS_CD,CLASS FROM  SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0 order by SCHOOL_CLASS_ID";
	
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLASS_CD'] = (int) $row['CLASS_CD'];
				
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
            $feespaymentid = ($_POST['feespaymentid'] == 'undefined' || $_POST['feespaymentid'] == '') ? 0 : $_POST['feespaymentid'];
			$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
			$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
			$TEXT_FEES_PAID  = $_POST['TEXT_FEES_PAID'] == 'undefined' ? 0 : $_POST['TEXT_FEES_PAID'];
			$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
			$TEXT_RECEIPT_NO  = $_POST['TEXT_RECEIPT_NO'] == 'undefined' ? 0 : $_POST['TEXT_RECEIPT_NO'];
			

			
			if($feespaymentid == 0){
				throw new Exception('FEES_PAYMENT_ID Error.');
			}

	
	
	// $stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_FEES_PAYMENT_SP] 3,$feespaymentid,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_STUDENT_ID,'',$TEXT_FEES_PAID,$TEXT_FEES_FY_YEAR_CD,$userid,'',650,'','','','' ");
    $stmt=sqlsrv_query($mysqli, "EXEC [INSERT_STUDENT_FEES_PAYMENT_SP] 3,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_STUDENT_ID,$TEXT_FEES_FY_YEAR_CD,'',$TEXT_FEES_PAID,$userid,'',$TEXT_RECEIPT_NO,'','','','','','' ");
	
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







