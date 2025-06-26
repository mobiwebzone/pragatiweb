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
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		
		$TEXT_PAYMENT_DATE_FROM = $_POST['TEXT_PAYMENT_DATE_FROM'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE_FROM'];
		$TEXT_PAYMENT_DATE_TO  = $_POST['TEXT_PAYMENT_DATE_TO'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE_TO'];	
       
   $cleanedDateFrom = preg_replace('/\s*\([^)]*\)/', '', $TEXT_PAYMENT_DATE_FROM);
    
    // Convert the cleaned date string to a DateTime object
    $dateObjectFrom = new DateTime($cleanedDateFrom);
    
    // Format to 'Y-m-d' to get only the date (or 'Y-m-d H:i:s' for full datetime)
    $formattedDateFrom = $dateObjectFrom->format('Y-m-d');

   
	$cleanedDateTo = preg_replace('/\s*\([^)]*\)/', '', $TEXT_PAYMENT_DATE_TO);
    
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
							FROM SCHOOL_EXPENSES WHERE ISDELETED=0 
							AND  SCHOOL_ID = $TEXT_SCHOOL_ID
							AND CAST(PAYMENT_DATE AS DATE) BETWEEN '$formattedDateFrom' AND '$formattedDateTo'
							order by MONTH_CD,payment_date ";
						
							
		
	     

		// $query .= " AND A.FY_YEAR_CD = $TEXT_FY_YEAR_CD ";
       

      

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







