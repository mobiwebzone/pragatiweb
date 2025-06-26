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
        case "getPlans":getPlans($conn);break;
        case "getStudentByPlan":getStudentByPlan($conn);break;
        case "getTestByRegid":getTestByRegid($conn);break;
        case "getTestSection":getTestSection($conn);break;
        case "getTestSectionAttempts":getTestSectionAttempts($conn);break;
        case "getStudentAnswer":getStudentAnswer($conn);break;
        case "UpdateAnswerFinal":UpdateAnswerFinal($conn);break;
        case "DeleteAttempt":DeleteAttempt($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */






/*============ GET PLANS =============*/ 
 function getPlans($mysqli){
	try
	{
		$data = array();

		$query = "SELECT PLANID,PLANNAME FROM PLANS WHERE ISDELETED=0 ORDER BY PLANNAME";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Plans not found.';
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
/*============ Get PLANS =============*/ 





/*============ GET STUDENT BY PLAN =============*/ 
 function getStudentByPlan($mysqli){
	try
	{
		$data = array();
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		if($ddlPlan == 0) throw new Exception('PlanId not found.');
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$LOCID = $ddlLocation > 0 ? $ddlLocation : $_SESSION['USER_LOCID'];
       
	   $query = "SELECT REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)STUDENT
		FROM REGISTRATION_DETAILS RD
		WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID=$ddlPlan AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=1 AND REGID=RD.REGID)
		AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)";
		
		

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .= " AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID'].")";
		// }
		$query .=" ORDER BY STUDENT";

		
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Students not found.';
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
/*============ GET STUDENT BY PLAN =============*/ 






/*============ GET STUDENT TEST BY REGID =============*/ 
 function getTestByRegid($mysqli){
	try
	{
		$data = array();
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		if($ddlStudent == 0) throw new Exception('StudentId not found.');
		
		// $query = "SELECT DISTINCT TM.TESTID,TESTDESC,LEN(TESTDESC)ss,
		// ISNULL((SELECT TGID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TESTID=S.TESTID),0)TGID
		// FROM TEST_MASTER TM,TEST_SECTIONS S
		// WHERE TM.TESTID=S.TESTID AND TM.ISDELETED=0 AND S.ISDELETED=0
		// AND TM.TESTID IN (SELECT TESTID FROM TESTS_TO_PRODUCTS WHERE PRODUCTID IN
		// (SELECT PRODUCTID FROM PLAN_PRODUCTS WHERE PLANID IN 
		// (SELECT PLANID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND REGID=$ddlStudent AND DISCONTINUE_APPROVED=0)))
		// ORDER BY LEN(TESTDESC),TESTDESC";
		$query = "SELECT DISTINCT TESTID,(SELECT TESTDESC+' ('+CAST(TESTYEAR AS VARCHAR)+')' FROM TEST_MASTER WHERE TESTID=ST.TESTID)TESTDESC,
		(SELECT LEN(TESTDESC) FROM TEST_MASTER WHERE TESTID=ST.TESTID)ss
		FROM STUDENT_TESTS ST WHERE ISDELETED=0 AND REGID=$ddlStudent
		ORDER BY ss,TESTDESC";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Student Test not found.';
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
/*============ GET STUDENT TEST BY REGID =============*/






/*============ GET TEST SECTION BY TEST =============*/ 
 function getTestSection($mysqli){
	try
	{
		$data = array();
		$ddlTest = ($_POST['ddlTest'] == 'undefined' || $_POST['ddlTest'] == '') ? 0 : $_POST['ddlTest'];
		if($ddlTest == 0) throw new Exception('TestId not found.');
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		if($ddlStudent == 0) throw new Exception('Studentid not found.');
		$txtTestDate = ($_POST['txtTestDate'] == 'undefined' || $_POST['txtTestDate'] == '') ? '' : $_POST['txtTestDate'];
		
		// $query = "SELECT DISTINCT STID,REGID,TESTID,TSECID, ATTEMPT,
		// (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID AND ISDELETED=0) SECTION
		// FROM STUDENT_TESTS ST WHERE TESTID=$ddlTest --AND ATTEMPT=1 
		// AND ISDELETED=0 AND REGID=$ddlStudent
		// AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID AND ISDELETED=0) NOT LIKE '%BREAK%'";

		$query = "SELECT DISTINCT TSECID,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID AND ISDELETED=0) SECTION
		FROM STUDENT_TESTS ST WHERE TESTID=$ddlTest --AND ATTEMPT=1 
		AND ISDELETED=0 AND REGID=$ddlStudent
		AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=ST.TSECID AND ISDELETED=0) NOT LIKE '%BREAK%'";

		if($txtTestDate != '') $query .= " AND CONVERT(DATE,TESTDATE,105)='$txtTestDate'";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Test Section not found.';
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
/*============ GET TEST SECTION BY TEST =============*/ 






/*============ GET TEST SECTION ATTEMPTS =============*/ 
 function getTestSectionAttempts($mysqli){
	try
	{
		$data = array();
		$TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
		if($TESTID == 0) throw new Exception('TESTID not found.');
		$TSECID = ($_POST['TSECID'] == 'undefined' || $_POST['TSECID'] == '') ? 0 : $_POST['TSECID'];
		if($TSECID == 0) throw new Exception('TSECID not found.');
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0) throw new Exception('REGID not found.');
		
		$query = "SELECT COUNT(*)[COUNT],ATTEMPT FROM STUDENT_TESTS 
		WHERE ISDELETED=0 AND REGID=$REGID AND TSECID=$TSECID AND TESTID=$TESTID
		GROUP BY ATTEMPT";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Test Section Attempts not found.';
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
/*============ GET TEST SECTION ATTEMPTS =============*/ 





/*============ GET STUDENT ANSWERS =============*/ 
 function getStudentAnswer($mysqli){
	try
	{
		$data = array();
		// $STID = ($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];
		// if($STID == 0) throw new Exception('STID not found.');
		$TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
		if($TESTID == 0) throw new Exception('TESTID not found.');
		$TSECID = ($_POST['TSECID'] == 'undefined' || $_POST['TSECID'] == '') ? 0 : $_POST['TSECID'];
		if($TSECID == 0) throw new Exception('TSECID not found.');
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0) throw new Exception('REGID not found.');
		$ATTEMPT = ($_POST['ATTEMPT'] == 'undefined' || $_POST['ATTEMPT'] == '') ? 0 : $_POST['ATTEMPT'];
		if($ATTEMPT == 0) throw new Exception('ATTEMPT not found.');
		
		$query = "SELECT RID,REGID,STID,TSECID,QUEID,
		(SELECT QUESTION FROM QUESTION_BANK WHERE QUEID=ST.QUEID)QUESTION,
		(SELECT QUEOPTIONS FROM QUESTION_BANK WHERE QUEID=ST.QUEID)QUE_OPTIONS,
		(SELECT QUETYPE FROM QUESTION_BANK WHERE QUEID=ST.QUEID)QUETYPE,
		CORRECTANS,STUDENTANS,RESULT,
		(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=$TSECID AND ISDELETED=0)TEST,
		(SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID = ST.TSECID AND ISDELETED=0)SECTION,
		(SELECT CONVERT(VARCHAR,TESTDATE,106) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$ATTEMPT AND ISDELETED=0)TESTDATE,
		(SELECT CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),STARTDATETIME,108) AS TIME),100) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$ATTEMPT AND ISDELETED=0)STARTDATETIME,
		(SELECT CONVERT(VARCHAR(15),CAST(CONVERT(VARCHAR(8),ENDDATETIME,108) AS TIME),100) FROM STUDENT_TESTS WHERE STID=ST.STID AND ATTEMPT=$ATTEMPT AND ISDELETED=0)ENDDATETIME
		FROM STUDENT_TEST_RESULTS ST
		WHERE ISDELETED=0 AND REGID=$REGID AND TSECID=$TSECID AND
		STID=(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=$ATTEMPT AND ISDELETED=0 AND
		REGID=ST.REGID AND TESTID=$TESTID AND TSECID=ST.TSECID)";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				// $row['QUE_OPTIONS'] = str_replace(";#;,",", ",$row['QUE_OPTIONS']);
				// $row['QUE_OPTIONS'] = rtrim($row['QUE_OPTIONS'],';#; ');
				$row['QUE_OPTIONS'] = str_replace(';#;', ' ', $row['QUE_OPTIONS']);
				$row['STUDENTANS'] = str_replace(';#;', ' ', $row['STUDENTANS']);
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Student test answers not found.';
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
/*============ GET STUDENT ANSWERS =============*/ 






// =============== UPDATE ANSWER ==================
function UpdateAnswerFinal($mysqli){
	try
	{
	   	$data = array();
	   	global $userid;
   
	   	$RID  = ($_POST['RID'] == 'undefined' || $_POST['RID'] == '') ? 0 : $_POST['RID'];
	   	if($RID == 0){throw new Exception("RID not found.");}
	   	$ansFinal  = ($_POST['ansFinal'] == 'undefined' || $_POST['ansFinal'] == '') ? '' : $_POST['ansFinal'];
	   	if($ansFinal == ''){throw new Exception("Answer not found.");}
		$ansFinal=str_replace("'","''",$ansFinal);
	   	$CORRECTANS  = ($_POST['CORRECTANS'] == 'undefined' || $_POST['CORRECTANS'] == '') ? '' : $_POST['CORRECTANS'];

		$result = strtolower($ansFinal) == strtolower($CORRECTANS) ? 1 : 0;

	   	if(strpos($CORRECTANS, ';') !== false ) {
			$CA = explode(";",$CORRECTANS);

			for($i=0; $i<count($CA); $i++){
				$array_text=$CA[$i];

				if(strtolower($ansFinal) == strtolower($array_text)){
					$result = 1;
					break;
				}
			}
		}

		$query="UPDATE STUDENT_TEST_RESULTS SET STUDENTANS='$ansFinal',RESULT=$result WHERE RID=$RID";
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
			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Record successfully updated.';
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
// =============== UPDATE ANSWER ==============





// =============== DELETE ATTEMPT ==============
function DeleteAttempt($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STID = ($_POST['STID'] == 'undefined' || $_POST['STID'] == '') ? 0 : $_POST['STID'];
			if($STID == 0) throw new Exception('STID Error.');
			$delQuery = "UPDATE STUDENT_TESTS SET ISDELETED=1 WHERE STID=$STID; UPDATE STUDENT_TEST_RESULTS SET ISDELETED=1 WHERE STID=$STID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Attempt successfully deleted';
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
// =============== DELETE ATTEMPT ==============

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







