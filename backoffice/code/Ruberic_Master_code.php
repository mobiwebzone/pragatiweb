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
		case "login":login($conn);break;
        case "saveData":saveData($conn);break;
        case "getRubericData":getRubericData($conn);break;
        case "getTestMaster":getTestMaster($conn);break;
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
    
        $rmid  = ($_POST['rmid'] == 'undefined' || $_POST['rmid'] == '') ? 0 : $_POST['rmid'];
        $ddlTest  = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
        $txtCriteria  = $_POST['txtCriteria'] == 'undefined' ? '' : $_POST['txtCriteria'];
        $txtAllotedMarks  = $_POST['txtAllotedMarks'] == 'undefined' ? 0 : $_POST['txtAllotedMarks'];
		$actionid = $rmid == 0 ? 1 : 2;

		if($ddlTest == 0){throw new Exception("Please Select 'Test Name'.");}
		if($txtCriteria == ''){throw new Exception("Please Enter 'Criteria'.");}
		if($txtAllotedMarks == 0){throw new Exception("Please Enter 'Alloted Marks'.");}

		$sql = "SELECT * FROM RUBERIC_MASTER WHERE TESTID=$ddlTest AND CRITERIA='$txtCriteria' AND RMID!=$rmid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [RUBERIC_MASTER_SP] $actionid,$rmid,$ddlTest,'$txtCriteria',$txtAllotedMarks,$userid";
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
				if(!empty($rmid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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





/*============ GET RUBERIC DATA =============*/ 
 function getRubericData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT RMID,TESTID,(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=RM.TESTID)TESTDESC,
		(SELECT TESTYEAR FROM TEST_MASTER WHERE TESTID=RM.TESTID)TESTYEAR,
		CRITERIA,ALLOTEDMARKS FROM RUBERIC_MASTER RM WHERE ISDELETED=0 ORDER BY TESTDESC,TESTYEAR";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ALLOTEDMARKS'] = (int)$row['ALLOTEDMARKS'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Ruberic not found.';
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
/*============ GET RUBERIC DATA =============*/ 





/*============ Get Test Master =============*/ 
function getTestMaster($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TESTID,TESTDESC,TESTYEAR,TESTOPEN,REMARKS,MULTIPLEATTEMPTS,NUMOFATTEMPTS 
			FROM TEST_MASTER 
			WHERE ISDELETED=0
			ORDER BY TESTID DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Test Master =============*/ 





/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $rmid = ($_POST['rmid'] == 'undefined' || $_POST['rmid'] == '') ? 0 : $_POST['rmid'];
			if($rmid == 0){throw new Exception('RMID Error.');}
			$delQuery = "EXEC [RUBERIC_MASTER_SP] 3,$rmid,0,'',0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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







