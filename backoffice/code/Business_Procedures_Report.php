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
        case "getData":getData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ========== GET DATA =========== */
function getData($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$ddlZone = ($_POST['ddlZone'] == 'undefined' || $_POST['ddlZone'] == '') ? '' : $_POST['ddlZone'];

		$query = "SELECT BP.BPID,BP.TDSSUBCATID,(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)))CATEGORY,
		ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M4.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M4 WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU FROM BO_MENU M1 WHERE MENUID=BPD.MENUID),'')UNDER_MENU,
		BP.[ZONE],BPD.STEP,BPD.STEP_DESC,BPD.INOUT,BPD.MENUID,BPD.LOCATION_ENABLED,
		BPD.DISPLAY_WEBSITE,BPD.PDF_LINK,BPD.VIDEO_LINK
		FROM BUSINESS_PROCEDURE_DETAILS BPD,BUSINESS_PROCEDURES BP WHERE BPD.ISDELETED=0 AND BP.ISDELETED=0
		AND BPD.BPID=BP.BPID AND BP.LOCID=$ddlLocation";

		if($ddlSSubCategory > 0) $query .= " AND BP.TDSSUBCATID = $ddlSSubCategory";
		if($ddlZone != '') $query .= " AND BP.[ZONE] = '$ddlZone'";
		
		$query .= " ORDER BY (SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID), BP.[ZONE]";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			$BPID_NEW=$BPID_OLD=0;
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['UNDER_MENU'] = str_replace(","," <span class='font-weight-bold font-18'> &#10230; </span> ",$row['UNDER_MENU']);
				$BPID_NEW = $row['BPID'];
				if($BPID_NEW!==$BPID_OLD){
					$data['BPID_LIST'][] = $row['BPID'];
					$data['data_new'][$row['BPID']][] = $row;
				}else{
					$data['data_new'][$row['BPID']][] = $row;
				}

				$data['data'][] = $row;

				$BPID_OLD = $BPID_NEW;
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
/* ========== GET DATA =========== */







function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







