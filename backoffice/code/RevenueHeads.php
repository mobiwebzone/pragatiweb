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
        case "getRevHeads":getRevHeads($conn);break;
        case "delete":delete($conn);break;
		
		// ============ SUB HEAD ===========
        case "saveDataSubHead":saveDataSubHead($conn);break;
        case "getRevSubHeads":getRevSubHeads($conn);break;
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

					$sql = "SELECT * FROM REVENUE_HEADS WHERE LOC_ID=$ddlLocation AND REVENUE_HEAD='$HEAD' AND RHID!=0 AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count == 0)
					{
						$queryHead="EXEC [REVENUE_HEADS_SP] 1,0,$ddlLocation,'$HEAD',$userid";
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
							$data['GET_RHID'] = (int)$row['RHID'];
							$GET_RHID = (int)$row['RHID'];

							if($GET_RHID>0){
								$querySUB="EXEC [REVENUE_SUB_HEADS_SP] 1,0,$GET_RHID,'$HEAD',$userid";
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

	$rhid  = ($_POST['rhid'] == 'undefined' || $_POST['rhid'] == '') ? 0 : $_POST['rhid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtRevHead  = $_POST['txtRevHead'] == 'undefined' ? '' : $_POST['txtRevHead'];
	
	$actionid = $rhid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($txtRevHead == ''){throw new Exception("Please Enter 'Revenue Head Name'.");}


	$sql = "SELECT * FROM REVENUE_HEADS WHERE LOC_ID=$ddlLocation AND REVENUE_HEAD='$txtRevHead' AND RHID!=$rhid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [REVENUE_HEADS_SP] $actionid,$rhid,$ddlLocation,'$txtRevHead',$userid";
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
			$data['GET_RHID'] = (int)$row['RHID'];

			$data['success'] = true;
			if(!empty($rhid))$data['message'] = 'Revenue Head successfully updated.';
			else $data['message'] = 'Revenue Head successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Revenue Head already exists';
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






/*============ GET REVENUE HEADS =============*/ 
function getRevHeads($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT RHID,LOC_ID,REVENUE_HEAD,
		(SELECT REVENUE_SUB_HEAD+', ' FROM REVENUE_SUB_HEADS WHERE ISDELETED=0 AND RHID=RH.RHID FOR XML PATH(''))REVENUE_SUB_HEAD
		FROM REVENUE_HEADS RH WHERE ISDELETED=0 AND LOC_ID=$ddlLocation ORDER BY REVENUE_HEAD";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['REVENUE_SUB_HEAD'] = rtrim($row['REVENUE_SUB_HEAD'],', ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Revenue Head Not Found.';
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
/*============ GET REVENUE HEADS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RHID = ($_POST['RHID'] == 'undefined' || $_POST['RHID'] == '') ? 0 : $_POST['RHID'];
			if($RHID == 0){throw new Exception('RHID Error.');}
			$delQuery = "EXEC [REVENUE_HEADS_SP] 3,$RHID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Revenue head successfully deleted.';
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

	$rshid  = ($_POST['rshid'] == 'undefined' || $_POST['rshid'] == '') ? 0 : $_POST['rshid'];
	$rhid  = ($_POST['rhid'] == 'undefined' || $_POST['rhid'] == '') ? 0 : $_POST['rhid'];
	$txtRevSubHead  = $_POST['txtRevSubHead'] == 'undefined' ? '' : $_POST['txtRevSubHead'];
	
	$actionid = $rshid == 0 ? 1 : 2;

	if($rhid == 0){throw new Exception("RHID Not Found.");}
	if($txtRevSubHead == ''){throw new Exception("Please Enter 'Sub Head Name'.");}


	$sql = "SELECT * FROM REVENUE_SUB_HEADS WHERE RHID=$rhid AND REVENUE_SUB_HEAD='$txtRevSubHead' AND RSHID!=$rshid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [REVENUE_SUB_HEADS_SP] $actionid,$rshid,$rhid,'$txtRevSubHead',$userid";
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
			if(!empty($rshid))$data['message'] = 'Sub head successfully updated.';
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






/*============ GET REVENUE SUB HEADS =============*/ 
function getRevSubHeads($mysqli){
	try
	{
		$data = array();
		$rhid = ($_POST['rhid'] == 'undefined' || $_POST['rhid'] == '') ? 0 : $_POST['rhid'];
		if($rhid == 0) throw new Exception('RHID Not Found.');

		$query = "SELECT RSHID,RHID,REVENUE_SUB_HEAD FROM REVENUE_SUB_HEADS WHERE ISDELETED=0 AND RHID=$rhid ORDER BY REVENUE_SUB_HEAD";
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
/*============ GET REVENUE SUB HEADS =============*/ 






/* =========== DELETE SUB HEAD =========== */ 
function deleteSubHead($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RSHID = ($_POST['RSHID'] == 'undefined' || $_POST['RSHID'] == '') ? 0 : $_POST['RSHID'];
			if($RSHID == 0){throw new Exception('RSHID Error.');}
			$delQuery = "EXEC [REVENUE_SUB_HEADS_SP] 3,$RSHID,0,'',$userid";
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







