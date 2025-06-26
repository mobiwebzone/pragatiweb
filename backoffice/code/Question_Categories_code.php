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
		case "login":login($conn);break;

		// ========= CATEGORY =========
        case "saveDataCat":saveDataCat($conn);break;
        case "getCategories":getCategories($conn);break;
        case "deleteCat":deleteCat($conn);break;
		// ========= CATEGORY =========


		// ========= SUB CATEGORY =========
		case "saveDataSubCat":saveDataSubCat($conn);break;
        case "getSubCategories":getSubCategories($conn);break;
        case "deleteSubCat":deleteSubCat($conn);break;
		// ========= SUB CATEGORY =========
		
		
		// ========= TOPIC =========
		case "saveDataTopic":saveDataTopic($conn);break;
        case "getTopic":getTopic($conn);break;
        case "deleteTopic":deleteTopic($conn);break;
		// ========= TOPIC =========
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// ##################################################### CATEGORY SECTION START #####################################################
	// =============== SAVE DATA ==================
	function saveDataCat($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$catid  = ($_POST['catid'] == 'undefined' || $_POST['catid'] == '') ? 0 : $_POST['catid'];
			$ddlSection  = ($_POST['ddlSection'] == 'undefined' || $_POST['ddlSection'] == '') ? 0 : $_POST['ddlSection'];
			$txtCategory  = $_POST['txtCategory'] == 'undefined' ? '' : $_POST['txtCategory'];
			
			$actionid = $catid == 0 ? 1 : 2;
	
			if($ddlSection == 0){throw new Exception("SectionId Not Found.");}
			if($txtCategory == ''){throw new Exception("Please Enter Category Name.");}
			$txtCategory = str_replace("'","''",$txtCategory);
	
			$sql = "SELECT * FROM SECTION_CATEGORIES WHERE SECID=$ddlSection AND CATEGORY='$txtCategory' AND CATID!=$catid AND ISDELETED=0";
			$row_count = unique($sql);
	
			$data = array();
			if($row_count == 0)
			{
				$queryCat="EXEC [SECTION_CATEGORIES_SP] $actionid,$catid,$ddlSection,'$txtCategory',$userid";
				$stmtCat=sqlsrv_query($mysqli, $queryCat);
				
				if($stmtCat === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $queryCat );
					$data['success'] = false;
					$data['queryCat'] = $queryCat;
				}
				else
				{
					$row = sqlsrv_fetch_array($stmtCat);
					$data['CATID'] = $row['CATID'];
					$data['queryCat'] = $queryCat;
					$data['success'] = true;
					if(!empty($catid))$data['message'] = 'Record successfully updated';
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
	// =============== SAVE DATA ==============
	
	
	
	/*============ Get CATEGORIES =============*/ 
	function getCategories($mysqli){
		try
		{	
			$data = array();

			$secid = ($_POST['secid'] == 'undefined' || $_POST['secid'] == '') ? 0 : $_POST['secid'];
			if($secid == 0){throw new Exception('Secid error.');}

			$query = "SELECT CATID,SECID,(SELECT SECTION FROM SECTION_MASTER WHERE SECID=SC.SECID AND ISDELETED=0)SECTION,CATEGORY 
					FROM SECTION_CATEGORIES SC WHERE ISDELETED=0 AND SECID=$secid ORDER BY CATEGORY";
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
	/*============ Get CATEGORIES =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteCat($mysqli){
		try{   
				global $userid;
				$data = array();     
				$catid = ($_POST['catid'] == 'undefined' || $_POST['catid'] == '') ? 0 : $_POST['catid'];
				if($catid == 0){throw new Exception('catid Error.');}
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SECTION_CATEGORIES_SP] 3,$catid,0,'',$userid");
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
// ##################################################### CATEGORY SECTION END #####################################################










// ##################################################### SUB CATEGORY SECTION START #####################################################
	// =============== SAVE DATA ==================
	function saveDataSubCat($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$subcatid  = ($_POST['subcatid'] == 'undefined' || $_POST['subcatid'] == '') ? 0 : $_POST['subcatid'];
			$GetCatid  = ($_POST['GetCatid'] == 'undefined' || $_POST['GetCatid'] == '') ? 0 : $_POST['GetCatid'];
			$txtSubCategory  = $_POST['txtSubCategory'] == 'undefined' ? '' : $_POST['txtSubCategory'];
			
			$actionid = $subcatid == 0 ? 1 : 2;
			
			if($GetCatid == 0){throw new Exception("Catid Not Found.");}
			if($txtSubCategory == ''){throw new Exception("Please Enter Sub Category Name.");}
			$txtSubCategory = str_replace("'","''",$txtSubCategory);
	
			$sql = "SELECT * FROM SECTION_SUB_CATEGORIES WHERE CATID=$GetCatid AND SUBCATEGORY='$txtSubCategory' AND SUBCATID!=$subcatid AND ISDELETED=0";
			$row_count = unique($sql);
	
			$data = array();
			if($row_count == 0)
			{
				$querySubCat="EXEC [SECTION_SUB_CATEGORIES_SP] $actionid,$subcatid,$GetCatid,'$txtSubCategory',$userid";
				$stmtSubCat=sqlsrv_query($mysqli, $querySubCat);
				
				if($stmtSubCat === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $querySubCat );
					$data['success'] = false;
					$data['querySubCat'] = $querySubCat;
				}
				else
				{
					$row = sqlsrv_fetch_array($stmtSubCat);
					$data['SUBCATID'] = $row['SUBCATID'];

					$data['querySubCat'] = $querySubCat;
					$data['success'] = true;
					if(!empty($subcatid))$data['message'] = 'Record successfully updated';
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
	// =============== SAVE DATA ==============
	
	
	
	/*============ Get SUBCATEGORIES =============*/ 
	function getSubCategories($mysqli){
		try
		{	
			$data = array();

			$catid = ($_POST['catid'] == 'undefined' || $_POST['catid'] == '') ? 0 : $_POST['catid'];
			if($catid == 0){throw new Exception('Catid error.');}

			$query = "SELECT SUBCATID,CATID,(SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=SSC.CATID AND ISDELETED=0)CATEGORY,SUBCATEGORY 
					FROM SECTION_SUB_CATEGORIES SSC WHERE ISDELETED=0 AND CATID=$catid ORDER BY SUBCATEGORY";
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
	/*============ Get SUBCATEGORIES =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteSubCat($mysqli){
		try{   
				global $userid;
				$data = array();     
				$subcatid = ($_POST['subcatid'] == 'undefined' || $_POST['subcatid'] == '') ? 0 : $_POST['subcatid'];
				if($subcatid == 0){throw new Exception('Subcatid Error.');}
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SECTION_SUB_CATEGORIES_SP] 3,$subcatid,0,'',$userid");
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
// ##################################################### SUB CATEGORY SECTION END #####################################################









// ##################################################### TOPIC SECTION START #####################################################
	// =============== SAVE DATA ==================
	function saveDataTopic($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$topicid  = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
			$GetSubCatid  = ($_POST['GetSubCatid'] == 'undefined' || $_POST['GetSubCatid'] == '') ? 0 : $_POST['GetSubCatid'];
			$txtTopic  = $_POST['txtTopic'] == 'undefined' ? '' : $_POST['txtTopic'];
			
			$actionid = $topicid == 0 ? 1 : 2;
	
			if($GetSubCatid == 0){throw new Exception("SubCatid Not Found.");}
			if($txtTopic == ''){throw new Exception("Please Enter Topic Name.");}
			$txtTopic = str_replace("'","''",$txtTopic);
	
			$sql = "SELECT * FROM SECTION_TOPICS WHERE SUBCATID=$GetSubCatid AND TOPIC='$txtTopic' AND TOPICID!=$topicid AND ISDELETED=0";
			$row_count = unique($sql);
	
			$data = array();
			if($row_count == 0)
			{
				$queryTopic="EXEC [SECTION_TOPICS_SP] $actionid,$topicid,$GetSubCatid,'$txtTopic',$userid";
				$stmtTopic=sqlsrv_query($mysqli, $queryTopic);
				
				if($stmtTopic === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $queryTopic );
					$data['success'] = false;
					$data['queryTopic'] = $queryTopic;
				}
				else
				{
					$data['queryTopic'] = $queryTopic;
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
	// =============== SAVE DATA ==============
	
	
	
	/*============ Get TOPICS =============*/ 
	function getTopic($mysqli){
		try
		{	
			$data = array();

			$subcatid = ($_POST['subcatid'] == 'undefined' || $_POST['subcatid'] == '') ? 0 : $_POST['subcatid'];
			if($subcatid == 0){throw new Exception('SubCatId error.');}

			$query = "SELECT TOPICID,SUBCATID,(SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=ST.SUBCATID AND ISDELETED=0)SUBCATEGORY,TOPIC
					FROM SECTION_TOPICS ST WHERE ISDELETED=0 AND SUBCATID=$subcatid ORDER BY TOPIC";
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
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
	/*============ Get TOPICS =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteTopic($mysqli){
		try{   
				global $userid;
				$data = array();     
				$topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
				if($topicid == 0){throw new Exception('Topicid Error.');}
	
				$stmt=sqlsrv_query($mysqli, "EXEC [SECTION_TOPICS_SP] 3,$topicid,0,'',$userid");
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
// ##################################################### TOPIC SECTION END #####################################################





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







