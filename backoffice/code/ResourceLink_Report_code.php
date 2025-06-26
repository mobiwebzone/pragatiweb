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
        case "getVoting":getVoting($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Voting =============*/ 
 function getVoting($mysqli){
	try
	{
		$query = "SELECT SEQNO,RESLID,RESID,
		(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)PRODUCT_ID,
		(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=(SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID))PRODUCT,
		(SELECT RESOURCEID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_NAME,
		(SELECT RESOURCE_DESC FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID)RESOURCE_DESC,
		RESOURCE_LINK,
		(SELECT COUNT(UP) FROM TOPIC_RESOURCES_LINKS_VOTE WHERE RESLID=TRL.RESLID AND UP=1)THUMBSUP,
		(SELECT COUNT(DOWN) FROM TOPIC_RESOURCES_LINKS_VOTE WHERE RESLID=TRL.RESLID AND DOWN=1)THUMBSDOWN
		FROM TOPIC_RESOURCES_LINKS TRL WHERE ISDELETED=0
		ORDER BY (SELECT PRODUCT_ID FROM TOPIC_RESOURCES WHERE RESID=TRL.RESID),RESID,SEQNO";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







