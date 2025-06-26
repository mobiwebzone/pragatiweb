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
        case "saveTopics":saveTopics($conn);break;
        case "getTopics":getTopics($conn);break;
        case "getProduct":getProduct($conn);break;
        case "deleteTopic":deleteTopic($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveTopics($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $topicid  = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
        $txtTopic  = $_POST['txtTopic'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTopic']);
        $txtTopicDesc  = $_POST['txtTopicDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTopicDesc']);
        $txtDisplayOrder  = ($_POST['txtDisplayOrder'] == 'undefined' || $_POST['txtDisplayOrder'] == '') ? 0 : $_POST['txtDisplayOrder'];
		$ddlObjType = $_POST['ddlObjType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlObjType']);

		$actionid = $topicid == 0 ? 1 : 2;

		// === IMAGE
		$existingPictureUpload  = $_POST['existingPictureUpload'] == 'undefined' ? '' : $_POST['existingPictureUpload'];
		$fileSize = $ddlObjType=='IMAGE' ? 1048576 : ($ddlObjType=='PDF' ? 2097152 : 20971520);
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0 && $_FILES['pictureUpload']['size'] > $fileSize) throw new Exception('File size too large.');

		$pictureUpload = '';
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0){
			$ext = pathinfo($_FILES['pictureUpload']['name'],PATHINFO_EXTENSION);
			$pictureUpload .= strtolower($ddlObjType.'_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$pictureUpload=$existingPictureUpload;
		}
		// === IMAGE

		if($ddlProduct == 0)
		{throw new Exception("Select Product.");}

		$sql = "SELECT * FROM TOPICS WHERE PRODUCTID=$ddlProduct AND TOPIC='$txtTopic' AND TOPIC_ID!=$topicid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TOPICS_SP] $actionid,$topicid,$ddlProduct,'$txtTopic','$txtTopicDesc',$txtDisplayOrder,'$ddlObjType','$pictureUpload',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{

				//========== IMAGE
				if($pictureUpload != ''){

					if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/product_topic/'.$pictureUpload);
					}

					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/product_topic/'.$existingPictureUpload))
						{
							unlink('../images/product_topic/'.$existingPictureUpload);
						}
					}
				}
				//========== IMAGE


				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($topicid))$data['message'] = 'Record successfully updated';
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


/*============ Get Topics =============*/ 
 function getTopics($mysqli){
	try
	{
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];

		$query = "SELECT TOPIC_ID,PRODUCTID,TOPIC,TOPIC_DESC,DISPLAY_ORDER,OBJECTTYPE,OBJECTNAME FROM TOPICS WHERE ISDELETED=0 AND PRODUCTID=$ddlProduct";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TOPIC_ID'] = (int) $row['TOPIC_ID'];
			$row['DISPLAY_ORDER'] = (int) $row['DISPLAY_ORDER'];
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



/*============ Get getProduct =============*/ 
 function getProduct($mysqli){
	try
	{
		$query = "SELECT PRODUCT_ID,PRODUCT,PRODUCT_DESC,DISPLAY_ORDER,DISPLAY_COLOR FROM PRODUCTS WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PRODUCT_ID'] = (int) $row['PRODUCT_ID'];
			$row['DISPLAY_ORDER'] = (int) $row['DISPLAY_ORDER'];
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
function deleteTopic($mysqli){
	try{   
		global $userid;
		$data = array();     
		$topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];  
		$stmt=sqlsrv_query($mysqli, "EXEC [TOPICS_SP] 3,$topicid,0,'','',0,'','',$userid");
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







