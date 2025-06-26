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
        case "getReport":getReport($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Student Test Report =============*/ 
function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];

		$PLANIDS_ARRAY = (!isset($_POST['PLANIDS'])) ? array() : $_POST['PLANIDS'];
		if(!$PLANIDS_ARRAY || count($PLANIDS_ARRAY)<=0) throw new Exception('Select Plans First.');
		$PLANIDS_ARRAY = implode(",",$PLANIDS_ARRAY);
		$data['PLANIDS'] = $PLANIDS_ARRAY;

		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		$REGIDS_ARRAY = implode(",",$REGIDS_ARRAY);
		$data['REGIDS'] = $REGIDS_ARRAY;

		

		// ###############
		// GET ATTENDANCE
		// ###############
		$queryAtt = "SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=SC.PLANID)PLANNAME,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCC.REGID)STUDENTNAME,
		CONVERT(VARCHAR,SC.CDATE,106)CDATE,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SC.CHAPID)CHAPTER,
		SCC.REMARK,SCC.DOC,SCC.HOMEWORK_DONE,SCC.STUDENTWORK,SCC.HOMEWORK_IMG
		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCC,STUDENT_COURSE_COVERAGE SC 
		WHERE SCC.SCCID=SC.SCCID AND SCC.ISDELETED=0 AND SC.ISDELETED=0 AND REGID IN ($REGIDS_ARRAY)
		--AND PLANID IN ($PLANIDS_ARRAY)
		AND CONVERT(DATE,SC.CDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		ORDER BY PLANNAME ASC, STUDENTNAME ASC, CONVERT(DATE,SC.CDATE,106) DESC";
		$data['$queryAtt']=$queryAtt;
		$countAtt = unique($queryAtt);
		if($countAtt>0){
			$resultAtt = sqlsrv_query($mysqli, $queryAtt);
			set_time_limit(0);
			while ($rowAtt = sqlsrv_fetch_array($resultAtt,SQLSRV_FETCH_ASSOC)) {
				$data['data_attendance'][] = $rowAtt;
			}
			$data['successAtt'] = true;
		}else{
			$data['successAtt'] = false;
			$data['messageAtt'] = 'Attendance not found.';
		}
		


		// #############
		// GET RECEIPTS
		// #############
		$queryRec = "SELECT ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID),'-')PLANNAME,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SR.REGID)STUDENTNAME,
		CONVERT(VARCHAR,RECDATE,106)RDATE,INSTALLMENT,AMOUNT,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=SR.PMID)PAYMENTMODE,REFNO,REMARK
		FROM STUDENT_RECEIPTS SR
		WHERE ISDELETED=0 AND CONVERT(DATE,RECDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT' 
		AND REGID IN ($REGIDS_ARRAY) --AND PLANID IN ($PLANIDS_ARRAY)
		ORDER BY PLANNAME ASC, STUDENTNAME ASC, CONVERT(DATE,RECDATE,106) DESC";
		$data['$queryRec']=$queryRec;
		// $data['success'] = false;
		// echo json_encode($data);exit;
		$countRec = unique($queryRec);
		if($countRec>0){
			$resultRec = sqlsrv_query($mysqli, $queryRec);
			$TOTAL_AMT = 0;
			set_time_limit(0);
			while ($rowRec = sqlsrv_fetch_array($resultRec,SQLSRV_FETCH_ASSOC)) {
				$rowRec['AMOUNT'] =  (float) $rowRec['AMOUNT'];
				$TOTAL_AMT += (float) $rowRec['AMOUNT'];
				$data['data_receipts'][]=$rowRec;
			}
			$data['TOTAL_AMT'] = $TOTAL_AMT;
			$data['successRec'] = true;
		}else{
			$data['successRec'] = false;
			$data['messageRec'] = 'Receipts not found.';
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
/*============ Get Student Test Report =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







