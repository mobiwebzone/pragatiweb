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
        case "getMeetingLinks":getMeetingLinks($conn);break;
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
        $mtid  = ($_POST['mtid'] == 'undefined' || $_POST['mtid'] == '') ? 0 : $_POST['mtid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
        $txtMeetingID  = $_POST['txtMeetingID'] == 'undefined' ? '' : $_POST['txtMeetingID'];
        $txtMeetingLink  = $_POST['txtMeetingLink'] == 'undefined' ? '' : $_POST['txtMeetingLink'];
		$txtMeetingPasscode = ($_POST['txtMeetingPasscode'] == 'undefined' || $_POST['txtMeetingPasscode'] == '') ? '' : $_POST['txtMeetingPasscode'];
		$txtEmailID = $_POST['txtEmailID'] == 'undefined' ? '' : $_POST['txtEmailID'];
		$txtEmailPassword = $_POST['txtEmailPassword'] == 'undefined' ? '' : $_POST['txtEmailPassword'];
		
		$actionid = $mtid == 0 ? 1 : 2;
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($ddlPlan == 0)
		{throw new Exception("Please Select Plan Name.");}
		
		$sql = "SELECT * FROM ONLINE_MEETINGS_LINKS WHERE LOCID=$ddlLocation AND PLANID=$ddlPlan AND MEETINGID='$txtMeetingID' AND MTID!=$mtid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [ONLINE_MEETINGS_LINKS_SP] $actionid,$mtid,$ddlLocation,$ddlPlan,'$txtMeetingID','$txtMeetingLink','$txtMeetingPasscode','$txtEmailID','$txtEmailPassword',$userid";
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
				if(!empty($mtid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Meeting ID already exists.';
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


/*============ Get MEETING LINKS =============*/ 
 function getMeetingLinks($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT MTID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=OML.LOCID)[LOCATION],
		PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=OML.PLANID) [PLAN],
		MEETINGID,MEETINGLINK,MPASSCODE,EMAILID,EPASSCODE FROM ONLINE_MEETINGS_LINKS OML WHERE ISDELETED=0 AND LOCID=$ddlLocation";
		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MTID'] = (int) $row['MTID'];
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
            $mtid = ($_POST['mtid'] == 'undefined' || $_POST['mtid'] == '') ? 0 : $_POST['mtid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [ONLINE_MEETINGS_LINKS_SP] 3,$mtid,0,0,'','','','','',$userid");
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







