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
        case "getAnnouncements":getAnnouncements($conn);break;
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
    
        $anid  = $_POST['anid'] == 'undefined' ? 0 : $_POST['anid'];
        $txtAMDate  = $_POST['txtAMDate'] == 'undefined' ? '' : $_POST['txtAMDate'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$txtAnnouncement = $_POST['txtAnnouncement'] == 'undefined' ? '' : $_POST['txtAnnouncement'];
		$chkShowInDB  = ($_POST['chkShowInDB'] == 'false' || $_POST['chkShowInDB'] == '') ? 0 : 1;
        $txtTillDate  = $_POST['txtTillDate'] == 'undefined' ? '' : $_POST['txtTillDate'];

		$actionid = $anid == 0 ? 1 : 2;

		if($txtAMDate == '')
		{throw new Exception("Please Select Announcement Date.");}
		if($txtAnnouncement == '')
		{throw new Exception("Please Enter Announcement.");}
		if($chkShowInDB == 1 && $txtTillDate == ''){
			{throw new Exception("Please Enter Till Date.");}
		}

		// $sql = "SELECT * FROM ANNOUNCEMENTS WHERE FORYEAR=$txtForYear AND HDATE='$txtDate' AND HOCCASSION LIKE '%$txtOccassion%' AND HMID!=$hmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [ANNOUNCEMENTS_SP] $actionid,$anid,'$txtAMDate',$ddlLocation,$ddlPlan,'$txtAnnouncement',$chkShowInDB,'$txtTillDate',$userid";
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
				if(!empty($anid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Holiday already exists.';
		// 	echo json_encode($data);exit;
		// }

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


/*============ Get Announcements =============*/ 
 function getAnnouncements($mysqli){
	try
	{
		$query = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,LOCID,
			(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=A.LOCID)[LOCATION],PLANID,
			(SELECT PLANNAME FROM PLANS WHERE PLANID=A.PLANID)[PLAN],
			ANNOUNCEMENT,DB_ANNOUNCE,CONVERT(VARCHAR,DB_ANNOUNCE_TILLDATE,106)DB_ANNOUNCE_TILLDATE 
			FROM ANNOUNCEMENTS A WHERE ISDELETED=0 ORDER BY CONVERT(DATE,ANDATE,105) DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ANID'] = (int) $row['ANID'];
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
            $anid = $_POST['anid'] == 'undefined' ? 0 : $_POST['anid'];
			$stmt=sqlsrv_query($mysqli, "EXEC [ANNOUNCEMENTS_SP] 3,$anid,'',0,0,'',0,'',$userid");
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







