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
        case "saveMain":saveMain($conn);break;
        case "getTaskMainCategory":getTaskMainCategory($conn);break;
        case "deleteMain":deleteMain($conn);break;

        case "save":save($conn);break;
        case "getTaskCategory":getTaskCategory($conn);break;
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




// =======================================================================================================================================
//                                                          MAIN CATEGORY
// =======================================================================================================================================

/*============ SAVE =============*/ 
function saveMain($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $data = array();
	   $TASKMAINCATID  = ($_POST['TASKMAINCATID'] == 'undefined' || $_POST['TASKMAINCATID'] == '') ? 0 : $_POST['TASKMAINCATID'];
	   $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	   $txtTaskMainCat  = ($_POST['txtTaskMainCat'] == 'undefined' || $_POST['txtTaskMainCat'] == '') ? '' : $_POST['txtTaskMainCat'];
	   $ddlMainPriority  = ($_POST['ddlMainPriority'] == 'undefined' || $_POST['ddlMainPriority'] == '') ? '' : $_POST['ddlMainPriority'];
	   
	   $actionid = $TASKMAINCATID == 0 ? 1 : 2;
	   
	   if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
	   if($txtTaskMainCat == '')throw new Exception("Please Enter Category.");
	   if($ddlMainPriority == '')throw new Exception("Please Select Priority.");
	   
	   $sql = "SELECT * FROM TASK_MAIN_CATEGORIES WHERE LOCID=$ddlLocation AND TASKMAINCAT='$txtTaskMainCat' AND TASKMAINCATID!=$TASKMAINCATID AND ISDELETED=0";
	   $row_count = unique($sql);
	   
	   
	   if($row_count == 0)
	   {
		   $query="EXEC [TASK_MAIN_CATEGORIES_SP] $actionid,$TASKMAINCATID,$ddlLocation,'$txtTaskMainCat','$ddlMainPriority',$userid";
		   // $data['$query'] = $query;
		   // echo json_encode($data);exit;
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['query'] = $query;
		   }
		   else
		   {
				// GET TASKMAINCATID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['TASKMAINCATID'] = $row['TASKMAINCATID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($TASKMAINCATID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
		   }	
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'Task already exists.';
		   echo json_encode($data);exit;
	   }

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
/*============ SAVE =============*/ 


/*============ GET TASK MAIN CATEGORY =============*/ 
function getTaskMainCategory($mysqli){
   try
   {
	   $data = array();
	   $ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
	   $query = "SELECT TASKMAINCATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKMAINCAT,PRIORITY 
			   FROM TASK_MAIN_CATEGORIES GM
			   WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY [LOCATION],LEN(TASKMAINCAT),TASKMAINCAT";
	   
	   $count = unique($query);
	   if($count > 0){
		   $result = sqlsrv_query($mysqli, $query);
		   while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			   $row['TASKMAINCATID'] = (int) $row['TASKMAINCATID'];
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
/*============ GET TASK MAIN CATEGORY =============*/  


/* =========== DELETE =========== */ 
function deleteMain($mysqli){
   try{   
		global $userid;
		$data = array();     
		$TASKMAINCATID = ($_POST['TASKMAINCATID'] == 'undefined' || $_POST['TASKMAINCATID'] == '') ? 0 : $_POST['TASKMAINCATID'];  
		if($TASKMAINCATID == 0) throw new Exception("Error : Invalid TASKMAINCATID.");
		$query = "EXEC [TASK_MAIN_CATEGORIES_SP] 3,$TASKMAINCATID,0,'','',$userid";
		$data['$query'] = $query;
		$stmt=sqlsrv_query($mysqli,$query);
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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
/* =========== DELETE =========== */ 



// =======================================================================================================================================
//                                                          SUB CATEGORY
// =======================================================================================================================================

/*============ SAVE =============*/ 
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
        $TASKCATID  = ($_POST['TASKCATID'] == 'undefined' || $_POST['TASKCATID'] == '') ? 0 : $_POST['TASKCATID'];
        $TASKMAINCATID  = ($_POST['TASKMAINCATID'] == 'undefined' || $_POST['TASKMAINCATID'] == '') ? 0 : $_POST['TASKMAINCATID'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtTaskCAt  = ($_POST['txtTaskCAt'] == 'undefined' || $_POST['txtTaskCAt'] == '') ? '' : $_POST['txtTaskCAt'];
        $ddlPriority  = ($_POST['ddlPriority'] == 'undefined' || $_POST['ddlPriority'] == '') ? '' : $_POST['ddlPriority'];
		
		$actionid = $TASKCATID == 0 ? 1 : 2;
		
		if($TASKMAINCATID == 0)throw new Exception("Error : Invalid TASKMAINCATID.");
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtTaskCAt == '')throw new Exception("Please Enter Category.");
		if($ddlPriority == '')throw new Exception("Please Select Priority.");
		
		$sql = "SELECT * FROM TASK_CATEGORIES WHERE TASKMAINCATID=$TASKMAINCATID AND LOCID=$ddlLocation AND TASKCAT='$txtTaskCAt' AND TASKMAINCATID=$TASKMAINCATID AND TASKCATID!=$TASKCATID AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [TASK_CATEGORIES_SP] $actionid,$TASKCATID,$TASKMAINCATID,$ddlLocation,'$txtTaskCAt','$ddlPriority',$userid";
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($TASKCATID))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Sub-Task already exists.';
			echo json_encode($data);exit;
		}

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
/*============ SAVE =============*/ 


/*============ GET TASK SUBCATEGORY =============*/ 
 function getTaskCategory($mysqli){
	try
	{
		$data = array();
		$TASKMAINCATID = ($_POST['TASKMAINCATID'] =='undefined' || $_POST['TASKMAINCATID'] =='') ? 0 : $_POST['TASKMAINCATID'];
		if($TASKMAINCATID == 0) throw new Exception('Error : Invalid TASKMAINCATID.');
		$query = "SELECT TASKCATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],TASKCAT,PRIORITY 
				FROM TASK_CATEGORIES GM 
				WHERE ISDELETED=0 AND TASKMAINCATID=$TASKMAINCATID ORDER BY [LOCATION],LEN(TASKCAT),TASKCAT";
		
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			
			$idx=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TASKCATID'] = (int) $row['TASKCATID'];
				$data['data'][] = $row;

				$idx++;
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
/*============ GET TASK SUBCATEGORY =============*/ 


/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TASKCATID = ($_POST['TASKCATID'] == 'undefined' || $_POST['TASKCATID'] == '') ? 0 : $_POST['TASKCATID'];  
			$query = "EXEC [TASK_CATEGORIES_SP] 3,$TASKCATID,0,0,'','',$userid";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted.';
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
/* =========== DELETE =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







