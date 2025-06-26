<?php
session_start();
require_once '../../code/connection.php';

date_default_timezone_set('Asia/Kolkata'); // Ensure IST time zone

if (!empty($_SESSION['MEP_USERID'])) {
    $userid = $_SESSION['MEP_USERID'];
} else {
    $userid = 0;
}

if (isset($_POST['type']) && !empty($_POST['type'])) {
    $type = $_POST['type'];
    switch ($type) {
        case "save": save($conn); break;
        case "getQuery": getQuery($conn); break;
        case "getschoolname": getschoolname($conn); break;
        case "getClass": getClass($conn); break;
        case "getTeacher": getTeacher($conn);break;
        default: invalidRequest();
    }
} else {
    invalidRequest();
}

function save($mysqli) {
    try {
        $data = array('success' => false, 'data' => []);
        global $userid;
        
        $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : (int)$_POST['TEXT_SCHOOL_ID'];
        $TEXT_CLASS_CD = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : (int)$_POST['TEXT_CLASS_CD'];
        $TEXT_ATTENDANCE_DATE = $_POST['TEXT_ATTENDANCE_DATE'] == 'undefined' ? null : $_POST['TEXT_ATTENDANCE_DATE'];
        $TEXT_TEACHER_ID = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? null : $_POST['TEXT_TEACHER_ID'];


        // Validate date format
        if (!$TEXT_ATTENDANCE_DATE || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $TEXT_ATTENDANCE_DATE)) {
            $data['message'] = 'Invalid or missing attendance date. Use YYYY-MM-DD format.';
            echo json_encode($data);
            exit;
        }

        // Validate attendance date is not greater than today
        $today = date('Y-m-d');
        if ($TEXT_ATTENDANCE_DATE > $today) {
            throw new Exception('Attendance Date Cannot be greater than Today\'s Date');
        }
        
        $attendanceData = json_decode($_POST['attendanceData'], true);

        // Validate input
        if (empty($TEXT_SCHOOL_ID) || empty($TEXT_CLASS_CD) || empty($TEXT_ATTENDANCE_DATE)) {
            $data['message'] = 'Missing required fields: School, Class, or Attendance Date.';
            echo json_encode($data);
            exit;
        }

        if (empty($attendanceData) || !is_array($attendanceData)) {
            $data['message'] = 'No valid student attendance data provided.';
            echo json_encode($data);
            exit;
        }

        $success = true;
        foreach ($attendanceData as $student) {
            $student_id = isset($student['STUDENT_ID']) ? (int)$student['STUDENT_ID'] : 0;
            $attendance_status = isset($student['ATTENDANCE_STATUS']) && $student['ATTENDANCE_STATUS'] ? 1 : 2; // 1 = Present, 2 = Absent

            if ($student_id == 0) {
                $success = false;
                $data['message'] = 'Invalid STUDENT_ID for one or more records.';
                break;
            }

            // Use correct parameter name @STATUS
            $query = "EXEC [dbo].[ATTENDANCE_SP] @ACTIONID = 1, @ATTENDANCE_ID = 0, @SCHOOL_ID = ?, @CLASS_CD = ?, @STUDENT_ID = ?, @ATTENDANCE_DATE = ?, @STATUS = ?, @USERID = ? ,@TEACHER_ID=?";
            $params = [
                $TEXT_SCHOOL_ID,
                $TEXT_CLASS_CD,
                $student_id,
                $TEXT_ATTENDANCE_DATE,
                $attendance_status,
                $userid,
                $TEXT_TEACHER_ID
            ];

            $stmt = sqlsrv_query($mysqli, $query, $params);
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                $errorMessage = 'Database error: ' . print_r($errors, true);
                foreach ($errors as $error) {
                    if ($error['SQLSTATE'] == '42000' && strpos($error['message'], 'Attendance already recorded') !== false) {
                        $errorMessage = "Attendance already recorded for student ID $student_id on this date.";
                    }
                }
                $success = false;
                $data['query'] = $query;
                $data['params'] = $params;
                $data['message'] = $errorMessage;
                break;
            }
        }

        if ($success) {
            $data['success'] = true;
            $data['message'] = 'Attendance successfully recorded.';
        } else {
            $data['message'] = isset($data['message']) ? $data['message'] : 'Error recording attendance.';
        }
        
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array('success' => false, 'data' => [], 'message' => 'Exception: ' . $e->getMessage());
        echo json_encode($data);
        exit;
    }
}

function getQuery($mysqli) {
    try {
        $data = array('success' => false, 'data' => []);
        
        $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
        $TEXT_CLASS_CD = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_ATTENDANCE_DATE = $_POST['TEXT_ATTENDANCE_DATE'] == 'undefined' || empty($_POST['TEXT_ATTENDANCE_DATE']) 
            ? date('Y-m-d') // Default to today
            : $_POST['TEXT_ATTENDANCE_DATE'];

        // Log input for debugging
        error_log("getQuery input: SCHOOL_ID=$TEXT_SCHOOL_ID, CLASS_CD=$TEXT_CLASS_CD, ATTENDANCE_DATE=$TEXT_ATTENDANCE_DATE");

        if ($TEXT_SCHOOL_ID == 0 || $TEXT_CLASS_CD == 0) {
            $data['message'] = 'Invalid school or class selection.';
            echo json_encode($data);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $TEXT_ATTENDANCE_DATE)) {
            $data['message'] = 'Invalid date format. Using today\'s date (' . date('Y-m-d') . '). Received: ' . ($TEXT_ATTENDANCE_DATE ?? 'null');
            $TEXT_ATTENDANCE_DATE = date('Y-m-d'); // Fallback to today
        }

        $query = "SELECT 
                    A.STUDENT_CLASS_ID,
                    A.STUDENT_ID,
                    A.CLASS_CD,
                    A.SCHOOL_ID,
                    (B.STUDENT_FIRST_NAME + ' ' + B.STUDENT_LAST_NAME) AS STUDENT_NAME,
                    ISNULL((SELECT ATT.STATUS
                            FROM ATTENDANCE ATT 
                            WHERE ATT.STUDENT_ID = A.STUDENT_ID 
                            AND ATT.CLASS_CD = A.CLASS_CD 
                            AND ATT.SCHOOL_ID = A.SCHOOL_ID 
                            AND ATT.ATTENDANCE_DATE = ?
                            AND ATT.ISDELETED = 0), 1) AS ATTENDANCE_STATUS
                  FROM STUDENT_CLASSES A
                  INNER JOIN STUDENT B ON A.STUDENT_ID = B.STUDENT_ID 
                                      AND A.CLASS_CD = B.CLASS_CD 
                                      AND A.SCHOOL_ID = B.SCHOOL_ID
                  WHERE A.ISDELETED = 0
                  AND B.ISDELETED = 0
                  AND A.SCHOOL_ID = ?
                  AND A.CLASS_CD = ?";

        $params = [$TEXT_ATTENDANCE_DATE, $TEXT_SCHOOL_ID, $TEXT_CLASS_CD];

        $result = sqlsrv_query($mysqli, $query, $params);

        if ($result === false) {
            $data['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
            $data['query'] = $query;
            $data['params'] = $params;
            echo json_encode($data);
            exit;
        }

        $data['data'] = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $row['STUDENT_CLASS_ID'] = (int)$row['STUDENT_CLASS_ID'];
            $row['ATTENDANCE_STATUS'] = (int)$row['ATTENDANCE_STATUS']; // Always an integer (1 or 2)
            $data['data'][] = $row;
        }
        $data['success'] = true;
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array('success' => false, 'data' => [], 'message' => 'Exception: ' . $e->getMessage());
        echo json_encode($data);
        exit;
    }
}

function getClass($mysqli) {
    try {
        $data = array('success' => false, 'data' => []);
        $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];    
        
        if ($TEXT_SCHOOL_ID == 0) {
            $data['message'] = 'Invalid school selection.';
            echo json_encode($data);
            exit;
        }

        $query = "SELECT CLASS_CD, CLASS FROM SCHOOL_CLASSES WHERE SCHOOL_ID = ? AND ISDELETED = 0 ORDER BY SCHOOL_CLASS_ID";
        
        $result = sqlsrv_query($mysqli, $query, [$TEXT_SCHOOL_ID]);
        if ($result === false) {
            $data['message'] = 'SELECT failed: ' . print_r(sqlsrv_errors(), true);
            echo json_encode($data);
            exit;
        }

        $data['data'] = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $row['CLASS_CD'] = (int)$row['CLASS_CD'];
            $data['data'][] = $row;
        }
        $data['success'] = true;
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array('success' => false, 'data' => [], 'message' => 'Exception: ' . $e->getMessage());
        echo json_encode($data);
        exit;
    }
}

function getschoolname($mysqli){
    try {
        global $userid;
        $query = "SELECT SCHOOL_ID, SCHOOL_NAME 
                  FROM SCHOOL 
                  WHERE ISDELETED = 0 
                  AND SCHOOL_ID IN (SELECT SCHOOL_ID FROM SCHOOL_USER WHERE USER_ID = ? AND ISDELETED = 0)
                  ORDER BY SCHOOL_ID";

        $result = sqlsrv_query($mysqli, $query, [$userid]);
        if ($result === false) {
            $data['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
            echo json_encode($data);
            exit;
        }

        $data = array('success' => false, 'data' => []);
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $row['SCHOOL_ID'] = (int)$row['SCHOOL_ID'];
            $data['data'][] = $row;
        }

        $data['success'] = !empty($data['data']);
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array('success' => false, 'data' => [], 'message' => 'Exception: ' . $e->getMessage());
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
	          where  isdeleted = 0 
			  AND    ARCHIVED  = 0
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



function invalidRequest() {
    $data = array('success' => false, 'data' => [], 'message' => 'Invalid request.');
    echo json_encode($data);
    exit;
}
?>