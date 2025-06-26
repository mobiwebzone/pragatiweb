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
		case "getClass": getClass($conn);break;
		case "getFeesHead": getFeesHead($conn);break;
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
		$TEXT_CLASS_CD  = $_POST['TEXT_CLASS_CD'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD'];
        $TEXT_FEES_HEAD_CD  = $_POST['TEXT_FEES_HEAD_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_HEAD_CD'];
		$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
		$TEXT_FEES_DUE  = $_POST['TEXT_FEES_DUE'] == 'undefined' ? 0 : $_POST['TEXT_FEES_DUE'];
	    $txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
		
		
		$actionid = $feesid == 0 ? 1 : 2;

		
		
		$sql = "SELECT * FROM FEES_MASTER_DETAILS 
		WHERE FEES_MASTER_DETAIL_ID!=$feesid
		AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
		AND   FEES_HEAD_CD = $TEXT_FEES_HEAD_CD
		AND   CLASS_CD   = $TEXT_CLASS_CD
		AND   FEES_FY_YEAR_CD = $TEXT_FEES_FY_YEAR_CD
		AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
	    
		
		$query="EXEC [FEES_MASTER_SP] $actionid,$feesid,$TEXT_SCHOOL_ID,$TEXT_CLASS_CD,$TEXT_FEES_FY_YEAR_CD,$TEXT_FEES_HEAD_CD,$TEXT_FEES_DUE,$userid,'$txtremarks' ";
	
		
				
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
                 {                           
					$data['message'] = 'Record successfully updated';
					echo json_encode($data);exit;
				 }
				 
				else 
				{
					$data['message'] = 'Record successfully inserted.';
					echo json_encode($data);exit;
				}
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
	$TEXT_FEES_FY_YEAR_CD  = $_POST['TEXT_FEES_FY_YEAR_CD'] == 'undefined' ? 0 : $_POST['TEXT_FEES_FY_YEAR_CD'];
       		
       $query =     "SELECT 
	                FEES_MASTER_DETAIL_ID
					,SCHOOL_ID
					,SCHOOL_NAME
					,CLASS_CD
					,CLASS
					,FEES_HEAD_CD
					,FEES_HEAD
					,FEES_DUE
					,REMARKS 
					,FEES_FY_YEAR_CD
					,FEES_FY_YEAR
					from FEES_MASTER_DETAILS
					WHERE ISDELETED=0 
					AND SCHOOL_ID = $TEXT_SCHOOL_ID
					AND FEES_FY_YEAR_CD  = $TEXT_FEES_FY_YEAR_CD ";

		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FEES_MASTER_DETAIL_ID'] = (int) $row['FEES_MASTER_DETAIL_ID'];
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


function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0 order by CODE_DETAIL_ID desc";

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


function delete($mysqli){
	try{   
			global $userid;
		    $data = array();

            $feesid = ($_POST['feesid'] == 'undefined' || $_POST['feesid'] == '') ? 0 : $_POST['feesid'];  
           
				
	 
				$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_FEES_MASTER_SP]	3,$feesid,'','','','','',$userid,'' ") ;
				
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







