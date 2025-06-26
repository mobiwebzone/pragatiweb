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
        case "getSectionMaster":getSectionMaster($conn);break;
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
    
        $secid  = ($_POST['secid'] == 'undefined' || $_POST['secid'] == '') ? 0 : $_POST['secid'];
        $txtSection  = $_POST['txtSection'] == 'undefined' ? '' : $_POST['txtSection'];
        $rdCalcAllow  = ($_POST['rdCalcAllow'] == '0' || $_POST['rdCalcAllow'] == 'undefined') ? 0 : 1;
		
		$actionid = $secid == 0 ? 1 : 2;

		if($txtSection == '')
		{throw new Exception("Please Enter Section Name.");}

		$sql = "SELECT * FROM SECTION_MASTER WHERE SECTION='$txtSection' AND SECID!=$secid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [SECTION_MASTER_SP] $actionid,$secid,'$txtSection',$rdCalcAllow,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($secid))$data['message'] = 'Record successfully updated';
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





/*============ Get SECTION =============*/ 
 function getSectionMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT SECID,SECTION,ALLOWEDCALC FROM SECTION_MASTER WHERE ISDELETED=0 ORDER BY SECTION";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
/*============ Get SECTION =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $secid = ($_POST['secid'] == 'undefined' || $_POST['secid'] == '') ? 0 : $_POST['secid'];
			if($secid == 0){throw new Exception('Secid Error.');}

			$stmt=sqlsrv_query($mysqli, "EXEC [SECTION_MASTER_SP] 3,$secid,'',0,$userid");
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







