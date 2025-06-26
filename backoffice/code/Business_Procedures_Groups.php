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
        case "getSteps":getSteps($conn);break;

        case "saveGroup":saveGroup($conn);break;
        case "getGroups":getGroups($conn);break;
        case "getUsers":getUsers($conn);break;
        case "delete":delete($conn);break;

		// DET
        case "saveDet":saveDet($conn);break;
        case "getGroupDetails":getGroupDetails($conn);break;
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



// =============== GET STEPS ==================
function getSteps($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSSubCategory = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
		$ddlZone = ($_POST['ddlZone'] == 'undefined' || $_POST['ddlZone'] == '') ? '' : $_POST['ddlZone'];

		$query = "SELECT BPDID,STEP,STEP_DESC FROM BUSINESS_PROCEDURE_DETAILS WHERE ISDELETED=0";

		if($ddlSSubCategory > 0){
			$query .= " AND BPID IN (SELECT BPID FROM BUSINESS_PROCEDURES WHERE ISDELETED=0 AND LOCID=$ddlLocation AND TDSSUBCATID=$ddlSSubCategory AND [ZONE]='$ddlZone')";
		}else{
			$query .= " AND BPID IN (SELECT BPID FROM BUSINESS_PROCEDURES WHERE ISDELETED=0 AND LOCID=$ddlLocation AND [ZONE]='$ddlZone')";
		}


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
// =============== GET STEPS ==================




// =============== SAVE DATA ==================
 function saveGroup($mysqli){
     try
     {
		$data = array();
        global $userid;

		$bpgid = ($_POST['bpgid'] == 'undefined' || $_POST['bpgid'] == '') ? 0 : $_POST['bpgid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtGroupName = $_POST['txtGroupName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGroupName']);
		$actionid = $bpgid == 0 ? 1 : 2;

		if($ddlLocation == 0) throw new Exception("Please Select 'Location'.");
		if($txtGroupName == ''){throw new Exception("Please Enter 'Group Name'.");}

		$sql = "SELECT * FROM BUSINESS_PROCEDURE_GROUPS WHERE LOCID=$ddlLocation AND GROUP_NAME='$txtGroupName' AND BPGID!=$bpgid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [BUSINESS_PROCEDURE_GROUPS_SP] $actionid,$bpgid,$ddlLocation,'$txtGroupName',$userid";
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
				// GET BPGID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['BPGID'] = $row['BPGID'];

				$data['success'] = true;
				if(!empty($bpgid))$data['message'] = 'Data successfully updated.';
				else $data['message'] = 'Data successfully inserted.';
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



/* ========== GET GROUPS =========== */
 function getGroups($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT BPGID,GROUP_NAME,(SELECT (SELECT STEP FROM BUSINESS_PROCEDURE_DETAILS WHERE BPDID=BPGD.BPDID)+', ' FROM BUSINESS_PROCEDURE_GROUP_DETAILS BPGD WHERE ISDELETED=0 AND BPGID=BP.BPGID FOR XML PATH(''))STEPS
				FROM BUSINESS_PROCEDURE_GROUPS BP WHERE ISDELETED=0 AND LOCID=$ddlLocation
				ORDER BY GROUP_NAME";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['STEPS'] = rtrim($row['STEPS'],', ');
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
/* ========== GET GROUPS =========== */




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
            $BPGID = ($_POST['BPGID'] == 'undefined' || $_POST['BPGID'] == '') ? 0 : $_POST['BPGID'];
			if($BPGID == 0){throw new Exception('BPGID Error.');}
			$delQuery = "EXEC [BUSINESS_PROCEDURE_GROUPS_SP] 3,$BPGID,0,'',$userid";
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

	   $bpgdid = ($_POST['bpgdid'] == 'undefined' || $_POST['bpgdid'] == '') ? 0 : $_POST['bpgdid'];
	   $bpgid = ($_POST['bpgid'] == 'undefined' || $_POST['bpgid'] == '') ? 0 : $_POST['bpgid'];
	   $ddlStep = ($_POST['ddlStep'] == 'undefined' || $_POST['ddlStep'] == '') ? 0 : $_POST['ddlStep'];
	   
	   $actionid = $bpgdid == 0 ? 1 : 2;

	   if($bpgid == 0) throw new Exception("Invalid BPGID.");
	   if($ddlStep == 0) throw new Exception("Please Select Step.");

	   $sql = "SELECT * FROM BUSINESS_PROCEDURE_GROUP_DETAILS WHERE BPGID=$bpgid AND BPDID=$ddlStep AND BPGDID!=$bpgdid AND ISDELETED=0";
	   $row_count = unique($sql);
	   
	   if($row_count == 0)
	   {
			if($actionid==1){
				$query="INSERT INTO BUSINESS_PROCEDURE_GROUP_DETAILS (BPGID,BPDID,INSERTID)
						VALUES ($bpgid,$ddlStep,$userid)";
			}else{
				$query="UPDATE BUSINESS_PROCEDURE_GROUP_DETAILS SET BPDID=$ddlStep,UPDATEID=$userid,UPDATEDATE=GETDATE() WHERE BPGDID=$bpgdid";
			}
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
			   if(!empty($bpgdid))$data['message'] = 'Data successfully updated.';
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



/* ========== GET GROUP DETAILS =========== */
function getGroupDetails($mysqli){
	try
	{
		$data = array();

		$bpgid = ($_POST['bpgid'] == 'undefined' || $_POST['bpgid'] == '') ? 0 : $_POST['bpgid'];
		if($bpgid <= 0) throw new Exception('Error: Invalid BPGID.');

		$query = "SELECT BPGDID,BPG.BPDID,BP.STEP,BP.STEP_DESC,
		(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID)TDSSUBCATID,
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID))TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID)))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID))SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID)))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=(SELECT TDSSUBCATID FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID))))CATEGORY,
		(SELECT [ZONE] FROM BUSINESS_PROCEDURES WHERE BPID=BP.BPID)[ZONE]
		FROM BUSINESS_PROCEDURE_GROUP_DETAILS BPG,BUSINESS_PROCEDURE_DETAILS BP
		WHERE BPG.ISDELETED=0 AND BP.ISDELETED=0 AND BPG.BPDID=BP.BPDID AND BPGID=$bpgid";
		$data['query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				// $row['UNDER_MENU'] = str_replace(","," <span class='font-weight-bold font-18'> &#10230; </span> ",$row['UNDER_MENU']);
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
		$BPGDID = ($_POST['BPGDID'] == 'undefined' || $_POST['BPGDID'] == '') ? 0 : $_POST['BPGDID'];
		if($BPGDID == 0){throw new Exception('Error : Invalid BPGDID.');}
		$delQuery = "UPDATE BUSINESS_PROCEDURE_GROUP_DETAILS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE BPGDID=$BPGDID";
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







