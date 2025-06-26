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
		case "getTeacher": getTeacher($conn);break;
		case "getMonth": getMonth($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		// case "getPaymentMode": getPaymentMode($conn);break;
		// case "getGrossSalary": getGrossSalary($conn);break;
		// case "delete":delete($conn);break;
		
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
    
//         $feesid  = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];
// 		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
// 		$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];
		
// 		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
//         // $TEXT_MONTH_CD  = $_POST['TEXT_MONTH_CD'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_CD'];
// 		$TEXT_SALARY_DUE  = $_POST['TEXT_SALARY_DUE'] == 'undefined' ? 0 : $_POST['TEXT_SALARY_DUE'];
// 		$TEXT_SALARY_DEDUCTED  = $_POST['TEXT_SALARY_DEDUCTED'] == 'undefined' ? 0 : $_POST['TEXT_SALARY_DEDUCTED'];
// 		$TEXT_SALARY_PAID  = $_POST['TEXT_SALARY_PAID'] == 'undefined' ? 0 : $_POST['TEXT_SALARY_PAID'];
// 		$TEXT_DEDUCTION_REASON  = $_POST['TEXT_DEDUCTION_REASON'] == 'undefined' ? '' : $_POST['TEXT_DEDUCTION_REASON'];
// 	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
// 		$TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
// 	    $TEXT_PAYMENT_MODE_CD  = $_POST['TEXT_PAYMENT_MODE_CD'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_MODE_CD'];
	    
		
// 		$actionid = $feesid == 0 ? 1 : 2;

		
// 				$sql = "SELECT * FROM TEACHER_PAYROLL
// 		        WHERE TEACHER_SALARY_ID!=$feesid
// 				AND   SCHOOL_ID 		  =  $TEXT_SCHOOL_ID
// 				AND   TEACHER_ID 		  =  $TEXT_TEACHER_ID
// 				AND   FY_YEAR_CD          =  $TEXT_FY_YEAR_CD
// 				AND   PAYMENT_DATE        =  '$TEXT_PAYMENT_DATE'
// 				AND   ISDELETED = 0 ";	  
       
// 		// throw new Exception($sql);
// 	   $row_count = unique($sql);
	   
	
	   
// 	   if($row_count == 0)
// 	   {
	   
// 		$query="EXEC [TEACHER_PAYROLL_SP] $actionid,$feesid,$TEXT_SCHOOL_ID,$TEXT_TEACHER_ID,$TEXT_FY_YEAR_CD,$TEXT_SALARY_DUE,'$TEXT_SALARY_DEDUCTED',$TEXT_SALARY_PAID,'$TEXT_DEDUCTION_REASON',$userid,'$txtremarks','$TEXT_PAYMENT_DATE',$TEXT_PAYMENT_MODE_CD ";
	
		
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
// 				if(!empty($feesid))
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


 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID   = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
       	$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];
        $TEXT_MONTH_CD    = $_POST['TEXT_MONTH_CD'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_CD'];
		       
	   $query = "SELECT  
					A.TEACHER_SALARY_ID,
					A.TEACHER_ID,
					B.SCHOOL_ID,
					B.SCHOOL_NAME,
					B.TEACHER_NAME,
					A.FY_YEAR_CD,
					A.FY_YEAR,
					A.MONTH_CD,
					A.MONTH,
					CONVERT(VARCHAR, A.PAYMENT_DATE, 106) AS PAYMENT_DATE,
					A.PAYMENT_MODE_CD,
					A.PAYMENT_MODE,
					A.SALARY_DUE,
					A.SALARY_DEDUCTED,
					A.SALARY_PAID,
					A.DEDUCTION_REASON,
					A.REMARKS,
					NULL AS TOTAL_SALARY_PAID
				FROM 
					TEACHER_PAYROLL A
				JOIN 
					TEACHER B ON A.TEACHER_ID = B.TEACHER_ID
				WHERE 
					A.ISDELETED = 0
					AND B.ISDELETED = 0
					AND A.SCHOOL_ID = $TEXT_SCHOOL_ID
					AND A.FY_YEAR_CD = $TEXT_FY_YEAR_CD";

				if ($TEXT_TEACHER_ID != '') {
					$query .= " AND A.TEACHER_ID = $TEXT_TEACHER_ID"; 
				}

				if ($TEXT_MONTH_CD != '') {
					$query .= " AND A.MONTH_CD = $TEXT_MONTH_CD"; 
				}

				$query .= " UNION ALL

				SELECT
					NULL AS TEACHER_SALARY_ID,
					NULL AS TEACHER_ID,
					A.SCHOOL_ID,
					B.SCHOOL_NAME,
					NULL AS TEACHER_NAME,
					A.FY_YEAR_CD,
					A.FY_YEAR,
					NULL AS MONTH_CD,
					NULL AS MONTH,
					NULL AS PAYMENT_DATE,
					NULL AS PAYMENT_MODE_CD,
					NULL AS PAYMENT_MODE,
					NULL AS SALARY_DUE,
					NULL AS SALARY_DEDUCTED,
					SUM(A.SALARY_PAID) AS SALARY_PAID,
					NULL AS DEDUCTION_REASON,
					NULL AS REMARKS,
					SUM(A.SALARY_PAID) AS TOTAL_SALARY_PAID
				FROM 
					TEACHER_PAYROLL A
				JOIN 
					TEACHER B ON A.TEACHER_ID = B.TEACHER_ID
				WHERE 
					A.ISDELETED = 0
					AND B.ISDELETED = 0
					AND A.SCHOOL_ID = $TEXT_SCHOOL_ID
					AND A.FY_YEAR_CD = $TEXT_FY_YEAR_CD";

				if ($TEXT_TEACHER_ID != '') {
					$query .= " AND A.TEACHER_ID = $TEXT_TEACHER_ID"; 
				}

				if ($TEXT_MONTH_CD != '') {
					$query .= " AND A.MONTH_CD = $TEXT_MONTH_CD"; 
				}

				$query .= " GROUP BY 
					A.SCHOOL_ID, B.SCHOOL_NAME, A.FY_YEAR_CD, A.FY_YEAR";

	
	
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TEACHER_SALARY_ID'] = (int) $row['TEACHER_SALARY_ID'];
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


// function getGrossSalary($mysqli){
// 	try
// 	{
// 	    $data = array();
// 		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
// 		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
// 		$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];

// 	$query = "SELECT TEACHER_PAYROLL_ID, GROSS_SALARY FROM TEACHER_SALARY_MASTER 
// 			  WHERE SCHOOL_ID = $TEXT_SCHOOL_ID
// 			  AND FY_YEAR_CD = $TEXT_FY_YEAR_CD 
// 			  AND TEACHER_ID = $TEXT_TEACHER_ID
// 			  AND ISDELETED=0 ";

// 		$data = array();
// 		$count = unique($query);
// 		if($count > 0){
// 			$result = sqlsrv_query($mysqli, $query);
	
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['TEACHER_PAYROLL_ID'] = (int) $row['TEACHER_PAYROLL_ID'];
				
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


// function getPaymentMode($mysqli){
// 	try
// 	{
		
// 	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=50 and isdeleted=0";

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



function getMonth($mysqli){
	try
	{
		
	// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=47 and isdeleted=0";
       $query = "SELECT DISTINCT MONTH(PAYMENT_DATE) MONTH_CD, FORMAT(PAYMENT_DATE, 'MMMM') MONTH FROM TEACHER_PAYROLL
	   where ISDELETED=0";

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




function getTeacher($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];

	$query = "SELECT TEACHER_ID,TEACHER_NAME FROM TEACHER 
	          where  isdeleted=0 
			  AND SCHOOL_ID = $TEXT_SCHOOL_ID ORDER BY TEACHER_NAME"; 

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TEACHER_ID'] = (int) $row['TEACHER_ID'];
				
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


// function delete($mysqli){
// 	try{   
// 			global $userid;
// 			$data = array();     
//             $feesid = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];  

					
// 			if($feesid == 0){
// 				throw new Exception('TEACHER_SALARY_ID Error.');
// 			}
			
	
// 				$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_PAYROLL_SP]	3,$feesid,NULL,NULL,NULL,NULL,NULL,NULL,NULL,$userid,'','','' ") ;
				
// 				if( $stmt === false )       
// 				{
// 					die( print_r( sqlsrv_errors(), true));
// 					throw new Exception( $mysqli->sqlstate );
// 				}
// 				else
// 				{
// 					$data['success'] = true;
// 					$data['message'] = 'Record successfully deleted';
// 				}
		    
// 			    echo json_encode($data);exit;
		
		
	
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







