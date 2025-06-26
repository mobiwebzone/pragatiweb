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
        case "UpdateData":UpdateData($conn);break;
        case "updateStatus":updateStatus($conn);break;
        case "getTODO":getTODO($conn);break;
        case "getUserByLoc":getUserByLoc($conn);break;
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
    
        $tdlid  = ($_POST['tdlid'] == 'undefined' || $_POST['tdlid'] == '') ? 0 : $_POST['tdlid'];
        $ddlSSubCategory  = ($_POST['ddlSSubCategory'] == 'undefined' || $_POST['ddlSSubCategory'] == '') ? 0 : $_POST['ddlSSubCategory'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlUser  = ($_POST['ddlUser'] == 'undefined' || $_POST['ddlUser'] == '') ? 0 : $_POST['ddlUser'];
        $ddlPriority  = ($_POST['ddlPriority'] == 'undefined' || $_POST['ddlPriority'] == '') ? 'LOW' : $_POST['ddlPriority'];
        $txtToDo  = $_POST['txtToDo'] == 'undefined' ? '' : $_POST['txtToDo'];
		$txtToDo = str_replace("'","''",$txtToDo);
        $txtETADate  = $_POST['txtETADate'] == 'undefined' ? '' : $_POST['txtETADate'];
		//==== IMAGE
		$existingCatImage  = $_POST['existingCatImage'] == 'undefined' ? '' : $_POST['existingCatImage'];
		$txtCatImage = '';
		if(isset($_FILES['txtCatImage']['name']) && $_FILES['txtCatImage']['size'] > 0){
			$ext = pathinfo($_FILES['txtCatImage']['name'],PATHINFO_EXTENSION);
			$txtCatImage .= strtolower(time().'.'.$ext);
		}
		else
		{
			$txtCatImage="";
		}
		//==== IMAGE
		
		$actionid = $tdlid == 0 ? 1 : 2;

		if($ddlSSubCategory == 0){throw new Exception("Please Select 'Sub SubCategory'.");}
		if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
		if($ddlUser == 0){throw new Exception("Please Select 'User Name'.");}
		if($txtToDo == ''){throw new Exception("Please Enter 'To Do Topic'.");}
		if($txtETADate == ''){throw new Exception("Please Select 'ETA Date'.");}

		$sql = "SELECT * FROM TO_DO_LIST WHERE TDSSUBCATID=$ddlSSubCategory AND LOCID=$ddlLocation AND TOUSER=$ddlUser AND [PRIORITY]='$ddlPriority' AND TODO='$txtToDo' AND ETA='$txtETADate' AND TDLID!=$tdlid AND ISDELETED=0";
		// $data['$sql'] = $sql;
		// echo json_encode($data);exit;

		$row_count = unique($sql);

		$data = array();
		$GET_TDLID = 0;
		if($row_count == 0)
		{
			$query="EXEC [TO_DO_LIST_SP] $actionid,$tdlid,$ddlSSubCategory,$ddlLocation,$ddlUser,'$ddlPriority','$txtToDo','$txtETADate',$userid,$userid";
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
				$row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$GET_TDLID = (int)$row['TDLID'];

				if($txtCatImage != ''){

					if($GET_TDLID > 0)
					{
						$insertimage = "UPDATE TO_DO_LIST SET [IMAGE]='$txtCatImage' WHERE TDLID=$GET_TDLID";
						sqlsrv_query($mysqli,$insertimage);
					}
	
	
					if(isset($_FILES['txtCatImage']['name']) && $_FILES['txtCatImage']['size'] > 0)
					{
						move_uploaded_file($_FILES["txtCatImage"]["tmp_name"], '../todo_images/'.$txtCatImage);
					}

					
					if(isset($_FILES['txtCatImage']['name']) && $existingCatImage != '')
					{
						if (file_exists('../todo_images/'.$existingCatImage))
						{
							unlink('../todo_images/'.$existingCatImage);
						}
					}
				

				}

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($tdlid))$data['message'] = 'Todo successfully updated.';
				else $data['message'] = 'Todo successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Todo already exists';
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





// =============== UPDATE DATA ==================
 function UpdateData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $TDLID  = ($_POST['TDLID'] == 'undefined' || $_POST['TDLID'] == '') ? 0 : $_POST['TDLID'];
        $txtTodoUpd  = ($_POST['txtTodoUpd'] == 'undefined' || $_POST['txtTodoUpd'] == '') ? '' : $_POST['txtTodoUpd'];
		$ddlStatus =  ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? '' : $_POST['ddlStatus'];
		$FOR =  ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];
		if($TDLID == 0){throw new Exception("TDLID NOT FOUND.");}
		if($FOR=='TODO' && $txtTodoUpd == ''){throw new Exception("Please Enter 'Todo'.");}
		if($FOR=='STATUS' && $ddlStatus == ''){throw new Exception("Please Select 'Status'.");}
		$txtTodoUpd = str_replace("'","''",$txtTodoUpd);

		if($FOR=='TODO'){
			$query="UPDATE TO_DO_LIST SET TODO='$txtTodoUpd',UPDATEID=$userid,UPDATEDATE=GETDATE() WHERE TDLID=$TDLID";
		}
		else if($FOR=='STATUS'){
			$query="UPDATE TO_DO_LIST SET TDSTATUS='$ddlStatus',STATUSBY=$userid,STATUSON=GETDATE() WHERE TDLID=$TDLID";
		}
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
			$data['message'] = ($FOR=='TODO' ? 'Todo' : 'Status').' successfully updated.';
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
// =============== UPDATE TODO ==============





// =============== UPDATE STATUS ==================
 function updateStatus($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $TDLID  = ($_POST['TDLID'] == 'undefined' || $_POST['TDLID'] == '') ? 0 : $_POST['TDLID'];
        $ddlStatus  = ($_POST['ddlStatus'] == 'undefined' || $_POST['ddlStatus'] == '') ? 'OPEN' : $_POST['ddlStatus'];
		if($TDLID == 0){throw new Exception("TDLID NOT FOUND.");}
		if($ddlStatus == ''){throw new Exception("Please Select 'Status'.");}


		$query="UPDATE TO_DO_LIST SET TDSTATUS='$ddlStatus',STATUSBY=$userid,STATUSON=GETDATE() WHERE TDLID=$TDLID";
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
			$data['message'] = 'Todo Status successfully updated.';
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
 // =============== UPDATE STATUS ==============





/*============ Get TODO =============*/ 
 function getTODO($mysqli){
	try
	{
		$data = array();

		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlSearchCategory = ($_POST['ddlSearchCategory'] == 'undefined' || $_POST['ddlSearchCategory'] == '') ? 0 : $_POST['ddlSearchCategory'];
		$ddlSearchPriority = ($_POST['ddlSearchPriority'] == 'undefined' || $_POST['ddlSearchPriority'] == '') ? '' : $_POST['ddlSearchPriority'];
		$ddlSearchStatus = ($_POST['ddlSearchStatus'] == 'undefined' || $_POST['ddlSearchStatus'] == '') ? '' : $_POST['ddlSearchStatus'];

		$query = "SELECT TDLID,TDSSUBCATID,
		(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID)TDSUBCATID,
		(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID))TDCATID,
		(SELECT SSUBCATEGORY FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID)SSUBCATEGORY,
		(SELECT SUBCATEGORY FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID))SUBCATEGORY,
		(SELECT CATEGORY FROM TD_CATEGORIES WHERE TDCATID=(SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID)))CATEGORY,
		LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TDL.LOCID)[LOCATION],TOUSER,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=TDL.TOUSER)[USER],
		(SELECT USERROLE FROM USERS WHERE [UID]=TDL.TOUSER)USERROLE,PRIORITY,TODO,
		CONVERT(VARCHAR,ETA,106)ETA,[IMAGE],TDSTATUS,STATUSBY,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=TDL.STATUSBY)STATUSBYNAME,
		CONVERT(VARCHAR,STATUSON,106)STATUSON 
		FROM TO_DO_LIST TDL WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		if($ddlSearchCategory > 0){
			$query .= " AND (SELECT TDCATID FROM TD_SUBCATEGORIES WHERE TDSUBCATID=(SELECT TDSUBCATID FROM TD_SUB_SUBCATEGORIES WHERE TDSSUBCATID=TDL.TDSSUBCATID)) = $ddlSearchCategory";
		}
		if($ddlSearchPriority != ''){
			$query .= " AND PRIORITY = '$ddlSearchPriority'";
		}
		if($ddlSearchStatus != ''){
			$query .= " AND TDSTATUS = '$ddlSearchStatus'";
		}

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'TODO not found.';
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
/*============ Get TODO =============*/ 





/*============ GET USER BY LOCATION =============*/ 
 function getUserByLoc($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');

		$query = "SELECT [UID],FIRSTNAME,LASTNAME,USERROLE FROM USERS WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY USERROLE, LASTNAME";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Users not found.';
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
/*============ GET USER BY LOCATION =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $tdlid = ($_POST['tdlid'] == 'undefined' || $_POST['tdlid'] == '') ? 0 : $_POST['tdlid'];
			if($tdlid == 0){throw new Exception('TDLID Error.');}
			$delQuery = "EXEC [TO_DO_LIST_SP] 3,$tdlid,0,0,0,'','','',0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'To Do successfully deleted.';
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







