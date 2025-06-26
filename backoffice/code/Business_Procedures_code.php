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
        case "copy":copyData($conn);break;
        case "getBusinessProc":getBusinessProc($conn);break;
        case "getUsers":getUsers($conn);break;
        case "delete":delete($conn);break;

		// DET
        case "saveDet":saveDet($conn);break;
        case "getDetails":getDetails($conn);break;
        case "deleteDet":deleteDet($conn);break;
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

		$bpid = ($_POST['bpid'] == 'undefined' || $_POST['bpid'] == '') ? 0 : $_POST['bpid'];
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtProcedureShortDesc = $_POST['txtProcedureShortDesc'] == 'undefined' ? '' : $_POST['txtProcedureShortDesc'];
		$txtProcedureShortDesc = str_replace("'","''",$txtProcedureShortDesc);
		$txtProcedureLongDesc = $_POST['txtProcedureLongDesc'] == 'undefined' ? '' : $_POST['txtProcedureLongDesc'];
		$txtProcedureLongDesc = str_replace("'","''",$txtProcedureLongDesc);
		$ddlZone = ($_POST['ddlZone'] == 'undefined' || $_POST['ddlZone'] == '') ? 'All' : $_POST['ddlZone'];
		
		$actionid = $bpid == 0 ? 1 : 2;

		if($ddlSSubCategory == 0){throw new Exception("Please Select 'Sub SubCategory'.");}
		if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}

		$sql = "SELECT * FROM BUSINESS_PROCEDURES WHERE LOCID=$ddlLocation AND TDSSUBCATID=$ddlSSubCategory AND [ZONE]='$ddlZone' AND BPID!=$bpid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [BUSINESS_PROCEDURES_SP] $actionid,$bpid,$ddlSSubCategory,$ddlLocation,'$ddlZone',$userid";
			$data['query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
			}
			else
			{
				// GET BPID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['BPID'] = $row['BPID'];

				$data['success'] = true;
				if(!empty($bpid))$data['message'] = 'Data successfully updated.';
				else $data['message'] = 'Data successfully inserted.';
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Data already exists';
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



// =============== SAVE DATA ==================
function copyData($mysqli){
	try
	{
	$data = array();
	global $userid;

	$BPID = ($_POST['BPID'] == 'undefined' || $_POST['BPID'] == '') ? 0 : $_POST['BPID'];
	$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
	$ddlZone_old = ($_POST['ddlZone_old'] == 'undefined' || $_POST['ddlZone_old'] == '') ? '' : $_POST['ddlZone_old'];
	$ddlZone = ($_POST['ddlZone'] == 'undefined' || $_POST['ddlZone'] == '') ? '' : $_POST['ddlZone'];


	if($BPID == 0){throw new Exception("Error : Invalid 'BPID'.");}
	if($ddlLocation == 0){throw new Exception("Error : Invalid 'Location'.");}
	if($ddlSSubCategory == 0){throw new Exception("Error : Invalid 'Sub SubCategory'.");}
	if($ddlZone == ''){throw new Exception("Please Select 'Zone'.");}

	$sql = "SELECT * FROM BUSINESS_PROCEDURES WHERE LOCID=$ddlLocation AND TDSSUBCATID=$ddlSSubCategory AND [ZONE]='$ddlZone' AND ISDELETED=0";
	$row_count = unique($sql);

	$data = array();
	if($row_count == 0)
	{
		$query="EXEC [BUSINESS_PROCEDURES_COPY] $BPID,$ddlLocation,$ddlSSubCategory,'$ddlZone_old','$ddlZone',$userid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Data successfully copy.';
		}
		
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Data already exists.';
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





/* ========== GET BUSINESS PROCEDURES =========== */
 function getBusinessProc($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];

		$query = "SELECT BPID,TDSSUBCATID,LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID = BP.LOCID)[LOCATION],
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)))CATEGORY,
		[ZONE]
		FROM BUSINESS_PROCEDURES BP WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchCategory > 0){
			$query .= " AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=BP.TDSSUBCATID)) = $ddlSearchCategory";
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
/* ========== GET BUSINESS PROCEDURES =========== */




/* ========== GET USERS =========== */
function getUsers($mysqli){
	try{
		global $userid;
		$data = array();

		$query="SELECT [UID],FIRSTNAME+' '+LASTNAME AS USERNAME,USERROLE  FROM USERS WHERE ISDELETED=0
		ORDER BY FIRSTNAME";
		$stmt=sqlsrv_query($mysqli,$query);
		$count = unique($query);
		if($count > 0){
			while($row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC)){
				$data['data'][]=$row;
			}

			$data['success'] = true;
		}else{
			$data['success'] = false;
			$data['message'] = 'Data not found.';
		}
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	catch(Exception $e){
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
            $BPID = ($_POST['BPID'] == 'undefined' || $_POST['BPID'] == '') ? 0 : $_POST['BPID'];
			if($BPID == 0){throw new Exception('BPID Error.');}
			$delQuery = "EXEC [BUSINESS_PROCEDURES_SP] 3,$BPID,0,0,'',$userid";
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




// ==============================================================
// DETAILS START
// ==============================================================
// =============== SAVE DATA ==================
function saveDet($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $bpdid = ($_POST['bpdid'] == 'undefined' || $_POST['bpdid'] == '') ? 0 : $_POST['bpdid'];
	   $bpid = ($_POST['bpid'] == 'undefined' || $_POST['bpid'] == '') ? 0 : $_POST['bpid'];
	   $txtStep = $_POST['txtStep'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtStep']);
	   $txtStepDesc = $_POST['txtStepDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtStepDesc']);
	   $ddlInOut = ($_POST['ddlInOut'] == 'undefined' || $_POST['ddlInOut'] == '') ? 'NO': $_POST['ddlInOut'];
	   $ddlMenuName = ($_POST['ddlMenuName'] == 'undefined' || $_POST['ddlMenuName'] == '') ? 0 : $_POST['ddlMenuName'];
	   $LocEnabled = ($_POST['LocEnabled'] == 'undefined' || $_POST['LocEnabled'] == '' || $_POST['LocEnabled'] == '0') ? 0 : 1;
	   $displayOnWeb = ($_POST['displayOnWeb'] == 'undefined' || $_POST['displayOnWeb'] == '' || $_POST['displayOnWeb'] == '0') ? 0 : 1;
	   $txtPdfLink = $_POST['txtPdfLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPdfLink']);
	   $txtVideoLink = $_POST['txtVideoLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtVideoLink']);
	   
	   $actionid = $bpdid == 0 ? 1 : 2;

	   if($bpid == 0) throw new Exception("Invalid BPID.");
	   if($txtStep == '') throw new Exception("Please Enter Step.");

	   $sql = "SELECT * FROM BUSINESS_PROCEDURE_DETAILS WHERE BPID=$bpid AND STEP='$txtStep' AND BPDID!=$bpdid AND ISDELETED=0";
	   $row_count = unique($sql);
	   
	   if($row_count == 0)
	   {
		   $query="EXEC [BUSINESS_PROCEDURE_DETAILS_SP] $actionid,$bpdid,$bpid,'$txtStep','$txtStepDesc','$ddlInOut',$ddlMenuName,$LocEnabled,
		   $displayOnWeb,'$txtPdfLink','$txtVideoLink',$userid";
		   $data['query'] = $query;
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
		   }
		   else
		   {

			   $data['success'] = true;
			   if(!empty($bpdid))$data['message'] = 'Data successfully updated.';
			   else $data['message'] = 'Data successfully inserted.';
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'Data already exists';
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



/* ========== GET BUSINESS PROCEDURES DETAILS =========== */
function getDetails($mysqli){
	try
	{
		$data = array();

		$bpid = ($_POST['bpid'] == 'undefined' || $_POST['bpid'] == '') ? 0 : $_POST['bpid'];
		if($bpid <= 0) throw new Exception('Error: Invalid BPID.');

		$query = "SELECT BPDID,STEP,STEP_DESC,INOUT,MENUID,
				ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT ISNULL((SELECT MENU+',' FROM BO_MENU WHERE MENUID=M4.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M4 WHERE MENUID=M3.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M3 WHERE MENUID=M2.UNDER_MENUID),'')+''+MENU+',' FROM BO_MENU M2 WHERE MENUID=M1.UNDER_MENUID),'')+''+MENU FROM BO_MENU M1 WHERE MENUID=BPD.MENUID),'')UNDER_MENU,
				ISNULL((SELECT PAGE_LINK FROM BO_MENU WHERE MENUID=BPD.MENUID),'')PAGE_LINK,
				LOCATION_ENABLED,DISPLAY_WEBSITE,PDF_LINK,VIDEO_LINK 
				FROM BUSINESS_PROCEDURE_DETAILS BPD WHERE BPID=$bpid AND ISDELETED=0";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['UNDER_MENU'] = str_replace(","," <span class='font-weight-bold font-18'> &#10230; </span> ",$row['UNDER_MENU']);
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
/* ========== GET BUSINESS PROCEDURES DETAILS =========== */


/* =========== Delete =========== */ 
function deleteDet($mysqli){
	try{   
			global $userid;
			$data = array();     
            $BPDID = ($_POST['BPDID'] == 'undefined' || $_POST['BPDID'] == '') ? 0 : $_POST['BPDID'];
			if($BPDID == 0){throw new Exception('Error : Invalid BPDID.');}
			$delQuery = "EXEC [BUSINESS_PROCEDURE_DETAILS_SP] 3,$BPDID,0,'','','',0,0,0,'','',$userid";
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


// ==============================================================
// DETAILS END
// ==============================================================








// ==============================================================
// OTHER START
// ==============================================================
/*============ GET MENU DATA =============*/ 


// ==============================================================
// OTHER END
// ==============================================================


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







