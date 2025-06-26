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
        case "getTrainingProc":getTrainingProc($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ========== GET TRAINING PROC =========== */
 function getTrainingProc($mysqli){
	try
	{
		global $userid,$role;
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$FOR = ($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];
		if($LOCID == 0)throw new Exception('LOCID Not Found.');
		if($FOR == '')throw new Exception('Error.');

		$query = "SELECT TMID,LOCID,T_DESC,TDSSUBCATID,T_LINK,[LOGIN],PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=TM.PRODUCTID)PRODUCT,
		T_COST,T_MINUTE,T_ZONE,REMARK,CONVERT(VARCHAR,INSERTDATE,106)INSERTDATE 
		FROM TRAINING_MASTER TM
		WHERE ISDELETED=0 AND LOCID = $LOCID";

		if($FOR === 'TEACHER'){
			if($role == 'TEACHER'){
				$query .=" AND T_ZONE IN ('Teacher','All')";
			}
			if($role == 'VOLUNTEER'){
				$query .=" AND T_ZONE IN ('Volunteer','All')";
			}
			if($role == 'ADMINISTRATOR' || $role == 'SUPERADMIN'){
				$query .=" AND T_ZONE IN ('Admin','All')";
			}
		}
		else if($FOR === 'STUDENT'){
			$query .=" AND T_ZONE IN ('Student','All')";
		}
		else{
			throw new Exception('Error.');
		}

		$query .= " ORDER BY CONVERT(DATE,INSERTDATE,105) DESC";

		$RCOUNT = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($RCOUNT > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['COMPLETED_DATE'] = (!$row['COMPLETED_DATE'] || $row['COMPLETED_DATE'] == '') ? '-' : $row['COMPLETED_DATE'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			
			$data['message'] = 'Data Not Found.';
			$data['success'] = false;
		}
		$data['$$$role'] = $role;
		$data['$$FOR'] = $FOR;
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
/* ========== GET TRAINING PROC =========== */





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







