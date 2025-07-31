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
		case "getEmployeeName":getEmployeeName($conn);break;
		case "getMonth":getMonth($conn);break;
		case "delete":delete($conn);break;
		case "processAll": processAll($conn); break;
		case "checkIfAlreadyPaid": checkIfAlreadyPaid($conn); break;
        case "checkIfAlreadyPaidForSchool": checkIfAlreadyPaidForSchool($conn); break;
		case "getSalaryComponents": getSalaryComponents($conn); break;
		case "getTempComponents": getTempComponents($conn); break;
	
		case "getSalaryComponents": getSalaryComponents($conn); break;
        case "getFinancialYear": getFinancialYear($conn); break;
        case "getPayslipHeader": getPayslipHeader($conn); break;
        case "checkSalaryProcessed": checkSalaryProcessed($conn); break;


		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

function checkSalaryProcessed($conn) {
    $empId    = isset($_POST['EMPLOYEE_ID']) ? intval($_POST['EMPLOYEE_ID']) : 0;
    $schoolId = isset($_POST['SCHOOL_ID']) ? intval($_POST['SCHOOL_ID']) : 0;
    $monthId  = isset($_POST['MONTH_ID']) ? intval($_POST['MONTH_ID']) : 0;
    $yearCd   = isset($_POST['YEAR_CD']) ? intval($_POST['YEAR_CD']) : 0;

    // DEBUG OUTPUT
    error_log("checkSalaryProcessed INPUT => EMP_ID: $empId, SCHOOL_ID: $schoolId, MONTH_ID: $monthId, YEAR_CD: $yearCd");

    if (!$empId || !$schoolId || !$monthId || !$yearCd) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing input values'
        ]);
        return;
    }

    $query = "SELECT 1 FROM EMPLOYEE_SALARY_PROCESS 
              WHERE IS_PROCESSED = 1 
              AND EMPLOYEE_ID = $empId 
              AND SCHOOL_ID = $schoolId 
              AND MONTH_ID = $monthId 
              AND FY_YEAR_CD = $yearCd 
              AND ISDELETED = 0";

    error_log("Executing query: $query");

    $result = sqlsrv_query($conn, $query);

    if ($result === false) {
        error_log("SQL ERROR: " . print_r(sqlsrv_errors(), true));
        echo json_encode(['success' => false, 'message' => 'Query failed']);
        return;
    }

    $isProcessed = sqlsrv_fetch_array($result) ? true : false;

    echo json_encode([
        'success' => $isProcessed,
        'message' => $isProcessed ? 'Processed' : 'Not processed'
    ]);
}


function getPayslipHeader($conn) {
    $eid = $_POST['EMPLOYEE_ID'];
    $school_id = $_POST['SCHOOL_ID'];

    $query = "
        SELECT 
            E.EMPLOYEE_ID,
            E.EMPLOYEE_NAME,
            E.FATHER_HUSBAND_NAME,
            E.EMPLOYEE_CODE,
            E.DESIGNATION,
            E.DEPARTMENT,
            CONVERT(VARCHAR, E.DATE_OF_JOINING, 103) AS DATE_OF_JOINING,
            S.SCHOOL_NAME
        FROM EMPLOYEE_MASTER E
        JOIN SCHOOL S ON E.SCHOOL_ID = S.SCHOOL_ID
        WHERE E.EMPLOYEE_ID = $eid AND E.SCHOOL_ID = $school_id AND E.ISDELETED = 0 AND S.ISDELETED = 0
    ";

    $result = sqlsrv_query($conn, $query);
    $data = [];

    if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $data['data'] = $row;
        $data['success'] = true;
    } else {
        $data['success'] = false;
        $data['message'] = 'Employee not found.';
    }

    echo json_encode($data); exit;
}



function getSalaryComponents($mysqli) {
  try {
    $query = "SELECT COMPONENT_ID, COMPONENT_NAME FROM SALARY_COMPONENT_MASTER WHERE ISDELETED = 0 ORDER BY COMPONENT_TYPE,COMPONENT_NAME";
    $result = sqlsrv_query($mysqli, $query);
    $data = ['data' => [], 'success' => false];

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
      $row['COMPONENT_ID'] = (int)$row['COMPONENT_ID'];
      $data['data'][] = $row;
    }

    $data['success'] = true;
    echo json_encode($data); exit;
  } catch (Exception $e) {
    echo json_encode([
      'success' => false,
      'message' => $e->getMessage()
    ]); exit;
  }
}



function getTempComponents($conn) {
    $eid = $_POST['EMPLOYEE_ID'];
    $mid = $_POST['MONTH_ID'];
    $query = "SELECT T.COMPONENT_ID, S.COMPONENT_NAME, T.FIXED_AMOUNT ,S.COMPONENT_TYPE
              FROM EMPLOYEE_SALARY_TEMP_STRUCTURE T 
              JOIN SALARY_COMPONENT_MASTER S ON T.COMPONENT_ID = S.COMPONENT_ID 
              WHERE T.EMPLOYEE_ID = $eid AND T.MONTH_ID = $mid AND T.ISDELETED = 0";
    $result = sqlsrv_query($conn, $query);
    $data = [];
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $data['data'][] = $row;
    }
    $data['success'] = true;
    echo json_encode($data); exit;
}


function salary_is_processed($conn, $empId, $schoolId, $monthId, $yearCd) {
    $query = "SELECT 1 FROM EMPLOYEE_SALARY_PROCESS 
              WHERE IS_PROCESSED = 1 
              AND EMPLOYEE_ID    = $empId 
              AND MONTH_ID       = $monthId 
              AND FY_YEAR_CD     = $yearCd 
              AND ISDELETED      = 0";

    $result = sqlsrv_query($conn, $query);
    return $result && sqlsrv_fetch_array($result) ? true : false;
}



function getQuery($mysqli) {
    try {
        $data = array();

        $TEXT_SCHOOL_ID    = isset($_POST['TEXT_SCHOOL_ID']) && $_POST['TEXT_SCHOOL_ID'] != 'undefined' ? intval($_POST['TEXT_SCHOOL_ID']) : 0;
        $TEXT_EMPLOYEE_ID  = isset($_POST['TEXT_EMPLOYEE_ID']) && $_POST['TEXT_EMPLOYEE_ID'] != 'undefined' ? intval($_POST['TEXT_EMPLOYEE_ID']) : 0;
        $TEXT_MONTH_ID     = isset($_POST['TEXT_MONTH_ID']) && $_POST['TEXT_MONTH_ID'] != 'undefined' ? intval($_POST['TEXT_MONTH_ID']) : 0;
        $TEXT_YEAR_CD      = isset($_POST['TEXT_YEAR_CD']) && $_POST['TEXT_YEAR_CD'] != 'undefined' ? intval($_POST['TEXT_YEAR_CD']) : 0;

        if (!$TEXT_SCHOOL_ID || !$TEXT_EMPLOYEE_ID || !$TEXT_MONTH_ID || !$TEXT_YEAR_CD) {
            throw new Exception("School, Employee, Month, and Year must be selected.");
        }

        // ✅ Salary Processed Check
        if (!salary_is_processed($mysqli, $TEXT_EMPLOYEE_ID, $TEXT_SCHOOL_ID, $TEXT_MONTH_ID, $TEXT_YEAR_CD)) {
            echo json_encode([
                'success' => false,
                'message' => 'Salary is not processed for this month! Please process the Salary first.'
            ]);
            return;
        }

        $query = "
        WITH SalaryData AS (
            -- Permanent salary components
            SELECT
                A.SALARY_ID,
                A.SCHOOL_ID,
                A.EMPLOYEE_ID,
                A.COMPONENT_ID,
                A.FIXED_AMOUNT,
                B.COMPONENT_NAME,
                B.COMPONENT_TYPE,
                B.COMPONENT_TYPE_CD,
                0 AS IS_TEMP
            FROM EMPLOYEE_SALARY_STRUCTURE A
            JOIN SALARY_COMPONENT_MASTER B ON A.COMPONENT_ID = B.COMPONENT_ID
            WHERE A.ISDELETED = 0 AND B.ISDELETED = 0
              AND A.SCHOOL_ID = $TEXT_SCHOOL_ID
              AND A.EMPLOYEE_ID = $TEXT_EMPLOYEE_ID

            UNION ALL

            -- Temporary salary components
            SELECT
                NULL AS SALARY_ID,
                EM.SCHOOL_ID,
                T.EMPLOYEE_ID,
                T.COMPONENT_ID,
                T.FIXED_AMOUNT,
                B.COMPONENT_NAME,
                B.COMPONENT_TYPE,
                B.COMPONENT_TYPE_CD,
                1 AS IS_TEMP
            FROM EMPLOYEE_SALARY_TEMP_STRUCTURE T
            JOIN SALARY_COMPONENT_MASTER B ON T.COMPONENT_ID = B.COMPONENT_ID
            JOIN EMPLOYEE_MASTER EM ON T.EMPLOYEE_ID = EM.EMPLOYEE_ID
            WHERE T.ISDELETED = 0 
              AND T.EMPLOYEE_ID = $TEXT_EMPLOYEE_ID 
              AND T.MONTH_ID = $TEXT_MONTH_ID
              AND EM.SCHOOL_ID = $TEXT_SCHOOL_ID
        )
        SELECT * FROM SalaryData

        UNION ALL

        -- Total row
        SELECT
            NULL AS SALARY_ID,
            NULL AS SCHOOL_ID,
            NULL AS EMPLOYEE_ID,
            NULL AS COMPONENT_ID,
            SUM(CASE 
                WHEN COMPONENT_TYPE_CD = 832 THEN FIXED_AMOUNT
                WHEN COMPONENT_TYPE_CD = 833 THEN -FIXED_AMOUNT
                ELSE 0
            END) AS FIXED_AMOUNT,
            'Total Salary' AS COMPONENT_NAME,
            NULL AS COMPONENT_TYPE,
            NULL AS COMPONENT_TYPE_CD,
            NULL AS IS_TEMP
        FROM SalaryData
        HAVING COUNT(*) > 0
        ";

        $result = sqlsrv_query($mysqli, $query);

        if ($result === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $row['SALARY_ID'] = isset($row['SALARY_ID']) ? (int) $row['SALARY_ID'] : null;
            $data['data'][] = $row;
        }

        $data['success'] = true;
        echo json_encode($data);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
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


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







