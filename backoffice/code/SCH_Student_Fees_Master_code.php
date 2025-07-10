<?php
// error_reporting(0);
// ini_set('display_errors', 0);
// ob_start(); // Start output buffering

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
		case "getClass": getClass($conn);break;
		case "getStudent": getStudent($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "delete":delete($conn);break;
		case "updateRowAmountDue": updateRowAmountDue($conn); break;
		case "getFeesDue": getFeesDue($conn); break;
		case "getFeesHead": getFeesHead($conn);break;


		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

function save($mysqli) {
    ob_start(); // Start output buffering
    try {
        global $userid;
        $data = [];

        $detailid = ($_POST['detailid'] == 'undefined' || $_POST['detailid'] == '') ? 0 : $_POST['detailid'];
        $TEXT_SCHOOL_ID = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
        $TEXT_CLASS_CD = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_STUDENT_ID = ($_POST['TEXT_STUDENT_ID'] == 'undefined' || $_POST['TEXT_STUDENT_ID'] == '') ? 0 : $_POST['TEXT_STUDENT_ID'];
        $TEXT_FEES_FY_YEAR_CD = ($_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' || $_POST['TEXT_FEES_FY_YEAR_CD'] == '') ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
        $TEXT_FEES_HEAD_CD = ($_POST['TEXT_FEES_HEAD_CD'] == 'undefined' || $_POST['TEXT_FEES_HEAD_CD'] == '') ? 0 : $_POST['TEXT_FEES_HEAD_CD'];
        $TEXT_FEES_DUE_INSERT = ($_POST['TEXT_FEES_DUE_INSERT'] == 'undefined' || $_POST['TEXT_FEES_DUE_INSERT'] == '') ? 0 : $_POST['TEXT_FEES_DUE_INSERT'];

        $sql = "SELECT * FROM STUDENT_FEES_DETAIL 
                WHERE FEES_MASTER_DETAIL_ID != $detailid
                AND SCHOOL_ID = $TEXT_SCHOOL_ID
                AND FEES_HEAD_CD = $TEXT_FEES_HEAD_CD
                AND CLASS_CD = $TEXT_CLASS_CD
                AND FEES_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
                AND STUDENT_ID = $TEXT_STUDENT_ID
                AND ISDELETED = 0";

        $row_count = unique($sql);

        if ($row_count == 0) {
            $actionid = $detailid == 0 ? 1 : 2;

            $query = "EXEC [STUDENT_FEES_MASTER_SP] 
                      $actionid,
                      $detailid,
                      $TEXT_STUDENT_ID,
                      $TEXT_SCHOOL_ID,
                      $TEXT_CLASS_CD,
                      $TEXT_FEES_FY_YEAR_CD,
                      $TEXT_FEES_DUE_INSERT,
                      $userid,
                      $TEXT_FEES_HEAD_CD";

            $stmt = sqlsrv_query($mysqli, $query);

            if ($stmt === false) {
                $data['success'] = false;
                $data['message'] = print_r(sqlsrv_errors(), true);
            } else {
                $data['success'] = true;
                $data['message'] = $detailid ? 'Record successfully updated' : 'Record successfully inserted.';
            }
        } else {
            $data['success'] = false;
            $data['message'] = 'Object Type already exists.';
        }

        ob_end_clean(); // Clear output buffer before sending JSON
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        ob_end_clean(); // Clear output buffer in case of error
        $data = array();
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data);
        exit;
    }
}



function updateRowAmountDue($mysqli) {
    try {
        global $userid;
        $data = array();

        $DETAIL_ID = $_POST['DETAIL_ID'] ?? 0;
        $STUDENT_ID = $_POST['STUDENT_ID'] ?? 0;
        $SCHOOL_ID = $_POST['SCHOOL_ID'] ?? 0;
        $CLASS_CD = $_POST['CLASS_CD'] ?? 0;
        $FEES_YEAR_CD = $_POST['FEES_YEAR_CD'] ?? 0;
        $AMOUNT_DUE = $_POST['AMOUNT_DUE'] ?? 0;
		$FEES_HEAD_CD = $_POST['FEES_HEAD_CD'] ?? 0;
        $ACTIONID = 2;

        $sql = "EXEC STUDENT_FEES_MASTER_SP $ACTIONID, $DETAIL_ID, $STUDENT_ID, $SCHOOL_ID, $CLASS_CD, $FEES_YEAR_CD, $AMOUNT_DUE, $userid,$FEES_HEAD_CD";

        $stmt = sqlsrv_query($mysqli, $sql);
        if ($stmt === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        $data['success'] = true;
        $data['message'] = "Row updated.";
        echo json_encode($data); exit;

    } catch (Exception $e) {
        $data = array();
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data); exit;
    }
}

function getFeesHead($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=52 and isdeleted=0";

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



function getFeesDue($mysqli) {
  try {
    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] ?? 0;
    $TEXT_CLASS_CD   = $_POST['TEXT_CLASS_CD'] ?? 0;
    $TEXT_STUDENT_ID = $_POST['TEXT_STUDENT_ID'] ?? 0;
    $TEXT_FEES_FY_YEAR_CD = $_POST['TEXT_FEES_FY_YEAR_CD'] ?? 0;

    $query = "SELECT FEES_DUE, FEES_PAID, FEES_BALANCE
              FROM STUDENT_FEES_MASTER 
              WHERE ISDELETED = 0
              AND SCHOOL_ID = $TEXT_SCHOOL_ID
              AND CLASS_CD = $TEXT_CLASS_CD
              AND STUDENT_ID = $TEXT_STUDENT_ID
              AND FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD";

    $result = sqlsrv_query($mysqli, $query);
    if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
      echo json_encode([
        "success" => true,
        "FEE_DUE" => (int)$row["FEES_DUE"],
        "FEE_PAID" => (int)$row["FEES_PAID"],
        "FEE_BALANCE" => (int)$row["FEES_BALANCE"]
      ]);
    } else {
      echo json_encode(["success" => false]);
    }
    exit;
  } catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
  }
}



 function getQuery($mysqli){
		try
	{
	
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
	$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];
       		
       $query =     "SELECT 
						 A.DETAIL_ID
						,B.SCHOOL_ID
						,A.FEES_ID
						,A.STUDENT_ID
						,A.FEES_YEAR_CD
						,A.FEES_HEAD_CD
						,A.FEES_HEAD
						,A.AMOUNT_DUE
						,B.CLASS
						,B.FEES_FY_YEAR
						FROM STUDENT_FEES_DETAIL A , STUDENT_FEES_MASTER B
						WHERE A.FEES_ID = B.FEES_ID
						AND   B.STUDENT_ID = $TEXT_STUDENT_ID
						AND   B.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD 
						AND   B.SCHOOL_ID  = $TEXT_SCHOOL_ID
						AND   B.CLASS_CD  =  $TEXT_CLASS_CD ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['DETAIL_ID'] = (int) $row['DETAIL_ID'];
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

            $feesid = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid']; 
			$schoolid = ($_POST['schoolid'] == 'undefined' || $_POST['schoolid'] == '') ? 0 : $_POST['schoolid'];
			$classcd = ($_POST['classcd'] == 'undefined' || $_POST['classcd'] == '') ? 0 : $_POST['classcd'];
			$feesheadcd = ($_POST['feesheadcd'] == 'undefined' || $_POST['feesheadcd'] == '') ? 0 : $_POST['feesheadcd'];
			$studentid = ($_POST['studentid'] == 'undefined' || $_POST['studentid'] == '') ? 0 : $_POST['studentid'];
			$amountdue = ($_POST['amountdue'] == 'undefined' || $_POST['amountdue'] == '') ? 0 : $_POST['amountdue'];
			$feeyearcd = ($_POST['feeyearcd'] == 'undefined' || $_POST['feeyearcd'] == '') ? 0 : $_POST['feeyearcd']; 
           
			$sql = "SELECT A.* FROM STUDENT_FEES_PAYMENT_DETAILS A, STUDENT_FEES_PAYMENT B 
                WHERE A.FEES_PAYMENT_ID     = B.FEES_PAYMENT_ID
				AND   A.STUDENT_ID          = B.STUDENT_ID
				AND   B.SCHOOL_ID 		    = $schoolid
                AND   B.STUDENT_ID 		    = $studentid
                AND   B.CLASS_CD 		    = $classcd
                AND   B.FEES_FY_YEAR_CD 	= $feeyearcd
                AND   A.FEES_HEAD_CD 		= $feesheadcd ";
               

       			$row_count = unique($sql);
				
				if ($row_count> 0)
				{
						throw new Exception('Payment Received! Cannot Delete! Please delete the payment first');
				}
				
			
			$sql = "SELECT * FROM STUDENT_FEES_PAYMENT 
			WHERE FEES_ID    = $feesid
			AND   STUDENT_ID = $studentid
			AND   ISDELETED  = 0 ";	
				
			$row_count = unique($sql);
			

			
	  if($row_count == 0)
	      {
				$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_FEES_MASTER_SP]	3,$feesid,$studentid,$schoolid,$classcd,$feeyearcd,$amountdue,$userid,$feesheadcd ") ;
				
				if( $stmt === false )       
				{
					die( print_r( sqlsrv_errors(), true));
					throw new Exception( $mysqli->sqlstate );
					 echo json_encode($data);exit;
				}
				else
				{
					$data['success'] = true;
					$data['message'] = 'Record successfully deleted';
					 echo json_encode($data);exit;
				}
			}
		else
		  {

            $data['success'] = false;
			$data['message'] = 'Payment Received! Cannot Delete!  Please delete the payment first';

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







