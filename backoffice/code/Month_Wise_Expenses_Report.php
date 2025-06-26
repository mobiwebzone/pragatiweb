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
		case "getPaymentMode": getPaymentMode($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

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




 function getQuery($mysqli){
		try
	{
		$data = array();
		global $userid;
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_PAYMENT_MODE_CD  = $_POST['TEXT_PAYMENT_MODE_CD'] == 'undefined' ? 0 : $_POST['TEXT_PAYMENT_MODE_CD'];
          
				
		$query="EXEC [EXPENSES_MONTHLY_SUMMARY_SP] $TEXT_SCHOOL_ID,$TEXT_FEES_FY_YEAR_CD,$userid ";
		
		$result = sqlsrv_query($mysqli, $query);

       		
       $query =     " SELECT 
								ROW_NUMBER() OVER (ORDER BY EXPENSE_DESC) AS ROWID,
								EXPENSE_DESC,
								ISNULL([January], 0) AS January,
								ISNULL([February], 0) AS February,
								ISNULL([March], 0) AS March,
								ISNULL([April], 0) AS April,
								ISNULL([May], 0) AS [May],
								ISNULL([June], 0) AS [June],
								ISNULL([July], 0) AS [July],
								ISNULL([August], 0) AS [August],
								ISNULL([September], 0) AS [September],
								ISNULL([October], 0) AS [October],
								ISNULL([November], 0) AS [November],
								ISNULL([December], 0) AS [December],
								0 AS SortOrder  -- Add a sort column for the detailed rows
							FROM (
								SELECT MONTH, EXPENSE_DESC, AMOUNT
								FROM EXPENSES_MONTHLY_SUMMARY
								WHERE SCHOOL_ID = $TEXT_SCHOOL_ID
								AND FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
                                AND PAYMENT_MODE_CD = $TEXT_PAYMENT_MODE_CD
								AND isdeleted = 0
							) AS SourceTable
							PIVOT (
								SUM(AMOUNT)
								FOR MONTH IN ([January], [February], [March], [April], [May], [June], [July], [August], [September], [October], [November], [December])
							) AS PivotTable

							-- Add a total row for each month
							 UNION ALL

							-- Totals for each month
							 SELECT 
								NULL AS ROWID,  -- No row number for the total row
								'Total' AS EXPENSE_DESC,
								ISNULL(SUM([January]), 0) AS January,
								ISNULL(SUM([February]), 0) AS February,
								ISNULL(SUM([March]), 0) AS March,
								ISNULL(SUM([April]), 0) AS April,
								ISNULL(SUM([May]), 0) AS May,
								ISNULL(SUM([June]), 0) AS June,
								ISNULL(SUM([July]), 0) AS July,
								ISNULL(SUM([August]), 0) AS August,
								ISNULL(SUM([September]), 0) AS September,
								ISNULL(SUM([October]), 0) AS October,
								ISNULL(SUM([November]), 0) AS November,
								ISNULL(SUM([December]), 0) AS December,
								1 AS SortOrder  -- Add a sort column for the total row
							FROM (
								SELECT MONTH, EXPENSE_DESC, AMOUNT
								FROM EXPENSES_MONTHLY_SUMMARY
								WHERE SCHOOL_ID = $TEXT_SCHOOL_ID
								AND FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
								AND PAYMENT_MODE_CD = $TEXT_PAYMENT_MODE_CD
								AND isdeleted = 0
							) AS SourceTable
							PIVOT (
								SUM(AMOUNT)
								FOR MONTH IN ([January], [February], [March], [April], [May], [June], [July], [August], [September], [October], [November], [December])
							) AS PivotTable

							-- Sort by the SortOrder column and then by EXPENSE_DESC
							ORDER BY 
								SortOrder,  -- First order by SortOrder (to ensure the total row is last)
								EXPENSE_DESC  ";


					
					 
		
	    
		
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







