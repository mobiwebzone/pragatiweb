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
		case "getTeacher": getTeacher($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
		case "delete":delete($conn);break;
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $feesid  = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
        $TEXT_SALARY_DUE  = $_POST['TEXT_SALARY_DUE'] == 'undefined' ? 0 : $_POST['TEXT_SALARY_DUE'];
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $feesid == 0 ? 1 : 2;

		
				$sql = "SELECT * FROM TEACHER_SALARY_MASTER
		        WHERE TEACHER_PAYROLL_ID!=$feesid
				AND   SCHOOL_ID 		  =  $TEXT_SCHOOL_ID
				AND   TEACHER_ID 		  =  $TEXT_TEACHER_ID
				AND   FY_YEAR_CD          =  $TEXT_FY_YEAR_CD
				AND   ISDELETED = 0 ";	  
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [TEACHER_SALARY_MASTER_SP] $actionid,$feesid,$TEXT_SCHOOL_ID,$TEXT_TEACHER_ID,$TEXT_FY_YEAR_CD,$TEXT_SALARY_DUE,$userid,'$txtremarks' ";
	
		

		$data['$sql'] = $query;
		
		   
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($feesid))
				$data['message'] = 'Record successfully updated';
				else 
				$data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
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


 function getQuery($mysqli){
		try
	{
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
       		
       $query =     "SELECT  A.TEACHER_PAYROLL_ID
							,A.TEACHER_ID
							,A.SCHOOL_ID
							,B.SCHOOL_NAME
							,B.TEACHER_NAME
							,A.FY_YEAR_CD
							,A.FY_YEAR
							,A.GROSS_SALARY
					From TEACHER_SALARY_MASTER A , TEACHER B
					WHERE A.ISDELETED=0
					AND   B.ISDELETED =0
					AND   A.TEACHER_ID = B.TEACHER_ID
					AND   A.SCHOOL_ID = $TEXT_SCHOOL_ID
					AND   A.FY_YEAR_CD = $TEXT_FY_YEAR_CD ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['TEACHER_PAYROLL_ID'] = (int) $row['TEACHER_PAYROLL_ID'];
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


function getGrossSalary($mysqli){
	try
	{
	    $data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FY_YEAR_CD  = $_POST['TEXT_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FY_YEAR_CD'];
		$TEXT_TEACHER_ID  = $_POST['TEXT_TEACHER_ID'] == 'undefined' ? 0 : $_POST['TEXT_TEACHER_ID'];

	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=50 and isdeleted=0";

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


function getPaymentMode($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=50 and isdeleted=0";

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



// function getMonth($mysqli){
// 	try
// 	{
		
// 	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=47 and isdeleted=0";

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


function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $feesid = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];  

					
			// if($feesid == 0){
			// 	throw new Exception('TEACHER_SALARY_ID Error.');
			// }
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_SALARY_MASTER_SP]	3,$feesid,NULL,NULL,NULL,NULL,$userid,'' ") ;
				
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







