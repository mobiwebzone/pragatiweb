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
		// ====== CATEGORY SECTION =======
        case "saveCategoryData":saveCategoryData($conn);break;
        case "getCategory":getCategory($conn);break;
        case "delete":delete($conn);break;
		
		// ====== IMAGES SECTION =======
        case "AddImages":AddImages($conn);break;
        case "getImages":getImages($conn);break;
        case "deleteImages":deleteImages($conn);break;

		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

// ========================================================== CATEGORY SECTION ==================================================

// =============== SAVE CATEGORY DATA ==================
 function saveCategoryData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $gcatid  = ($_POST['gcatid'] == 'undefined' || $_POST['gcatid'] == '') ? 0 : $_POST['gcatid'];
        $txtCategory  = ($_POST['txtCategory'] == 'undefined' || $_POST['txtCategory'] == '') ? '' : $_POST['txtCategory'];
        $txtSEQNo  = ($_POST['txtSEQNo'] == 'undefined' || $_POST['txtSEQNo'] == '') ? 1 : $_POST['txtSEQNo'];
		if($txtCategory == ''){throw new Exception("Please Enter 'Category Name'.");}

		$actionid = $gcatid == 0 ? 1 : 2;

		$sql = "SELECT * FROM GALLERY_CATEGORIES WHERE CATEGORY='$txtCategory' AND GCATID!=$gcatid AND ISDELETED=0";
		// $data['$sql'] = $sql;
		// echo json_encode($data);exit;

		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [GALLERY_CATEGORIES_SP] $actionid,$gcatid,'$txtCategory',$txtSEQNo,$userid";
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
				$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['GET_GCATID'] = (int)$row['GCATID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($gcatid))$data['message'] = 'Category successfully updated.';
				else $data['message'] = 'Category successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Category already exists';
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
 // =============== SAVE CATEGORY DATA ==============






/*============ Get CATEGORY =============*/ 
 function getCategory($mysqli){
	try
	{
		$data = array();

		$query = "SELECT GCATID,CATEGORY,SEQNO,
				ISNULL((SELECT COUNT(*) FROM GALLERY_IMAGES WHERE GCATID=GC.GCATID AND ISDELETED=0),0)TOTAL_IMAGES
				FROM GALLERY_CATEGORIES GC WHERE ISDELETED=0 ORDER BY SEQNO,CATEGORY";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Category not found.';
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
/*============ Get CATEGORY =============*/ 







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $gcatid = ($_POST['gcatid'] == 'undefined' || $_POST['gcatid'] == '') ? 0 : $_POST['gcatid'];
			if($gcatid == 0){throw new Exception('GCATID Error.');}
			$delQuery = "EXEC [GALLERY_CATEGORIES_SP] 3,$gcatid,'',0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Category successfully deleted.';
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
// ========================================================== CATEGORY SECTION ==================================================






// ========================================================== IMAGE SECTION ==================================================

// =============== SAVE IMAGES DATA ==================
 function AddImages($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $gimgid  = ($_POST['gimgid'] == 'undefined' || $_POST['gimgid'] == '') ? 0 : $_POST['gimgid'];
        $GCATID  = ($_POST['GCATID'] == 'undefined' || $_POST['GCATID'] == '') ? 0 : $_POST['GCATID'];
        $txtIMG_Caption  = ($_POST['txtIMG_Caption'] == 'undefined' || $_POST['txtIMG_Caption'] == '') ? '' : $_POST['txtIMG_Caption'];
        $txtIMG_SEQNo  = ($_POST['txtIMG_SEQNo'] == 'undefined' || $_POST['txtIMG_SEQNo'] == '') ? 1 : $_POST['txtIMG_SEQNo'];
		
		//==== IMAGE
		$existingCatImage  = $_POST['existingCatImage'] == 'undefined' ? '' : $_POST['existingCatImage'];
		$txtCatImage = '';
		if(isset($_FILES['txtCatImage']['name']) && $_FILES['txtCatImage']['size'] > 0){
			$ext = pathinfo($_FILES['txtCatImage']['name'],PATHINFO_EXTENSION);
			$txtCatImage .= strtolower(time().'.'.$ext);
		}
		else
		{
			$txtCatImage="";
		}
		//==== IMAGE
		
		
		
		//==== CHECK VARIABLE
		if($GCATID == 0){throw new Exception("GCATID Missing.");}
		if($txtIMG_Caption == ''){throw new Exception("Please Enter 'Image Caption'.");}
		if($txtIMG_SEQNo == ''){throw new Exception("Please Enter 'SEQ No'.");}
		//==== CHECK VARIABLE

		$actionid = $gimgid == 0 ? 1 : 2;

		$sql = "SELECT * FROM GALLERY_IMAGES WHERE GCATID=$GCATID AND IMAGE_CAPTION='$txtIMG_Caption' AND GIMGID!=$gimgid AND ISDELETED=0";
		// $data['$sql'] = $sql;
		// echo json_encode($data);exit;

		$row_count = unique($sql);

		$data = array();
		$GET_GIMGID = 0;
		if($row_count == 0)
		{
			$query="EXEC [GALLERY_IMAGES_SP] $actionid,$gimgid,$GCATID,'$txtIMG_Caption',$txtIMG_SEQNo,$userid";
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
				$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_GIMGID = (int)$row['GIMGID'];

				if($txtCatImage != ''){

					if($GET_GIMGID > 0)
					{
						$insertimage = "UPDATE GALLERY_IMAGES SET [IMAGE]='$txtCatImage' WHERE GIMGID=$GET_GIMGID";
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['txtCatImage']['name']) && $_FILES['txtCatImage']['size'] > 0)
					{
						move_uploaded_file($_FILES["txtCatImage"]["tmp_name"], '../gallery_images/'.$txtCatImage);
					}

					
					if(isset($_FILES['txtCatImage']['name']) && $existingCatImage != '')
					{
						if (file_exists('../gallery_images/'.$existingCatImage))
						{
							unlink('../gallery_images/'.$existingCatImage);
						}
					}
				

				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($gimgid))$data['message'] = 'Image successfully updated.';
				else $data['message'] = 'Image successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Image already exists';
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
 // =============== SAVE CATEGORY DATA ==============






/*============ GET IMAGES =============*/ 
 function getImages($mysqli){
	try
	{
		$data = array();
		$GCATID = ($_POST['GCATID'] == 'undefined' || $_POST['GCATID'] == '') ? 0 : $_POST['GCATID'];
		if($GCATID == 0) throw new Exception("GCATID Missing.");
		$query = "SELECT GIMGID,[IMAGE],IMAGE_CAPTION,SEQNO
				FROM GALLERY_IMAGES WHERE ISDELETED=0 AND GCATID=$GCATID
				ORDER BY GIMGID DESC";
				// ORDER BY SEQNO";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Images not found.';
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
/*============ Get IMAGES =============*/ 







/* =========== Delete =========== */ 
function deleteImages($mysqli){
	try{   
			global $userid;
			$data = array();     
            $gimgid = ($_POST['gimgid'] == 'undefined' || $_POST['gimgid'] == '') ? 0 : $_POST['gimgid'];
			if($gimgid == 0){throw new Exception('GIMGID Error.');}
			$delQuery = "EXEC [GALLERY_IMAGES_SP] 3,$gimgid,0,'',0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Image successfully deleted.';
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
// ========================================================== IMAGE SECTION ==================================================

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







