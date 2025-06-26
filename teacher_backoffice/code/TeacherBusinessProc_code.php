<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['ROLE']))
{$role=$_SESSION['ROLE'];}
else
{$role='';}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getBusinessProc":getBusinessProc($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ========== GET BUSINESS PROC =========== */
 function getBusinessProc($mysqli){
	try
	{
		global $userid,$role;
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$FOR = ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');
		if($FOR == '')throw new Exception('Error.');

		// $query = "SELECT BPID,PROCEDURE_SHORTDESC,PROCEDURE_LONGDESC FROM BUSINESS_PROCEDURES WHERE ISDELETED=0 AND LOCID = $LOCID";
		$query = "SELECT BPDID,BP.BPID,STEP,STEP_DESC,INOUT,(SELECT MENU FROM BO_MENU WHERE MENUID=BPD.MENUID) MENU,
				LOCATION_ENABLED,DISPLAY_WEBSITE,PDF_LINK,VIDEO_LINK,
				(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)SSUBCATEGORY,
				(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID))SUBCATEGORY,
				(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)))CATEGORY
				FROM BUSINESS_PROCEDURE_DETAILS BPD, BUSINESS_PROCEDURES BP 
				WHERE BPD.BPID=BP.BPID AND BPD.ISDELETED=0 AND BP.ISDELETED=0 AND BP.LOCID=$LOCID";
		if($FOR === 'TEACHER'){
			if($role == 'TEACHER'){
				$query .=" AND BP.[ZONE] IN ('Teacher','All')";
			}
			if($role == 'VOLUNTEER'){
				$query .=" AND BP.[ZONE] IN ('Volunteer','All')";
			}
			if($role == 'ADMINISTRATOR' || $role == 'SUPERADMIN'){
				$query .=" AND BP.[ZONE] IN ('Admin','All')";
			}
		}
		else if($FOR === 'STUDENT'){
			$query .=" AND BP.[ZONE] IN ('Student','All')";
		}
		else{
			throw new Exception('Error.');
		}

		$query .= " ORDER BY CONVERT(DATE,BPD.INSERTDATE,105) DESC";		

		$data['$query'] = $query;
		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data Not Found.';
			$data['success'] = false;
		}
		$data['$for'] = $FOR;
		echo json_encode($data);exit;
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
 }
/* ========== GET BUSINESS PROC =========== */





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







