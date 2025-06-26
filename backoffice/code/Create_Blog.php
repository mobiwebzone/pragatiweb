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
        case "getBlog":getBlog($conn);break;
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

		$blogid = ($_POST['blogid'] == 'undefined' || $_POST['blogid'] == '') ? 0 : $_POST['blogid'];
		$ddlBlogCategory = ($_POST['ddlBlogCategory'] == 'undefined' || $_POST['ddlBlogCategory'] == '') ? 0 : $_POST['ddlBlogCategory'];
		$txtPostingDT = $_POST['txtPostingDT'] == 'undefined' ? '' : $_POST['txtPostingDT'];
		$txtTopic = $_POST['txtTopic'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTopic']);
		$txtTags = $_POST['txtTags'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtTags']);
		$txtBlog = $_POST['txtBlog'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtBlog']);
		
		$actionid = $blogid == 0 ? 1 : 2;

		if($ddlBlogCategory == ''){throw new Exception("Please Select 'Blog Category'.");}
		if($txtPostingDT == ''){throw new Exception("Please Select 'Posting Date'.");}
		if($txtTopic == ''){throw new Exception("Please Enter 'Topic'.");}
		if($txtBlog == ''){throw new Exception("Please Enter 'Blog Content'.");}


		$query="EXEC [BLOGS_SP] $actionid,$blogid,$ddlBlogCategory,'$txtPostingDT','$txtTopic','$txtTags','$txtBlog',$userid";
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
			if(!empty($blogid))$data['message'] = 'Data successfully updated.';
			else $data['message'] = 'Data successfully inserted.';
			echo json_encode($data);exit;
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





/* ========== GET BLOGS =========== */
 function getBlog($mysqli){
	try
	{
		$data = array();
		$query = "SELECT BLOGID,BCATID,(SELECT BLOG_CATEGORY FROM BLOG_CATEGORIES WHERE BCATID=BLOGS.BCATID)BLOG_CATEGORY,
		CONVERT(VARCHAR,POSTING_DATE,106)POSTING_DATE_SET, CONVERT(VARCHAR,POSTING_DATE,20)POSTING_DATE,TOPIC,TAGS,BLOG
		FROM BLOGS WHERE ISDELETED=0 ORDER BY CONVERT(DATE,POSTING_DATE,105) DESC";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$date=date_create($row['POSTING_DATE']);
				$row['POSTING_DATE'] = date_format($date,"d-m-Y h:i A");
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
/* ========== GET BLOGS =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BLOGID = ($_POST['BLOGID'] == 'undefined' || $_POST['BLOGID'] == '') ? 0 : $_POST['BLOGID'];
			if($BLOGID == 0){throw new Exception('BLOGID Error.');}
			$delQuery = "EXEC [BLOGS_SP] 3,$BLOGID,0,'','','','',$userid";
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







