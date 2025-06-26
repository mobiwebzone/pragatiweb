<?php
session_start();
require_once '../../code/connection.php';

// require_once '../../dompdf/autoload.inc.php';
// use Dompdf\Dompdf;


if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getStudentCollegeRoadmap":getStudentCollegeRoadmap($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET STUDENT COLLEGE ROADMAP =========== */
function getStudentCollegeRoadmap($mysqli){
	try
	{
		$data = array();
		$REGID =  ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
		$grid = '';

		$query = "SELECT ROADMAPID,STUDENT_TYPE,LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=SCR.LOCID)[LOCATION],
		REGID,FIRSTNAME,LASTNAME,EMAILID,PHONE,PARENT1_FIRST_NAME,PARENT1_LAST_NAME,
		PARENT1_EMAILID,PARENT1_PHONE,PARENT2_FIRST_NAME,PARENT2_LAST_NAME,PARENT2_EMAILID,PARENT2_PHONE,CURRENT_GRADEID,
		(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SCR.CURRENT_GRADEID)GRADE,ADMYEARID,
		(SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=SCR.ADMYEARID)SCHOOLYEAR,SCHOOL,COUNTY,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=SCR.COUNTRYID)COUNTRY,STATEID AS STATENAME,CITYID AS CITYNAME,REMARKS
		FROM STUDENT_COLLEGE_ROADMAP SCR
		WHERE ISDELETED=0 AND REGID=$REGID";
		

		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ROADMAPID'] = (int) $row['ROADMAPID'];
				$ROADMAPID = (int)$row['ROADMAPID'];
				$data['data'][] = $row;
			}

			// ###########################
			// ######### CLASSES #########
			// ###########################

			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2"><b>CLASSES</b></h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">CLASSES</th>';


			// GET GRADES
			$GRADE_GROUP = '';
			$GRADE_GROUP_ARR = array();
			$queryGrades ="SELECT DISTINCT (SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SRC.GRADEID)GRADE,
			(SELECT LEN(GRADE) FROM GRADES_MASTER WHERE GRADEID=SRC.GRADEID)GRADE_LEN
			FROM STUDENT_COLLEGE_ROADMAP_CLASSES SRC WHERE REGID=$REGID AND ROADMAPID=$ROADMAPID ORDER BY GRADE_LEN DESC,GRADE DESC";
			$data['$queryGrades']=$queryGrades;
			$resultGrades = sqlsrv_query($mysqli, $queryGrades);
			while ($rowGrades = sqlsrv_fetch_array($resultGrades,SQLSRV_FETCH_ASSOC)) {
				$GRADE_GROUP .= '['.$rowGrades['GRADE'].'],';
				$data['GRADE'][] = $rowGrades['GRADE'];
				$GRADE_GROUP_ARR[]=$rowGrades['GRADE'];

				$grid .= '<th style="" class="pl-4">Grade:'.$rowGrades['GRADE'].'</th>';
			}

			$GRADE_GROUP = rtrim($GRADE_GROUP,',');

			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			// $data['success'] = false;
			// echo json_encode($data);exit;
			if($GRADE_GROUP!==''){

				$queryClasses = "SELECT * FROM
				(
				SELECT GRADE,CLASSES,REMARKS
				FROM
				(SELECT GRADEID,(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=RMC.GRADEID) GRADE,
				CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=RMC.CSUBID) CLASSES, REMARKS
				FROM STUDENT_COLLEGE_ROADMAP_CLASSES  RMC
				--WHERE REGID=$REGID AND ISDELETED=0 AND ROADMAPID=$ROADMAPID AND REMARKS IS NOT NULL AND REMARKS<>'')TT
				WHERE REGID=$REGID AND ISDELETED=0 AND ROADMAPID=$ROADMAPID)TT
				)TTT 
				PIVOT (
				 max(REMARKS)
				  for GRADE in ($GRADE_GROUP)
				)
				AS PivotTable
				order by CLASSES DESC";

				$data['$queryClasses']=$queryClasses;
				$resultClasses = sqlsrv_query($mysqli, $queryClasses);
				while ($rowClasses = sqlsrv_fetch_array($resultClasses,SQLSRV_FETCH_ASSOC)) {
					$CLASSES = $rowClasses['CLASSES'];
					// $REMARKS = $rowClasses['REMARKS'];
					
					$grid .= '<tr class="">';
					$grid .= '<td class="pl-4">'.$CLASSES.'</td>';
	
					$INDEX = 0;
					for($i=0; $i<count($GRADE_GROUP_ARR); $i++){
						$INDEX++;
						$NEW_GRADE = $GRADE_GROUP_ARR[$i];
						$GET_REMARK = $rowClasses[$NEW_GRADE];
						$grid .= '<td class="pl-4">'.$GET_REMARK.'</td>';
					}
	
					// $row['ROADMAPID'] = (int) $row['ROADMAPID'];
					// $ROADMAPID = (int)$row['ROADMAPID'];
					$data['data'][] = $row;
	
					$grid .= '</tr>';
				}
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';

			// ###########################
			// ######### CLASSES #########
			// ###########################








			// ##############################
			// ######### ACTIVITIES #########
			// ##############################
			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2 mt-4"><b>ACTIVITIES</b></h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">ACTIVITY</th>';
			

			// GET GRADES
			$GRADE_GROUP_A = '';
			$GRADE_GROUP_ARR_A = array();
			// $queryGrades_A ="SELECT DISTINCT (SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SRA.GRADEID)GRADE
			// FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES SRA WHERE REGID=$REGID AND ROADMAPID=$ROADMAPID";
			$queryGrades_A ="SELECT DISTINCT (SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SRA.GRADEID)GRADE,
			(SELECT LEN(GRADE) FROM GRADES_MASTER WHERE GRADEID=SRA.GRADEID)GRADE_LEN
			FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES SRA WHERE REGID=$REGID AND ROADMAPID=$ROADMAPID ORDER BY GRADE_LEN DESC,GRADE DESC";
			$resultGrades_A = sqlsrv_query($mysqli, $queryGrades_A);
			while ($rowGrades_A = sqlsrv_fetch_array($resultGrades_A,SQLSRV_FETCH_ASSOC)) {
				$GRADE_GROUP_A .= '['.$rowGrades_A['GRADE'].'],';
				$data['GRADE_A'][] = $rowGrades_A['GRADE'];
				$GRADE_GROUP_ARR_A[]=$rowGrades_A['GRADE'];

				$grid .= '<th style="" class="pl-4">Grade:'.$rowGrades_A['GRADE'].'</th>';
			}
			$GRADE_GROUP_A = rtrim($GRADE_GROUP_A,',');


			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			$data['$queryGrades_A'] = $queryGrades_A;
			// $data['success'] = false;
			// echo json_encode($data);exit;

			if($GRADE_GROUP_A!==''){
				$queryActivity = "SELECT *
				FROM
				(
				SELECT GRADEID,(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=RMA.GRADEID) GRADE,
				ACTIVITYID,(SELECT ACTIVITY FROM ACTIVITIES_MASTER WHERE ACTIVITYID=RMA.ACTIVITYID) ACTIVITY,
				ISNULL((SELECT (SELECT LEGEND FROM ACTIVITY_LEGEND_MASTER WHERE LEGENDID=L.LEGENDID) + '|' 
				FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS L WHERE ROADMAPACTID=RMA.ROADMAPACTID AND ISDELETED=0 FOR XML PATH('')),'')
				+'$&$'+ JUNIOR_VARSITY +'$&$'+ VARSITY +'$&$'+ LOCAL_CLUB +'$&$'+ CAST(ISNULL(HOURS_PER_WEEK,0) AS VARCHAR) +'$&$'+ CAST(ISNULL(NO_OF_WEEKS,0) AS VARCHAR)+'$&$'+ REMARKS AS LEGENDS,
				--ADD ROW NUMBER FOR SHOW ALL DATA
				ROW_NUMBER() OVER(partition by GRADEID order by (SELECT (SELECT LEGEND FROM ACTIVITY_LEGEND_MASTER WHERE LEGENDID=L.LEGENDID) + '|'  FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES_LEGENDS L WHERE ROADMAPACTID=RMA.ROADMAPACTID AND ISDELETED=0 FOR XML PATH('')) DESC)AS ROWNUM
				FROM STUDENT_COLLEGE_ROADMAP_ACTIVITIES RMA WHERE REGID=$REGID AND ROADMAPID=$ROADMAPID AND ISDELETED=0
				) LEGENDS
				PIVOT
				(
				MAX(LEGENDS) FOR GRADE IN($GRADE_GROUP_A)
				) AS PIVOTTABLE
				ORDER BY ACTIVITY";
	
				$data['$queryActivity'] = $queryActivity;
	
	
				$resultActivity = sqlsrv_query($mysqli, $queryActivity);
				while ($rowActivity = sqlsrv_fetch_array($resultActivity,SQLSRV_FETCH_ASSOC)) {

					$ACTIVITY = $rowActivity['ACTIVITY'];
					// $REMARKS = $rowActivity['REMARKS'];
					
					$grid .= '<tr class="">';
					$grid .= '<td class="pl-4">'.$ACTIVITY.'</td>';
	
					$INDEX = 0;
					for($i=0; $i<count($GRADE_GROUP_ARR_A); $i++){
						$INDEX++;
						// $NEW_GRADE_A = $GRADE_GROUP_ARR_A[$i];
						if(!empty($rowActivity[$GRADE_GROUP_ARR_A[$i]])){
							$GET_ALL_DATA = explode('$&$', $rowActivity[$GRADE_GROUP_ARR_A[$i]]);

							if(count($GET_ALL_DATA)>0){
								$GET_LEGENDS =  !empty($GET_ALL_DATA[0]) ? rtrim($GET_ALL_DATA[0],' | ') : '&#8212';
								$GET_JUNIOR_VARSITY =  !empty($GET_ALL_DATA[1]) ? $GET_ALL_DATA[1] : '&#8212;';
								$GET_VARSITY =  !empty($GET_ALL_DATA[2]) ? $GET_ALL_DATA[2] : '&#8212;';
								$GET_ORGANIZATION =  !empty($GET_ALL_DATA[3]) ? $GET_ALL_DATA[3] : '&#8212;';
								$GET_HOURSE_PERWEEK =  !empty($GET_ALL_DATA[4]) ? $GET_ALL_DATA[4] : '&#8212;';
								$GET_NO_OF_WEEK =  !empty($GET_ALL_DATA[5]) ? $GET_ALL_DATA[5] : '&#8212;';
								$GET_REMARKS =  !empty($GET_ALL_DATA[6]) ? $GET_ALL_DATA[6] : '&#8212;';
	
								// $GET_REMARK_A = rtrim($rowActivity[$NEW_GRADE_A],' | ');
								$grid .= "<td class='pl-4'>
											<div><b class='text-nowrap'>LEGENDS : </b>$GET_LEGENDS</div>
											<div><b class='text-nowrap'>J.VARSITY : </b>$GET_JUNIOR_VARSITY</div>
											<div><b class='text-nowrap'>VARSITY : </b>$GET_VARSITY</div>
											<div><b class='text-nowrap'>ORG. : </b>$GET_ORGANIZATION</div>
											<div><b class='text-nowrap'>HOURS/WEEK : </b>$GET_HOURSE_PERWEEK</div>
											<div><b class='text-nowrap'>NO OF WEEK : </b>$GET_NO_OF_WEEK</div>
											<div><b class='text-nowrap'>REMARKS : </b>$GET_REMARKS</div>
										</td>";
							}else{
								$grid .= "<td class='pl-4'></td>";
							}
						}else{
							$grid .= "<td class='pl-4'></td>";
						}


					}
	
					// $row['ROADMAPID'] = (int) $row['ROADMAPID'];
					// $ROADMAPID = (int)$row['ROADMAPID'];
					$data['data'][] = $row;
	
					$grid .= '</tr>';
				}
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';
			// ##############################
			// ######### ACTIVITIES #########
			// ##############################








			// #########################
			// ######### TESTS #########
			// #########################
			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2 mt-4"">
						<b>TESTS</b>
						<span class="badge badge-light">SCORE / SUPER SCORE</span>
					</h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">TESTS</th>';
			

			// GET GRADES
			$GRADE_GROUP_T = '';
			$GRADE_GROUP_ARR_T = array();
			$queryGrades_T ="SELECT DISTINCT (SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SRT.GRADEID)GRADE
			FROM STUDENT_COLLEGE_ROADMAP_TESTS SRT WHERE ROADMAPID=$ROADMAPID";
			$resultGrades_T = sqlsrv_query($mysqli, $queryGrades_T);
			while ($rowGrades_T = sqlsrv_fetch_array($resultGrades_T,SQLSRV_FETCH_ASSOC)) {
				$GRADE_GROUP_T .= '['.$rowGrades_T['GRADE'].'],';
				$data['GRADE_A'][] = $rowGrades_T['GRADE'];
				$GRADE_GROUP_ARR_T[]=$rowGrades_T['GRADE'];

				$grid .= '<th style="" class="pl-4">Grade:'.$rowGrades_T['GRADE'].'</th>';
			}
			$GRADE_GROUP_T = rtrim($GRADE_GROUP_T,',');


			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			// $data['$GRADE_GROUP'] = $GRADE_GROUP;
			// $data['success'] = false;
			// echo json_encode($data);exit;

			if($GRADE_GROUP_T!==''){
				$queryTests = "SELECT *
				FROM
				(
				SELECT GRADEID,(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=RM.GRADEID) GRADE,
				--TESTNAME + ' [' + CONVERT(VARCHAR,APPROX_TEST_DATE,105) + ']' TEST,
				TESTNAME TEST,
				CASE WHEN (APPROX_TEST_DATE='1900-01-01 00:00:00.000' OR APPROX_TEST_DATE = null OR APPROX_TEST_DATE='') 
					THEN '-' ELSE CONVERT(VARCHAR,APPROX_TEST_DATE,21) 
				END APPROX_TEST_DATE,
				CAST(TESTSCORE AS VARCHAR) + '/' + CAST(TESTSUPERSCORE AS VARCHAR) SCORE_SUPERSCORE
				FROM STUDENT_COLLEGE_ROADMAP_TESTS RM WHERE ROADMAPID=$ROADMAPID AND ISDELETED=0
				) LEGENDS
				PIVOT
				(
				MAX(SCORE_SUPERSCORE) FOR GRADE IN($GRADE_GROUP_T)
				) AS PIVOTTABLE
				ORDER BY CONVERT(DATE,CAST(APPROX_TEST_DATE AS DATETIME),105),TEST ASC";
				$data['$queryTests'] = $queryTests;
	
				$resultTests = sqlsrv_query($mysqli, $queryTests);
				while ($rowTests = sqlsrv_fetch_array($resultTests,SQLSRV_FETCH_ASSOC)) {
					if($rowTests['APPROX_TEST_DATE'] != '-'){
						$date=date_create($rowTests['APPROX_TEST_DATE']);
						$rowTests['APPROX_TEST_DATE'] = date_format($date,"M-d-Y");
					}
					$TEST = $rowTests['TEST'];
					// $REMARKS = $rowTests['REMARKS'];
					
					$grid .= '<tr class="">';
					if($rowTests['APPROX_TEST_DATE'] != '-'){
						$grid .= '<td class="pl-4">'.$TEST.' ['.$rowTests['APPROX_TEST_DATE'].']</td>';
					}else{
						$grid .= '<td class="pl-4">'.$TEST.'</td>';
					}
	
					$INDEX = 0;
					for($i=0; $i<count($GRADE_GROUP_ARR_T); $i++){
						$INDEX++;
						$NEW_GRADE_T = $GRADE_GROUP_ARR_T[$i];
						$GET_REMARK_T = $rowTests[$NEW_GRADE_T];
						$grid .= '<td class="pl-4">'.$GET_REMARK_T.'</td>';
					}
	
					// $row['ROADMAPID'] = (int) $row['ROADMAPID'];
					// $ROADMAPID = (int)$row['ROADMAPID'];
					$data['data'][] = $row;
	
					$grid .= '</tr>';
				}
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';
			// #########################
			// ######### TESTS #########
			// #########################
			







			// #########################
			// ######### MAJOR #########
			// #########################
			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2 mt-4""><b>MAJOR / MINOR</b></h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">MAJOR/MINOR</th>';
			$grid .= '<th style="" class="pl-4">Minor</th>';
			$grid .= '<th style="" class="pl-4">Major</th>';
			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			// $data['$GRADE_GROUP'] = $GRADE_GROUP;
			// $data['success'] = false;
			// echo json_encode($data);exit;

			$queryMajor = "SELECT *
			FROM
			(
			SELECT MTYPE,MAJOR MAJOR_MINOR , REMARKS
			FROM STUDENT_COLLEGE_ROADMAP_MAJOR_MINORS RM WHERE ROADMAPID=$ROADMAPID AND ISDELETED=0
			) MAJOR
			PIVOT
			(
			MAX(REMARKS) FOR MTYPE IN([Major],[Minor])
			) AS PIVOTTABLE
			ORDER BY MAJOR_MINOR";

			$data['queryMajor']= $queryMajor;
			$resultMajor = sqlsrv_query($mysqli, $queryMajor);
			while ($rowTests = sqlsrv_fetch_array($resultMajor,SQLSRV_FETCH_ASSOC)) {
				$MAJOR_MINOR = $rowTests['MAJOR_MINOR'];
				$MINOR = $rowTests['Minor'];
				$MAJOR = $rowTests['Major'];
				
				$grid .= '<tr class="">';
				$grid .= '<td class="pl-4">'.$MAJOR_MINOR.'</td>';
				$grid .= '<td class="pl-4">'.$MINOR.'</td>';
				$grid .= '<td class="pl-4">'.$MAJOR.'</td>';

				$data['data'][] = $row;

				$grid .= '</tr>';
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';
			// #########################
			// ######### MAJOR #########
			// #########################
			







			// ############################
			// ######### COLLEGES #########
			// ############################
			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2 mt-4"">
						<b>COLLEGES</b>
						<span class="badge badge-light">UNIVERSITY | COLLEGE (REMARK)</span>
					</h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">COLLEGE</th>';
			$grid .= '<th style="" class="pl-4">Private</th>';
			$grid .= '<th style="" class="pl-4">Public</th>';
			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			// $data['$GRADE_GROUP'] = $GRADE_GROUP;
			// $data['success'] = false;
			// echo json_encode($data);exit;

			$queryTests = "SELECT * FROM
			(
			--SELECT ISNULL((SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=RM.UNIVERSITYID)+' | '+ (SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=RM.CLID) + ' (' + REMARKS + ')','-') COLLEGE, 
			SELECT ISNULL((SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=RM.UNIVERSITYID),'')+' | '+ ISNULL((SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=RM.CLID),'') + ' (REMARK:-' + REMARKS + ')' COLLEGE,
			CASE IN_STATE WHEN 'Yes' THEN 'IN STATE' ELSE 'NOT IN STATE' END IN_STATE,
			COLTYPE
			FROM STUDENT_COLLEGE_ROADMAP_COLLEGES RM
			WHERE REGID=$REGID AND ROADMAPID=$ROADMAPID AND ISDELETED=0
			) COLLEGES
			PIVOT
			(
			MAX(IN_STATE)
			FOR COLTYPE IN ([Public],[Private])
			) AS PIVOTTABLE
			ORDER BY COLLEGE";
			$data['$queryTestsCollege'] = $queryTests;
			$resultTests = sqlsrv_query($mysqli, $queryTests);
			while ($rowTests = sqlsrv_fetch_array($resultTests,SQLSRV_FETCH_ASSOC)) {
				$COLLEGE = $rowTests['COLLEGE'];
				$PRIVATE = $rowTests['Private'];
				$PUBLIC = $rowTests['Public'];
				
				$grid .= '<tr class="">';
				$grid .= '<td class="pl-4">'.$COLLEGE.'</td>';
				$grid .= '<td class="pl-4">'.$PRIVATE.'</td>';
				$grid .= '<td class="pl-4">'.$PUBLIC.'</td>';

				// $INDEX = 0;
				// for($i=0; $i<count($GRADE_GROUP_ARR_T); $i++){
				// 	$INDEX++;
				// 	$NEW_GRADE_T = $GRADE_GROUP_ARR_T[$i];
				// 	$GET_REMARK_T = $rowTests[$NEW_GRADE_T];
				// 	$grid .= '<td class="pl-4">'.$GET_REMARK_T.'</td>';
				// }

				// $row['ROADMAPID'] = (int) $row['ROADMAPID'];
				// $ROADMAPID = (int)$row['ROADMAPID'];
				$data['data'][] = $row;

				$grid .= '</tr>';
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';
			// ############################
			// ######### COLLEGES #########
			// ############################
			







			// ##################################
			// ######### RECOMMENDATION #########
			// ##################################
			$grid .= '<div style="break-inside: avoid!important;"><h4 class="ml-2 mt-4""><b>RECOMMENDATIONS</b></h4>';
			$grid .= '<table class="table table-sm table-bordered">';
			$grid .= '<thead>';
			$grid .= '<tr class="bg-light">';
			$grid .= '<th style="" class="pl-4">AREA</th>';
			$grid .= '<th style="" class="pl-4">Recommend Date</th>';
			$grid .= '<th style="" class="pl-4">Recommendations</th>';
			$grid .= '</tr>';
			$grid .= '</thead>';
			$grid .= '<tbody>';
			// $data['$GRADE_GROUP'] = $GRADE_GROUP;
			// $data['success'] = false;
			// echo json_encode($data);exit;

			$queryTests = "EXEC [STUDENT_COLLEGE_RM_RECOMMENDATIONS] $ROADMAPID";
			$data['$queryTestsRecomm'] = $queryTests;

			$resultTests = sqlsrv_query($mysqli, $queryTests);
			while ($rowTests = sqlsrv_fetch_array($resultTests,SQLSRV_FETCH_ASSOC)) {
				$AREA = $rowTests['AREA'];
				$rowTests['RECOMMENDATION'] = rtrim($rowTests['RECOMMENDATION'],' | ');
				$rowTests['RECOMMENDATION'] = str_replace(' | ', ",\n", $rowTests['RECOMMENDATION']);
				$RECOMMENDATION = (!$rowTests['RECOMMENDATION'] || $rowTests['RECOMMENDATION'] == '') ? '-' : $rowTests['RECOMMENDATION'];
				// DATE
				$rowTests['RECOMMENDDATE'] = rtrim($rowTests['RECOMMENDDATE'],' | ');
				$rowTests['RECOMMENDDATE'] = str_replace(' | ', ",\n", $rowTests['RECOMMENDDATE']);
				$RECOMMENDDATE = (!$rowTests['RECOMMENDDATE'] || $rowTests['RECOMMENDDATE'] == '') ? '-' : $rowTests['RECOMMENDDATE'];
				
				
				$grid .= '<tr class="">';
				$grid .= '<td class="pl-4">'.$AREA.'</td>';
				$grid .= '<td class="pl-4"><p class="my-0 text-dark" style="white-space: pre-line;">'.$RECOMMENDDATE.'</p></td>';
				$grid .= '<td class="pl-4"><p class="my-0 text-dark" style="white-space: pre-line;">'.$RECOMMENDATION.'</p></td>';

				$data['data'][] = $row;

				$grid .= '</tr>';
			}


			$grid .= '</tbody>';
			$grid .= '</table></div>';
			// ##################################
			// ######### RECOMMENDATION #########
			// ##################################



			$data['ROADMAP'] = $grid;
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
/* ========== GET STUDENT COLLEGE ROADMAP =========== */


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







