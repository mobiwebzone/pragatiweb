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
        case "getStandardTextEmails":getStandardTextEmails($conn);break;
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

		$steid = ($_POST['steid'] == 'undefined' || $_POST['steid'] == '') ? 0 : $_POST['steid'];
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtCommunicationShortDesc = $_POST['txtCommunicationShortDesc'] == 'undefined' ? '' : $_POST['txtCommunicationShortDesc'];
		$txtCommunicationShortDesc = str_replace("'","''",$txtCommunicationShortDesc);
		$txtCommunicationLongDesc = $_POST['txtCommunicationLongDesc'] == 'undefined' ? '' : $_POST['txtCommunicationLongDesc'];
		$txtCommunicationLongDesc = str_replace("'","''",$txtCommunicationLongDesc);
		
		$actionid = $steid == 0 ? 1 : 2;

		if($ddlSSubCategory == 0){throw new Exception("Please Select 'Sub SubCategory'.");}
		if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}

		$sql = "SELECT * FROM STANDARD_TEXT_EMAIL WHERE LOCID=$ddlLocation AND TDSSUBCATID=$ddlSSubCategory AND COMMUNICATION_SHORTDESC='$txtCommunicationShortDesc' AND STEID!=$steid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [STANDARD_TEXT_EMAIL_SP] $actionid,$steid,$ddlSSubCategory,$ddlLocation,'$txtCommunicationShortDesc','$txtCommunicationLongDesc',$userid";
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
				if(!empty($steid))$data['message'] = 'Data successfully updated.';
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





/* ========== GET STD TEXT/EMAIL =========== */
 function getStandardTextEmails($mysqli){
	try
	{
		$data = array();

		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT STEID,TDSSUBCATID,LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID = STE.LOCID)[LOCATION],
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID)TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID)))CATEGORY,
		COMMUNICATION_SHORTDESC,COMMUNICATION_LONGDESC
		FROM STANDARD_TEXT_EMAIL STE WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchCategory > 0){
			$query .= " AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=STE.TDSSUBCATID)) = $ddlSearchCategory";
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
/* ========== GET STD TEXT/EMAIL =========== */






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STEID = ($_POST['STEID'] == 'undefined' || $_POST['STEID'] == '') ? 0 : $_POST['STEID'];
			if($STEID == 0){throw new Exception('STEID Error.');}
			$delQuery = "EXEC [STANDARD_TEXT_EMAIL_SP] 3,$STEID,0,0,'','',$userid";
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







