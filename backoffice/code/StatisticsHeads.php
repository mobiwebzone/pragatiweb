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
		case "saveExcelFile":saveExcelFile($conn);break;
        case "saveData":saveData($conn);break;
        case "getStaHeads":getStaHeads($conn);break;
        case "delete":delete($conn);break;
		
		// ============ SUB HEAD ===========
        case "saveDataSubHead":saveDataSubHead($conn);break;
        case "getStaSubHeads":getStaSubHeads($conn);break;
        case "deleteSubHead":deleteSubHead($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

 // =============== SAVE EXCEL DATA ==================
 function saveExcelFile($mysqli){
	try
	{
		$data = array();
		global $userid;
	
		// $ehid  = ($_POST['ehid'] == 'undefined' || $_POST['ehid'] == '') ? 0 : $_POST['ehid'];
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];

		// if($ehid == 0){throw new Exception("EHID Error.");}
		if($ddlLocation == 0){throw new Exception("Locid Error.");}
		if($txtUploadExcel == ''){throw new Exception("Please Select Excel File.");}

		
		$filename=$_FILES["txtUploadExcelData"]["tmp_name"];
		if($_FILES["txtUploadExcelData"]["size"] > 0)
		{
			$file = fopen($filename, "r");
			$count = 0;
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
			{ 
				$count++;
				if($count>1){
					$data['$emapData'][]=$emapData[0];

					// $CalcAllow = $emapData[4] == 'N' ? 0 : 1;
					// $GridIn = $emapData[5] == 'N' ? 0 : 1;
					// $WordProb = $emapData[6] == 'N' ? 0 : 1;
					$HEAD = $emapData[0];

					$sql = "SELECT * FROM STATISTICS_HEADS WHERE LOC_ID=$ddlLocation AND STATISTICS_HEAD='$HEAD' AND SHID!=0 AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count == 0)
					{
						$queryHead="EXEC [STATISTICS_HEADS_SP] 1,0,$ddlLocation,'$HEAD',$userid";
						$stmt=sqlsrv_query($mysqli, $queryHead);
						
						if($stmt === false)
						{
							// die( print_r( sqlsrv_errors(), true));
							// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
							$data['success'] = false;
							$data['query'] = $queryHead;
						}
						else
						{
							$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
							$data['GET_SHID'] = (int)$row['SHID'];
							$GET_SHID = (int)$row['SHID'];

							if($GET_SHID>0){
								$querySUB="EXEC [STATISTICS_SUB_HEADS_SP] 1,0,$GET_SHID,'$HEAD',$userid";
								$data['querySUB'] = $querySUB;
								sqlsrv_query($mysqli, $querySUB);
							}

							// $data['success'] = true;
							// if(!empty($ehid))$data['message'] = 'Expense Head successfully updated.';
							// else $data['message'] = 'Expense Head successfully inserted.';
						}
						
						// echo json_encode($data);exit;
					}
					// else
					// {
					// 	$data['success'] = false;
					// 	$data['message'] = 'Expense Head already exists';
					// 	echo json_encode($data);exit;
					// }

					
				}  
			}
			fclose($file);
			$data['message'] = "<i class='fa fa-check'> Data successfully uploaded.";
			$data['success'] = true;
			echo json_encode($data);exit;
		}
		else {
			$data['success'] = false;
			$data['message'] = 'Upload error';
		}
		$data['message'] = 'failed Outside';
		// $data['tmp_name']=$_FILES["txtUploadExcelData"]["tmp_name"];
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
// =============== SAVE EXCEL DATA ==============


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% HEAD SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$shid  = ($_POST['shid'] == 'undefined' || $_POST['shid'] == '') ? 0 : $_POST['shid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtStaHead  = $_POST['txtStaHead'] == 'undefined' ? '' : $_POST['txtStaHead'];
	
	$actionid = $shid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($txtStaHead == ''){throw new Exception("Please Enter 'Statistics Head Name'.");}


	$sql = "SELECT * FROM STATISTICS_HEADS WHERE LOC_ID=$ddlLocation AND STATISTICS_HEAD='$txtStaHead' AND SHID!=$shid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [STATISTICS_HEADS_SP] $actionid,$shid,$ddlLocation,'$txtStaHead',$userid";
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
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_SHID'] = (int)$row['SHID'];

			$data['success'] = true;
			if(!empty($shid))$data['message'] = 'Statistics Head successfully updated.';
			else $data['message'] = 'Statistics Head successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Statistics Head already exists';
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






/*============ GET STATISTICS HEADS =============*/ 
function getStaHeads($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT SHID,LOC_ID,STATISTICS_HEAD,
		(SELECT STATISTICS_SUB_HEAD+', ' FROM STATISTICS_SUB_HEADS WHERE ISDELETED=0 AND SHID=RH.SHID FOR XML PATH(''))STATISTICS_SUB_HEAD
		FROM STATISTICS_HEADS RH WHERE ISDELETED=0 AND LOC_ID=$ddlLocation ORDER BY STATISTICS_HEAD";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['STATISTICS_SUB_HEAD'] = rtrim($row['STATISTICS_SUB_HEAD'],', ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Statistics Head Not Found.';
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
/*============ GET STATISTICS HEADS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SHID = ($_POST['SHID'] == 'undefined' || $_POST['SHID'] == '') ? 0 : $_POST['SHID'];
			if($SHID == 0){throw new Exception('SHID Error.');}
			$delQuery = "EXEC [STATISTICS_HEADS_SP] 3,$SHID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Statistics head successfully deleted.';
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
/* =========== DELETE =========== */ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SUB HEAD SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE SUB HEAD DATA ==================
function saveDataSubHead($mysqli){
	try
	{
	global $userid;
	$data = array();

	$sshid  = ($_POST['sshid'] == 'undefined' || $_POST['sshid'] == '') ? 0 : $_POST['sshid'];
	$shid  = ($_POST['shid'] == 'undefined' || $_POST['shid'] == '') ? 0 : $_POST['shid'];
	$txtStaSubHead  = $_POST['txtStaSubHead'] == 'undefined' ? '' : $_POST['txtStaSubHead'];
	
	$actionid = $sshid == 0 ? 1 : 2;

	if($shid == 0){throw new Exception("SHID Not Found.");}
	if($txtStaSubHead == ''){throw new Exception("Please Enter 'Sub Head Name'.");}


	$sql = "SELECT * FROM STATISTICS_SUB_HEADS WHERE SHID=$shid AND STATISTICS_SUB_HEAD='$txtStaSubHead' AND SSHID!=$sshid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [STATISTICS_SUB_HEADS_SP] $actionid,$sshid,$shid,'$txtStaSubHead',$userid";
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
			$data['success'] = true;
			if(!empty($sshid))$data['message'] = 'Sub head successfully updated.';
			else $data['message'] = 'Sub head successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Sub head already exists';
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
 // =============== SAVE SUB HEAD DATA ==============






/*============ GET STATISTICS SUB HEADS =============*/ 
function getStaSubHeads($mysqli){
	try
	{
		$data = array();
		$shid = ($_POST['shid'] == 'undefined' || $_POST['shid'] == '') ? 0 : $_POST['shid'];
		if($shid == 0) throw new Exception('SHID Not Found.');

		$query = "SELECT SSHID,SHID,STATISTICS_SUB_HEAD FROM STATISTICS_SUB_HEADS WHERE ISDELETED=0 AND SHID=$shid ORDER BY STATISTICS_SUB_HEAD";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Sub Head not found.';
			$data['success'] = false;
		}
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
/*============ GET STATISTICS SUB HEADS =============*/ 






/* =========== DELETE SUB HEAD =========== */ 
function deleteSubHead($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SSHID = ($_POST['SSHID'] == 'undefined' || $_POST['SSHID'] == '') ? 0 : $_POST['SSHID'];
			if($SSHID == 0){throw new Exception('SSHID Error.');}
			$delQuery = "EXEC [STATISTICS_SUB_HEADS_SP] 3,$SSHID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Sub Head successfully deleted.';
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
/* =========== DELETE SUB HEAD =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}



?>



