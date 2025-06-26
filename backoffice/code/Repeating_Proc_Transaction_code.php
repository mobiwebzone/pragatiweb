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
        case "addTransactions":addTransactions($conn);break;
        case "saveData":saveData($conn);break;
        case "getRepeatingProcTransaction":getRepeatingProcTransaction($conn);break;
        case "getRepeatingProcMasters":getRepeatingProcMasters($conn);break;
        case "getUsers":getUsers($conn);break;
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



// =============== Add Transactions ==================
 function addTransactions($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$chkSelectedProc = (!empty($_POST['chkSelectedProc'])) ? $_POST['chkSelectedProc'] : '';
		if($chkSelectedProc == '') throw new Exception('Error.');
		$chkSelectedProc = explode(",",$chkSelectedProc);

		// $data['$chkSelectedProc']=$chkSelectedProc;
		// echo json_encode($data);exit;

	
		for($i=0; $i<count($chkSelectedProc); $i++){
			$RPID = $chkSelectedProc[$i];
			$query="EXEC [REPEATING_PROCEDURE_TRANSACTIONS_SP] 1,0,$ddlLocation,$RPID,'Open','',0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
		}
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
			$data['message'] = 'Data successfully inserted.';
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
 // =============== Add Transactions ==============







// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$rp_transid = ($_POST['rp_transid'] == 'undefined' || $_POST['rp_transid'] == '') ? 0 : $_POST['rp_transid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlRepeatingProc = ($_POST['ddlRepeatingProc'] == 'undefined' || $_POST['ddlRepeatingProc'] == '') ? 0 : $_POST['ddlRepeatingProc'];
		$ddlStatus = $_POST['ddlStatus'] == 'undefined' ? 'Open' : $_POST['ddlStatus'];
		$txtCompleteDT = $_POST['txtCompleteDT'] == 'undefined' ? '' : $_POST['txtCompleteDT'];
		$ddlUser = ($_POST['ddlUser'] == 'undefined' || $_POST['ddlUser'] == '') ? 0 : $_POST['ddlUser'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];

		$actionid = $rp_transid == 0 ? 1 : 2;

		if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
		if($ddlRepeatingProc == 0){throw new Exception("Please Select 'Repeating Procedure'.");}
		if($ddlStatus == ''){throw new Exception("Please Select 'Status'.");}

		// $sql = "SELECT * FROM REPEATING_PROCEDURE_TRANSACTIONS WHERE RPID=$ddlRepeatingProc AND [STATUS]='$ddlStatus' 
		// AND CONVERT(DATE,DATE_COMPLETE,105)='$txtCompleteDT' AND COMPLETEDBY=$ddlUser AND RP_TRANSID!=$rp_transid AND ISDELETED=0";
		// $row_count = unique($sql);
		
		
		// $data = array();
		// if($row_count == 0)
		// {
			
				$query="EXEC [REPEATING_PROCEDURE_TRANSACTIONS_SP] $actionid,$rp_transid,$ddlLocation,$ddlRepeatingProc,'$ddlStatus','$txtCompleteDT',$ddlUser,'$txtRemark',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
			

			// $data['$query']=$query;
			// echo json_encode($data);exit;
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
				if(!empty($rp_transid))$data['message'] = 'Data successfully updated.';
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





/* ========== GET REPEATING PROC TRANSACTIONS =========== */
 function getRepeatingProcTransaction($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlSearchRepeatingProc = ($_POST['ddlSearchRepeatingProc'] == 'undefined' || $_POST['ddlSearchRepeatingProc'] == '') ? 0 : $_POST['ddlSearchRepeatingProc'];
		$ddlSearchStatus = ($_POST['ddlSearchStatus'] == 'undefined' || $_POST['ddlSearchStatus'] == '') ? '' : $_POST['ddlSearchStatus'];

		$query = "SELECT RP_TRANSID,RPID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=RPT.LOCID)[LOCATION],
		(SELECT PROC_NAME FROM REPEATING_PROCEDURES WHERE RPID=RPT.RPID)PROC_NAME,
		(SELECT FREQUENCY FROM REPEATING_PROCEDURES WHERE RPID=RPT.RPID)FREQUENCY,
		[STATUS],CONVERT(VARCHAR,DATE_COMPLETE,106)DATE_COMPLETE,COMPLETEDBY,
		ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=RPT.COMPLETEDBY),'-')USERNAME,
		ISNULL((SELECT USERROLE FROM USERS WHERE [UID]=RPT.COMPLETEDBY),'-')USERROLE,REMARK
		FROM REPEATING_PROCEDURE_TRANSACTIONS RPT WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchRepeatingProc > 0){
			$query .= " AND RPID=$ddlSearchRepeatingProc";
		}
		if($ddlSearchStatus != ''){
			$query .= " AND [STATUS]='$ddlSearchStatus'";
		}
		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";

		$data['$query'] = $query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['DATE_COMPLETE']=(!$row['DATE_COMPLETE'] || $row['DATE_COMPLETE'] == '' || $row['DATE_COMPLETE'] == '01 Jan 1900') ? '-' : $row['DATE_COMPLETE'];
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
/* ========== GET REPEATING PROC TRANSACTIONS =========== */





/* ========== GET REPEATING PROC MASTER =========== */
function getRepeatingProcMasters($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];
		$ddlSearchSubCategory = ($_POST['ddlSearchSubCategory'] == 'undefined' || $_POST['ddlSearchSubCategory'] == '') ? 0 : $_POST['ddlSearchSubCategory'];
		$ddlSearchSSubCategory = ($_POST['ddlSearchSSubCategory'] == 'undefined' || $_POST['ddlSearchSSubCategory'] == '') ? 0 : $_POST['ddlSearchSSubCategory'];
		$ddlSearchFrequency = ($_POST['ddlSearchFrequency'] == 'undefined' || $_POST['ddlSearchFrequency'] == '') ? '' : $_POST['ddlSearchFrequency'];

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
		if($ddlSearchSubCategory > 0){
			$query .= " AND (SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=PP.TDSSUBCATID) = $ddlSearchSubCategory";
		}
		if($ddlSearchSSubCategory > 0){
			$query .= " AND TDSSUBCATID = $ddlSearchSSubCategory";
		}
		if($ddlSearchFrequency != ''){
			$query .= " AND FREQUENCY = '$ddlSearchFrequency'";
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
		$data['$query'] = $query;
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






/* ========== GET USERS =========== */
 function getUsers($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT [UID],FIRSTNAME+' '+LASTNAME AS USERNAME, USERROLE FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation
		ORDER BY FIRSTNAME";

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
/* ========== GET USERS =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RP_TRANSID = ($_POST['RP_TRANSID'] == 'undefined' || $_POST['RP_TRANSID'] == '') ? 0 : $_POST['RP_TRANSID'];
			if($RP_TRANSID == 0){throw new Exception('RP_TRANSID Error.');}
			$delQuery = "EXEC [REPEATING_PROCEDURE_TRANSACTIONS_SP] 3,$RP_TRANSID,0,0,'','',0,'',$userid";
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







