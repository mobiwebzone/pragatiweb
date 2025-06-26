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
        case "getTeacherReport":getTeacherReport($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get Teacher Report =============*/ 
 function getTeacherReport($mysqli){
	try
	{
		$data = array();
		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $txtFromDT  = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
        $txtToDT  = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

		$query = "SELECT TAID,CONVERT(VARCHAR,ATTDATE,106)ATTDATE,TEACHERID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TA.TEACHERID)FULLNAME,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=TA.PLANID)PLANNAME,
		CONVERT(VARCHAR,TIME_IN,100)TIME_IN,CONVERT(VARCHAR,TIME_OUT,100)TIME_OUT,
		CONVERT(VARCHAR,DATEADD(SECOND, - DATEDIFF(SECOND, TIME_OUT, TIME_IN), '00:00:00'),8) AS NO_OF_HOURS,
		DATEDIFF(second, CONVERT(VARCHAR,TIME_IN,8), CONVERT(VARCHAR,TIME_OUT,8)) / 3600.0 AS NO_OF_HOURS_DECIMAL,REMARKS
		FROM TEACHER_ATTENDANCE TA WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";

		if($ddlTeacher > 0){
			$query .=" AND TEACHERID=$ddlTeacher";
		}else{
			$query .=" AND TEACHERID IN (SELECT UID FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0)";
		}
		$query .=" ORDER BY (SELECT LASTNAME FROM USERS WHERE UID=TA.TEACHERID),PLANID,CONVERT(DATE,ATTDATE,105)";

		$data['$query ']=$query;

		$result = sqlsrv_query($mysqli, $query);

		$TOTAL_NOH = array();
		$TOTAL_NOH_DECIMAL = 0;
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TAID'] = (int) $row['TAID'];
			$row['NO_OF_HOURS'] = substr($row['NO_OF_HOURS'], 0, -3);
			$row['NO_OF_HOURS_DECIMAL'] = substr($row['NO_OF_HOURS_DECIMAL'], 0, -4);
			$data['data'][] = $row;

			$TOTAL_NOH[] = $row['NO_OF_HOURS'];
			$TOTAL_NOH_DECIMAL = number_format($TOTAL_NOH_DECIMAL + $row['NO_OF_HOURS_DECIMAL'],2);
		}


		// Get Attendance Summry
		// $querySummry = "SELECT COUNT(PLANID),(SELECT PLANNAME FROM PLANS WHERE PLANID=TA.PLANID)PLANNAME,
		// (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TA.TEACHERID)FULLNAME,
		// CONVERT(VARCHAR,DATEADD(ms, SUM(DATEDIFF(ms, '00:00:00.000', convert(datetime,DATEADD(SECOND, - DATEDIFF(SECOND, TIME_OUT, TIME_IN), '00:00:00')))), '00:00:00.000'),8) AS NO_OF_HOURS,
		// CAST(sum((DATEDIFF(ss,TIME_IN,TIME_OUT))/3600) AS VARCHAR) +':'+ CAST(sum(((DATEDIFF(ss,TIME_IN,TIME_OUT))-3600 * (DATEDIFF(ss,TIME_IN,TIME_OUT)/3600))/60) AS VARCHAR) NO_OF_HOURS2,
		// DATEDIFF(second, 
		// 	DATEADD(ms, SUM(DATEDIFF(ms, '00:00:00.000', CONVERT(VARCHAR,TIME_IN,8))), '00:00:00.000'),
		// 	DATEADD(ms, SUM(DATEDIFF(ms, '00:00:00.000', CONVERT(VARCHAR,TIME_OUT,8))), '00:00:00.000')
		// ) / 3600.0 AS NO_OF_HOURS_DECIMAL
		// FROM TEACHER_ATTENDANCE TA 
		// WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'";

		// if($ddlTeacher > 0){
		// 	$querySummry .=" AND TEACHERID=$ddlTeacher";
		// }else{
		// 	$querySummry .=" AND TEACHERID IN (SELECT UID FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0)";
		// }
		// $querySummry .=" GROUP BY PLANID,TEACHERID 
		// 				ORDER BY (SELECT LASTNAME FROM USERS WHERE UID=TA.TEACHERID),PLANID";

		$querySummry = "EXEC [GET_TEACHER_ATT_SUMMARY]$ddlLocation,'$txtFromDT','$txtToDT',$ddlTeacher";

		$resultSummry = sqlsrv_query($mysqli, $querySummry);
		$TOTAL_NOH_SUM = array();
		$TOTAL_NOH_DECIMAL_SUM = 0;
		$dn=0;
		$TEACHERID=$TEACHERID_OLD=0;
		$idx=-1;
		$S_DATA = array();
		while ($rowSummry = sqlsrv_fetch_array($resultSummry,SQLSRV_FETCH_ASSOC)) {
			// $rowSummry['NO_OF_HOURS'] = substr($rowSummry['NO_OF_HOURS'], 0, -3);
			$rowSummry['NO_OF_HOURS_DECIMAL'] = substr($rowSummry['NO_OF_HOURS_DECIMAL'], 0, -4);

			$TEACHERID = $rowSummry['TEACHERID'];
			if($TEACHERID!=$TEACHERID_OLD)$idx++;
			$S_DATA[$idx]['TEACHERID'] = $rowSummry['TEACHERID'];
			$S_DATA[$idx]['FULLNAME'] = $rowSummry['FULLNAME'];
			$S_DATA[$idx]['NO_OF_HOURS'][] = $rowSummry['NO_OF_HOURS'];
			$S_DATA[$idx]['NO_OF_HOURS_DECIMAL'][] = (float)$rowSummry['NO_OF_HOURS_DECIMAL'];


			
			$data['Summry'][] = $rowSummry;
			$TOTAL_NOH_SUM[] = $rowSummry['NO_OF_HOURS'];
			$TOTAL_NOH_DECIMAL_SUM = number_format($TOTAL_NOH_DECIMAL_SUM + $rowSummry['NO_OF_HOURS_DECIMAL'],2);
			

			$TEACHERID_OLD = $TEACHERID;
		}

		if(count($S_DATA)>0){

			foreach($S_DATA as $d){
				$NOH = CalculateTime($d['NO_OF_HOURS']);
				$NOH_D = round(array_sum($d['NO_OF_HOURS_DECIMAL']),2);
				// $NOH_D = 0;
				$data['FINAL_DATA'][] = ['TEACHERID'=>$d['TEACHERID'],'FULLNAME'=>$d['FULLNAME'],'NO_OF_HOURS'=>$NOH,'NO_OF_HOURS_DECIMAL'=>$NOH_D];
			}
		}
		
		$data['TOTAL_NOH'] = CalculateTime($TOTAL_NOH);
		$data['TOTAL_NOH_DECIMAL'] = $TOTAL_NOH_DECIMAL;
		$data['TOTAL_NOH_SUM'] = CalculateTime($TOTAL_NOH_SUM);
		$data['TOTAL_NOH_DECIMAL_SUM'] = $TOTAL_NOH_DECIMAL_SUM;
		$data['querySummry'] = $querySummry;
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $hmid = ($_POST['hmid'] == 'undefined' || $_POST['hmid'] == '') ? 0 : $_POST['hmid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [HOLIDAYS_MASTER_SP] 3,$hmid,0,'','','',$userid");
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}




function CalculateTime($times) {
	$i = 0;
	foreach ($times as $time) {
		sscanf($time, '%d:%d', $hour, $min);
		$i += $hour * 60 + $min;
	}

	if($h = floor($i / 60)) {
		$i %= 60;
	}

	return sprintf('%02d:%02d', $h, $i);
}

// $date[] = '02:32';
// $date[] = '01:29';
// echo CalculateTime($date);




function invalidRequest(){
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







