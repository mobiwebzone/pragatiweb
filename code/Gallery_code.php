<?php
session_start();
require_once 'connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getMainCat":getMainCat($conn);break;
        case "getImages":getImages($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




/*============ Get Free Resource Main category =============*/ 
function getMainCat($mysqli){
	try
	{
		$data = array();
		$query = "SELECT GCATID,CATEGORY,SEQNO FROM GALLERY_CATEGORIES WHERE ISDELETED=0 ORDER BY SEQNO,CATEGORY";
		$result = sqlsrv_query($mysqli, $query);
		$COUNT = unique($query);
		if($COUNT > 0){
			$CINDEX=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['GCATID'] = (int) $row['GCATID'];
				$GCATID = (int) $row['GCATID'];
				$data['data'][] = $row;
				
				//====== IMAGES
				$IMAGES = array();
				$queryImages = "SELECT GIMGID,GCATID,[IMAGE],IMAGE_CAPTION,SEQNO FROM GALLERY_IMAGES WHERE ISDELETED=0 AND GCATID=$GCATID ORDER BY SEQNO";
				$resultImages = sqlsrv_query($mysqli, $queryImages);
				$countImages = unique($queryImages);

				if($countImages > 0){
					while ($rowImages = sqlsrv_fetch_array($resultImages, SQLSRV_FETCH_ASSOC)) {
						$rowImages['GIMGID'] = (int) $rowImages['GIMGID'];
						$Images = $rowImages;
						$data['data'][$CINDEX]['IMAGES'][] = $Images;
					}
					// $data['ListField'] = $PRINT_RESOURCE;
					$data['success'] = true;
				}else{
					$data['success'] = false;
					$data['message'] = 'Images Not Found.';
				}
				$CINDEX++;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Category Not Found.';
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
 /*============ Get Free Resource Main category =============*/ 


 

/*============ GET IMAGES =============*/ 
function getImages($mysqli){
	try
	{
		global $PRINT_RESOURCE;
		
		$GCATID = ($_POST['GCATID'] == 'undefined' || $_POST['GCATID'] == '') ? 0 : $_POST['GCATID'];
		if($GCATID == 0){
			throw new Exception("error");
		}
		$query = "SELECT GIMGID,[IMAGE],IMAGE_CAPTION,SEQNO FROM GALLERY_IMAGES WHERE ISDELETED=0 AND GCATID=$GCATID ORDER BY SEQNO";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);

		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['GIMGID'] = (int) $row['GIMGID'];
				$data['data'][] = $row;
				// $varid=(int) $row['ID'];
				// $PRINT_RESOURCE.='<h3>'.$mainCat.'</h3>';
				// $PRINT_RESOURCE.='<ul class="list-group"><li class="list-group-item" ng-init="test()" >'.$row['RESOURCE_CATEGORY_TEXT'].'</li></ul>';
				
			
			}
			// $data['ListField'] = $PRINT_RESOURCE;
			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Images Not Found.';
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
/*============ GET IMAGES =============*/ 











function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







