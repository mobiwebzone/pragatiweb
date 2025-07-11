<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID'])) {
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
        case "getStudent": getStudent($conn); break;
        case "delete": delete($conn); break;
        default: invalidRequest();
    }
} else {
    invalidRequest();
}

function save($mysqli) {
    try {
        global $userid;
        $data = [];
        $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
        $TEXT_STUDENT_ID = ($_POST['TEXT_STUDENT_ID'] == 'undefined' || $_POST['TEXT_STUDENT_ID'] == '') ? 0 : $_POST['TEXT_STUDENT_ID'];
        $TEXT_SECURITY_RETURNED_AMOUNT = ($_POST['TEXT_SECURITY_RETURNED_AMOUNT'] == 'undefined' || $_POST['TEXT_SECURITY_RETURNED_AMOUNT'] == '') ? 0 : $_POST['TEXT_SECURITY_RETURNED_AMOUNT'];

        // Check balance before saving
        $checkQuery = "SELECT SECURITY_BALANCE FROM STUDENT_SECURITY WHERE STUDENT_ID = $TEXT_STUDENT_ID AND SCHOOL_ID = $TEXT_SCHOOL_ID AND ISDELETED = 0";
        $checkResult = sqlsrv_query($mysqli, $checkQuery);
        $balanceRow = sqlsrv_fetch_array($checkResult, SQLSRV_FETCH_ASSOC);

        if ($balanceRow && $TEXT_SECURITY_RETURNED_AMOUNT > $balanceRow['SECURITY_BALANCE']) {
            throw new Exception("Refund Amount cannot be greater than Security Balance");
        }

        $query = "EXEC [STUDENT_SECURITY_REFUND_SP] 1, $pmid, $TEXT_STUDENT_ID, $TEXT_SCHOOL_ID, $TEXT_SECURITY_RETURNED_AMOUNT, $userid";
        $stmt = sqlsrv_query($mysqli, $query);

        if ($stmt === false) {
            $data['success'] = false;
            $data['message'] = print_r(sqlsrv_errors(), true);
        } else {
            $data['success'] = true;
            $data['message'] = 'Record successfully Updated.';
        }

        echo json_encode($data);
        exit;

    } catch (Exception $e) {
        ob_end_clean();
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
	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
       		
       $query =     "   SELECT
						STUDENT_SECURITY_ID
						,SCHOOL_ID
						,STUDENT_ID
						,SECURITY_DEMAND_AMOUNT
						,SECURITY_RECEIVED_AMOUNT
						,SECURITY_RETURNED_AMOUNT
						,SECURITY_BALANCE
						,SECURITY_STATUS_CD
						,SECURITY_STATUS
						,CONVERT(VARCHAR,SECURITY_PAYMENT_DATE,106) SECURITY_PAYMENT_DATE
						,CONVERT(VARCHAR,SECURITY_RETURN_DATE,106) SECURITY_RETURN_DATE
						FROM STUDENT_SECURITY 
						WHERE SCHOOL_ID = $TEXT_SCHOOL_ID
						AND STUDENT_ID = $TEXT_STUDENT_ID
						 AND ISDELETED = 0 ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['STUDENT_SECURITY_ID'] = (int) $row['STUDENT_SECURITY_ID'];
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


function getStudent($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	

	$query = "SELECT STUDENT_ID, 
	        (STUDENT_FIRST_NAME + ' ' + ISNULL(STUDENT_LAST_NAME, '')) AS STUDENT_NAME
			FROM STUDENT 
			where  isdeleted=0 
			and SCHOOL_ID = $TEXT_SCHOOL_ID
			and CLASS_CD  = $TEXT_CLASS_CD ";

		$data = array();
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
       		 $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        	 $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
       		 $TEXT_STUDENT_ID = ($_POST['TEXT_STUDENT_ID'] == 'undefined' || $_POST['TEXT_STUDENT_ID'] == '') ? 0 : $_POST['TEXT_STUDENT_ID'];
           
			
				$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_SECURITY_REFUND_SP]	
				                             3,
											 $pmid
											 ,$TEXT_STUDENT_ID
											 ,$TEXT_SCHOOL_ID
											 ,''
											 ,$userid ") ;
				
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


