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
		case "getMonth":getMonth($conn);break;
		case "delete":delete($conn);break;
		case "updatePaidStatus": updatePaidStatus($conn); break;
		case "markAllPaid": markAllPaid($conn); break;
		case "togglePaymentStatus": togglePaymentStatus($conn); break;
		case "markAllUnpaid": markAllUnpaid($conn); break;
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

function updatePaidStatus($mysqli) {
    $id = $_POST['SALARY_PROCESS_ID'];
    $is_paid = $_POST['IS_PAID'];
    $paid_by = $_POST['PAID_BY'];
    $date = $_POST['PAYMENT_CONFIRMED_DATE'];

    $query = "UPDATE EMPLOYEE_SALARY_PROCESS SET IS_PAID = $is_paid, PAID_BY = $paid_by, PAYMENT_CONFIRMED_DATE = '$date' WHERE SALARY_PROCESS_ID = $id";
    $result = sqlsrv_query($mysqli, $query);

    echo json_encode(["success" => $result ? true : false]);
    exit;
}

function markAllPaid($mysqli) {
    $school = $_POST['TEXT_SCHOOL_ID'];
    $month = $_POST['TEXT_MONTH_ID'];
    $paid_by = $_POST['PAID_BY'];
    $date = $_POST['PAYMENT_CONFIRMED_DATE'];

    $query = "UPDATE EMPLOYEE_SALARY_PROCESS 
              SET IS_PAID = 1, 
                  PAID_BY = $paid_by, 
                  PAYMENT_CONFIRMED_DATE = '$date' 
              WHERE IS_PAID = 0 
              AND MONTH_ID = $month 
              AND EMPLOYEE_ID IN (
                  SELECT EMPLOYEE_ID FROM EMPLOYEE_MASTER 
                  WHERE SCHOOL_ID = $school AND ISDELETED = 0
              )";

    $result = sqlsrv_query($mysqli, $query);
    echo json_encode(["success" => $result ? true : false]);
    exit;
}


function markAllUnpaid($mysqli) {
    $school = $_POST['TEXT_SCHOOL_ID'];
    $month = $_POST['TEXT_MONTH_ID'];

    $query = "UPDATE EMPLOYEE_SALARY_PROCESS 
              SET IS_PAID = 0, 
                  PAID_BY = NULL, 
                  PAYMENT_CONFIRMED_DATE = NULL 
              WHERE IS_PAID = 1 
              AND MONTH_ID = $month 
              AND EMPLOYEE_ID IN (
                  SELECT EMPLOYEE_ID FROM EMPLOYEE_MASTER 
                  WHERE SCHOOL_ID = $school AND ISDELETED = 0
              )";

    $result = sqlsrv_query($mysqli, $query);
    echo json_encode(["success" => $result ? true : false]);
    exit;
}



function togglePaymentStatus($mysqli) {
    global $userid;
    $salaryid = isset($_POST['SALARY_PROCESS_ID']) ? (int)$_POST['SALARY_PROCESS_ID'] : 0;
    $isPaid = isset($_POST['IS_PAID']) ? (int)$_POST['IS_PAID'] : 0;

    if ($salaryid === 0) {
        echo json_encode(['success' => false, 'message' => 'Missing SALARY_PROCESS_ID']);
        exit;
    }

    $query = "UPDATE EMPLOYEE_SALARY_PROCESS SET
                IS_PAID = $isPaid,
                PAID_BY = " . ($isPaid ? $userid : "NULL") . ",
                PAYMENT_CONFIRMED_DATE = " . ($isPaid ? "GETDATE()" : "NULL") . "
              WHERE SALARY_PROCESS_ID = $salaryid";

    $stmt = sqlsrv_query($mysqli, $query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => print_r(sqlsrv_errors(), true)]);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

 function getQuery($mysqli){
		try
	{
	$data = array();
	
      $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	  $TEXT_MONTH_ID  = $_POST['TEXT_MONTH_ID'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_ID'];
      $TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];

       $query =     "SELECT 
							
							A.SALARY_PROCESS_ID,
							A.EMPLOYEE_ID,
							A.MONTH_ID,
							A.GROSS_EARNINGS,
							A.TOTAL_DEDUCTIONS,
							A.NET_SALARY,
							A.PAYMENT_DATE,
							B.EMPLOYEE_NAME,
							A.IS_PROCESSED,
							CASE 
								WHEN A.IS_PAID = 1 THEN 'Yes'
								ELSE 'No'
							END AS IS_PAID_STATUS
						FROM EMPLOYEE_SALARY_PROCESS A
						JOIN EMPLOYEE_MASTER B ON A.EMPLOYEE_ID = B.EMPLOYEE_ID
						WHERE A.IS_PROCESSED = 1
						AND A.ISDELETED = 0
						AND B.ISDELETED = 0
						AND A.MONTH_ID = $TEXT_MONTH_ID
						AND B.SCHOOL_ID = $TEXT_SCHOOL_ID
						AND A.FY_YEAR_CD = $TEXT_FY_YEAR_CD
						ORDER BY A.EMPLOYEE_ID
						";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['SALARY_PROCESS_ID'] = (int) $row['SALARY_PROCESS_ID'];
			//    $row['IS_PAID'] = (int) $row['IS_PAID']; // ✅ Important
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
	 $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];

	$query = "SELECT EMPLOYEE_ID,EMPLOYEE_NAME FROM EMPLOYEE_MASTER 
	          where  isdeleted=0  AND SCHOOL_ID = $TEXT_SCHOOL_ID";
	

		$data = array();
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

function getMonth($mysqli){
	try
	{
		
	$query = "SELECT MONTH_ID,MONTH FROM MONTH ORDER BY MONTH_ID ";
	

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
            $salaryid = ($_POST['salaryid'] == 'undefined' || $_POST['salaryid'] == '') ? 0 : $_POST['salaryid'];  

					
			if($salaryid == 0){
				throw new Exception('SALARY_PROCESS_ID Error.');
			}
			
					$stmt=sqlsrv_query($mysqli, "EXEC [EMPLOYEE_SALARY_STRUCTURE_SP]3,$salaryid,0,0,0,0,$userid ") ;
				
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







