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
    
        $cid  = ($_POST['cid'] == 'undefined' || $_POST['cid'] == '') ? 0 : $_POST['cid'];
		$ddlDisplayType  = ($_POST['ddlDisplayType'] == 'undefined' || $_POST['ddlDisplayType'] == '') ? '' : $_POST['ddlDisplayType'];
		$txtCaption = $_POST['txtCaption'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtCaption']);
		$txtValidFrom = ($_POST['txtValidFrom'] == 'undefined' || $_POST['txtValidFrom'] == '') ? '' : $_POST['txtValidFrom'];
		$txtValidTo = ($_POST['txtValidTo'] == 'undefined' || $_POST['txtValidTo'] == '') ? '' : $_POST['txtValidTo'];
		$txtInterval = ($_POST['txtInterval'] == 'undefined' || $_POST['txtInterval'] == '') ? 0 : $_POST['txtInterval'];
		$txtInterval = ($txtInterval*1000);
		$txtSeqno = ($_POST['txtSeqno'] == 'undefined' || $_POST['txtSeqno'] == '') ? 0 : $_POST['txtSeqno'];
		$data['$txtCaption'] = $_POST['txtCaption'];
		// echo json_encode($data);exit;

		// === IMAGE
		$existingPictureUpload  = $_POST['existingPictureUpload'] == 'undefined' ? '' : $_POST['existingPictureUpload'];
		$fileSize = $ddlDisplayType=='VIDEO' ? 20971520 : 1048576;
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0 && $_FILES['pictureUpload']['size'] > $fileSize) throw new Exception('File size too large.');
		// === IMAGE
		
		$actionid = $cid == 0 ? 1 : 2;
		if($ddlDisplayType == '')throw new Exception("Please Select Display Type.");
		if($txtCaption == '')throw new Exception("Please Enter Caption.");
		if($txtValidFrom == '')throw new Exception("Please Enter Valid From.");
		if($txtValidTo == '')throw new Exception("Please Enter Valid Upto.");
		if($txtInterval == 0)throw new Exception("Please Enter Interval In Miliseconds.");
		if($txtSeqno == 0)throw new Exception("Please Enter Seq Number.");

		$sql = "SELECT * FROM CAROUSEL_MASTER WHERE SEQNO='$txtSeqno' AND CID!=$cid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [CAROUSEL_MASTER_SP] $actionid,$cid,'$ddlDisplayType','$txtCaption','$txtValidFrom','$txtValidTo',$txtInterval,$txtSeqno,$userid";
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
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['GET_CID'] = (int)$row['CID'];
				$GET_CID = (int)$row['CID'];

				//========== IMAGE
				$pictureUpload = '';
				if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0){
					$ext = pathinfo($_FILES['pictureUpload']['name'],PATHINFO_EXTENSION);
					$pictureUpload .= strtolower($ddlDisplayType.''.$GET_CID.'_slide_'.time().'.'.$ext);
				}
				else
				{
					$pictureUpload="";
				}
				
				if($pictureUpload != ''){
					if($GET_CID > 0)
					{
						$insertimage = "UPDATE CAROUSEL_MASTER SET PIC='$pictureUpload' WHERE CID=$GET_CID";
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/carousel/'.$pictureUpload);
					}
	
					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/carousel/'.$existingPictureUpload))
						{
							unlink('../images/carousel/'.$existingPictureUpload);
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
		$query = "SELECT CID,DISPLAY_TYPE,PIC,PIC_CAPTION,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
				PIC_INTERVAL,SEQNO FROM CAROUSEL_MASTER WHERE ISDELETED=0 ORDER BY SEQNO ASC";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['CID'] = (int) $row['CID'];
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
            $CID = ($_POST['CID'] == 'undefined' || $_POST['CID'] == '') ? 0 : $_POST['CID'];  
			if($CID == 0)throw new Exception('CID Error.');
			$del = "EXEC [CAROUSEL_MASTER_SP] 3,$CID,'','','','',0,0,$userid";
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







