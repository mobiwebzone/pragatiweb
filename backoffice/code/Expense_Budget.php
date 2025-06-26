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
        case "getExpenseBudget":getExpenseBudget($conn);break;
        case "getBudgetByYear":getBudgetByYear($conn);break;
        case "saveBudget":saveBudget($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET EXPENSE BUDGET =============*/ 
function getExpenseBudget($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtYear = ($_POST['txtYear'] =='undefined' || $_POST['txtYear'] =='') ? 0 : $_POST['txtYear'];
		$NoOfYear = ($_POST['NoOfYear'] =='undefined' || $_POST['NoOfYear'] =='') ? 0 : $_POST['NoOfYear'];
		$txtFactor = ($_POST['txtFactor'] =='undefined' || $_POST['txtFactor'] =='') ? 0 : $_POST['txtFactor'];


		$query = "EXEC [FY_EXP_BUDGET]$ddlLocation,$txtYear,$NoOfYear,$txtFactor";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$result = sqlsrv_query($mysqli, $query);
		$MONTHS = array(1=>[],2=>[],3=>[],4=>[],5=>[],6=>[],7=>[],8=>[],9=>[],10=>[],11=>[],12=>[]);
		$EXP_HEADS_ONLY = array();
		$EXP_HEADS = array();
		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$row['AMOUNT'] = (float)$row['AMOUNT'];
			$row['BUDGET'] = (float)$row['BUDGET'];
			// $EXP_HEADS[] = in_array($row['EXPHEAD'],$EXP_HEADS) ? 
			if(in_array($row['EXPHEAD'],$EXP_HEADS_ONLY) == false) {
				$EXP_HEADS_ONLY[] = $row['EXPHEAD'];
				$EXP_HEADS[] = ['EHID'=>$row['EHID'],'HEAD'=>$row['EXPHEAD']];
			}
			$MON = (int)$row['MON'];
			$MONTHS[$MON][] = ['AMOUNT'=>$row['AMOUNT'],'BUDGET'=>$row['BUDGET']];
			$data['data'][] = $row;
		}
		$data['$EXP_HEADS'] = $EXP_HEADS;
		$data['$MONTHS'] = $MONTHS;
		$index = 0;
		foreach($EXP_HEADS_ONLY as $key=>$value){
			
			$data['key'][]=$key;
			for($i=1; $i<=12; $i++){
				// $EXP_HEADS[$key][$i] = ['AMOUNT'=>0,'BUDGET'=>0];
				$EXP_HEADS[$key][$i] = $MONTHS[$i][$key];
			}
			$index++;
		}
		$data['FINAL_DATA'] = $EXP_HEADS;
		$data['success'] = true;

		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET EXPENSE BUDGET =============*/ 



/*============ GET BUDGET BY YEAR =============*/ 
function getBudgetByYear($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtYear = ($_POST['txtYear'] =='undefined' || $_POST['txtYear'] =='') ? 0 : $_POST['txtYear'];

		$query = "SELECT DISTINCT NOOFYEAR,FACTOR FROM FY_EXPENSES_BUDGET WHERE LOCID=$ddlLocation AND FY=$txtYear";
		$data['$query']=$query;
		$count = unique($query);
		// echo json_encode($data);exit;
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['FACTOR'] = (float) $row['FACTOR'];
				$data['data'][] = $row;
				$data['success'] = true;
			}
		}else{
			$data['success'] = false;
			$data['message'] = 'Budget Not Found.';
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
/*============ GET BUDGET BY YEAR =============*/ 


// =============== SAVE DATA ==================
function saveBudget($mysqli){
	try
	{
	global $userid;
	$data = array();

	$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
	$txtYear = ($_POST['txtYear'] =='undefined' || $_POST['txtYear'] =='') ? 0 : $_POST['txtYear'];
	$EHID = ($_POST['EHID'] =='undefined' || $_POST['EHID'] =='') ? 0 : $_POST['EHID'];
	$MONTH = ($_POST['MONTH'] =='undefined' || $_POST['MONTH'] =='') ? 0 : $_POST['MONTH'];
	$VAL = ($_POST['VAL'] =='undefined' || $_POST['VAL'] =='') ? 0 : $_POST['VAL'];
	$NOOFYEAR = ($_POST['NOOFYEAR'] =='undefined' || $_POST['NOOFYEAR'] =='') ? 0 : $_POST['NOOFYEAR'];
	// $txtBudget = ($_POST['txtBudget'] =='undefined' || $_POST['txtBudget'] =='') ? array() : json_decode($_POST['txtBudget'],true);

	if($ddlLocation==0)throw new Exception('Select Location First.');
	if($txtYear==0)throw new Exception('Enter Year First.');


	$insertData = "UPDATE FY_EXPENSES_BUDGET SET BUDGET=$VAL,UPDATEID=$userid,UPDATEDATE=GETDATE() 
				WHERE EHID=$EHID AND LOCID=$ddlLocation AND FY=$txtYear AND MON=$MONTH  AND NOOFYEAR=$NOOFYEAR";
	$stmt = sqlsrv_query($mysqli, $insertData);
	if(!$stmt){
		$data['success'] = false;
		$data['message'] = 'Error.';			
	}else{
		$data['success'] = true;
		$data['message'] = 'Budget successfully update.';			
	}

	// $data['query'] = $txtBudget;
	// echo json_encode($data);exit;
	
	
	// $EHID_ARRAY = array();
	// $queryGetHeads = "SELECT EHID FROM EXPENSE_HEADS WHERE ISDELETED=0 AND LOC_ID=$ddlLocation ORDER BY EXPENSE_HEAD";
	// $resultHeads = sqlsrv_query($mysqli, $queryGetHeads);
	// while ($rowHeads = sqlsrv_fetch_array($resultHeads, SQLSRV_FETCH_ASSOC)) {
	// 	$EHID_ARRAY[] = $rowHeads['EHID'];
	// }
	// $data['EHID_ARRAY'] = $EHID_ARRAY;
	
	// if(count($txtBudget)>0){
		
	// 	for($i=0;$i<count($txtBudget);$i++){
	// 		$MONTH = $i+1;
	// 		$MONTH_DATA = $txtBudget[$i];

	// 		foreach($MONTH_DATA as $key=>$budget){
	// 			$EHID=$EHID_ARRAY[$key];
	// 			$insertData = "INSERT INTO FY_EXPENSES_BUDGET (LOCID,FY,EHID,MON,BUDGET,INSERTID)
	// 							VALUES ($ddlLocation,$txtYear,$EHID,$MONTH,$budget,$userid)";
	// 			sqlsrv_query($mysqli, $insertData);
	// 		}
			
	// 	}
	// 	$data['success'] = true;
	// 	$data['message'] = 'Budget successfully saved.';
		
	// }else{
	// 	throw new Exception('Budget Data Not Found.');
	// }
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







