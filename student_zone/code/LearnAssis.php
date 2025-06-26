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
		$ID=($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];
		$FOR=($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];
		if($ID == 0)throw new Exception('UserID Invalid.');
		if($FOR == '') throw new Exception('Invalid Grade & Subject For.');

		if($FOR=='STUDENT'){
			$query = "SELECT GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SG.GRADEID)GRADE,SUBID,
			(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SG.SUBID)SUBJECTNAME
			FROM STUDENTS_GRADES_SUBJECTS SG WHERE ISDELETED=0 AND REGID=$ID
			ORDER BY GRADE";
		}else{
			$query = "SELECT GRADEID,(SELECT GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SG.GRADEID)GRADE,SUBID,
			(SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SG.SUBID)SUBJECTNAME
			FROM TEACHERS_GRADES_SUBJECTS SG WHERE ISDELETED=0 AND TEACHERID=$ID
			ORDER BY GRADE";
		}
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
$all_data=array();
$RETURN_DATA='';
function getTopics($mysqli){
	try
	{
		global $userid,$all_data;
		$data = array();
		// if(!isset($userid) || $userid<=0) throw new Exception('Invalid User.');
		
		$USERID = ($_POST['USERID'] == 'undefined' || $_POST['USERID'] == '') ? 0 : $_POST['USERID'];
		$ddlLocation = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$ddlGrade = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
		$ddlSubject = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];
		$FOR=($_POST['FOR'] == 'undefined' || $_POST['FOR'] == '') ? '' : $_POST['FOR'];
		if($USERID == 0) throw new Exception('Invalid Login Userid.');
		if($ddlLocation == 0) throw new Exception('Select Location First.');
		if($ddlGrade == 0) throw new Exception('Select Grade First.');
		if($ddlSubject == 0) throw new Exception('Select Subject First.');
		if($FOR == '') throw new Exception('Invalid Grade & Subject For.');
		$data['G/S']=$ddlGrade.'/'.$ddlSubject;

		// $query = "EXEC [GET_LA_TOPIC_MASTER] $ddlLocation,$ddlGrade,$ddlSubject,$userid";
		if($FOR=='STUDENT'){
			$query = "EXEC [LA_GET_TOPICS_FOR_STUDENT] $ddlGrade,$ddlSubject,$USERID";
		}else{
			$query = "EXEC [LA_GET_TOPICS_FOR_TEACHER] $ddlGrade,$ddlSubject,$USERID";
		}
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		if(sqlsrv_has_rows($result) !== false){
			
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SLIDES'] = rtrim($row['SLIDES'],'#,# ');
				$row['SLIDES_LIST'] = explode('#,# ',$row['SLIDES']);

				$row['SLIDEID'] = rtrim($row['SLIDEID'],'#,# ');
				$row['SLIDEID_LIST'] = explode('#,# ',$row['SLIDEID']);

				$row['ATT_STATUS'] = rtrim($row['ATT_STATUS'],'#,# ');
				$row['ATT_STATUS_LIST'] = explode('#,# ',$row['ATT_STATUS']);

				$row['OPEN_ISSUE'] = rtrim($row['OPEN_ISSUE'],'#,# ');
				$row['OPEN_ISSUE_LIST'] = explode('#,# ',$row['OPEN_ISSUE']);
				$all_data[] = $row;
				if($row['UNDERTOPICID'] > 0){
					$data['data'][] = $row;
				}
			}

			$data['finalData'] = getUnderTopics($mysqli,0);

			// $filteredObjects = array_filter($all_data, function($object) {
			// 	return $object['UNDERTOPICID'] == 0;
			// });
			// $data['$filteredObjects']=$filteredObjects;

			$data['success'] = true;
		}else{
			$data['finalData'] = '';
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
		$RETURN_DATA .=$index == 0 ? "<ol>" : "";
		$IDX=1;
		foreach($filteredObjects as $key => $rows){
			// if($IDX <= 3){

				$UNDERTOPICID = $rows['UNDERTOPICID'];
				$SLIDES_LIST = $rows['SLIDES_LIST'];
				$SLIDEID_LIST = $rows['SLIDEID_LIST'];
				$ATT_STATUS_LIST = $rows['ATT_STATUS_LIST'];
				$data['ATT_STATUS_LIST'][] = $ATT_STATUS_LIST;
				$OPEN_ISSUE_LIST = $rows['OPEN_ISSUE_LIST'];
				$TOPIC = $rows['TOPIC'];
				$TSTATUS = $rows['TSTATUS']=='COVERED'?1:0;
				$TOPICID_NEW = (int)$rows['TOPICID'];
				$LEARN_EXIST = (int)$rows['LEARN_EXIST'];
				$ASS_EXIST = (int)$rows['ASS_EXIST'];
				$SHOW_ASSIGNMENT = (int)$rows['SHOW_ASSIGNMENT'];
				$NEXT_TOPIC_EXIST = $rows['NEXT_TOPIC_EXIST'];
				$TOTAL_SLIDES = $rows['TOTAL_SLIDES'];
				$first = $UNDERTOPICID == 0 ? 'pb-2' : 'py-2';
				$event = '$event';
				$CLASS = $UNDERTOPICID == 0 ? 'GRADES px-4 rounded-pill' : 'SUBJECTS px-2';
				$LiCLASS = $UNDERTOPICID > 0 ? 'SUBJECT_LI pl-4 mt-2' : '';
				// $indexExist = $NEXT_TOPIC_EXIST > 0 ? ''
	
				$FLEX = $NEXT_TOPIC_EXIST==0 ? 'd-flex justify-content-between' : '';
				$UNDERHEAD_CLASS = $NEXT_TOPIC_EXIST>0 ? 'GRADES px-4 rounded-pill' : '';
				if($UNDERTOPICID > 0){
					$RETURN_DATA.="<li class='py-2 $LiCLASS' >";
					
					// if($NEXT_TOPIC_EXIST>0){
						if($UNDERHEAD_CLASS==''){
							$RETURN_DATA.="<div class='$FLEX'><div>";
							if($NEXT_TOPIC_EXIST==0)$RETURN_DATA.= "<span class='topicIndex'>$IDX</span>";
							$RETURN_DATA.="<span class='$CLASS'>$TOPIC</span>";
							// $RETURN_DATA.="<span class='$CLASS'>$TOPIC <i title='Topic Covered By Teacher.' class='fa fa-check-circle font-20 text-success' ng-show='$TSTATUS'></i></span>";
							
							$RETURN_DATA.="</div>
							<div class='d-flex'>
								<!--<button ng-show='$LEARN_EXIST>0' class='btn btn-yellow-grad-1 text-dark px-4 py-1 mx-1' ng-click='getSlides($TOPICID_NEW,\"$TOPIC\",$event)' data-toggle='modal' data-target='#slideModal'>LEARN <b title='Total Slides'>($TOTAL_SLIDES)</b></button>-->
								<button ng-if='$ASS_EXIST>0 && $SHOW_ASSIGNMENT==1 && PAGEFOR==\"STUDENT\"' class='btn btn-success-grad-3 px-4 py-1 mx-1' ng-click='startAssis($TOPICID_NEW,\"$TOPIC\")' data-toggle='modal' data-target='#assignmentModal'>ASSIGNMENTS</button>
							</div></div>";
							if($SLIDES_LIST>0){
								$RETURN_DATA.="<div class='text-monospace font-12 d-flex flex-column slide_text'>";
								for($s=0;$s<count($SLIDES_LIST);$s++){
									$SLID=!isset($SLIDEID_LIST[$s]) ? 0 : $SLIDEID_LIST[$s];
									$open_issue_det = !isset($OPEN_ISSUE_LIST[$s]) ? array() : explode('#__#',$OPEN_ISSUE_LIST[$s]);
									
									if(count($open_issue_det)>0){
										$open_issue = $open_issue_det[0];
										$issue_remark = $open_issue_det[1]=='-' ? '' : $open_issue_det[1];
									}else{
										$open_issue = 0;
										$issue_remark = '';
									}
									// $open_issue = 0;
									// $issue_remark = '';


									if(isset($SLID)==true && $SLID>0){
										$ATT_STATUS=!isset($ATT_STATUS_LIST[$s]) ? '' : ($ATT_STATUS_LIST[$s] == 0 ? '' : '<i title="Topic Covered By Teacher." class="fa fa-check-circle font-16 px-2 text-success"></i>');
										$RETURN_DATA.="<div class='d-flex justify-content-between slide_text_chiled1'>
												<span class='slideHover px-4 d-flex align-items-center' ng-click='getSlides($TOPICID_NEW,$SLID,\"$TOPIC\")' data-toggle='modal' data-target='#slideModal'>".($SLIDES_LIST[$s])." ".$ATT_STATUS."</span>

												<div class='d-flex flex-row align-items-center px-4'>
													<div class='custom-control custom-switch pl-3'>
														<input type='checkbox' class='custom-control-input issueInputStudent$key$s' ng-model='openIssueStudent$key$s' id='openIssueStudent$key$s' 
															ng-change='openCloseIssue($SLID,openIssueStudent$key$s,$key$s)'
															ng-true-value='\"1\"' ng-false-value='\"0\"' ng-init='openIssueStudent$key$s=\"$open_issue\"'>
														<label class='custom-control-label pointer' for='openIssueStudent$key$s'></label>
													</div>
													<span ng-if='$open_issue == 0' class='d-block text-nowrap spinopenIssueStudent$key$s' style='color: #3f51b5;'>ISSUE</span>
													<button ng-if='$open_issue == 1' class='btn btn-dark rounded-pill py-0 text-nowrap' style='background: #3f51b5;' ng-click='getIsuuesDet($SLID,\"$issue_remark\")' data-toggle='modal' data-target='#IssueModal'>View</button>
												</div>
										</div>";		
									}
								}
								$RETURN_DATA.="</div>";
							}
						}else{
							$RETURN_DATA.="<div>";
							if($NEXT_TOPIC_EXIST==0)$RETURN_DATA.= "<span class='topicIndex'>$IDX</span>";
							$RETURN_DATA.="<span class='$UNDERHEAD_CLASS'>$TOPIC</span>";
							$RETURN_DATA.="</div>";
							
						}
					// }else{
					// 	$RETURN_DATA.= "<span class='topicIndex'>$IDX</span>";
					// 	$RETURN_DATA.="<span class='$CLASS' ng-click='getSlides($TOPICID,\"$TOPIC\",$event)' data-toggle='modal' data-target='#slideModal'>$TOPIC</span>";
					// }
				}else{
					$RETURN_DATA.="<li class='py-2 $LiCLASS'>";
					$RETURN_DATA.="<span class='$CLASS' ng-click=''>$TOPIC</span>";
					// if($NEXT_TOPIC_EXIST==0) $RETURN_DATA.= "<span class='topicIndex'>$IDX</span>";
				}
					getUnderTopics($mysqli ,$rows['TOPICID']);
				$RETURN_DATA.="</li>";
			// }

			// $one[] = $rows;
			// $one['UNDER'] = getUnderTopics($mysqli ,$rows['TOPICID']);
			// $one[] = $subtopic;
			$IDX++;
		}
		$RETURN_DATA .="</ol>";
		// $RETURN_DATA[] = $subtopic;
	}else{
	}
	// $RETURN_DATA = ['data'=>$cardList, 'success'=>$SUCCESS];
	return $RETURN_DATA;
}




/*============ GET SLIDES =============*/ 
 function getSlides($mysqli){
	try
	{
		$data = array();
		$TOPICID=($_POST['TOPICID'] == 'undefined' || $_POST['TOPICID'] == '') ? 0 : $_POST['TOPICID'];
		$SLIDEID=($_POST['SLIDEID'] == 'undefined' || $_POST['SLIDEID'] == '') ? 0 : $_POST['SLIDEID'];
		if($TOPICID == 0)throw new Exception('TOPICID Invalid.');
		if($SLIDEID == 0)throw new Exception('SLIDEID Invalid.');

		$query = "SELECT SLIDEID,SLIDEHEADING FROM LA_TOPIC_SLIDE_MASTER WHERE ISDELETED=0 AND TOPICID=$TOPICID AND SLIDEID=$SLIDEID ORDER BY SLIDEHEADING";
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
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
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
				FROM LA_STUDENT_TEST ST WHERE ISDELETED=0 AND MQUEID=$MQUEID AND REGID=$REGID";


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
					$input .= preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,$MIndex,$REVIEW,$STUDENTANS,$QUE_TYPE,$TESTID,$AID,$CANS,$RESULT) {
						$modalName = 'typeinText'.$i.''.$MIndex;
						$i++;
						// return '<input id="' . $id . '">';
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : ($RESULT==0? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
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
					$input .= preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,$MIndex,$REVIEW,$STUDENTANS,$QUE_TYPE,$TESTID,$AID,$CANS,&$dragModalNameList,$RESULT) {
						$modalName = 'dragDropText'.$i.''.$MIndex;
						$dragModalNameList[] = $modalName;

						$i++;
						// return '<input id="' . $id . '">';
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : ($RESULT==0? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
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
					$data['CANS']=$CANS;
					$data['STUDENTANS']=$STUDENTANS;
					// echo json_encode($data);exit;
					// // DEBUG
					// $QUESTION = str_replace(',','||',$QUESTION);
					$i = 0;
					$AND_IDX = 0;
					$input = preg_replace_callback('/<INPUT>/i', function ($matches) use (&$i,&$AND_IDX,$MIndex,$REVIEW,$QUE_TYPE,$TESTID,$AID,$STUDENTANS,$CANS,$RESULT) {
						$i++;
						if($REVIEW>0){
							if(count($CANS)>0 && count($STUDENTANS)>0){
								// $CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : ($RESULT==0? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
								$CLASS = (($STUDENTANS[$i-1])=='') ? 'text-danger' : ($STUDENTANS[$AND_IDX]!=$CANS[$AND_IDX]? 'text-white bg-danger px-1 rounded-sm' : 'text-white bg-success px-1 rounded-sm ');
							}else{
								$CLASS = 'text-danger';
							}
							$AND_IDX++;
							return "<b class='$CLASS'>".(count($STUDENTANS)<=0 ? '-' : ($STUDENTANS[$i-1]==''?'-':$STUDENTANS[$i-1]))."</b>";
						}else{
							$A = str_replace(';','||',$AID);
							return '<input type="text" ng-model="gridText'.$i.'" ng-blur="saveAnswer('.$TESTID.',\''.$A.'\',\'gridText'.$i.'\',\''.$QUE_TYPE.'\','.$MIndex.')" style="height: 100%" class="d-inline col-12 mb-0 form-control inputStyle text-center grid-input" name="gridText'.$i.'" id="gridText'.$i.'" placeholder="">';
						}
						
					}, $QUESTION);
					$row['QUESTION_INPUT_TEST'] = $input;
					
					// $data['input'][] = $input;
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
	   	// $data['RESULT'] = $RESULT;
	   	// echo json_encode($data);exit;

		$search = ['×', '*'];
		$AID_CHK = str_replace(' ','',$AID);
		$AID_CHK = str_replace($search,'x',$AID_CHK);
		
		$ANS_CHK = str_replace(' ','',$ANS);
		$ANS_CHK = str_replace($search,'x',$ANS_CHK);

		$RESULT = strtolower($AID_CHK)===strtolower($ANS_CHK) ? 1 : 0;

		$data['RESULT'] = $AID_CHK.'/'.$ANS_CHK;


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







