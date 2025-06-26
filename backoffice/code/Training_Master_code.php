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
        case "getTrainingMasters":getTrainingMasters($conn);break;
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

		$tmid = ($_POST['tmid'] == 'undefined' || $_POST['tmid'] == '') ? 0 : $_POST['tmid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtDescription = $_POST['txtDescription'] == 'undefined' ? '' : $_POST['txtDescription'];
		$txtDescription = str_replace("'","''",$txtDescription);
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$txtTLink = $_POST['txtTLink'] == 'undefined' ? '' : $_POST['txtTLink'];
		$txtLogin = $_POST['txtLogin'] == 'undefined' ? '' : $_POST['txtLogin'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$txtCost = ($_POST['txtCost'] == 'undefined' || $_POST['txtCost'] == '') ? 0 : $_POST['txtCost'];
		$txtMinute = ($_POST['txtMinute'] == 'undefined' || $_POST['txtMinute'] == '') ? 0 : $_POST['txtMinute'];
		$ddlZone = ($_POST['ddlZone'] == 'undefined' || $_POST['ddlZone'] == '') ? 'All' : $_POST['ddlZone'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
		$txtRemark = str_replace("'","''",$txtRemark);
		
		$actionid = $tmid == 0 ? 1 : 2;

		if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
		if($ddlSSubCategory == 0){throw new Exception("Please Select 'Sub SubCategory'.");}

		// $sql = "SELECT * FROM TRAINING_MASTER WHERE TDSSUBCATID=$ddlSSubCategory AND TMID!=$tmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [TRAINING_MASTER_SP] $actionid,$tmid,$ddlLocation,'$txtDescription',$ddlSSubCategory,'$txtTLink','$txtLogin',
			$ddlProduct,$txtCost,$txtMinute,'$ddlZone','$txtRemark',$userid";
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
				if(!empty($tmid))$data['message'] = 'Data successfully updated.';
				else $data['message'] = 'Data successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Data already exists';
		// 	echo json_encode($data);exit;
		// }

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





/* ========== GET TRAINING MASTERS =========== */
 function getTrainingMasters($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];
		$FOR = ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? 0 : $_POST['FOR'];

		$query = "SELECT TMID,LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID = TM.LOCID)[LOCATION],T_DESC,
		TDSSUBCATID,
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID)TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID)))CATEGORY,
		T_LINK,[LOGIN],PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TM.PRODUCTID)PRODUCT,T_COST,T_MINUTE,T_ZONE,REMARK,CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE
		FROM TRAINING_MASTER TM
		WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchCategory > 0){
			$query .= " AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TM.TDSSUBCATID)) = $ddlSearchCategory";
		}

		if($FOR === 'TEACHER') $query .=" AND T_ZONE IN ('Teacher','All')";
		// if($FOR === 'ADMIN') $query .=" AND T_ZONE IN ('Admin','All')";
		if($FOR === 'STUDENT') $query .=" AND T_ZONE IN ('Student','All')";

		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";

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
/* ========== GET TRAINING MASTERS =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TMID = ($_POST['TMID'] == 'undefined' || $_POST['TMID'] == '') ? 0 : $_POST['TMID'];
			if($TMID == 0){throw new Exception('TMID Error.');}
			$delQuery = "EXEC [TRAINING_MASTER_SP] 3,$TMID,0,'',0,'','',0,0,0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
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
		$data['success'] = false;
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







