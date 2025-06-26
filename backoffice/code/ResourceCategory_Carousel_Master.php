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
        case "getCarouselMaster":getCarouselMaster($conn);break;
        case "getResourceCategories":getResourceCategories($conn);break;
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
    
        $rccid  = ($_POST['rccid'] == 'undefined' || $_POST['rccid'] == '') ? 0 : $_POST['rccid'];
        $ddlResCat  = ($_POST['ddlResCat'] == 'undefined' || $_POST['ddlResCat'] == '') ? 0 : $_POST['ddlResCat'];
        $ddlDisplayType  = ($_POST['ddlDisplayType'] == 'undefined' || $_POST['ddlDisplayType'] == '') ? '' : $_POST['ddlDisplayType'];
		$txtCaption = $_POST['txtCaption'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCaption']);
		$txtValidFrom = ($_POST['txtValidFrom'] == 'undefined' || $_POST['txtValidFrom'] == '') ? '' : $_POST['txtValidFrom'];
		$txtValidTo = ($_POST['txtValidTo'] == 'undefined' || $_POST['txtValidTo'] == '') ? '' : $_POST['txtValidTo'];
		$txtInterval = ($_POST['txtInterval'] == 'undefined' || $_POST['txtInterval'] == '') ? 2 : $_POST['txtInterval'];
		$txtInterval = ($txtInterval*1000);
		$txtSeqno = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 1 : $_POST['txtSeqno'];
		$data['$txtCaption'] = $_POST['txtCaption'];
		// echo json_encode($data);exit;

		// === IMAGE
		$existingPictureUpload  = $_POST['existingPictureUpload'] == 'undefined' ? '' : $_POST['existingPictureUpload'];
		$fileSize = $ddlDisplayType=='VIDEO' ? 20971520 : 1048576;
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0 && $_FILES['pictureUpload']['size'] > $fileSize) throw new Exception('File size too large.');
		// === IMAGE
		
		$actionid = $rccid == 0 ? 1 : 2;
		if($ddlResCat == 0)throw new Exception("Please Select Resource Category Name.");
		if($ddlDisplayType == '')throw new Exception("Please Select Display Type.");
		if($txtCaption == '')throw new Exception("Please Enter Caption.");
		if($txtValidFrom == '')throw new Exception("Please Enter Valid From.");
		if($txtValidTo == '')throw new Exception("Please Enter Valid Upto.");
		if($txtInterval == 0)throw new Exception("Please Enter Interval In Miliseconds.");
		if($txtSeqno == 0)throw new Exception("Please Enter Seq Number.");

		$pictureUpload = '';
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0){
			$ext = pathinfo($_FILES['pictureUpload']['name'],PATHINFO_EXTENSION);
			$pictureUpload .= strtolower($ddlDisplayType.'_'.rand().'_'.time().'.'.$ext);
		}
		else{
			$pictureUpload = $existingPictureUpload;
		}

		$sql = "SELECT * FROM RESOURCE_CATEGORY_CAROUSEL_DISPLAY WHERE SEQNO='$txtSeqno' AND ID=$ddlResCat AND RCCID!=$rccid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [RESOURCE_CATEGORY_CAROUSEL_DISPLAY_SP] $actionid,$rccid,$ddlResCat,'$ddlDisplayType','$pictureUpload','$txtCaption','$txtValidFrom','$txtValidTo',$txtInterval,$txtSeqno,$userid";
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
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/resource_category_carousel/'.$pictureUpload);
					}
	
					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/resource_category_carousel/'.$existingPictureUpload))
						{
							unlink('../images/resource_category_carousel/'.$existingPictureUpload);
						}
					}
				}

				//========== IMAGE


				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($rccid))$data['message'] = 'Carousel successfully updated';
				else $data['message'] = 'Carousel successfully inserted.';
			}
			echo json_encode($data);exit;
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Seq. Number already exists.';
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


/*============ GET CAROUSEL MASTER =============*/ 
 function getCarouselMaster($mysqli){
	try
	{
		$data = array();
		$ddlResCat = ($_POST['ddlResCat'] == 'undefined' || $_POST['ddlResCat'] == '') ? 0 : $_POST['ddlResCat'];
		if($ddlResCat == 0) throw new Exception('Invalid Resource ID.');
		$query = "SELECT RCCID,DISPLAY_TYPE,PIC,PIC_CAPTION,ID,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
				PIC_INTERVAL,SEQNO
				FROM RESOURCE_CATEGORY_CAROUSEL_DISPLAY 
				WHERE ISDELETED=0 AND ID=$ddlResCat 
				ORDER BY SEQNO ASC";
		$data['query'] = $query;
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PIC_INTERVAL'] = ($row['PIC_INTERVAL']/1000);
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
/*============ GET CAROUSEL MASTER =============*/ 


/*============ GET RESOURCE CATEGORIES =============*/ 
 function getResourceCategories($mysqli){
	try
	{
		$data = array();
		$query = "SELECT ID,RESOURCE_CATEGORY_TEXT
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category' AND UNDER_ID=0 ORDER BY SEQNO";
		$data['query'] = $query;
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
/*============ GET RESOURCE CATEGORIES =============*/ 


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RCCID = ($_POST['RCCID'] == 'undefined' || $_POST['RCCID'] == '') ? 0 : $_POST['RCCID'];  
			if($RCCID == 0)throw new Exception('RCCID Error.');
			$del = "EXEC [RESOURCE_CATEGORY_CAROUSEL_DISPLAY_SP] 3,$RCCID,0,'','','','','',0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $del);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Carousel successfully deleted.';
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







