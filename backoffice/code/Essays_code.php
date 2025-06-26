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
        case "getTestSection":getTestSection($conn);break;
        case "getEssays":getEssays($conn);break;
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
    
        $essid  = ($_POST['essid'] == 'undefined' || $_POST['essid'] == '') ? 0 : $_POST['essid'];
        $ddlTest  = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
        $ddlTestSection  = ($_POST['ddlTestSection'] == 'undefined' || $_POST['ddlTestSection'] == '') ? 0 : $_POST['ddlTestSection'];
        $txtEssayTopic  = $_POST['txtEssayTopic'] == 'undefined' ? '' : $_POST['txtEssayTopic'];
        $ddlLimitOn  = $_POST['ddlLimitOn'] == 'undefined' ? '' : $_POST['ddlLimitOn'];
        $txtLimit  = ($_POST['txtLimit'] == 'undefined' || $_POST['txtLimit'] == '') ? 0 : $_POST['txtLimit'];
        $txtTimeAllowed  = ($_POST['txtTimeAllowed'] == 'undefined' || $_POST['txtTimeAllowed'] == '') ? 0 : $_POST['txtTimeAllowed'];
		
		$actionid = $essid == 0 ? 1 : 2;

		if($ddlTest == 0){throw new Exception("Please Select 'Test Name'.");}
		if($ddlTestSection == 0){throw new Exception("Please Select 'Test Section Name'.");}
		if($txtEssayTopic == ''){throw new Exception("Please Enter 'Essay Topic'.");}
		if($ddlLimitOn == ''){throw new Exception("Please Select 'Limit on'.");}
		if($txtLimit == 0){throw new Exception("Please Enter 'Words/Char Limit'.");}
		if($txtTimeAllowed == 0){throw new Exception("Please Enter 'Time Allowed (Minutes)'.");}

		$sql = "SELECT * FROM ESSAYS WHERE TESTID=$ddlTest AND TSECID=$ddlTestSection AND ESSTOPIC='$txtEssayTopic' AND ESSID!=$essid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ESSAYS_SP] $actionid,$essid,$ddlTest,$ddlTestSection,'$txtEssayTopic','$ddlLimitOn',$txtLimit,$txtTimeAllowed,$userid";
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
				if(!empty($essid))$data['message'] = 'Record successfully updated.';
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





/*============ Get ESSAYS =============*/ 
 function getEssays($mysqli){
	try
	{
		$data = array();

		$query = "SELECT ESSID,TESTID,
		(SELECT TESTDESC FROM TEST_MASTER WHERE ISDELETED=0 AND TESTID=E.TESTID)TEST,TSECID,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE ISDELETED=0 AND TSECID=E.TSECID)TESTSECTION,
		ESSTOPIC,LIMITON,LIMIT,TIMEALLOWED 
		FROM ESSAYS E
		WHERE ISDELETED=0 ORDER BY ESSTOPIC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Essays not found.';
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
/*============ Get ESSAYS =============*/ 





/*============ Get TEST SECTION =============*/ 
 function getTestSection($mysqli){
	try
	{
		$data = array();
		$ddlTest = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
		if($ddlTest == 0) throw new Exception('TestId not found.');
		$query = "SELECT TSECID,TESTSECTION FROM TEST_SECTIONS WHERE ISDELETED=0 AND TESTID=$ddlTest AND
		TESTSECTION NOT LIKE '%Break%' ORDER BY SEQNO";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Test section not found.';
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
/*============ Get TEST SECTION =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $essid = ($_POST['essid'] == 'undefined' || $_POST['essid'] == '') ? 0 : $_POST['essid'];
			if($essid == 0){throw new Exception('Essid Error.');}
			$delQuery = "EXEC [ESSAYS_SP] 3,$essid,0,0,'','',0,0,$userid";
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







