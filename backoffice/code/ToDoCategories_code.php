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
        case "saveCategory":saveCategory($conn);break;
        case "getCategories":getCategories($conn);break;
        case "deleteCat":deleteCat($conn);break;
		// ========= CATEGORY =========


		// ========= SUB CATEGORY =========
		case "saveSubCategory":saveSubCategory($conn);break;
        case "getSubCategories":getSubCategories($conn);break;
        case "deleteSubCat":deleteSubCat($conn);break;
		// ========= SUB CATEGORY =========
		
		
		// ========= SUB SUBCATEGORY =========
		case "saveSSubCategory":saveSSubCategory($conn);break;
        case "getSSubCategories":getSSubCategories($conn);break;
        case "deleteSSubCat":deleteSSubCat($conn);break;
		// ========= SUB SUBCATEGORY =========
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
	function saveCategory($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$tdcatid  = ($_POST['tdcatid'] == 'undefined' || $_POST['tdcatid'] == '') ? 0 : $_POST['tdcatid'];
			$txtCategory  = $_POST['txtCategory'] == 'undefined' ? '' : $_POST['txtCategory'];
			$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
			
			$actionid = $tdcatid == 0 ? 1 : 2;
	
			if($txtCategory == ''){throw new Exception("Please Enter Category Name.");}
			$txtCategory = str_replace("'","''",$txtCategory);
			if($ddlLocation == 0) throw new Exception("Please Select Location Name.");
	
			$sql = "SELECT * FROM TD_CATEGORIES WHERE LOCID=$ddlLocation AND CATEGORY='$txtCategory' AND TDCATID!=$tdcatid AND ISDELETED=0";
			$row_count = unique($sql);
			
			$data = array();
			if($row_count == 0)
			{
				$queryCat="EXEC [TD_CATEGORIES_SP] $actionid,$tdcatid,$ddlLocation,'$txtCategory',$userid";
				$data['queryCat'] = $queryCat;
				// echo json_encode($data);exit;
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
					$data['TDCATID'] = $row['TDCATID'];
					$data['success'] = true;
					if(!empty($tdcatid))$data['message'] = 'Category successfully updated.';
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
	// =============== SAVE DATA ==============
	
	
	
	/*============ GET CATEGORIES =============*/ 
	function getCategories($mysqli){
		try
		{	
			$data = array();
			$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
			$query = "SELECT TDCATID,CATEGORY FROM TD_CATEGORIES WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY CATEGORY";
			$result = sqlsrv_query($mysqli, $query);
			$count = unique($query);
			if($count > 0){
				while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
					$data['data'][] = $row;
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
	/*============ GET CATEGORIES =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteCat($mysqli){
		try{   
				global $userid;
				$data = array();     
				$tdcatid = ($_POST['tdcatid'] == 'undefined' || $_POST['tdcatid'] == '') ? 0 : $_POST['tdcatid'];
				if($tdcatid == 0){throw new Exception('tdcatid Error.');}

				$stmt=sqlsrv_query($mysqli, "EXEC [TD_CATEGORIES_SP] 3,$tdcatid,0,'',$userid");
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
// ##################################################### CATEGORY SECTION END #####################################################










// ##################################################### SUB CATEGORY SECTION START #####################################################
	// =============== SAVE DATA ==================
	function saveSubCategory($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$tdsubcatid  = ($_POST['tdsubcatid'] == 'undefined' || $_POST['tdsubcatid'] == '') ? 0 : $_POST['tdsubcatid'];
			$GetTDCatid  = ($_POST['GetTDCatid'] == 'undefined' || $_POST['GetTDCatid'] == '') ? 0 : $_POST['GetTDCatid'];
			$txtSubCategory  = $_POST['txtSubCategory'] == 'undefined' ? '' : $_POST['txtSubCategory'];
			
			$actionid = $tdsubcatid == 0 ? 1 : 2;
			
			if($GetTDCatid == 0){throw new Exception("TDCatid Not Found.");}
			if($txtSubCategory == ''){throw new Exception("Please Enter Sub Category Name.");}
			$txtSubCategory = str_replace("'","''",$txtSubCategory);
	
			$sql = "SELECT * FROM TD_SUBCATEGORIES WHERE TDCATID=$GetTDCatid AND SUBCATEGORY='$txtSubCategory' AND TDSUBCATID!=$tdsubcatid AND ISDELETED=0";
			$row_count = unique($sql);
	
			$data = array();
			if($row_count == 0)
			{
				$querySubCat="EXEC [TD_SUBCATEGORIES_SP] $actionid,$tdsubcatid,$GetTDCatid,'$txtSubCategory',$userid";
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
					$data['TDSUBCATID'] = $row['TDSUBCATID'];

					$data['querySubCat'] = $querySubCat;
					$data['success'] = true;
					if(!empty($tdsubcatid))$data['message'] = 'SubCategory successfully updated.';
					else $data['message'] = 'SubCategory successfully inserted.';
					echo json_encode($data);exit;
				}
				
			}
			else
			{
				$data['success'] = false;
				$data['message'] = 'SubCategory already exists';
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
	
	
	
	/*============ GET SUBCATEGORIES =============*/ 
	function getSubCategories($mysqli){
		try
		{	
			$data = array();
			$tdcatid = ($_POST['tdcatid'] == 'undefined' || $_POST['tdcatid'] == '') ? 0 : $_POST['tdcatid'];
			if($tdcatid == 0){throw new Exception('tdcatid error.');}

			$query = "SELECT TDSUBCATID,TDCATID,(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=TS.TDCATID AND ISDELETED=0)CATEGORY,SUBCATEGORY 
					FROM TD_SUBCATEGORIES TS WHERE ISDELETED=0 AND TDCATID=$tdcatid ORDER BY SUBCATEGORY";
			$result = sqlsrv_query($mysqli, $query);
			$count = unique($query);
			if($count > 0){
				while ($row = sqlsrv_fetch_array($result)) {
					$data['data'][] = $row;
				}
				$data['success'] = true;
			}else{
				$data['success'] = false;
				$data['message'] = 'SubCategory Not Found.';
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
	/*============ GET SUBCATEGORIES =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteSubCat($mysqli){
		try{   
				global $userid;
				$data = array();     
				$tdsubcatid = ($_POST['tdsubcatid'] == 'undefined' || $_POST['tdsubcatid'] == '') ? 0 : $_POST['tdsubcatid'];
				if($tdsubcatid == 0){throw new Exception('TDSubcatid Error.');}
	
				$stmt=sqlsrv_query($mysqli, "EXEC [TD_SUBCATEGORIES_SP] 3,$tdsubcatid,0,'',$userid");
				if( $stmt === false ) 
				{
					die( print_r( sqlsrv_errors(), true));
					throw new Exception( $mysqli->sqlstate );
				}
				else
				{
					$data['success'] = true;
					$data['message'] = 'SubCategory successfully deleted.';
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









// ##################################################### SUB SUBCATEGORY SECTION START #####################################################
	// =============== SAVE DATA ==================
	function saveSSubCategory($mysqli){
		try
		{
			$data = array();
			global $userid;
		
			$tdssubcatid  = ($_POST['tdssubcatid'] == 'undefined' || $_POST['tdssubcatid'] == '') ? 0 : $_POST['tdssubcatid'];
			$GetTDSubCatid  = ($_POST['GetTDSubCatid'] == 'undefined' || $_POST['GetTDSubCatid'] == '') ? 0 : $_POST['GetTDSubCatid'];
			$txtSSubCategory  = $_POST['txtSSubCategory'] == 'undefined' ? '' : $_POST['txtSSubCategory'];
			
			$actionid = $tdssubcatid == 0 ? 1 : 2;
	
			if($GetTDSubCatid == 0){throw new Exception("TDSSubCatid Not Found.");}
			if($txtSSubCategory == ''){throw new Exception("Please Enter Sub SubCategory Name.");}
			$txtSSubCategory = str_replace("'","''",$txtSSubCategory);
	
			$sql = "SELECT * FROM TD_SUB_SUBCATEGORIES WHERE TDSUBCATID=$GetTDSubCatid AND SSUBCATEGORY='$txtSSubCategory' AND TDSSUBCATID!=$tdssubcatid AND ISDELETED=0";
			$row_count = unique($sql);
	
			$data = array();
			if($row_count == 0)
			{
				$querySSubCat="EXEC [TD_SUB_SUBCATEGORIES_SP] $actionid,$tdssubcatid,$GetTDSubCatid,'$txtSSubCategory',$userid";
				$stmtTopic=sqlsrv_query($mysqli, $querySSubCat);
				
				if($stmtTopic === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $querySSubCat );
					$data['success'] = false;
					$data['querySSubCat'] = $querySSubCat;
				}
				else
				{
					$data['querySSubCat'] = $querySSubCat;
					$data['success'] = true;
					if(!empty($tdssubcatid))$data['message'] = 'Sub-SubCategory successfully updated.';
					else $data['message'] = 'Sub-SubCategory successfully inserted.';
					echo json_encode($data);exit;
				}
				
			}
			else
			{
				$data['success'] = false;
				$data['message'] = 'Sub-SubCategory already exists.';
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
	
	
	
	/*============ GET SUB-SUBCATEGORIES =============*/ 
	function getSSubCategories($mysqli){
		try
		{	
			$data = array();

			$tdsubcatid = ($_POST['tdsubcatid'] == 'undefined' || $_POST['tdsubcatid'] == '') ? 0 : $_POST['tdsubcatid'];
			if($tdsubcatid == 0){throw new Exception('TDSubcatid error.');}

			$query = "SELECT TDSSUBCATID,TDSUBCATID,(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=TSS.TDSUBCATID AND ISDELETED=0)SUBCATEGORY,SSUBCATEGORY
			FROM TD_SUB_SUBCATEGORIES TSS WHERE ISDELETED=0 AND TDSUBCATID=$tdsubcatid ORDER BY SSUBCATEGORY";
			$result = sqlsrv_query($mysqli, $query);
			$count=unique($query);
				if($count > 0){
					while ($row = sqlsrv_fetch_array($result)) {
						$data['data'][] = $row;
					}
					$data['success'] = true;
				}
				else{
					$data['success'] = false;
					$data['message'] = 'Sub SubCategory Not Found.';
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
	/*============ GET SUB-SUBCATEGORIES =============*/ 
	
	
	
	/* =========== Delete =========== */ 
	function deleteSSubCat($mysqli){
		try{   
				global $userid;
				$data = array();     
				$tdssubcatid = ($_POST['tdssubcatid'] == 'undefined' || $_POST['tdssubcatid'] == '') ? 0 : $_POST['tdssubcatid'];
				if($tdssubcatid == 0){throw new Exception('TDSSubcatid Error.');}
	
				$stmt=sqlsrv_query($mysqli, "EXEC [TD_SUB_SUBCATEGORIES_SP] 3,$tdssubcatid,0,'',$userid");
				if( $stmt === false ) 
				{
					die( print_r( sqlsrv_errors(), true));
					throw new Exception( $mysqli->sqlstate );
				}
				else
				{
					$data['success'] = true;
					$data['message'] = 'Sub SubCategory successfully deleted.';
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
// ##################################################### SUB SUBCATEGORY SECTION END #####################################################





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







