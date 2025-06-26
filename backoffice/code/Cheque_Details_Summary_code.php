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
		// case "save":save($conn);break;
        case "getQuery":getQuery($conn);break;
		case "getschoolname":getschoolname($conn);break;
		// case "getClass": getClass($conn);break;
		// case "getStudent": getStudent($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		// case "getFeesDue": getFeesDue($conn);break;
		// case "delete":delete($conn);break;
		case "getRte": getRte($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


//  function save($mysqli){
//      try
//      {
// 		$data = array();
//         global $userid;
    
//         $feespaymentid  = ($_POST['feespaymentid'] == 'undefined' || $_POST['feespaymentid'] == '') ? 0 : $_POST['feespaymentid'];
// 		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
// 		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
//         $TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
// 		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
// 		$TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
// 		$TEXT_FEES_PAID  = $_POST['TEXT_FEES_PAID'] == 'undefined' ? 0 : $_POST['TEXT_FEES_PAID'];
// 	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
// 		$actionid = $feespaymentid == 0 ? 1 : 2;

			
// 				$sql = "SELECT * FROM STUDENT_FEES_PAYMENT
// 		        WHERE FEES_PAYMENT_ID!=$feespaymentid
// 				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
// 				AND   STUDENT_ID = $TEXT_STUDENT_ID
// 				AND   CLASS_CD   = $TEXT_CLASS_CD
// 			    AND   PAYMENT_DATE = '$TEXT_PAYMENT_DATE'
// 				AND   ISDELETED = 0";	
       
// 		// throw new Exception($sql);
// 	   $row_count = unique($sql);
	
	   
// 	   if($row_count == 0)
// 	   {
	   
// 		$query="EXEC [STUDENT_FEES_PAYMENT_SP] $actionid,$feespaymentid,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_STUDENT_ID,'$TEXT_PAYMENT_DATE',$TEXT_FEES_PAID,$TEXT_FEES_FY_YEAR_CD,$userid,'$txtremarks' ";
        
		

// 		$data['$sql'] = $query;
		
		   
// 			$stmt=sqlsrv_query($mysqli, $query);
			
// 			if($stmt === false)
// 			{
				
// 				$data['success'] = false;
// 				$data['query'] = $query;
// 			}
// 			else
// 			{
// 				$data['query'] = $query;
// 				$data['success'] = true;
// 				if(!empty($feespaymentid))
// 				$data['message'] = 'Record successfully updated';
// 				else 
// 				$data['message'] = 'Record successfully inserted.';
// 				echo json_encode($data);exit;
// 			}
			
// 		}
// 		else
// 		{
// 			$data['success'] = false;
// 			$data['message'] = 'Object Type already exists.';
// 		}
// 		echo json_encode($data);exit;

//      }
//      catch(Exception $e)
//      {
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
//      }
//  }

function getRte($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";

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
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		// $TEXT_RTE_CD  = $_POST['TEXT_RTE_CD'] == 'undefined' ? 0 : $_POST['TEXT_RTE_CD'];
       		
						
						
		$query =      " SELECT 
						EXPENSE_ID
						,SCHOOL_ID
						,SCHOOL_NAME
						,FY_YEAR_CD
						,FY_YEAR
						,MONTH_CD
						,MONTH
						,CONVERT(VARCHAR, PAYMENT_DATE, 106) AS PAYMENT_DATE
						,EXPENSE_CD
						,EXPENSE_DESC
						,PAYMENT_MODE_CD
						,PAYMENT_MODE
						,AMOUNT
						,VOUCHER_NO
						,INSTRUMENT_NO
					    FROM SCHOOL_EXPENSES 
						WHERE  ISDELETED = 0 
						AND    PAYMENT_MODE_CD = 702 
						AND    SCHOOL_ID  = $TEXT_SCHOOL_ID 
						AND    FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD ";
									
		
		$query .= " ORDER BY MONTH_CD";
		//  throw new Exception($query);

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



// function getFeesDue($mysqli){
	

// 	try
// 	{
// 	$data = array();	
// 	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
//     $TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
// 	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
//     $TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
	
// 	$query = "SELECT FEES_ID, (FEES_DUE-FEES_PAID) FEES_DUE
// 	         FROM  STUDENT_FEES_MASTER 
// 			 where ISDELETED = 0
// 			 and   STUDENT_ID = $TEXT_STUDENT_ID
// 			 and   CLASS_CD = $TEXT_CLASS_CD 
// 			 and   FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
// 			 and   SCHOOL_ID = $TEXT_SCHOOL_ID ";

// 		$data['query'] = $query;

// 		$count = unique($query);
// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
	
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['FEES_ID'] = (int) $row['FEES_ID'];
				
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}else{
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }




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



// function getStudent($mysqli){
	

// 	try
// 	{
// 	$data = array();	
// 	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
//     $TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	
// 	$query = "SELECT STUDENT_ID, (STUDENT_FIRST_NAME+''+STUDENT_LAST_NAME) STUDENT_NAME 
// 	         FROM  STUDENT_FEES_MASTER 
// 			 where ISDELETED = 0
// 			 and   CLASS_CD = $TEXT_CLASS_CD 
// 			 and   SCHOOL_ID = $TEXT_SCHOOL_ID ";

// 		$data['query'] = $query;

// 		$count = unique($query);
// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
	
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['STUDENT_ID'] = (int) $row['STUDENT_ID'];
				
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}else{
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }


// function getClass($mysqli){
// 	try
// 	{
		
// 	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0 order by CODE_DETAIL_ID";

// 		$data = array();
// 		$count = unique($query);
// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
	
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 		}else{
// 			$data['success'] = false;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }


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


// function delete($mysqli){
// 	try{   
// 			global $userid;
// 			$data = array();     
//             $feespaymentid = ($_POST['feespaymentid'] == 'undefined' || $_POST['feespaymentid'] == '') ? 0 : $_POST['feespaymentid'];
// 			$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
// 			$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
//         	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
// 			$TEXT_FEES_PAID  = $_POST['TEXT_FEES_PAID'] == 'undefined' ? 0 : $_POST['TEXT_FEES_PAID'];
// 			$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
			
			
// 			if($feespaymentid == 0){
// 				throw new Exception('STUDENT_ID Error.');
// 			}

	
	
// 	$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_FEES_PAYMENT_SP] 3,$feespaymentid,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_STUDENT_ID,'',$TEXT_FEES_PAID,$TEXT_FEES_FY_YEAR_CD,$userid,'' ");
	
// 	if( $stmt === false ) 
// 			{
// 				die( print_r( sqlsrv_errors(), true));
// 				throw new Exception( $mysqli->sqlstate );
// 			}
// 			else
// 			{
// 				$data['success'] = true;
// 				$data['message'] = 'Record successfully deleted';
// 			}
// 		echo json_encode($data);exit;
		
		
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false . $query;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







