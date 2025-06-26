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
        case "getScrollData":getScrollData($conn);break;
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
		global $userid;
		$data = array();

		$psid = ($_POST['psid'] == 'undefined' || $_POST['psid'] == '') ? 0 : $_POST['psid'];
		$ddlObjType = $_POST['ddlObjType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlObjType']);
		$txtTitle = $_POST['txtTitle'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTitle']);
		$txtDesc = $_POST['txtDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDesc']);
		$txtLink = $_POST['txtLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLink']);
		$txtSeqno = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
		$isActive = ($_POST['isActive'] == 'undefined' || $_POST['isActive'] == '' || $_POST['isActive'] == '0') ? 0 : 1;
		// echo json_encode($data);exit;

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
		
		$actionid = $psid == 0 ? 1 : 2;
		if($txtTitle == '')throw new Exception("Please Enter Title.");
		if($txtSeqno == 0)throw new Exception("Please Enter Seq Number.");


		$sql = "SELECT * FROM HOME_PAGE_SCROLL_MASTER WHERE SEQNO='$txtSeqno' AND PSID!=$psid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [HOME_PAGE_SCROLL_MASTER_SP] $actionid,$psid,'$ddlObjType','$pictureUpload','$txtTitle','$txtDesc','$txtLink',$txtSeqno,$isActive,$userid";
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

				//========== IMAGE
				if($pictureUpload != ''){

					if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/home_page_scroll/'.$pictureUpload);
					}

					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/home_page_scroll/'.$existingPictureUpload))
						{
							unlink('../images/home_page_scroll/'.$existingPictureUpload);
						}
					}
				}
				//========== IMAGE


				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($psid))$data['message'] = 'Data successfully updated';
				else $data['message'] = 'Data successfully inserted.';
			}
			echo json_encode($data);exit;
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Sequence Number already exists.';
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


/*============ GET DATA =============*/ 
 function getScrollData($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PSID,OBJECTTYPE,OBJECTNAME,TITLE,TITLE_DESC,LINK,SEQNO,INACTIVE FROM HOME_PAGE_SCROLL_MASTER WHERE ISDELETED=0 ORDER BY SEQNO ASC";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
/*============ GET DATA =============*/ 


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $PSID = ($_POST['PSID'] == 'undefined' || $_POST['PSID'] == '') ? 0 : $_POST['PSID'];  
			if($PSID == 0)throw new Exception('PSID Error.');
			$del = "EXEC [HOME_PAGE_SCROLL_MASTER_SP] 3,$PSID,'','','','','',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $del);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Data successfully deleted';
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







