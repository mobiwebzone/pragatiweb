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
        case "getScrolls":getScrolls($conn);break;
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

		$infoid = ($_POST['infoid'] == 'undefined' || $_POST['infoid'] == '') ? 0 : $_POST['infoid'];
		$ddlDivNumber = ($_POST['ddlDivNumber'] == 'undefined' || $_POST['ddlDivNumber'] == '') ? 0 : $_POST['ddlDivNumber'];
		$txtInfo = $_POST['txtInfo'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtInfo']);
		$txtInfoLink = $_POST['txtInfoLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtInfoLink']);
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$txtSEQNo = ($_POST['txtSEQNo'] == 'undefined' || $_POST['txtSEQNo'] == '') ? 1 : $_POST['txtSEQNo'];
		$txtColor = $_POST['txtColor'] == 'undefined' ? '#000000' : $_POST['txtColor'];
		$isHeader  = ($_POST['isHeader'] == 'undefined' || $_POST['isHeader'] == '' || $_POST['isHeader'] == '0') ? 0 : 1;
    	$txtHeader  = ($_POST['txtHeader'] == 'undefined' || $_POST['txtHeader'] == 'null') ? '' : str_replace("'","''",$_POST['txtHeader']);

		$actionid = $infoid == 0 ? 1 : 2;

		if($ddlDivNumber == 0) throw new Exception('Select Division Number.');
		if($txtInfo == '') throw new Exception('Enter Scoll Info.');
		if($txtFromDT == '') throw new Exception('Select From Date.');
		if($txtToDT == '') throw new Exception('Select To Date.');

		$sql = "SELECT * FROM INDEX_INFO_SCROLL WHERE DIV_NO=$ddlDivNumber AND INFO='$txtInfo' AND INFO_LINK='$txtInfoLink' 
		AND CONVERT(DATE,DISPLAY_FROM,105)='$txtFromDT' AND CONVERT(DATE,DISPLAY_TO,105)='$txtToDT' 
		AND INFOID!=$infoid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [INDEX_INFO_SCROLL_SP] $actionid,$infoid,$ddlDivNumber,'$txtInfo','$txtInfoLink','$txtFromDT','$txtToDT',
			$txtSEQNo,'$txtColor',$isHeader,'$txtHeader',$userid";
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
				if(!empty($infoid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Data already exists.';
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


/*============ Get Scrolls =============*/ 
 function getScrolls($mysqli){
	try
	{
		$data = array();
		$ddlDivNumber = ($_POST['ddlDivNumber'] == 'undefined' || $_POST['ddlDivNumber'] == '') ? 0 : $_POST['ddlDivNumber'];

		$query = "SELECT INFOID,DIV_NO,INFO,INFO_LINK,
		CONVERT(VARCHAR,DISPLAY_FROM,106)DISPLAY_FROM,
		CONVERT(VARCHAR,DISPLAY_TO,106)DISPLAY_TO,SEQNO,COLOR,ISHEADER,HEADER 
		FROM INDEX_INFO_SCROLL WHERE ISDELETED=0";
		if($ddlDivNumber > 0) $query .="  AND DIV_NO=$ddlDivNumber";
		$query .=" ORDER BY DIV_NO,SEQNO";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ISHEADER'] = (string) $row['ISHEADER'];
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
		$INFOID = ($_POST['INFOID'] == 'undefined' || $_POST['INFOID'] == '') ? 0 : $_POST['INFOID'];
		$query="EXEC [INDEX_INFO_SCROLL_SP] 3,$INFOID,0,'','','','',0,'',0,'',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
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







