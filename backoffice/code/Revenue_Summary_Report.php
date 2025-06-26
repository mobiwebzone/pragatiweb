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
		
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		
		case "getFinancialYear": getFinancialYear($conn);break;
		
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */





 function getQuery($mysqli){
		try
	{
		$data = array();
		global $userid;
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];

         
       $query =     " SELECT 
							NULL AS ROWID,
							PAYMENT_MONTH_CD, 
							PAYMENT_MONTH,
							SUM(CASE WHEN PAYMENT_MODE = 'Cash' THEN FEES_PAID ELSE 0 END) AS CASH,
							SUM(CASE WHEN PAYMENT_MODE = 'UPI' THEN FEES_PAID ELSE 0 END) AS UPI,
							SUM(CASE WHEN PAYMENT_MODE = 'Cheque' THEN FEES_PAID ELSE 0 END) AS CHEQUE,
							SUM(FEES_PAID) AS TOTAL  -- Total fees paid across all modes
						FROM 
							STUDENT_FEES_PAYMENT
						WHERE 
							ISDELETED = 0
							AND FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
							AND SCHOOL_ID       = $TEXT_SCHOOL_ID
						GROUP BY 
							PAYMENT_MONTH_CD, 
							PAYMENT_MONTH
						ORDER BY 
							PAYMENT_MONTH_CD
						";

		
		//  throw new Exception($query);

        $result = sqlsrv_query($mysqli, $query);
		
		$data = array();
		
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['ROWID'] = (int) $row['ROWID'];
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







