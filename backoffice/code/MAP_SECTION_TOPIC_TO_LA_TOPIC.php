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
        case "saveLaTopics":saveLaTopics($conn);break;
        case "getMappedData":getMappedData($conn);break;
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



// =============== SAVE DATA ==================
 function saveLaTopics($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $ddlTopic  = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
        $selectedLATopics  = (!isset($_POST['selectedLATopics'])) ? array() : explode(',',$_POST['selectedLATopics']);

		$data['selectedLATopics']=$selectedLATopics;
		// echo json_encode($data);exit;


		if($ddlTopic == 0){throw new Exception("Please Select 'Section Topic'.");}
		if(count($selectedLATopics)==0){throw new Exception("Please Select 'La Topics'.");}

		for ($i=0; $i < count($selectedLATopics); $i++) {
			$LA_TID = $selectedLATopics[$i];
			$query="INSERT INTO SECTION_TOPICS_LA_TOPICS (SECTION_TOPICID,LA_TOPICID,INSERTID)
					VALUES ($ddlTopic,$LA_TID,$userid)";
			$stmt=sqlsrv_query($mysqli, $query);

			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
		}


		$data['query'] = $query;
		$data['success'] = true;
		$data['message'] = 'Record successfully inserted.';
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
 // =============== SAVE DATA ==============





/*============ GET RUBERIC DATA =============*/ 
 function getMappedData($mysqli){
	try
	{
		$data = array();
		$SECID = ($_POST['SECID'] == 'undefined' || $_POST['SECID'] == '') ? 0 : $_POST['SECID'];
		$CATID = ($_POST['CATID'] == 'undefined' || $_POST['CATID'] == '') ? 0 : $_POST['CATID'];
		$SUBCATID = ($_POST['SUBCATID'] == 'undefined' || $_POST['SUBCATID'] == '') ? 0 : $_POST['SUBCATID'];
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];

		$query = "EXEC [GET_SECTION_TOPICS_LA_TOPICS] $SECID,$CATID,$SUBCATID,$TOPICID";
		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Data not found.';
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
/*============ GET RUBERIC DATA =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STLATID = ($_POST['STLATID'] == 'undefined' || $_POST['STLATID'] == '') ? 0 : $_POST['STLATID'];
			if($STLATID == 0){throw new Exception('Invalid STLATID.');}
			$delQuery = "UPDATE SECTION_TOPICS_LA_TOPICS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE STLATID=$STLATID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







