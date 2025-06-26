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
		//######## MASTER
        case "saveData":saveData($conn);break;
        case "getTestMasters":getTestMasters($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "getQueForMap":getQueForMap($conn);break;
        case "saveDataDET":saveDataDET($conn);break;
        case "getMapedQuestions":getMapedQuestions($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE
		case "getTopics":getTopics($conn);break;

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;	

		$testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$txtTestDesc = ($_POST['txtTestDesc'] == 'undefined' || $_POST['txtTestDesc'] == '') ? '' : str_replace("'","''",$_POST['txtTestDesc']);

		$actionid = $testid == 0 ? 1 : 2;

		if($ddlLocation == 0) throw new Exception("Select Location First.");
		if($ddlGrade == 0) throw new Exception("Select Grade First.");
		if($ddlSubject == 0) throw new Exception("Select Subject First.");
		if($txtTestDesc == '') throw new Exception("Enter Test Description First.");


		$sql = "SELECT * FROM LA_TEST_MASTER WHERE LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject AND
		TESTDESC='$txtTestDesc' AND TESTID!=$testid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [LA_TEST_MASTER_SP] $actionid,$testid,$ddlLocation,$ddlGrade,$ddlSubject,'$txtTestDesc',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET TESTID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_TESTID'] = $row['TESTID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($testid))$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET TEST MASTERS =========== */
 function getTestMasters($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];

		$query = "SELECT TESTID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=TM.LOCID)[LOCATION],
		(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=TM.GRADEID)GRADE,GRADEID,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=TM.SUBID)SUBJECTNAME,SUBID,TESTDESC
		FROM LA_TEST_MASTER TM WHERE ISDELETED=0";
		if($ddlLocation > 0)$query .= " AND LOCID=$ddlLocation";
		if($ddlGrade > 0)$query .= " AND GRADEID=$ddlGrade";
		if($ddlSubject > 0)$query .= " AND SUBID=$ddlSubject";
		$query .= " ORDER BY TESTID DESC";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
/* ========== GET TEST MASTERS =========== */








/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
		global $userid;
		$data = array();     
		$TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];  
		if($TESTID == 0)throw new Exception('TESTID Not Found.');
		$delQuery = "EXEC [LA_TEST_MASTER_SP] 3,$TESTID,0,0,0,'',$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataDET($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
	   $subid = ($_POST['subid'] == 'undefined' || $_POST['subid'] == '') ? 0 : $_POST['subid'];
	   $topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
	   $val = ($_POST['val'] == 'undefined' || $_POST['val'] == '') ? 0 : $_POST['val'];
	//    $mappedQueArr = (!isset($_POST['mappedQueArr']) || $_POST['mappedQueArr'] == 'undefined' || $_POST['mappedQueArr'] == '') ? array() : json_decode($_POST['mappedQueArr'],true);
	//    $data['$mappedQueArr']=$mappedQueArr;

	   if($testid == 0) throw new Exception("Error : TESTID Not Found.");
	   if($subid == 0) throw new Exception("Error : SUBID Not Found.");
	   if($topicid == 0) throw new Exception("Error : TOPICID Not Found.");
	   //    if(count($mappedQueArr) == 0) throw new Exception("Error : Please Select Questions First.");
	   if($val == 0) throw new Exception("Error : Please select Student First.");


	   $sql = "SELECT * FROM LA_TEST_MASTER_QUESTIONS WHERE TESTID=$testid AND SUBID=$subid AND TOPICID=$topicid AND QUEID=$val AND ISDELETED=0";
	   $row_count = unique($sql);

	   if($row_count == 0)
	   {

		// for($i=0;$i<count($mappedQueArr);$i++){
		// 	$MQUEID = $mappedQueArr[$i];
			$query="EXEC [LA_TEST_MASTER_QUESTIONS_SP] 1,0,$testid,$subid,$topicid,$val,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'][] = $query;
			}
		// }
		$data['success'] = true;
		$data['message'] = 'Record successfully inserted.';
		echo json_encode($data);exit;
		   
	   }
	   else
	   {
	   	$data['success'] = false;
	   	$data['message'] = 'Question already exists';
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
/* ============ SAVE DATA ============= */ 





/* ========== GET MAPPED QUESTIONS=========== */
function getMapedQuestions($mysqli){
	try
	{
		$data = array();
		$testid = ($_POST['testid'] == 'undefined' || $_POST['testid'] == '') ? 0 : $_POST['testid'];
		$topicid = ($_POST['topicid'] == 'undefined' || $_POST['topicid'] == '') ? 0 : $_POST['topicid'];
		if($testid == 0)throw new Exception("Error : TESTID Not Found.");
		// if($topicid == 0)throw new Exception("Error : TOPICID Not Found.");

		$query = "SELECT TESTDID,TOPICID,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=Q.TOPICID)TOPIC,
		QUEID,(SELECT QUESTION FROM LA_QUESTIONS_MAIN WHERE MQUEID=Q.QUEID)QUESTION
		FROM LA_TEST_MASTER_QUESTIONS Q WHERE ISDELETED=0 AND TESTID=$testid";

		if($topicid>0) $query .=" AND TOPICID=$topicid";
		$query .=" ORDER BY QUESTION";
		$count = unique($query);

		$data['$query']=$query;

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
/* ========== GET MAPPED QUESTIONS=========== */







/* ========== GET MAIN QUESTIONS =========== */
function getQueForMap($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$ddlTopic = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];

		$query = "SELECT MQUEID,LOCID,
		(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=QM.GRADEID)GRADE,GRADEID,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=QM.SUBID)SUBJECTNAME,SUBID,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=QM.TOPICID)TOPIC,TOPICID,DAYNO,TESTCODE,QUESTION,PASSAGE,
		CASE WHEN (SELECT COUNT(*) FROM LA_TEST_MASTER_QUESTIONS WHERE ISDELETED=0 AND QUEID=QM.MQUEID AND SUBID=QM.SUBID AND TOPICID=QM.TOPICID)>0
			THEN 1 ELSE 0 
		END CHK_EXIST
		FROM LA_QUESTIONS_MAIN QM WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject AND TOPICID=$ddlTopic
		ORDER BY QUESTION";

		$data['$query']=$query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['MQUEID'] = (string)$row['MQUEID'];
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
/* ========== GET MAIN QUESTIONS =========== */






/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TESTDID = ($_POST['TESTDID'] == 'undefined' || $_POST['TESTDID'] == '') ? 0 : $_POST['TESTDID'];  
			if($TESTDID == 0)throw new Exception('TESTDID Not Found.');
			$delQuery = "EXEC [LA_TEST_MASTER_QUESTIONS_SP] 3,$TESTDID,0,0,0,0,$userid";
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
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== Delete =========== */ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 










/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 


/*============ GET TOPICS =============*/ 
function getTopics($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlGrade = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');
		if($ddlGrade == 0) throw new Exception('Select Grade First.');
		if($ddlSubject == 0) throw new Exception('Select Subject First.');

		$query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID 
				FROM LA_TOPICS_MASTER TM 
				WHERE ISDELETED=0 AND LOCID IN (1, $ddlLocation) AND GRADEID=$ddlGrade AND SUBID=$ddlSubject 
				AND TOPICID NOT IN (SELECT TOPICID FROM LA_HIDE_TOPICS_LOCATIONWISE WHERE TOPICID=TM.TOPICID AND LOCID=$ddlLocation AND HIDE=1)
				ORDER BY LOCID DESC,UNDER,TOPIC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Topic not found.';
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
/*============ GET TOPICS =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







