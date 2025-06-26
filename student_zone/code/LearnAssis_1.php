<?php
session_start();
require_once '../code/connection.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getGradeSubject":getGradeSubject($conn);break;
        case "getTopics":getTopics($conn);break;
        case "getSlides":getSlides($conn);break;
        case "getSlideContent":getSlideContent($conn);break;
        case "getAssignmentsQuestions":getAssignmentsQuestions($conn);break;
        case "getAssQueOptions":getAssQueOptions($conn);break;
        case "saveAnswer":saveAnswer($conn);break;
        case "resetTest":resetTest($conn);break;
        // case "getAssignments":getAssignments($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */





/*============ GET GRADE & SUBJECT =============*/ 
 function getGradeSubject($mysqli){
	try
	{
		$data = array();
		$REGID=($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID == 0)throw new Exception('REGID Invalid.');

		$query = "SELECT GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SG.GRADEID)GRADE,SUBID,
		(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SG.SUBID)SUBJECTNAME
		FROM STUDENTS_GRADES_SUBJECTS SG WHERE ISDELETED=0 AND REGID=$REGID
		ORDER BY GRADE";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			$GRADE_LIST=$SUBJECT_LIST=$ALL_DATA=array();
			$GRADEID=$GRADEID_NEW=$SUBID=$SUBID_NEW=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$GRADEID = $row['GRADEID'];
				$SUBID = $row['SUBID'];
				if($GRADEID !== $GRADEID_NEW){
					$GRADE_LIST[] = ['GRADE'=>$row['GRADE'],'GRADEID'=>$row['GRADEID']];
				}
				if($SUBID !== $SUBID_NEW){
					$SUBJECT_LIST[] = ['SUBJECT'=>$row['SUBJECTNAME'],'SUBID'=>$row['SUBID']];
				}
				$ALL_DATA[] = $row;
				$data['data'][] = $row;
				$GRADEID_NEW = $GRADEID;
				$SUBID_NEW = $SUBID;
			}
			// $SUBJECT_LIST = array_unique($SUBJECT_LIST, SORT_REGULAR);
			// $data['GRADE_LIST']=$GRADE_LIST;
			// $data['SUBJECT_LIST']=$SUBJECT_LIST;
			
			// CREATE GRADE & SUBJECT LIST
			$data['GRADE_SUBJECT_LIST']=array();
			if(COUNT($GRADE_LIST)>0){
				for($i=0;$i<COUNT($GRADE_LIST);$i++){
					$GRADE_LIST[$i]['SUBJECT']=array_filter($ALL_DATA,function($x)use($GRADE_LIST,$i){
						return $x['GRADEID']==$GRADE_LIST[$i]['GRADEID'];
					});
					
				}
			}
			$data['success'] = true;
			$data['GRADE_SUBJECT_LIST']=$GRADE_LIST;
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



/*============ GET TOPICS =============*/ 
function getTopics($mysqli){
	try
	{
		global $userid;
		$data = array();
		$ddlLocation = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$ddlGrade = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$ddlSubject = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');
		if($ddlGrade == 0) throw new Exception('Select Grade First.');
		if($ddlSubject == 0) throw new Exception('Select Subject First.');
		$data['G/S']=$ddlGrade.'/'.$ddlSubject;

		
		// $query = "SELECT TOPICID,TOPIC,SEQNO,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID,
		// 		(SELECT SEQNO FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER_SEQNO
		// 		FROM LA_TOPICS_MASTER TM 
		// 		WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
		// 		AND TOPICID IN (SELECT TOPICID FROM LA_BATCH_TOPICS_COVERED WHERE ISDELETED=0  AND BATCHID IN(SELECT BATCHID FROM LA_BATCH_STUDENTS WHERE REGID=$userid))

		// 		UNION ALL

		// 		SELECT TOP 5  
		// 		TOPICID,TOPIC,SEQNO,(SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER,UNDERTOPICID,
		// 		(SELECT SEQNO FROM LA_TOPICS_MASTER WHERE TOPICID=TM.UNDERTOPICID)UNDER_SEQNO
		// 		FROM LA_TOPICS_MASTER TM where ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject 
		// 		AND SEQNO>(SELECT TOP 1 SEQNO FROM LA_TOPICS_MASTER TM WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
		// 					AND TOPICID IN (SELECT TOPICID FROM LA_BATCH_TOPICS_COVERED WHERE ISDELETED=0  AND BATCHID IN(SELECT BATCHID FROM LA_BATCH_STUDENTS WHERE REGID=$userid)))
		// 		AND UNDERTOPICID=(SELECT TOP 1 UNDERTOPICID FROM LA_TOPICS_MASTER TM WHERE ISDELETED=0 AND LOCID=$ddlLocation AND GRADEID=$ddlGrade AND SUBID=$ddlSubject
		// 					AND TOPICID IN (SELECT TOPICID FROM LA_BATCH_TOPICS_COVERED WHERE ISDELETED=0  AND BATCHID IN(SELECT BATCHID FROM LA_BATCH_STUDENTS WHERE REGID=$userid)))

		// 		ORDER BY UNDER_SEQNO,SEQNO,UNDER,TOPIC";

		$query = "EXEC [GET_LA_TOPIC_MASTER] $ddlLocation,$ddlGrade,$ddlSubject,$userid";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
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



/*============ GET SLIDES =============*/ 
 function getSlides($mysqli){
	try
	{
		$data = array();
		$TOPICID=($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];
		if($TOPICID == 0)throw new Exception('TOPICID Invalid.');

		$query = "SELECT SLIDEID,SLIDEHEADING FROM LA_TOPIC_SLIDE_MASTER WHERE ISDELETED=0 AND TOPICID=$TOPICID ORDER BY SLIDEHEADING";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
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


/*============ GET SLIDE CONTENT =============*/ 
 function getSlideContent($mysqli){
	try
	{
		$data = array();
		$SLIDEID=($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];
		if($SLIDEID == 0)throw new Exception('SLIDEID Invalid.');

		$query = "SELECT SLIDEDETID,CONTENT_TYPE,CONTENT,CONTENTFILE,SEQNO FROM LA_TOPIC_SLIDES WHERE ISDELETED=0 AND SLIDEID=$SLIDEID
		ORDER BY SEQNO";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
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


/*============ GET ASSIGNMENTS QUESTIONS =============*/ 
function getAssignmentsQuestions($mysqli){
	try
	{
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];

		if($REGID == 0) throw new Exception('Error : STUDENTID Not Found.');
		if($LOCID == 0) throw new Exception('Select Location First.');
		if($GRADEID == 0) throw new Exception('Select Grade First.');
		if($SUBID == 0) throw new Exception('Select Subject First.');
		if($TOPICID == 0) throw new Exception('Select Topic First.');

		// CHECK TEST EXIST
		$queryExist="SELECT COUNT(*)CNT FROM LA_STUDENT_TEST WHERE ISDELETED=0 AND REGID=$REGID AND GRADEID=$GRADEID AND SUBID=$SUBID AND TOPICID=$TOPICID";
		$data['$queryExist']=$queryExist;
		$resultExist = sqlsrv_query($mysqli, $queryExist);
		$rowExist = sqlsrv_fetch_array($resultExist,SQLSRV_FETCH_ASSOC);
		$CNT = $rowExist['CNT'];
		$data['REVIEW'] = $CNT > 0 ? true : false;
		
		$query = "SELECT MQUEID,DAYNO,TESTCODE,QUESTION,PASSAGE
		FROM LA_QUESTIONS_MAIN Q WHERE ISDELETED=0 AND GRADEID=$GRADEID AND SUBID=$SUBID AND TOPICID=$TOPICID AND LOCID=$LOCID";
		$data['$query']=$query;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$MQUEID = $row['MQUEID'];

				if($CNT==0){
					// GET SUB QUESTION AND ANS
					$querySUB = "SELECT SQUEID,QUESTION,QUE_TYPE,
					CASE WHEN QUE_TYPE='TYPE IN' OR QUE_TYPE='GRID' OR QUE_TYPE='DRAG & DROP' 
						THEN ANSWER
						ELSE ISNULL((SELECT CAST(OPTIONID AS VARCHAR)+',' FROM LA_QUESTIONS_OPTIONS WHERE SQUEID=Q.SQUEID AND ISCORRECT=1 FOR XML PATH('')),0) 
					END CANSWER
					FROM LA_QUESTIONS_SUB Q WHERE ISDELETED=0 AND MQUEID=$MQUEID ORDER BY QUESTION";
					$RCOUNTSUB = unique($querySUB);
					if($RCOUNTSUB>0){
						$resultSUB = sqlsrv_query($mysqli, $querySUB);
						while ($rowSUB = sqlsrv_fetch_array($resultSUB,SQLSRV_FETCH_ASSOC)) {
							$SQUEID = $rowSUB['SQUEID'];
							$CANSWER = rtrim($rowSUB['CANSWER'],';');
							$CANSWER = rtrim($rowSUB['CANSWER'],',');
	
							// TRUNCATE BEFORE SAVE
							// sqlsrv_query($mysqli, '');
	
							// INSERT DATA
							$queryINS = "EXEC [LA_STUDENT_TEST_SP] 1,$REGID,$GRADEID,$SUBID,$TOPICID,$MQUEID,$SQUEID,'$CANSWER'";
							$resultINS = sqlsrv_query($mysqli, $queryINS);
							if($resultINS === false){
								$data['resultINS']=$resultINS;
								$data['success'] = false;
								echo json_encode($data);exit;	
							}
						}
					}
				}

				$data['data'][] = $row;
			}
			// $data['data'][] = $MAIN_DATA;
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


/*============ GET ASSIGNMENTS MAIN QUESTIONS & OPTIONS =============*/ 
function getAssQueOptions($mysqli){
	try
	{
		global $userid;
		$data = array();

		$MQUEID = ($_POST['MQUEID'] == 'undefined' || $_POST['MQUEID'] == '') ? 0 : $_POST['MQUEID'];
		$REVIEW = ($_POST['REVIEW'] == 'undefined' || $_POST['REVIEW'] == '' || $_POST['REVIEW'] == 'false') ? 0 : 1;
		if($MQUEID == 0) throw new Exception('Error : MQUEID Not Found.');

		$query = "SELECT TESTID,SQUEID,(SELECT QUESTION FROM LA_QUESTIONS_SUB WHERE SQUEID=ST.SQUEID)QUESTION,
				(SELECT QUE_TYPE FROM LA_QUESTIONS_SUB WHERE SQUEID=ST.SQUEID)QUE_TYPE,
				(SELECT [ROWS] FROM LA_QUESTIONS_SUB WHERE SQUEID=ST.SQUEID)ROWS,
				(SELECT [COLUMNS] FROM LA_QUESTIONS_SUB WHERE SQUEID=ST.SQUEID)COLUMNS,
				CASE WHEN (SELECT QUE_TYPE FROM LA_QUESTIONS_SUB WHERE SQUEID=ST.SQUEID)='MCQ SINGLE'
					THEN (SELECT OPTIONS FROM LA_QUESTIONS_OPTIONS WHERE OPTIONID=ST.CORRECT_ANS)
					ELSE CORRECT_ANS
				END AAID,
				CORRECT_ANS AID,STUDENT_ANS,RESULT
				FROM LA_STUDENT_TEST ST WHERE ISDELETED=0 AND MQUEID=$MQUEID AND REGID=$userid";
		$data['$query']=$query;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			$MIndex = 1;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$SQUEID = $row['SQUEID'];
				$QUE_TYPE = $row['QUE_TYPE'];
				$QUESTION = $row['QUESTION'];
				$RESULT = $row['RESULT'];
				$TESTID = $row['TESTID'];
				$AID = $row['AID'];
				// $NOANS_CLASs = $STUDENTANS=='-'?'text-danger':'';
				
				if($row['QUE_TYPE'] == 'TYPE IN'){
					$ST= isset($row['STUDENT_ANS']) ? $row['STUDENT_ANS'] : '';
					$CA= $row['AID'];
					$CANS = (!$CA || $CA == '') ? array() : explode(';',$CA);
					$STUDENTANS = (!$ST || $ST == '') ? array() : explode(';',$ST);

					
					

					// $CLASS = ($STUDENTANS=='-') ? 'text-danger' : ($RESULT==0 ? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');

					$input = "<div id='pera$MIndex'>";
					$i = 0;
					$input .= preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,$MIndex,$REVIEW,$STUDENTANS,$QUE_TYPE,$TESTID,$AID,$CANS) {
						$modalName = 'typeinText'.$i.''.$MIndex;
						$i++;
						// return '<input id="' . $id . '">';
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : (($STUDENTANS[$i-1])!==($CANS[$i-1] )? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
							}else{
								$CLASS = 'text-danger';
							}
							return "<b class='$CLASS'>".(count($STUDENTANS)<=0 ? '-' : ($STUDENTANS[$i-1]==''?'-':$STUDENTANS[$i-1]))."</b>";
						}else{
							$A = str_replace(';','||',$AID);
							return "<input type='text' ng-model='$modalName' ng-blur='saveAnswer($TESTID,\"$A\",$modalName,\"$QUE_TYPE\",$MIndex)' class='d-inline col-2 form-control inputStyle text-center' name='$modalName' id='$modalName' placeholder=''>";
						}
					}, $QUESTION);
					$input .= '</div>';
					$row['QUESTION_INPUT'] = $input;
					
					
				// ###################
				// ###### DRAG & DROP
				// ###################
				}else if($row['QUE_TYPE'] == 'DRAG & DROP'){
					$ST= isset($row['STUDENT_ANS']) ? $row['STUDENT_ANS'] : '';
					$CA= $row['AID'];
					$CANS = (!$CA || $CA == '') ? array() : explode(';',$CA);
					$STUDENTANS = (!$ST || $ST == '') ? array() : explode(';',$ST);

					$input = "<div id='pera$MIndex'>";
					$i = 0;
					$dragModalNameList = array();
					$input .= preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,$MIndex,$REVIEW,$STUDENTANS,$QUE_TYPE,$TESTID,$AID,$CANS,&$dragModalNameList) {
						$modalName = 'dragDropText'.$i.''.$MIndex;
						$dragModalNameList[] = $modalName;

						$i++;
						// return '<input id="' . $id . '">';
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : (($STUDENTANS[$i-1])!==($CANS[$i-1] )? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
							}else{
								$CLASS = 'text-danger';
							}
							return "<b class='$CLASS'>".(count($STUDENTANS)<=0 ? '-' : ($STUDENTANS[$i-1]==''?'-':$STUDENTANS[$i-1]))."</b>";
						}else{
							$A = str_replace(';','||',$AID);
							$d = '$data';
							return "<input type='text' readonly  ng-drop='true' ng-drop-success='onDropComplete($d,\"$modalName\")' ng-model='$modalName' ng-mouseleave='saveAnswer($TESTID,\"$A\",$modalName,\"$QUE_TYPE\",$MIndex)' class='d-inline col-2 form-control dragDropinput inputStyle text-center' name='$modalName' id='$modalName' placeholder='' >";
						}
					}, $QUESTION);
					$input .= '</div>';
					$row['QUESTION_INPUT'] = $input;
					$data['dragModalNameList'] = $dragModalNameList;

					// #### DRAG LIST
					$DRAG_DROP_OPTION = explode(';',$row['AAID']);
					if($REVIEW==0){
						shuffle($DRAG_DROP_OPTION);
					}
					$row['DRAG_DROP_OPTION'] = $DRAG_DROP_OPTION;

				}else if($row['QUE_TYPE'] == 'GRID'){
					// #################
					// ########### GRID
					// #################
					$ST= isset($row['STUDENT_ANS']) ? str_replace(' ','',$row['STUDENT_ANS']) : '';
					$CA= isset($row['AID']) ? str_replace(' ','',$row['AID']) : '';
					

					$CANS = (!$CA || $CA == '') ? array() : explode(';',$CA);
					$STUDENTANS = (!$ST || $ST == '') ? array() : explode(';',$ST);

					// // DEBUG
					// $data['success'] = false;
					// $data['STUDENTANS']=$STUDENTANS;
					// echo json_encode($data);exit;
					// // DEBUG
					// $QUESTION = str_replace(',','||',$QUESTION);
					$i = 0;
					$input = preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,$MIndex,$REVIEW,$QUE_TYPE,$TESTID,$AID,$STUDENTANS,$CANS) {
						$i++;
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : (($STUDENTANS[$i-1])!==($CANS[$i-1] )? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
							}else{
								$CLASS = 'text-danger';
							}
							return "<b class='$CLASS'>".(count($STUDENTANS)<=0 ? '-' : ($STUDENTANS[$i-1]==''?'-':$STUDENTANS[$i-1]))."</b>";
						}else{
							$A = str_replace(';','||',$AID);
							return '<input type="text" ng-model="gridText'.$i.'" ng-blur="saveAnswer('.$TESTID.',\''.$A.'\',\'gridText'.$i.'\',\''.$QUE_TYPE.'\','.$MIndex.')" style="height: 100%" class="d-inline col-12 mb-0 form-control inputStyle text-center grid-input" name="gridText'.$i.'" id="gridText'.$i.'" placeholder="">';
						}
						
					}, $QUESTION);
					$row['QUESTION_INPUT_TEST'] = $input;
					
					$simpleArray = explode(';',$input);
					
					$ROWS = $row['ROWS'];
					$COLS = $row['COLUMNS'];

					$associativeArray = array();
					$index = 0;

					$table = "<div id='grid$MIndex'>";
					for ($i = 0; $i < $ROWS; $i++) {
						$table .= "<ul class='list-group list-group-horizontal'>";
						
						for ($j = 0; $j < $COLS; $j++) {
							if (isset($simpleArray[$index])) {
								$associativeArray[$i][$j] = $simpleArray[$index];

								$table.="<li class='list-group-item d-flex justify-content-center align-items-center p-0 rounded-0' style='width:100px;height:100px'>".$simpleArray[$index]."</li>";
								$index++;
							} else {
								break;
							}
						}
						$table .= "</ul>";
					}
					$table .= "</div>";
					$row['QUESTION_INPUT'] = $table;
				
				
					

				}else{
					$row['QUESTION_INPUT'] = $QUESTION;
					$row['STUDENT_ANS'] = (int)$row['STUDENT_ANS'];
				}

				// GET OPTIONS
				$OPTION_ARR =array();
				if($QUE_TYPE=='MCQ SINGLE'){
					$queryOP = "SELECT OPTIONID,OPTIONS,ISCORRECT,[IMAGE] FROM LA_QUESTIONS_OPTIONS WHERE SQUEID=$SQUEID";
					$RCOUNTOP = unique($queryOP);
					if($RCOUNTOP > 0){
						$resultOP = sqlsrv_query($mysqli, $queryOP);
						while ($rowOP = sqlsrv_fetch_array($resultOP,SQLSRV_FETCH_ASSOC)){
							$OPTION_ARR[] =$rowOP;
						}
					}
				}
				$row['OPTIONS'] = $OPTION_ARR;
				$data['data'][] = $row;

				$MIndex++;
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


/*============ GET ASSIGNMENTS =============*/ 
 function getAssignments($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];
		if($LOCID == 0) throw new Exception('Select Location First.');
		if($GRADEID == 0) throw new Exception('Select Grade First.');
		if($SUBID == 0) throw new Exception('Select Subject First.');
		if($TOPICID == 0) throw new Exception('Select Topic First.');

		$query = "SELECT MQUEID,DAYNO,TESTCODE,QUESTION,PASSAGE,
		ISNULL((SELECT CAST(SQUEID AS VARCHAR)+'#SQUEID#'+ANSWER+'#ANSWER#'+QUE_TYPE+'#QUETYPE#'+QUESTION+'#OPTION#'+
				ISNULL((SELECT CAST(OPTIONID AS VARCHAR)+'#OPTIONID#'+CAST(ISCORRECT AS VARCHAR)+'#ISCONRRECT#'+[IMAGE]+'#IMAGE#'+OPTIONS+'#NEXT#' FROM LA_QUESTIONS_OPTIONS WHERE SQUEID=QS.SQUEID FOR XML PATH('')),'')+'||' 
				FROM LA_QUESTIONS_SUB QS WHERE MQUEID=Q.MQUEID FOR XML PATH('')),'')QUESTIONS
		FROM LA_QUESTIONS_MAIN Q WHERE ISDELETED=0 AND GRADEID=$GRADEID AND SUBID=$SUBID AND TOPICID=$TOPICID AND LOCID=$LOCID";
		$data['$query']=$query;
		// echo json_encode($data);exit;
		$RCOUNT = unique($query);
		if($RCOUNT > 0){
			$result = sqlsrv_query($mysqli, $query);
			$QUE_ARR = array();
			$MAIN_DATA = array();
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['QUESTIONS'] = rtrim($row['QUESTIONS'],'||');
				$QUE_ARR[] = explode('||',$row['QUESTIONS']);
				$MAIN_DATA[]=$row;
			}
			// $data['data'][] = $MAIN_DATA;
			$data['$QUE_ARR'] = $QUE_ARR;
			foreach($MAIN_DATA as $key=>$val){
				$QUES = array();
				if(count($QUE_ARR[$key])>0){
					for($i=0;$i<count($QUE_ARR[$key]); $i++){
						$s = explode('#SQUEID#',$QUE_ARR[$key][$i]);
						$SQUEID =  $s[0];
						$ANS_QUETYPE_QUE_OPTION =$s[1];
						$a = explode('#ANSWER#',$ANS_QUETYPE_QUE_OPTION);
						$ANSWER = $a[0];
						$QUETYPE_QUE_OPTION = $a[1];
						$t = explode('#QUETYPE#',$QUETYPE_QUE_OPTION);
						$QUETYPE =  $t[0];
						$QUE_OPTION = $t[1];
						$o = explode('#OPTION#',$QUE_OPTION);
						$QUESTION =$o[0];
						
						$oval = $o[1];
						// $oVAL =array();
						// OPTIONS
						// foreach($o as $oval){
							if($oval!=''){
								$oval = rtrim($oval,'#NEXT#');
								$oval = explode('#NEXT#',$oval);
								$OPTION_DET = array();
	
								
								for($a=0;$a<count($oval);$a++){
									$opid = explode('#OPTIONID#',$oval[$a]);
									$OPTIONID = $opid[0];
									$ISCORRECT_IMG = $opid[1];
									$ic = explode('#ISCONRRECT#',$ISCORRECT_IMG);
									$ISCORRECT = $ic[0];
									$IMG_OP = $ic[1];
									$img = explode('#IMAGE#',$IMG_OP);
									$IMAGE = $img[0];
									$OPTION = $img[1];
									
									$OPTION_DET[] = ['OPTIONID'=>$OPTIONID,'OPTION'=>$OPTION,'ISCORRECT'=>$ISCORRECT,'IMAGE'=>$IMAGE];
								}
								$QUES[] = ['SQUEID' => $SQUEID,'QUESTION' => $QUESTION,'QUETYPE'=>$QUETYPE,'ANSWER'=>$ANSWER,'OPTIONS'=>isset($OPTION_DET) ? $OPTION_DET : []];
							}else{
								$QUES[] = ['SQUEID' => $SQUEID,'QUESTION' => $QUESTION,'QUETYPE'=>$QUETYPE,'ANSWER'=>$ANSWER,'OPTIONS'=>[]];
							}
						}
					}
					// ======== DEBUG
					// $data['$$QUES']=$QUES;
					// echo json_encode($data);exit;
					// ======== DEBUG
					// }
				$MAIN_DATA[$key]['FINAL_QUESTIONS'] = $QUES;
			}
			$data['data'] = $MAIN_DATA;
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


/* ======== SAVE ANSWER ======= */ 
function saveAnswer($mysqli){
	try
	{
	   $data = array();
	   global $userid;	

	   $TESTID = ($_POST['TESTID'] == 'undefined' || $_POST['TESTID'] == '') ? 0 : $_POST['TESTID'];
	   $AID = ($_POST['AID'] == 'undefined' || $_POST['AID'] == '') ? '' : str_replace('||',';',$_POST['AID']);
	   $ANS = ($_POST['ANS'] == 'undefined' || $_POST['ANS'] == '') ? '' : json_decode($_POST['ANS'],true);
	   $ANS = is_array($ANS) ? implode(';',$ANS) : $ANS;
	//    $ANS = str_replace(',',';',$ANS);

	    //   $data['success'] = false;
	   	// $data['AID'] = $AID;
	   	// $data['ANS1'] = $_POST['ANS'];
	   	// $data['ANS'] = $ANS;
	   	// // $data['RESULT'] = $RESULT;
	   	// echo json_encode($data);exit;

	   $AID_CHK = str_replace(' ','',$AID);
	   $ANS_CHK = str_replace(' ','',$ANS);
	   $RESULT = strtolower($AID_CHK)===strtolower($ANS_CHK) ? 1 : 0;


	   if($TESTID == 0) throw new Exception("Error :  TESTID.");


		$query="UPDATE LA_STUDENT_TEST SET STUDENT_ANS='$ANS',RESULT=$RESULT WHERE TESTID=$TESTID";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			$data['success'] = false;
			$data['query'] = $query;
			echo json_encode($data);exit;
		}
		else
		{

			$data['query'] = $query;
			$data['success'] = true;
			// if(!empty($mqueid))$data['message'] = 'Record successfully updated.';
			// else $data['message'] = 'Record successfully inserted.';
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


/* ======== RESET TEST ======= */ 
function resetTest($mysqli){
	try
	{
		$data = array();
		global $userid;	

	   	$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		$TOPICID = ($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];

		if($REGID == 0) throw new Exception('Error : Studentid Not Found.');
		if($LOCID == 0) throw new Exception('Select Location First.');
		if($GRADEID == 0) throw new Exception('Select Grade First.');
		if($SUBID == 0) throw new Exception('Select Subject First.');
		if($TOPICID == 0) throw new Exception('Select Topic First.');


		$query="DELETE FROM LA_STUDENT_TEST WHERE ISDELETED=0 AND REGID=$REGID AND GRADEID=$GRADEID AND SUBID=$SUBID AND TOPICID=$TOPICID";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			$data['success'] = false;
			$data['query'] = $query;
			echo json_encode($data);exit;
		}
		else
		{

			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Test successfully reset.';
			// if(!empty($mqueid))$data['message'] = 'Record successfully updated.';
			// else $data['message'] = 'Record successfully inserted.';
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







