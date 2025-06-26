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
        case "saveData":saveData($conn);break;
        case "getRecommendations":getRecommendations($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		


		
		$recommendid = ($_POST['recommendid'] == 'undefined' || $_POST['recommendid'] == '') ? 0 : $_POST['recommendid'];
		$txtRecommendType = $_POST['txtRecommendType'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRecommendType']);
		$txtDesc = $_POST['txtDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDesc']);
		$ddlRecommendFrom = $_POST['ddlRecommendFrom'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlRecommendFrom']);
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);

		$actionid = $recommendid == 0 ? 1 : 2;

		if($txtRecommendType == '') throw new Exception("Enter Recommend Type.");


		$sql = "SELECT * FROM RECOMMENDATION_TYPE_MASTER WHERE RECOMMENDTYPE='$txtRecommendType' AND RECOMMENDFROM='$ddlRecommendFrom' AND 
		RECOMMENDID!=$recommendid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [RECOMMENDATION_TYPE_MASTER_SP] $actionid,$recommendid,'$txtRecommendType','$txtDesc','$ddlRecommendFrom',
			'$txtComments',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($recommendid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET RECOMMENDATIONS =========== */
 function getRecommendations($mysqli){
	try
	{
		$data = array();
		$query = "SELECT RECOMMENDID,RECOMMENDTYPE,RECOMMENDDESC,RECOMMENDFROM,COMMENTS
		FROM RECOMMENDATION_TYPE_MASTER WHERE ISDELETED=0 ORDER BY RECOMMENDTYPE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['RECOMMENDID'] = (int) $row['RECOMMENDID'];
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
/* ========== GET RECOMMENDATIONS =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RECOMMENDID = ($_POST['RECOMMENDID'] == 'undefined' || $_POST['RECOMMENDID'] == '') ? 0 : $_POST['RECOMMENDID'];  
			if($RECOMMENDID == 0)throw new Exception('RECOMMENDID Not Found.');
			$delQuery = "EXEC [RECOMMENDATION_TYPE_MASTER_SP] 3,$RECOMMENDID,'','','','',$userid";
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
		$data['success'] = false . $query;
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







