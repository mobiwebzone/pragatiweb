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
        case "getStudentByPlanProduct":getStudentByPlanProduct($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ Get Report =============*/ 
 function getReport($mysqli){
	try
	{
		$data = array();
		$txtFromDT = ($_POST['txtFromDT'] == 'undefined' || $_POST['txtFromDT'] == '') ? '' : $_POST['txtFromDT'];
		$txtToDT = ($_POST['txtToDT'] == 'undefined' || $_POST['txtToDT'] == '') ? '' : $_POST['txtToDT'];
		$REGIDS_ARRAY = (!isset($_POST['REGIDS'])) ? array() : $_POST['REGIDS'];
		if(!$REGIDS_ARRAY || count($REGIDS_ARRAY)<=0) throw new Exception('Select Student First.');
		
		// $data['REGIDS123'] = in_array(1332,array(1045, 202, 1044, 1332));
		$data['REGIDS'] = $REGIDS_ARRAY;
		// echo json_encode($data);exit;
		// if($ddlPlan<=0) throw new Exception('Plan Not Found.');

		$MAIN_DATA = array();
		$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

		$query = "EXEC [RPT_STUDENT_ATTENDANCE_PAYMENT_P2] '$txtFromDT','$txtToDT'";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);

		// GET WEEKS
		$WEEK_ARRAY = array();
		set_time_limit(0);
		while ($rowWeeks = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$WEEK_ARRAY[] = $rowWeeks['WEEKNO'];
			$data['WEEKS'][]=$rowWeeks;
		}


		$REGID1=$REGID2=0;
		$IDX=0;
		sqlsrv_next_result($result);
		set_time_limit(0);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['PAYMENTREMARKS']= rtrim($row['PAYMENTREMARKS'],', ');
			$row['PAYMENTREMARKS']=  str_replace(',',' / ',$row['PAYMENTREMARKS']);
			$REGID1=(int)$row['REGID'];
			// $CHK = in_array($REGID1,$REGIDS_ARRAY);
			// $data['CHK'][] = $CHK;
			// $data['R'][] = $row['REGID'];
			$data['$REGID1'][] =$row['REGID'];
		
			if(in_array($row['REGID'],$REGIDS_ARRAY)){
				if($REGID1!=$REGID2){
					$IDX++;
					$data['data'][$IDX][] = $row;
				}else{
					$data['data'][$IDX][] = $row;
				}
			}
			
			$REGID2=$REGID1;
			// for($i=0;$i<count($WEEK_ARRAY);$i++){
			// 	$WEEK = $WEEK_ARRAY[$i];
			// 	if($WEEK == $row['WEEKNO']){
			// 		$data['data'][$WEEK][] = $row;
			// 	}
			// }


			// $data['data'][]=$row;
		}


		
		

		// $data['MAIN_DATA']=$MAIN_DATA;
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



/*============ Get Student By Plan_Product =============*/ 
function getStudentByPlanProduct($mysqli){
	try
	{
		global $locid;
		$data = array();
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$data['$planid']=$planid;
		$classof = (!empty($_POST['CLASSOF'])) ? $_POST['CLASSOF'] : '';
		// if($classof == '')throw new Exception('Class Of Not Found.');
		$classof = $classof !== '' ? implode(',',$classof) : '';
		$data['$classof']=$classof;
		$subject = (!empty($_POST['SUBJECT'])) ? $_POST['SUBJECT'] : '';
		// if($subject == '')throw new Exception('Subject Not Found.');
		$subject = $subject !== '' ? implode(',',$subject) : '';
		$data['$subject']=$subject;

		$ddlLocation = ($_POST['ddlLocation']=='undefined' || $_POST['ddlLocation']=='') ? 0 : $_POST['ddlLocation'];
		$LID = $ddlLocation>0?$ddlLocation:$locid;

		// $query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(EMAIL,'')EMAIL,
		// ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, ISNULL(P1_PHONE,'')P1_PHONE,ISNULL(P1_EMAIL,'')P1_EMAIL,
		// ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,ISNULL(P2_PHONE,'')P2_PHONE,ISNULL(P2_EMAIL,'')P2_EMAIL
		// FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))
		// ORDER BY label";

		$query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,
		ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE,
		ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL,
		ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, 
		ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE,
		ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL,
		ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,
		ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE,
		ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND LOCATIONID=$LID AND
		REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))";
		if($classof!='') $query .= " AND CLASSOF IN ($classof)";
		if($subject!='') $query .= " AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE CSUBID IN ($subject))";
		$query .= " ORDER BY label";

		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		$data['COUNT'] = $count;
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
				$row['FINAL_PHONE'] = '';
				$row['FINAL_EMAIL'] = '';

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				$row['PHONE'] = is_numeric($row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';

				if (filter_var($row['EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['EMAIL']) > 0 && $row['EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL'].', ';
				}
				
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				$row['P1_PHONE'] = is_numeric($row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';

				if (filter_var($row['P1_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P1_EMAIL']) > 0 && $row['P1_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				$row['P2_PHONE'] = is_numeric($row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';

				if (filter_var($row['P2_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P2_EMAIL']) > 0 && $row['P2_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P2_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));

				$row['FINAL_EMAIL'] = rtrim($row['FINAL_EMAIL'],', ');
				$row['FINAL_EMAIL'] = implode(", ",array_unique(explode(", ",$row['FINAL_EMAIL'])));

				// $row['FINAL_PHONE'] = ($row['PHONE'] && $row['PHONE'] != '') ? $row['PHONE'] : (($row['P1_PHONE'] && $row['P1_PHONE'] != '') ? $row['P1_PHONE'] : (($row['P2_PHONE'] && $row['P2_PHONE'] != '') ? $row['P2_PHONE'] : ''));
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
/*============ Get Student By Plan_Product =============*/ 

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







