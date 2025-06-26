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
        case "getTeacherAttSettings":getTeacherAttSettings($conn);break;
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
		
		$data = array();
        $tasid  = ($_POST['tasid'] == 'undefined' || $_POST['tasid'] == '') ? 0 : $_POST['tasid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtAttClosedDT  = ($_POST['txtAttClosedDT'] == 'undefined' || $_POST['txtAttClosedDT'] == '') ? '' : $_POST['txtAttClosedDT'];
		
		$actionid = $tasid == 0 ? 1 : 2;
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtAttClosedDT == ''){throw new Exception("Please Select Closed Date.");}
		
		$sql = "SELECT * FROM TEACHER_ATTENDANCE_SETTING WHERE LOCID=$ddlLocation AND TASID!=$tasid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [TEACHER_ATTENDANCE_SETTING_SP] $actionid,$tasid,$ddlLocation,'$txtAttClosedDT',$userid";
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			// throw new Exception($query);
			
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
				if(!empty($tasid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
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


/*============ GET TEACHER ATT SETTINGS =============*/ 
 function getTeacherAttSettings($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT TASID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TAS.LOCID)[LOCATION],convert(VARCHAR,CLOSED_DATE,106)CLOSED_DATE
		 FROM TEACHER_ATTENDANCE_SETTING TAS WHERE ISDELETED=0 AND LOCID=$ddlLocation";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TASID'] = (int) $row['TASID'];
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TASID = ($_POST['TASID'] == 'undefined' || $_POST['TASID'] == '') ? 0 : $_POST['TASID']; 
			if($TASID==0)throw new Exception('TASID Error.');
			$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_ATTENDANCE_SETTING_SP] 3,$TASID,0,'',$userid");
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
		$data['success'] = false;
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







