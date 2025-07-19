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
    
        $gradingschemeid  = ($_POST['gradingschemeid'] == 'undefined' || $_POST['gradingschemeid'] == '') ? 0 : $_POST['gradingschemeid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD_FROM  = $_POST['TEXT_CLASS_CD_FROM'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD_FROM'];
     	$TEXT_CLASS_CD_TO  = $_POST['TEXT_CLASS_CD_TO'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD_TO'];
     	$TEXT_SCHEME_NAME  = $_POST['TEXT_SCHEME_NAME'] == 'undefined' ? 0 : $_POST['TEXT_SCHEME_NAME'];
     	
		
		$actionid = $gradingschemeid == 0 ? 1 : 2;

		
		
				$sql = "SELECT * FROM GRADING_SCHEME_MASTER
		        WHERE GRADING_SCHEME_ID!=$gradingschemeid
				AND   SCHOOL_ID =  $TEXT_SCHOOL_ID
				AND   CLASS_CD_FROM   = $TEXT_CLASS_CD_FROM
				AND   CLASS_CD_TO   = $TEXT_CLASS_CD_TO
				AND   SCHEME_NAME    = '$TEXT_SCHEME_NAME '
			 	AND   ISDELETED = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [GRADING_SCHEME_MASTER_SP] 
												$actionid
												,$gradingschemeid
												,$TEXT_SCHOOL_ID
												,'$TEXT_SCHEME_NAME'
												,$TEXT_CLASS_CD_FROM
												,$TEXT_CLASS_CD_TO
												,$userid 
												";
	
		
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
				if(!empty($gradingschemeid))
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

       		
       $query =     "SELECT 
							 A.GRADING_SCHEME_ID
							,A.SCHOOL_ID
							,A.SCHEME_NAME
							,A.CLASS_CD_FROM
							,A.CLASS_CD_TO
							,B.CLASS CLASS_FROM
							,C.CLASS  CLASS_TO
							FROM GRADING_SCHEME_MASTER A , SCHOOL_CLASSES B, SCHOOL_CLASSES C
							WHERE A.SCHOOL_ID		= B.SCHOOL_ID
							AND   A.CLASS_CD_FROM   = B.CLASS_CD
							AND   A.SCHOOL_ID       = C.SCHOOL_ID
							AND   A.CLASS_CD_TO     = C.CLASS_CD
							AND   A.ISDELETED		= 0
							AND   B.ISDELETED		= 0
							AND   C.ISDELETED		= 0
							AND   A.SCHOOL_ID		= $TEXT_SCHOOL_ID 
							";
        
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['GRADING_SCHEME_ID'] = (int) $row['GRADING_SCHEME_ID'];
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



function getClass($mysqli){
	try
	{
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
	
   
	$query = "SELECT CLASS_CD,CLASS FROM SCHOOL_CLASSES where SCHOOL_ID = $TEXT_SCHOOL_ID and isdeleted=0
	         ORDER BY CLASS_CD";
	
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
            $gradingschemeid = ($_POST['gradingschemeid'] == 'undefined' || $_POST['gradingschemeid'] == '') ? 0 : $_POST['gradingschemeid'];  

					
			if($gradingschemeid == 0){
				throw new Exception('GRADING_SCHEME_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [GRADING_SCHEME_MASTER_SP]	3,$gradingschemeid,'','','','',$userid ") ;
				
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







