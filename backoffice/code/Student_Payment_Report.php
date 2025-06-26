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
		case "getClass": getClass($conn);break;
		case "getStudent": getStudent($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		// case "getFeesDue": getFeesDue($conn);break;
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




 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
       	$TEXT_STUDENT_ID  = $_POST['TEXT_STUDENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_STUDENT_ID'];	
       
	 
	//    $query =     "SELECT A.FEES_PAYMENT_ID,A.SCHOOL_ID,A.FEES_ID,A.STUDENT_ID
	//                 ,CONVERT(VARCHAR,A.PAYMENT_DATE,106)PAYMENT_DATE,A.FEES_PAID,A.FEES_FY_YEAR_CD,A.FEES_FY_YEAR
	// 				,A.REMARKS,A.CLASS_CD,A.CLASS , (B.STUDENT_FIRST_NAME +' '+B.STUDENT_LAST_NAME) STUDENT_NAME ,
	// 				B.FEES_DUE  
	// 				FROM STUDENT_FEES_PAYMENT A, STUDENT_FEES_MASTER B 
	// 				WHERE A.ISDELETED=0 
	// 				AND A.STUDENT_ID = B.STUDENT_ID
	// 				AND A.FEES_ID = B.FEES_ID
	// 				AND B.ISDELETED=0 
	// 				AND A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
	// 				AND A.SCHOOL_ID = $TEXT_SCHOOL_ID ";

$query = "SELECT A.FEES_PAYMENT_ID,
                 A.SCHOOL_ID,
                 A.FEES_ID,
                 A.STUDENT_ID,
                 CONVERT(VARCHAR, A.PAYMENT_DATE, 106) AS PAYMENT_DATE,
                 A.FEES_PAID,
                 A.FEES_FY_YEAR_CD,
                 A.FEES_FY_YEAR,
                 A.REMARKS,
                 A.CLASS_CD,
                 A.CLASS,
                 (B.STUDENT_FIRST_NAME + ' ' + B.STUDENT_LAST_NAME) AS STUDENT_NAME,
                 B.FEES_DUE
          FROM STUDENT_FEES_PAYMENT A
          JOIN STUDENT_FEES_MASTER B
            ON A.STUDENT_ID = B.STUDENT_ID
            AND A.FEES_ID = B.FEES_ID
          WHERE A.ISDELETED = 0
            AND B.ISDELETED = 0
            AND A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
            AND A.SCHOOL_ID = $TEXT_SCHOOL_ID";

if ($TEXT_CLASS_CD != '') {
    $query .= " AND A.CLASS_CD = $TEXT_CLASS_CD";
}

if ($TEXT_STUDENT_ID != '') {
    $query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID";
}

$query .= " UNION ALL
           SELECT NULL AS FEES_PAYMENT_ID,
                  A.SCHOOL_ID,
                  NULL AS FEES_ID,
                  NULL AS STUDENT_ID,
                  NULL AS PAYMENT_DATE,
                  SUM(A.FEES_PAID) AS FEES_PAID,
                  A.FEES_FY_YEAR_CD,
                  NULL AS FEES_FY_YEAR,
                  NULL AS REMARKS,
                  NULL AS CLASS_CD,
                  NULL AS CLASS,
                  'Total' AS STUDENT_NAME,
                  NULL AS FEES_DUE
           FROM STUDENT_FEES_MASTER B
           JOIN STUDENT_FEES_PAYMENT A
             ON A.STUDENT_ID = B.STUDENT_ID
             AND A.FEES_ID = B.FEES_ID
           WHERE A.ISDELETED = 0
             AND B.ISDELETED = 0
             AND A.FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
             AND A.SCHOOL_ID = $TEXT_SCHOOL_ID";

if ($TEXT_CLASS_CD != '') {
    $query .= " AND A.CLASS_CD = $TEXT_CLASS_CD";
}

if ($TEXT_STUDENT_ID != '') {
    $query .= " AND A.STUDENT_ID = $TEXT_STUDENT_ID";
}

$query .= " GROUP BY A.SCHOOL_ID, A.FEES_FY_YEAR_CD";


        $result = sqlsrv_query($mysqli, $query);
		
		$data = array();
		
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FEES_PAYMENT_ID'] = (int) $row['FEES_PAYMENT_ID'];
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
	$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
	
	$query = "SELECT STUDENT_ID, (STUDENT_FIRST_NAME+''+STUDENT_LAST_NAME) STUDENT_NAME 
	         FROM  STUDENT_FEES_MASTER 
			 where ISDELETED = 0
			 and   CLASS_CD = $TEXT_CLASS_CD 
			 and   SCHOOL_ID = $TEXT_SCHOOL_ID 
			 AND   FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD";

		$data['query'] = $query;

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


function getClass($mysqli){
	try
	{
		
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
	// $query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0 order by CODE_DETAIL_ID";
    
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







