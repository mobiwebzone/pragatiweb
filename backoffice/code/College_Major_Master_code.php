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
        case "getCollegeMajor":getCollegeMajor($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $majorid  = ($_POST['majorid'] == 'undefined' || $_POST['majorid'] == '') ? 0 : $_POST['majorid'];
        $txtMajor  = $_POST['txtMajor'] == 'undefined' ? '' : $_POST['txtMajor'];
        $txtDescription  = $_POST['txtDescription'] == 'undefined' ? '' : $_POST['txtDescription'];
        $chkGraduate  = $_POST['chkGraduate'] == 'true' ? 1 : 0;
        $chkUnderGraduate  = $_POST['chkUnderGraduate'] == 'true' ? 1 : 0;
        $txtNoOfYear  = ($_POST['txtNoOfYear'] == 'undefined' || $_POST['txtNoOfYear'] == '') ? 0 : $_POST['txtNoOfYear'];

		$actionid = $majorid == 0 ? 1 : 2;
		
		if($txtMajor == ''){throw new Exception("Enter College Major Name.");}
		if($txtDescription == ''){throw new Exception("Enter College Major Description.");}
		if($txtNoOfYear == 0){throw new Exception("Enter No of Year.");}

		$sql = "SELECT * FROM COLLEGE_MAJOR_MASTER WHERE MAJOR='$txtMajor' AND NO_OF_YEARS=$txtNoOfYear AND MAJORID!=$majorid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [COLLEGE_MAJOR_MASTER_SP]$actionid,$majorid,'$txtMajor','$txtDescription',$chkUnderGraduate,$chkGraduate,$txtNoOfYear,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($majorid))$data['message'] = 'Record successfully updated';
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
/* ========== SAVE DATA =========== */




/* ========== GET COLLEGE MAJOR =========== */
 function getCollegeMajor($mysqli){
	try
	{
		$query = "SELECT MAJORID,MAJOR,MAJOR_DESC,UNDERGRADUATE,GRADUATE,NO_OF_YEARS FROM COLLEGE_MAJOR_MASTER WHERE ISDELETED=0 ORDER BY MAJOR";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['MAJORID'] = (int) $row['MAJORID'];
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
/* ========== GET COLLEGE MAJOR =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $MAJORID = ($_POST['MAJORID'] == 'undefined' || $_POST['MAJORID'] == '') ? 0 : $_POST['MAJORID'];  
			if($MAJORID == 0){throw new Exception('MAJORID NOT FOUND.');}
			$delQuery = "EXEC [COLLEGE_MAJOR_MASTER_SP] 3,$MAJORID,'','',0,0,0,$userid";
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
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







