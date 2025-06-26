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
        case "getExpHeads":getExpHeads($conn);break;
        case "delete":delete($conn);break;
		
		// ============ SUB HEAD ===========
        case "saveDataSubHead":saveDataSubHead($conn);break;
        case "getExpSubHeads":getExpSubHeads($conn);break;
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

					$sql = "SELECT * FROM EXPENSE_HEADS WHERE LOC_ID=$ddlLocation AND EXPENSE_HEAD='$HEAD' AND EHID!=0 AND ISDELETED=0";
					$row_count = unique($sql);
					if($row_count == 0)
					{
						$queryHead="EXEC [EXPENSE_HEADS_SP] 1,0,$ddlLocation,'$HEAD',$userid";
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
							$data['GET_EHID'] = (int)$row['EHID'];
							$GET_EHID = (int)$row['EHID'];

							if($GET_EHID>0){
								$querySUB="EXEC [EXPENSE_SUB_HEADS_SP] 1,0,$GET_EHID,'$HEAD',$userid";
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

	$ehid  = ($_POST['ehid'] == 'undefined' || $_POST['ehid'] == '') ? 0 : $_POST['ehid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtExpHead  = $_POST['txtExpHead'] == 'undefined' ? '' : $_POST['txtExpHead'];
	
	$actionid = $ehid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location'.");}
	if($txtExpHead == ''){throw new Exception("Please Enter 'Expense Head Name'.");}


	$sql = "SELECT * FROM EXPENSE_HEADS WHERE LOC_ID=$ddlLocation AND EXPENSE_HEAD='$txtExpHead' AND EHID!=$ehid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [EXPENSE_HEADS_SP] $actionid,$ehid,$ddlLocation,'$txtExpHead',$userid";
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
			$data['GET_EHID'] = (int)$row['EHID'];

			$data['success'] = true;
			if(!empty($ehid))$data['message'] = 'Expense Head successfully updated.';
			else $data['message'] = 'Expense Head successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Expense Head already exists';
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






/*============ GET EXPENSE HEADS =============*/ 
function getExpHeads($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT EHID,LOC_ID,EXPENSE_HEAD,
		(SELECT EXPENSE_SUB_HEAD+', ' FROM EXPENSE_SUB_HEADS WHERE ISDELETED=0 AND EHID=EH.EHID FOR XML PATH(''))EXPENSE_SUB_HEAD
		FROM EXPENSE_HEADS EH WHERE ISDELETED=0 AND LOC_ID=$ddlLocation ORDER BY EXPENSE_HEAD";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['EXPENSE_SUB_HEAD'] = rtrim($row['EXPENSE_SUB_HEAD'],', ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Expense Head Not Found.';
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
/*============ GET EXPENSE HEADS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $EHID = ($_POST['EHID'] == 'undefined' || $_POST['EHID'] == '') ? 0 : $_POST['EHID'];
			if($EHID == 0){throw new Exception('EHID Error.');}
			$delQuery = "EXEC [EXPENSE_HEADS_SP] 3,$EHID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Expense head successfully deleted.';
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

	$eshid  = ($_POST['eshid'] == 'undefined' || $_POST['eshid'] == '') ? 0 : $_POST['eshid'];
	$ehid  = ($_POST['ehid'] == 'undefined' || $_POST['ehid'] == '') ? 0 : $_POST['ehid'];
	$txtExpSubHead  = $_POST['txtExpSubHead'] == 'undefined' ? '' : $_POST['txtExpSubHead'];
	
	$actionid = $eshid == 0 ? 1 : 2;

	if($ehid == 0){throw new Exception("EHID Not Found.");}
	if($txtExpSubHead == ''){throw new Exception("Please Enter 'Sub Head Name'.");}


	$sql = "SELECT * FROM EXPENSE_SUB_HEADS WHERE EHID=$ehid AND EXPENSE_SUB_HEAD='$txtExpSubHead' AND ESHID!=$eshid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [EXPENSE_SUB_HEADS_SP] $actionid,$eshid,$ehid,'$txtExpSubHead',$userid";
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
			if(!empty($eshid))$data['message'] = 'Sub head successfully updated.';
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






/*============ GET EXPENSE SUB HEADS =============*/ 
function getExpSubHeads($mysqli){
	try
	{
		$data = array();
		$ehid = ($_POST['ehid'] == 'undefined' || $_POST['ehid'] == '') ? 0 : $_POST['ehid'];
		if($ehid == 0) throw new Exception('EHID Not Found.');

		$query = "SELECT ESHID,EHID,EXPENSE_SUB_HEAD FROM EXPENSE_SUB_HEADS WHERE ISDELETED=0 AND EHID=$ehid ORDER BY EXPENSE_SUB_HEAD";
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
/*============ GET EXPENSE SUB HEADS =============*/ 






/* =========== DELETE SUB HEAD =========== */ 
function deleteSubHead($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ESHID = ($_POST['ESHID'] == 'undefined' || $_POST['ESHID'] == '') ? 0 : $_POST['ESHID'];
			if($ESHID == 0){throw new Exception('ESHID Error.');}
			$delQuery = "EXEC [EXPENSE_SUB_HEADS_SP] 3,$ESHID,0,'',$userid";
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







