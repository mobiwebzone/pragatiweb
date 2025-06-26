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
        case "getRevenueBudget":getRevenueBudget($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET REVENUE BUDGET =============*/ 
function getRevenueBudget($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtYear = ($_POST['txtYear'] =='undefined' || $_POST['txtYear'] =='') ? 0 : $_POST['txtYear'];
		// $NoOfYear = ($_POST['NoOfYear'] =='undefined' || $_POST['NoOfYear'] =='') ? 0 : $_POST['NoOfYear'];
		// $txtFactor = ($_POST['txtFactor'] =='undefined' || $_POST['txtFactor'] =='') ? 0 : $_POST['txtFactor'];


		$query = "EXEC [FY_REV_BUDGET_ACTUAL_TRACKING]$ddlLocation,$txtYear";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$result = sqlsrv_query($mysqli, $query);
		$MONTHS = array(1=>[],2=>[],3=>[],4=>[],5=>[],6=>[],7=>[],8=>[],9=>[],10=>[],11=>[],12=>[]);
		$REV_HEADS_ONLY = array();
		$REV_HEADS = array();
		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$row['AMOUNT'] = (float)$row['AMOUNT'];
			$row['BUDGET'] = (float)$row['BUDGET'];
			// $REV_HEADS[] = in_array($row['REVHEAD'],$REV_HEADS) ? 
			if(in_array($row['REVHEAD'],$REV_HEADS_ONLY) == false) {
				$REV_HEADS_ONLY[] = $row['REVHEAD'];
				$REV_HEADS[] = ['RHID'=>$row['RHID'],'HEAD'=>$row['REVHEAD']];
			}
			$MON = (int)$row['MON'];
			$MONTHS[$MON][] = ['AMOUNT'=>$row['AMOUNT'],'BUDGET'=>$row['BUDGET']];
			$data['data'][] = $row;
		}
		$data['$REV_HEADS'] = $REV_HEADS;
		$data['$MONTHS'] = $MONTHS;
		$index = 0;
		foreach($REV_HEADS_ONLY as $key=>$value){
			
			$data['key'][]=$key;
			$AMT=$BUD=0;
			for($i=1; $i<=12; $i++){
				// $REV_HEADS[$key][$i] = ['AMOUNT'=>0,'BUDGET'=>0];
				$AMT+=$MONTHS[$i][$key]['AMOUNT'];
				$BUD+=$MONTHS[$i][$key]['BUDGET'];
				$REV_HEADS[$key][$i] = $MONTHS[$i][$key];
			}
			$REV_HEADS[$key]['TOTAL'] = ['AMOUNT'=>$AMT,'BUDGET'=>$BUD];
			$index++;
		}
		$data['FINAL_DATA'] = $REV_HEADS;
		$data['success'] = true;

		// ======= GET NOOFYEAR / FACTOR
		$query = "SELECT DISTINCT NOOFYEAR,FACTOR FROM FY_REVENUE_BUDGET WHERE LOCID=$ddlLocation AND FY=$txtYear";
		$data['$query']=$query;
		$count = unique($query);
		// echo json_encode($data);exit;
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['FACTOR'] = (float) $row['FACTOR'];
				$data['NOF_FACTOR'][] = $row;
				$data['successBudget'] = true;
			}
		}else{
			$data['successBudget'] = false;
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
/*============ GET REVENUE BUDGET =============*/ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







