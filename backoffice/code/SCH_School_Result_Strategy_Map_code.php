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
		case "getClassFrom": getClassFrom($conn);break;
		case "getClassTo": getClassTo($conn);break;
		case "getResultStrategy": getResultStrategy($conn);break;
		case "getEffectiveYear": getEffectiveYear($conn);break;
		
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
    
        $mapid  = ($_POST['mapid'] == 'undefined' || $_POST['mapid'] == '') ? 0 : $_POST['mapid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_CLASS_CD_FROM  = $_POST['TEXT_CLASS_CD_FROM'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD_FROM'];
		$TEXT_CLASS_CD_TO  = $_POST['TEXT_CLASS_CD_TO'] == 'undefined' ? 0 : $_POST['TEXT_CLASS_CD_TO'];
    	$TEXT_STRATEGY_ID  = $_POST['TEXT_STRATEGY_ID'] == 'undefined' ? 0 : $_POST['TEXT_STRATEGY_ID'];
	    $TEXT_YEAR  = $_POST['TEXT_YEAR'] == 'undefined' ? 0 : $_POST['TEXT_YEAR'];
		
		
		
		$actionid = $mapid == 0 ? 1 : 2;
	
				$sql = "SELECT * FROM SCHOOL_RESULT_STRATEGY_MAP
		        WHERE MAP_ID			!= $mapid
				AND   SCHOOL_ID  		=  $TEXT_SCHOOL_ID
				AND   CLASS_CD_FROM   	=  $TEXT_CLASS_CD_FROM
				AND   CLASS_CD_TO   	=  $TEXT_CLASS_CD_TO
			    AND   STRATEGY_ID    	=  $TEXT_STRATEGY_ID
				AND   EFFECTIVE_YEAR 	=  $TEXT_YEAR
				AND   ISDELETED 		= 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
	
	   
	   if($row_count == 0)
	   {
	   
		$query="EXEC [SCHOOL_RESULT_STRATEGY_MAP_SP] 
										   $actionid
										  ,$mapid
										  ,$TEXT_SCHOOL_ID
										  ,$TEXT_STRATEGY_ID
										  ,$TEXT_CLASS_CD_FROM
										  ,$TEXT_CLASS_CD_TO
										  ,$TEXT_YEAR
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
				if(!empty($mapid))
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
	
       		
       $query =     "SELECT TOP 1
								A.MAP_ID,
								A.SCHOOL_ID,
								A.STRATEGY_ID,
								A.CLASS_CD_FROM,
								A.CLASS_CD_TO,
								A.EFFECTIVE_YEAR,
								B.SCHOOL_NAME,
								C.YEAR_ID,
								C.YEAR,
								D.STRATEGY_NAME,
								E.CLASS AS CLASS_FROM,
								F.CLASS AS CLASS_TO
							FROM SCHOOL_RESULT_STRATEGY_MAP A
							JOIN SCHOOL B ON A.SCHOOL_ID = B.SCHOOL_ID AND B.ISDELETED = 0
							JOIN EFFECTIVE_YEAR C ON A.EFFECTIVE_YEAR = C.YEAR
							JOIN RESULT_STRATEGY_MASTER D ON A.STRATEGY_ID = D.STRATEGY_ID AND D.ISDELETED = 0
							JOIN SCHOOL_CLASSES E ON A.CLASS_CD_FROM = E.CLASS_CD AND E.ISDELETED = 0
							JOIN SCHOOL_CLASSES F ON A.CLASS_CD_TO = F.CLASS_CD AND F.ISDELETED = 0
							WHERE A.ISDELETED = 0
							";
        
	
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['MAP_ID'] = (int) $row['MAP_ID'];
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


function getEffectiveYear($mysqli){
	try
	{
		
	$query = "SELECT YEAR_ID,YEAR FROM EFFECTIVE_YEAR ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['YEAR_ID'] = (int) $row['YEAR_ID'];
				
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



function getResultStrategy($mysqli){
	try
	{
	
	$query = "SELECT STRATEGY_ID,STRATEGY_NAME FROM RESULT_STRATEGY_MASTER 
	          WHERE isdeleted   = 0 ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STRATEGY_ID'] = (int) $row['STRATEGY_ID'];
				
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



function getClassFrom($mysqli){
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


function getClassTo($mysqli){
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
            $mapid = ($_POST['mapid'] == 'undefined' || $_POST['mapid'] == '') ? 0 : $_POST['mapid'];  

					
			if($mapid == 0){
				throw new Exception('MAP_ID Error.');
			}
			
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SCHOOL_RESULT_STRATEGY_MAP_SP]	3,$mapid,'','','','','',$userid ") ;
				
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







