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
        case "saveAttendance":saveAttendance($conn);break;
        case "saveAllAttendance":saveAllAttendance($conn);break;
        case "getStudents":getStudents($conn);break;
		
		// ADD STUDENTS
        case "saveTC":saveTC($conn);break;
        case "getCoveredTopics":getCoveredTopics($conn);break;
        case "deleteTC":deleteTC($conn);break;

		case "getTopicsByLoc_Grade_Subject":getTopicsByLoc_Grade_Subject($conn);break;
		
		
		// EXTRA
		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveAttendance($mysqli){
     try
     {
		$data = array();
        global $userid;
		
        $attid  = ($_POST['attid'] == 'undefined' || $_POST['attid'] == '') ? 0 : $_POST['attid'];
        $tcid  = ($_POST['tcid'] == 'undefined' || $_POST['tcid'] == '') ? 0 : $_POST['tcid'];
        $remark  = ($_POST['remark'] == 'undefined' || $_POST['remark'] == '') ? '' : $_POST['remark'];
        $attendance  = ($_POST['attendance'] == 'undefined' || $_POST['attendance'] == '' || $_POST['attendance'] == '0') ? '0' : '1';

		if($attid == 0)throw new Exception("Error : ATTID Not Found.");
		if($tcid == 0)throw new Exception("Error : TCID Not Found.");

		$query="UPDATE LA_BATCH_ATTENDANCE SET ATTENDANCE='$attendance',REMARKS='$remark',UPDATEDATE=GETDATE(),UPDATEID=$userid WHERE ATTID=$attid AND TCID=$tcid";
		$data['$query'][] = $query;
		// echo json_encode($data);exit;
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			echo json_encode($data);exit;
		}
		// UPDATE COURSE COVERAGE
		$queryUpd="EXEC [UPDATE_COVERAGE_ATTENDANCE_FROM_LA]$tcid,$attid,$attendance";
		sqlsrv_query($mysqli, $queryUpd);
		
		$data['success'] = true;
		$data['message'] = 'Attendance successfully updated';

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



 function saveAllAttendance($mysqli){
     try
     {
		$data = array();
        global $userid;
		
        // $attid  = ($_POST['attid'] == 'undefined' || $_POST['attid'] == '') ? 0 : $_POST['attid'];
		$tcid  = ($_POST['tcid'] == 'undefined' || $_POST['tcid'] == '') ? 0 : $_POST['tcid'];
        $txtAttDate  = ($_POST['txtAttDate'] == 'undefined' || $_POST['txtAttDate'] == '') ? '' : $_POST['txtAttDate'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $ddlBatches  = ($_POST['ddlBatches'] == 'undefined' || $_POST['ddlBatches'] == '') ? 0 : $_POST['ddlBatches'];
        $studentIdList  = (empty($_POST['studentIdList']) ||  $_POST['studentIdList'] == 'undefined' || $_POST['studentIdList'] == '') ? array() : explode(',',$_POST['studentIdList']);
        // $attRemarksList  = (empty($_POST['attRemarksList']) ||  $_POST['attRemarksList'] == 'undefined' || $_POST['attRemarksList'] == '') ? array() : json_decode($_POST['attRemarksList'],true);
        $attAll  = ($_POST['attAll'] == 'undefined' || $_POST['attAll'] == '' || $_POST['attAll'] == '0') ? 0 : 1;
		
		// $data['studentIdList']=$studentIdList;
		// echo json_encode($data);exit;
		
		
		
		if($tcid == 0)throw new Exception("Error : TCID Not Found.");
		if($txtAttDate == '')throw new Exception("Please Select Attendance Date.");
		if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
		if($ddlBatches == 0)throw new Exception("Please Select Batch Name.");
		
		if(count($studentIdList)>0){
			for($i=0;$i<count($studentIdList);$i++){
				// $val = explode('|',$studentIdList[$i]);
				// $regid=$val[0];
				// $att=$val[1];
				// $remark = str_replace("'","''",$attRemarksList[$i]);
				$regid=$studentIdList[$i];
				$query="UPDATE LA_BATCH_ATTENDANCE SET ATTENDANCE='$attAll',UPDATEDATE=GETDATE(),UPDATEID=$userid
						WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105)='$txtAttDate'
						AND TEACHERID=$ddlTeacher AND BATCHID=$ddlBatches AND REGID=$regid AND TCID=$tcid";
				$data['$query'][] = $query;
				// echo json_encode($data);exit;
				$stmt=sqlsrv_query($mysqli, $query);
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					echo json_encode($data);exit;
				}
			}

			// UPDATE COURSE COVERAGE
			$queryUpd="EXEC [UPDATE_COVERAGE_ATTENDANCE_FROM_LA]$tcid,0,$attAll";
			sqlsrv_query($mysqli, $queryUpd);
			
			$data['success'] = true;
			if(!empty($attid))$data['message'] = 'Record successfully updated';
			else $data['message'] = 'Record successfully inserted.';
		}

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



/*============ Get Student =============*/ 
function getStudents($mysqli){
	try
	{
		global $userid;
		$data = array();
		$tcid = ($_POST['tcid'] == 'undefined' || $_POST['tcid'] == '') ? 0 : $_POST['tcid'];
		$txtAttDate = ($_POST['txtAttDate'] == 'undefined' || $_POST['txtAttDate'] == '') ? '' : $_POST['txtAttDate'];
		$ddlTeacher = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $ddlBatches  = ($_POST['ddlBatches'] == 'undefined' || $_POST['ddlBatches'] == '') ? 0 : $_POST['ddlBatches'];

		if($tcid==0)throw new Exception("Error : TCID Not Found. Please Refresh And Try Again.");

		// check Attendance Exist
		$AttExist = "SELECT COUNT(*)CNT FROM LA_BATCH_ATTENDANCE WHERE REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1) 
					AND ISDELETED=0 AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' AND TEACHERID=$ddlTeacher AND TCID=$tcid AND BATCHID=$ddlBatches";
		$resultAE = sqlsrv_query($mysqli, $AttExist);
		$rowAE = sqlsrv_fetch_array($resultAE);
		$ATT_EXIST = $rowAE['CNT'];
		if($ATT_EXIST == 0){
			$insertAtt = "INSERT INTO LA_BATCH_ATTENDANCE (TCID,ATTDATE,TEACHERID,BATCHID,REGID,ATTENDANCE,INSERTID)
			SELECT $tcid,'$txtAttDate',$ddlTeacher,$ddlBatches,REGID,'0',$userid FROM LA_BATCH_STUDENTS BS WHERE ISDELETED=0 AND BATCHID=$ddlBatches";
			sqlsrv_query($mysqli, $insertAtt);
		}else{

		}

		$query = "SELECT ATTID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=BA.REGID)STUDENTNAME,
				ATTENDANCE,REMARKS FROM LA_BATCH_ATTENDANCE BA 
				WHERE ISDELETED=0 
				AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ARCHIVED=1) 
				AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' AND TEACHERID=$ddlTeacher AND BATCHID=$ddlBatches AND TCID=$tcid
				ORDER BY STUDENTNAME";
		$data['$query ']=$query;

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID']=(string)$row['REGID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}
		else{
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
/*============ Get Student =============*/




// ####################################################################################
//                                      ADD STUDENT START
// ####################################################################################
function saveTC($mysqli){
	try
	{
		$data = array();
		global $userid;
		
		$tcid  = ($_POST['tcid'] == 'undefined' || $_POST['tcid'] == '') ? 0 : $_POST['tcid'];
		$txtAttDate  = ($_POST['txtAttDate'] == 'undefined' || $_POST['txtAttDate'] == '') ? '' : $_POST['txtAttDate'];
		$ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		$ddlBatches  = ($_POST['ddlBatches'] == 'undefined' || $_POST['ddlBatches'] == '') ? 0 : $_POST['ddlBatches'];
		$ddlGrade  = ($_POST['ddlGrade'] == 'undefined' || $_POST['ddlGrade'] == '') ? 0 : $_POST['ddlGrade'];
		$ddlSubject  = ($_POST['ddlSubject'] == 'undefined' || $_POST['ddlSubject'] == '') ? 0 : $_POST['ddlSubject'];
		$ddlTopic  = ($_POST['ddlTopic'] == 'undefined' || $_POST['ddlTopic'] == '') ? 0 : $_POST['ddlTopic'];
		$txtRemark_CP  = ($_POST['txtRemark_CP'] == 'undefined' || $_POST['txtRemark_CP'] == '') ? '' : str_replace("'","''",$_POST['txtRemark_CP']);
		
	   	$actionid = $tcid == 0 ? 1 : 2;
	   
	   
		if($txtAttDate == '')throw new Exception("Please Select Date First.");
		if($ddlTeacher == 0)throw new Exception("Please Select Teacher Name.");
		if($ddlBatches == 0)throw new Exception("Please Select Batch Name.");
		if($ddlGrade == 0)throw new Exception("Please Select Grade Name.");
		if($ddlSubject == 0)throw new Exception("Please Select Subject Name.");
		if($ddlTopic == 0)throw new Exception("Please Select Topic Name.");

		$ddlSlide = (!empty($_POST['ddlSlide'])) ? $_POST['ddlSlide'] : '';
		if($ddlSlide == '')throw new Exception('Select Slide First.');
		$ddlSlide = explode(',',$ddlSlide);
		// $data['ddlSlide']=$ddlSlide;
		// echo json_encode($data);exit;
		   
		   $sql = "SELECT * FROM LA_BATCH_TOPICS_COVERED WHERE ATTDATE='$txtAttDate' AND TEACHERID=$ddlTeacher AND BATCHID=$ddlBatches AND
		   		GRADEID=$ddlGrade AND SUBID=$ddlSubject AND TOPICID=$ddlTopic AND REMARK='$txtRemark_CP' AND TCID!=$tcid AND ISDELETED=0";
		   $row_count = unique($sql);
		   
		   if($row_count == 0)
		   {
			   $query="EXEC [LA_BATCH_TOPICS_COVERED_SP] $actionid,$tcid,'$txtAttDate',$ddlTeacher,$ddlBatches,$ddlGrade,$ddlSubject,$ddlTopic,'$txtRemark_CP',$userid";
			   $data['$query'] = $query;
			   // echo json_encode($data);exit;
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
					// GET TCID
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$GET_TCID = $row['TCID'];
					$data['GET_TCID'] = $row['TCID'];

					if($GET_TCID>0 && count($ddlSlide)>0){
						if($actionid==2){
							$slideDel="EXEC [LA_BATCH_TOPIC_SLIDES_COVERED_SP] 3,$GET_TCID,0,$userid";
							$stmtDel=sqlsrv_query($mysqli, $slideDel);
						}
						// INSERT LA BATCH TOPIC SLIDES COVERED
						for($i=0;$i<count($ddlSlide);$i++){
							$SLIDEID = $ddlSlide[$i];
							$slideQry="EXEC [LA_BATCH_TOPIC_SLIDES_COVERED_SP] 1,$GET_TCID,$SLIDEID,$userid";
							$stmtSlide=sqlsrv_query($mysqli, $slideQry);
						}
					}
					$data['query'] = $query;
					$data['success'] = true;
			   }	
   
			   if(!empty($tcid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
					   
		   }
		   else
		   {
			   $data['success'] = false;
			   $data['message'] = 'Data already exists.';
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

/*============ GET DATA=============*/ 
function getCoveredTopics($mysqli){
	try
	{
		$data = array();
		$txtAttDate = ($_POST['txtAttDate'] =='undefined' || $_POST['txtAttDate'] =='') ? '' : $_POST['txtAttDate'];
		$ddlTeacher = ($_POST['ddlTeacher'] =='undefined' || $_POST['ddlTeacher'] =='') ? 0 : $_POST['ddlTeacher'];
		$ddlBatches = ($_POST['ddlBatches'] =='undefined' || $_POST['ddlBatches'] =='') ? 0 : $_POST['ddlBatches'];

		$query = "SELECT TCID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=TC.GRADEID)GRADE,GRADEID,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=TC.SUBID)SUBJECTNAME,SUBID,
		(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TC.TOPICID)TOPIC,TOPICID,REMARK,
		(SELECT CAST(SLIDEID AS VARCHAR)+', ' FROM LA_BATCH_TOPIC_SLIDES_COVERED WHERE ISDELETED=0 AND TCID=TC.TCID FOR XML PATH(''))SLIDEID,
		(SELECT (SELECT SLIDEHEADING FROM LA_TOPIC_SLIDE_MASTER WHERE SLIDEID=LB.SLIDEID)+', ' FROM LA_BATCH_TOPIC_SLIDES_COVERED LB WHERE ISDELETED=0 AND TCID=TC.TCID FOR XML PATH(''))SLIDES
		FROM LA_BATCH_TOPICS_COVERED TC WHERE ISDELETED=0 AND CONVERT(DATE,ATTDATE,105)='$txtAttDate' 
		AND TEACHERID=$ddlTeacher AND BATCHID=$ddlBatches ORDER BY TCID DESC";
		$data['query']=$query;
		// echo json_encode($data);exit;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['TCID'] = (int) $row['TCID'];
				$row['SLIDEID'] = rtrim($row['SLIDEID'],', ');
				$row['SLIDE_LIST'] = explode(",",$row['SLIDEID']);
				$row['SLIDES'] = rtrim($row['SLIDES'],', ');
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
/*============ GET DATA =============*/ 


/* =========== DELETE =========== */ 
function deleteTC($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TCID = ($_POST['TCID'] == 'undefined' || $_POST['TCID'] == '') ? 0 : $_POST['TCID'];  
			if($TCID==0) throw new Exception('Error : TCID Not Found.');
			$query = "EXEC [LA_BATCH_TOPICS_COVERED_SP] 3,$TCID,'',0,0,0,0,0,'',$userid";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== DELETE =========== */ 
// ####################################################################################
//                                      ADD STUDENT END
// ####################################################################################







// ####################################################################################
//                                      EXTRA START
// ####################################################################################
/*============ GET TOPICS =============*/ 
$all_data=array();
$RETURN_DATA='';
function getTopicsByLoc_Grade_Subject($mysqli){
	try
	{
		global $all_data,$RETURN_DATA;
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$TEACHERID = ($_POST['TEACHERID'] == 'undefined' || $_POST['TEACHERID'] == '') ? 0 : $_POST['TEACHERID'];
		$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		if($LOCID == 0) throw new Exception('Select Location First.');
		if($GRADEID == 0) throw new Exception('Select Grade First.');
		if($SUBID == 0) throw new Exception('Select Subject First.');

		$query = "SELECT TOPICID,TOPIC,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID,
				(SELECT SEQNO FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER_SEQNO,SEQNO,
				CASE WHEN (SELECT COUNT(*) FROM LA_TOPICS_MASTER WHERE UNDERTOPICID=TM.TOPICID)>0 THEN 1 ELSE 0 END NEXT_TOPIC_EXIST
			FROM LA_TOPICS_MASTER TM 
			WHERE ISDELETED=0 AND LOCID IN (1,$LOCID) AND GRADEID=$GRADEID AND SUBID=$SUBID AND 
			TM.TOPICID NOT IN (SELECT TOPICID FROM LA_HIDE_TOPICS_LOCATIONWISE WHERE TOPICID=TM.TOPICID AND LOCID=(SELECT LOCID FROM USERS WHERE UID=$TEACHERID) AND HIDE=1)
			ORDER BY LOCID DESC,UNDER_SEQNO,SEQNO,UNDER,TOPIC";

		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$all_data[] = $row;
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}
			// $RETURN_DATA.='<div class="form-group">';
			// $RETURN_DATA.='<label for="ddlTopic">Topic :<span class="text-danger">*</span> <i class="fa fa-spinner fa-spin ml-2 spinTopic" style="display: none;"></i></label>';
			// $RETURN_DATA.='<select class="form-control" onchange="angular.element(this).scope().setTopicId(this)" id="ddlTopic1">';
			$RETURN_DATA.='<option value="">--SELECT--</option>';
			$data['finalData'] = getUnderTopics($mysqli,0);
			// $RETURN_DATA.='</select>';
			// $RETURN_DATA.='</div>';
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

$index=0;
function getUnderTopics($mysqli,$TOPICID){
	global $RETURN_DATA,$all_data,$index;

	$filteredObjects = array_filter($all_data, function($object) use($TOPICID) {
				return $object['UNDERTOPICID'] == $TOPICID;
			});
	$count = count($filteredObjects);
	if($count > 0){
		foreach($filteredObjects as $rows){
			$TOPIC = $rows['TOPIC'];
			$TOPICID = (int)$rows['TOPICID'];
			$UNDERTOPICID = $rows['UNDERTOPICID'];
			$NEXT_TOPIC_EXIST = $rows['NEXT_TOPIC_EXIST'];
			if($NEXT_TOPIC_EXIST > 0){
				$RETURN_DATA.="<optgroup label='$TOPIC'>";
			}

			getUnderTopics($mysqli ,$TOPICID);
			if($NEXT_TOPIC_EXIST== 0){
				$RETURN_DATA .= "<option label='$TOPIC' value='$TOPICID'></option>";
			}
		}
		$RETURN_DATA .="</optgroup>";
	}else{
	}
	// $RETURN_DATA = ['data'=>$cardList, 'success'=>$SUCCESS];
	return $RETURN_DATA;
}


// ####################################################################################
//                                      EXTRA START
// ####################################################################################


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







