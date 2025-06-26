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
    
        $spcid  = ($_POST['spcid'] == 'undefined' || $_POST['spcid'] == '') ? 0 : $_POST['spcid'];
        $ddlPage  = ($_POST['ddlPage'] == 'undefined' || $_POST['ddlPage'] == '') ? '' : $_POST['ddlPage'];
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
		
		$actionid = $spcid == 0 ? 1 : 2;
		if($ddlPage == '')throw new Exception("Please Select Page Name.");
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

		$sql = "SELECT * FROM STATIC_PAGE_CAROUSEL_DISPLAY WHERE SEQNO='$txtSeqno' AND PAGENAME='$ddlPage' AND SPCID!=$spcid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [STATIC_PAGE_CAROUSEL_DISPLAY_SP] $actionid,$spcid,'$ddlPage',$ddlDisplayType,'$pictureUpload','$txtCaption','$txtValidFrom','$txtValidTo',$txtInterval,$txtSeqno,$userid";
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
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/static_page_carousel/'.$pictureUpload);
					}
	
					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/static_page_carousel/'.$existingPictureUpload))
						{
							unlink('../images/static_page_carousel/'.$existingPictureUpload);
						}
					}
				}

				//========== IMAGE


				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($cid))$data['message'] = 'Carousel successfully updated';
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
		$ddlPage = ($_POST['ddlPage'] == 'undefined' || $_POST['ddlPage'] == '') ? '' : $_POST['ddlPage'];
		if($ddlPage == '') throw new Exception('Invalid Page Name.');
		$query = "SELECT SPCID,DISPLAY_TYPE,PIC,PIC_CAPTION,PAGENAME,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
				PIC_INTERVAL,SEQNO
				FROM STATIC_PAGE_CAROUSEL_DISPLAY 
				WHERE ISDELETED=0 AND PAGENAME='$ddlPage'
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SPCID = ($_POST['SPCID'] == 'undefined' || $_POST['SPCID'] == '') ? 0 : $_POST['SPCID'];  
			if($SPCID == 0)throw new Exception('SPCID Error.');
			$del = "EXEC [STATIC_PAGE_CAROUSEL_DISPLAY_SP] 3,$SPCID,'','','','','','',0,0,$userid";
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







