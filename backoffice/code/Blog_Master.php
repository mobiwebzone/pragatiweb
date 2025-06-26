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
        case "getBlogCategories":getBlogCategories($conn);break;
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
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$bcatid = ($_POST['bcatid'] == 'undefined' || $_POST['bcatid'] == '') ? 0 : $_POST['bcatid'];
		$txtBlogCategory = $_POST['txtBlogCategory'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtBlogCategory']);
		
		$actionid = $bcatid == 0 ? 1 : 2;

		if($txtBlogCategory == ''){throw new Exception("Please Enter 'Category Name'.");}

		$sql = "SELECT * FROM BLOG_CATEGORIES WHERE BLOG_CATEGORY='$txtBlogCategory' AND BCATID!=$bcatid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			
			$query="EXEC [BLOG_CATEGORIES_SP] $actionid,$bcatid,'$txtBlogCategory',$userid";
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
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($bcatid))$data['message'] = 'Data successfully updated.';
				else $data['message'] = 'Data successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Category already exists.';
		}
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





/* ========== GET BLOG CATEGORIS =========== */
 function getBlogCategories($mysqli){
	try
	{
		$data = array();
		$query = "SELECT BCATID,BLOG_CATEGORY FROM BLOG_CATEGORIES WHERE ISDELETED=0 ORDER BY BLOG_CATEGORY";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
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
/* ========== GET BLOG CATEGORIS =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BCATID = ($_POST['BCATID'] == 'undefined' || $_POST['BCATID'] == '') ? 0 : $_POST['BCATID'];
			if($BCATID == 0){throw new Exception('BCATID Error.');}
			$delQuery = "EXEC [BLOG_CATEGORIES_SP] 3,$BCATID,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if($stmt === false) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Data successfully deleted.';
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







