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
		case "login":login($conn);break;
        case "save":save($conn);break;
        case "getWorkingHours":getWorkingHours($conn);break;
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
		$Week=0;
    
        $whid  = ($_POST['whid'] == 'undefined' || $_POST['whid'] == '') ? 0 : $_POST['whid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlWeek  = $_POST['ddlWeek'] == 'undefined' ? '' : $_POST['ddlWeek'];
        $txtTimeFrom  = $_POST['txtTimeFrom'] == 'undefined' ? '' : $_POST['txtTimeFrom'];
        $txtTimeTo  = $_POST['txtTimeTo'] == 'undefined' ? '' : $_POST['txtTimeTo'];
        $chkClosed  = ($_POST['chkClosed'] == 'false' || $_POST['chkClosed'] == '') ? 0 : 1;

		$actionid = $whid == 0 ? 1 : 2;

		if($ddlLocation == 0)
		{throw new Exception("Please Select Location.");}
		// if($txtTimeFrom == '')
		// {throw new Exception("Please Select Time From.");}
		// if($txtTimeTo == '')
		// {throw new Exception("Please Select Time To.");}
		
		if($ddlWeek == '')
		{throw new Exception("Please Select Week Day.");}
		else {
			
			if($ddlWeek == 'Monday'){
				$Week = 1;
			}
			else if($ddlWeek == 'Tuesday'){
				$Week = 2;
			}
			else if($ddlWeek == 'Wednesday'){
				$Week = 3;
			}
			else if($ddlWeek == 'Thursday'){
				$Week = 4;
			}
			else if($ddlWeek == 'Friday'){
				$Week = 5;
			}
			else if($ddlWeek == 'Saturday'){
				$Week = 6;
			}
			else if($ddlWeek == 'Sunday'){
				$Week = 7;
			}
		}

		$sql = "SELECT * FROM LOCATION_WORK_HOURS WHERE LOCID=$ddlLocation AND WDAY_NAME='$ddlWeek' AND WDAY=$Week  AND TIME_FROM='$txtTimeFrom' AND TIME_TO='$txtTimeTo' AND CLOSED=$chkClosed AND WHID!=$whid AND ISDELETED=0";
		// throw new Exception($sql);
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [LOCATION_WORK_HOURS_SP] $actionid,$whid,$ddlLocation,$Week,'$ddlWeek','$txtTimeFrom','$txtTimeTo',$chkClosed,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($whid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Working day already exists.';
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


/*============ Get Working Hours =============*/ 
 function getWorkingHours($mysqli){
	try
	{
		$query = "SELECT WHID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=LW.LOCID)[LOCATION],
		WDAY,WDAY_NAME,CONVERT(VARCHAR,TIME_FROM,100)TIME_FROM,CONVERT(VARCHAR,TIME_TO,100)TIME_TO,CLOSED
		FROM LOCATION_WORK_HOURS LW WHERE ISDELETED=0 
		ORDER BY LOCATION,WDAY";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['WHID'] = (int) $row['WHID'];
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $whid = ($_POST['whid'] == 'undefined' || $_POST['whid'] == '') ? 0 : $_POST['whid'];  

			if($whid == 0){
				throw new Exception('WHID Error.');
			}
			$queryDel ="EXEC [LOCATION_WORK_HOURS_SP] 3,$whid,0,0,'','','',0,$userid";
			$data['$queryDel']=$queryDel;
			$stmt=sqlsrv_query($mysqli, $queryDel);
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







