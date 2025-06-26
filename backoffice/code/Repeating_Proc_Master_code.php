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
        case "getRepeatingProcMasters":getRepeatingProcMasters($conn);break;
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

		$rpid = ($_POST['rpid'] == 'undefined' || $_POST['rpid'] == '') ? 0 : $_POST['rpid'];
		$txtProcName = $_POST['txtProcName'] == 'undefined' ? '' : $_POST['txtProcName'];
		$txtProcName = str_replace("'","''",$txtProcName);
		$txtProcDescription = $_POST['txtProcDescription'] == 'undefined' ? '' : $_POST['txtProcDescription'];
		$txtProcDescription = str_replace("'","''",$txtProcDescription);
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlFrequency = $_POST['ddlFrequency'] == 'undefined' ? '' : $_POST['ddlFrequency'];
		
		$actionid = $rpid == 0 ? 1 : 2;

		if($txtProcName == ''){throw new Exception("Please Enter 'Procedure Name'.");}
		// if($ddlSSubCategory == 0){throw new Exception("Please Select 'Sub SubCategory'.");}
		if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}

		$sql = "SELECT * FROM REPEATING_PROCEDURES WHERE PROC_NAME='$txtProcName' AND LOCID=$ddlLocation AND TDSSUBCATID=$ddlSSubCategory AND RPID!=$rpid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			
			$query="EXEC [REPEATING_PROCEDURES_SP] $actionid,$rpid,'$txtProcName','$txtProcDescription',$ddlSSubCategory,$ddlLocation,'$ddlFrequency',$userid";
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
				// GET RPID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_RPID = $row['RPID'];

				if($actionid == 1){
					// INSERT TRANSACTION
					$queryTrans = "EXEC [REPEATING_PROCEDURE_TRANSACTIONS_SP] 1,0,$GET_RPID,'Open',NULL,0,$userid";
					sqlsrv_query($mysqli, $queryTrans);
				}

				$data['queryTrans'] = $queryTrans;
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($rpid))$data['message'] = 'Data successfully updated.';
				else $data['message'] = 'Data successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Data already exists';
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





/* ========== GET REPEATING PROC MASTER =========== */
 function getRepeatingProcMasters($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];

		$query = "SELECT RPID,PROC_NAME,PROC_DESC,TDSSUBCATID,
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID)TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID)))CATEGORY,
		LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID = PP.LOCID)[LOCATION],FREQUENCY
		FROM REPEATING_PROCEDURES PP
		WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchCategory > 0){
			$query .= " AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID)) = $ddlSearchCategory";
		}
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
/* ========== GET REPEATING PROC MASTER =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RPID = ($_POST['RPID'] == 'undefined' || $_POST['RPID'] == '') ? 0 : $_POST['RPID'];
			if($RPID == 0){throw new Exception('RPID Error.');}
			$delQuery = "EXEC [REPEATING_PROCEDURES_SP] 3,$RPID,'','',0,0,'',$userid";
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







