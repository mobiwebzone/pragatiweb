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
		case "getSalaryComponent":getSalaryComponent($conn);break;
	
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
    
        $salaryid  = ($_POST['salaryid'] == 'undefined' || $_POST['salaryid'] == '') ? 0 : $_POST['salaryid'];
	    $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_EMPLOYEE_ID = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
		$TEXT_COMPONENT_ID  = $_POST['TEXT_COMPONENT_ID'] == 'undefined' ? 0 : $_POST['TEXT_COMPONENT_ID'];
		
		$TEXT_FIXED_AMOUNT = is_numeric($_POST['TEXT_FIXED_AMOUNT']) ? number_format((float)$_POST['TEXT_FIXED_AMOUNT'], 2, '.', '') : '0.00';


		$actionid = $salaryid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM EMPLOYEE_SALARY_STRUCTURE
		        WHERE SALARY_ID!=$salaryid
				AND   SCHOOL_ID        = $TEXT_SCHOOL_ID
				AND   EMPLOYEE_ID      = $TEXT_EMPLOYEE_ID
				AND   COMPONENT_ID     = $TEXT_COMPONENT_ID
				AND   ISDELETED        = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [EMPLOYEE_SALARY_STRUCTURE_SP] 
											$actionid
											,$salaryid
											,$TEXT_SCHOOL_ID
											,$TEXT_EMPLOYEE_ID
											,$TEXT_COMPONENT_ID
											,$TEXT_FIXED_AMOUNT 
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
	  $TEXT_EMPLOYEE_ID  = $_POST['TEXT_EMPLOYEE_ID'] == 'undefined' ? 0 : $_POST['TEXT_EMPLOYEE_ID'];
       		
       $query =     "WITH SalaryData AS (
										SELECT
											A.SALARY_ID,
											A.SCHOOL_ID,
											A.EMPLOYEE_ID,
											A.COMPONENT_ID,
											A.FIXED_AMOUNT,
											B.COMPONENT_NAME,
											B.COMPONENT_TYPE,
											B.COMPONENT_TYPE_CD
										FROM EMPLOYEE_SALARY_STRUCTURE A
										JOIN SALARY_COMPONENT_MASTER B ON A.COMPONENT_ID = B.COMPONENT_ID
										WHERE A.ISDELETED = 0 AND B.ISDELETED = 0
										AND A.SCHOOL_ID = $TEXT_SCHOOL_ID
										AND A.EMPLOYEE_ID = $TEXT_EMPLOYEE_ID
									)
									SELECT * FROM SalaryData

									UNION ALL

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
										NULL AS COMPONENT_TYPE_CD
									FROM SalaryData
									HAVING COUNT(*) > 0
						";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['SALARY_ID'] = (int) $row['SALARY_ID'];
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

function getSalaryComponent($mysqli){
	try
	{
		
	$query = "SELECT COMPONENT_ID,COMPONENT_NAME FROM SALARY_COMPONENT_MASTER where  isdeleted=0 ";
	

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['COMPONENT_ID'] = (int) $row['COMPONENT_ID'];
				
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







