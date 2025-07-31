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
		case "addTempComponent": addTempComponent($conn); break;
		case "deleteTempComponent": deleteTempComponent($conn); break;
		case "getSalaryComponents": getSalaryComponents($conn); break;



		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

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


function addTempComponent($conn) {
    global $userid;
    $eid = (int) $_POST['EMPLOYEE_ID'];
    $mid = (int) $_POST['MONTH_ID'];
    $cid = (int) $_POST['COMPONENT_ID'];
    $amount = $_POST['FIXED_AMOUNT'];

    // Check in permanent structure
    $permCheck = "SELECT 1 FROM EMPLOYEE_SALARY_STRUCTURE WHERE EMPLOYEE_ID = $eid AND COMPONENT_ID = $cid AND ISDELETED = 0";
    $permRes = sqlsrv_query($conn, $permCheck);
    if (sqlsrv_has_rows($permRes)) {
        echo json_encode(['success' => false, 'message' => 'This component already exists in the permanent salary structure.']); exit;
    }

    // Reactivate if exists with ISDELETED = 1
    $reactivate = "UPDATE EMPLOYEE_SALARY_TEMP_STRUCTURE 
                   SET ISDELETED = 0, FIXED_AMOUNT = $amount 
                   WHERE EMPLOYEE_ID = $eid AND MONTH_ID = $mid AND COMPONENT_ID = $cid AND ISDELETED = 1";
    sqlsrv_query($conn, $reactivate);

    // Check if record already exists and is active
    $dupCheck = "SELECT 1 FROM EMPLOYEE_SALARY_TEMP_STRUCTURE 
                 WHERE EMPLOYEE_ID = $eid AND MONTH_ID = $mid AND COMPONENT_ID = $cid AND ISDELETED = 0";
    $dupRes = sqlsrv_query($conn, $dupCheck);
    if (sqlsrv_has_rows($dupRes)) {
        echo json_encode(['success' => true, 'message' => 'Component reactivated or already exists.']); exit;
    }

    // If not exists at all, insert new
    $insert = "INSERT INTO EMPLOYEE_SALARY_TEMP_STRUCTURE 
                (EMPLOYEE_ID, MONTH_ID, COMPONENT_ID, FIXED_AMOUNT, INSERTID) 
                VALUES ($eid, $mid, $cid, $amount, $userid)";
    $res = sqlsrv_query($conn, $insert);

    if ($res === false) {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . print_r(sqlsrv_errors(), true)]); exit;
    }

    // Recalculate salary
        $recalc = "EXEC PROCESS_EMPLOYEE_SALARY_SP $eid, $mid, NULL, $userid";
        sqlsrv_query($conn, $recalc);

    echo json_encode(['success' => true, 'message' => 'Component added successfully.']); exit;
}


function deleteTempComponent($conn) {
    global $userid;
    $eid = $_POST['EMPLOYEE_ID'];
    $mid = $_POST['MONTH_ID'];
    $cid = $_POST['COMPONENT_ID'];
    $query = "UPDATE EMPLOYEE_SALARY_TEMP_STRUCTURE SET ISDELETED = 1 WHERE EMPLOYEE_ID = $eid AND MONTH_ID = $mid AND COMPONENT_ID = $cid";
    sqlsrv_query($conn, $query);

    // Recalculate salary
    $recalc = "EXEC PROCESS_EMPLOYEE_SALARY_SP $eid, $mid, NULL, $userid";
    sqlsrv_query($conn, $recalc);

    echo json_encode(['success' => true, 'message' => 'Head removed']); exit;
}


function checkIfAlreadyPaid($mysqli) {
    $EMPLOYEE_ID = $_POST['EMPLOYEE_ID'];
    $MONTH_ID = $_POST['MONTH_ID'];

    $query = "SELECT 1 FROM EMPLOYEE_SALARY_PROCESS WHERE EMPLOYEE_ID = $EMPLOYEE_ID AND MONTH_ID = $MONTH_ID AND IS_PAID = 1";
    $result = sqlsrv_query($mysqli, $query);

    $response = ['alreadyPaid' => false];
    if (sqlsrv_has_rows($result)) {
        $response['alreadyPaid'] = true;
    }
    echo json_encode($response);
    exit;
}

function checkIfAlreadyPaidForSchool($mysqli) {
    $SCHOOL_ID = $_POST['SCHOOL_ID'];
    $MONTH_ID = $_POST['MONTH_ID'];

    $query = "SELECT 1 FROM EMPLOYEE_SALARY_PROCESS WHERE EMPLOYEE_ID IN (
                SELECT EMPLOYEE_ID FROM EMPLOYEE_MASTER WHERE SCHOOL_ID = $SCHOOL_ID AND ISDELETED = 0
              ) AND MONTH_ID = $MONTH_ID AND IS_PAID = 1";
    $result = sqlsrv_query($mysqli, $query);

    $response = ['anyPaid' => false];
    if (sqlsrv_has_rows($result)) {
        $response['anyPaid'] = true;
    }
    echo json_encode($response);
    exit;
}



 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $salaryid  = ($_POST['salaryid'] == 'undefined' || $_POST['salaryid'] == '') ? 0 : $_POST['salaryid'];
	    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_EMPLOYEE_ID = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
		$TEXT_MONTH_ID  = $_POST['TEXT_MONTH_ID'] == 'undefined' ? 0 : $_POST['TEXT_MONTH_ID'];
		$TEXT_PAYMENT_DATE  = $_POST['TEXT_PAYMENT_DATE'] == 'undefined' ? '' : $_POST['TEXT_PAYMENT_DATE'];
		
   
		$query="EXEC [PROCESS_EMPLOYEE_SALARY_SP] 
												 $TEXT_EMPLOYEE_ID
												,$TEXT_MONTH_ID
												,'$TEXT_PAYMENT_DATE' 
												,$userid ";
	
		
			   
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
				{
					$errors = sqlsrv_errors();
					$data['success'] = false;
					$data['message'] = "SQL Error: " . print_r($errors, true);
					echo json_encode($data);
					exit;
				}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($salaryid))
				$data['message'] = 'Record successfully updated';
				else 
				$data['message'] = 'Salary Processed for this month.';
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


function processAll($mysqli) {
  try {
    global $userid;
    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] ?? 0;
    $TEXT_MONTH_ID   = $_POST['TEXT_MONTH_ID'] ?? 0;
    $TEXT_PAYMENT_DATE = $_POST['TEXT_PAYMENT_DATE'] ?? '';

    if (!$TEXT_SCHOOL_ID || !$TEXT_MONTH_ID || !$TEXT_PAYMENT_DATE) {
      throw new Exception("Invalid input parameters.");
    }

    $query = "EXEC PROCESS_SALARY_FOR_SCHOOL_SP 
              $TEXT_SCHOOL_ID, 
              $TEXT_MONTH_ID, 
              '$TEXT_PAYMENT_DATE', 
              $userid";

    $stmt = sqlsrv_query($mysqli, $query);

    if ($stmt === false) {
      $errors = sqlsrv_errors();
      throw new Exception("SQL Error: " . print_r($errors, true));
    }

    $data['success'] = true;
    $data['message'] = "Salary processed for all employees of the selected school.";
    echo json_encode($data);
    exit;

  } catch (Exception $e) {
    $data = array();
    $data['success'] = false;
    $data['message'] = $e->getMessage();
    echo json_encode($data);
    exit;
  }
}



function getQuery($mysqli) {
    try {
        $data = array();

        $TEXT_SCHOOL_ID    = isset($_POST['TEXT_SCHOOL_ID']) && $_POST['TEXT_SCHOOL_ID'] != 'undefined' ? intval($_POST['TEXT_SCHOOL_ID']) : 0;
        $TEXT_EMPLOYEE_ID  = isset($_POST['TEXT_EMPLOYEE_ID']) && $_POST['TEXT_EMPLOYEE_ID'] != 'undefined' ? intval($_POST['TEXT_EMPLOYEE_ID']) : 0;
        $TEXT_MONTH_ID     = isset($_POST['TEXT_MONTH_ID']) && $_POST['TEXT_MONTH_ID'] != 'undefined' ? intval($_POST['TEXT_MONTH_ID']) : 0;

        if (!$TEXT_SCHOOL_ID || !$TEXT_EMPLOYEE_ID || !$TEXT_MONTH_ID) {
            throw new Exception("School, Employee and Month must be selected.");
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
				throw new Exception('SALARY_ID Error.');
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







