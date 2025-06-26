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
        case "getStudentTest":getStudentTest($conn);break;
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
 function getStudentTest($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];

		$REGIDS_ARRAY = (!isset($_POST['REGIDS']) || $_POST['REGIDS'] =='undefined' || $_POST['REGIDS']=='') ? '' : $_POST['REGIDS'];
		// if($REGIDS_ARRAY=='') throw new Exception('Select Student First.');
		// $REGIDS_ARRAY = implode(",",$REGIDS_ARRAY);
		$data['REGIDS'] = $REGIDS_ARRAY;
		// echo json_encode($data);exit;

		if($txtFromDT == '' || $txtToDT == ''){
			throw new Exception('Select date first.');	
		}
		$REPORT_DATA_ARR = array();
		$grid = '';

		
		// =========== TEST NAME ==============

		$queryForTest = "SELECT DISTINCT REGID,ISNULL((SELECT TGID FROM TEST_GROUPS_DETAILS GD WHERE ISDELETED=0 AND TESTID=T.TESTID),0) TGID,
		ISNULL((SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=T.REGID),'')STUDENT,
		ISNULL((SELECT (SELECT GROUPNAME FROM TEST_GROUPS WHERE TGID=GD.TGID)  FROM TEST_GROUPS_DETAILS GD WHERE ISDELETED=0 AND TESTID=T.TESTID),'') GROUPNAME,
		TESTID,TSECID
		FROM STUDENT_TESTS T WHERE
		CONVERT(DATE,TESTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND ISDELETED=0 AND REGID>0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation)
		AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=T.TSECID) NOT LIKE '%BREAK%'";
		if($REGIDS_ARRAY!='') $queryForTest .= " AND REGID IN ($REGIDS_ARRAY)";
		$queryForTest .= " ORDER BY TGID";
		$data['queryForTest']=$queryForTest;
		// echo json_encode($data);exit;
		$resultForTest = sqlsrv_query($mysqli, $queryForTest);
		$countForTest = unique($queryForTest);
		if($countForTest > 0){
			$grid .= '<div class="table-responsive mt-3 table2excel">';
			set_time_limit(0);
			while ($rowForTest = sqlsrv_fetch_array($resultForTest,SQLSRV_FETCH_ASSOC)) {
				$REPORT_DATA_ARR[] = $rowForTest;
			}

			// =========== TEST SECTION NAME ==============
			$TGID = 0;
			$REGID = 0;
			foreach($REPORT_DATA_ARR as $key => $ITEMS){
				$TGID = $ITEMS['TGID'];
				$REGID = $ITEMS['REGID'];
				$queryForTSEC= "SELECT SM.SECID,SM.SECTION ,COUNT(*) TOTAL,
				SUM(CAST(R.RESULT AS INT)) SCORED
				FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				WHERE R.STID=T.STID
				AND SM.SECTION NOT LIKE '%BREAK%'
				AND T.STID IN 
				(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID)  AND REGID=$REGID AND
				--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				ISDELETED=0)
				AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				AND C.SECID=SM.SECID
				-- CHECK ALL ISDELETED
				AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				AND C.ISDELETED=0 AND SM.ISDELETED=0 
				AND R.QUEID=Q.QUEID
				GROUP BY  SM.SECID,SM.SECTION
				ORDER BY SCORED ASC";

				$resultForTSEC = sqlsrv_query($mysqli, $queryForTSEC);
				$countForTSEC = unique($queryForTSEC);
				if($countForTSEC > 0){
					$TEST_SECTION_DATA = array();
					set_time_limit(0);
					while ($rowForTSEC = sqlsrv_fetch_array($resultForTSEC,SQLSRV_FETCH_ASSOC)) {
						$TEST_SECTION_DATA[] = $rowForTSEC;
						// $REPORT_DATA_ARR[$key]['TEST_SECTION_DATA'][] = $rowForTSEC;
						
					}

					// foreach($TEST_SECTION_DATA as $key => $ITEMS){
					// }
				}
			}

			// =========== CATEGORY ==============
			foreach($TEST_SECTION_DATA as $key => $ITEMS){
				$SECID = $ITEMS['SECID'];
				$queryForCat= "SELECT C.CATID,C.CATEGORY ,COUNT(*) TOTAL,
				SUM(CAST(R.RESULT AS INT)) SCORED
				FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				WHERE R.STID=T.STID
				AND T.STID IN 
				(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				ISDELETED=0)
				AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				AND C.SECID=SM.SECID
				-- CHECK ALL ISDELETED
				AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				AND C.ISDELETED=0 AND SM.ISDELETED=0 
				AND R.QUEID=Q.QUEID
				AND SM.SECID=$SECID
				GROUP BY 
				C.CATID,C.CATEGORY 
				ORDER BY SCORED ASC";
				$resultForCat = sqlsrv_query($mysqli, $queryForCat);
				$countForCat = unique($queryForCat);
				if($countForCat > 0){
					set_time_limit(0);
					$CATEGORY_DATA=array();
					while ($rowForCat = sqlsrv_fetch_array($resultForCat,SQLSRV_FETCH_ASSOC)) {
						// $TEST_SECTION_DATA[$key]['CATEGORY_DATA'][] = $rowForCat;
						$CATEGORY_DATA[]=$rowForCat;
					}
				}
				
			}

			// =========== SUB CATEGORY ==============
			foreach($CATEGORY_DATA as $key => $ITEMS){
				$CATID = $ITEMS['CATID'];

				$queryForSubCat = "SELECT SC.SUBCATID,SC.SUBCATEGORY ,COUNT(*) TOTAL,
				SUM(CAST(R.RESULT AS INT)) SCORED
				FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				WHERE R.STID=T.STID
				AND T.STID IN 
				(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				ISDELETED=0)
				AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				AND C.SECID=SM.SECID
				-- CHECK ALL ISDELETED
				AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				AND C.ISDELETED=0 AND SM.ISDELETED=0 
				AND R.QUEID=Q.QUEID
				AND C.CATID=$CATID
				GROUP BY 
				SC.SUBCATID,SC.SUBCATEGORY
				ORDER BY SCORED ASC";
				$resultForSubCat = sqlsrv_query($mysqli, $queryForSubCat);
				$countForSubCat = unique($queryForSubCat);
				if($countForSubCat > 0){
					$SUBCATEGORY_DATA=array();
					set_time_limit(0);
					while ($rowForSubCat = sqlsrv_fetch_array($resultForSubCat,SQLSRV_FETCH_ASSOC)) {
						$SUBCATEGORY_DATA[] = $rowForSubCat;
					}
				}
			}

			
			// =========== TOPIC ==============
			foreach($SUBCATEGORY_DATA as $key => $ITEMS){
				$SUBCATID = $ITEMS['SUBCATID'];
				$queryForTopic = "SELECT ST.TOPICID,ST.TOPIC ,COUNT(*) TOTAL,
				SUM(CAST(R.RESULT AS INT)) SCORED
				FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				WHERE R.STID=T.STID
				AND T.STID IN 
				(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				ISDELETED=0)
				AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				AND C.SECID=SM.SECID
				-- CHECK ALL ISDELETED
				AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				AND C.ISDELETED=0 AND SM.ISDELETED=0 
				AND R.QUEID=Q.QUEID
				AND SC.SUBCATID=$SUBCATID
				GROUP BY 
				ST.TOPICID,ST.TOPIC
				ORDER BY SCORED ASC";
				$resultForTopic = sqlsrv_query($mysqli, $queryForTopic);
				$countForTopic = unique($queryForTopic);
				if($countForTopic > 0){
					// $TOPIC_DATA = array();
					set_time_limit(0);
					while ($rowForTopic = sqlsrv_fetch_array($resultForTopic,SQLSRV_FETCH_ASSOC)) {
						$SUBCATEGORY_DATA[$key]['TOPIC_DATA'][] = $rowForTopic;
					}
				}
			}

			$data['$REPORT_DATA_ARR'] = $REPORT_DATA_ARR;
			$data['$TEST_SECTION_DATA'] = $TEST_SECTION_DATA;
			$data['$CATEGORY_DATA'] = $CATEGORY_DATA;
			$data['$SUBCATEGORY_DATA'] = $SUBCATEGORY_DATA;
			// $data['$TOPIC_DATA'] = $TOPIC_DATA;
			
			// $data['success'] = false;
			// echo json_encode($data);exit;




			// ==================== START DESIGN ========================


			foreach($REPORT_DATA_ARR as $rowForTest){
				// $TNAME = $rowForTest['TESTDESC'];
				$TNAME = $rowForTest['GROUPNAME'];
				// $TESTID = $rowForTest['TESTID'];
				$TGID = $rowForTest['TGID'];
				$REGID = $rowForTest['REGID'];
				$STUDENT = $rowForTest['STUDENT'];
					$grid .= '<table class="table table-sm table-borderless border">';
					$grid .= '<thead>';
					$grid .= '<tr class="bg-light">';
					$grid .= '<th colspan="10"><h3 class="mb-0 font-18 text-center"><span class="font-weight-bold">'.$STUDENT.'</span> <small>('.$TNAME.')</small></h3></th>';
					$grid .= '</tr>';
					
					if(count($TEST_SECTION_DATA)>0){
						for($sec=0;$sec<count($TEST_SECTION_DATA);$sec++){
							$SECTION = $TEST_SECTION_DATA[$sec]['SECTION'];
							$TSEC_TOTAL = $TEST_SECTION_DATA[$sec]['TOTAL'];
							$TSEC_SCORED = $TEST_SECTION_DATA[$sec]['SCORED'];
	
							// $grid .= '<tr class="bg-light"><th colspan="4"><u>SECTION :</u></th><tr>';
							$grid .= '</thead>';
	
							$grid .= '<tbody class="border-top">';
							$grid .= '<tr class="thead-light">';
							$grid .= '<td style="width:25%">
										<p class="my-0 font-weight-bold text-dark font-16">'.$SECTION.'</p>
										<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$TSEC_SCORED.' / '.$TSEC_TOTAL.'</span>
									  </td>';
							$grid .= '<td>
										<table class="table mb-0">';
	
							foreach($CATEGORY_DATA as $rowForCat){
								$CATEGORY = $rowForCat['CATEGORY'];
								$CAT_TOTAL = $rowForCat['TOTAL'];
								$CAT_SCORED = $rowForCat['SCORED'];
								
								$grid .= '<tr>';
								$grid .= '<td style="width:32%">
											<p class="my-0 font-weight-bold text-dark font-16">'.$CATEGORY.'</p>
											<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$CAT_SCORED.' / '.$CAT_TOTAL.'</span>
											</td>';
								$grid .= '<td>
											<table class="table mb-0">';
	
								for($sc=0;$sc<COUNT($SUBCATEGORY_DATA);$sc++){
									$SUB_CATEGORY = $SUBCATEGORY_DATA[$sc]['SUBCATEGORY'];
									$SUBCAT_TOTAL = $SUBCATEGORY_DATA[$sc]['TOTAL'];
									$SUBCAT_SCORED = $SUBCATEGORY_DATA[$sc]['SCORED'];
									$TOPIC_DATA = $SUBCATEGORY_DATA[$sc]['TOPIC_DATA'];
	
									$grid .= '<tr>';
									$grid .= '<td style="width:50%">
												<p class="my-0 font-weight-bold text-dark font-16">'.$SUB_CATEGORY.'</p>
												<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$SUBCAT_SCORED.' / '.$SUBCAT_TOTAL.'</span>
												</td>';
									$grid .= '<td><table class="table mb-0">';
	
									foreach($TOPIC_DATA as $rowForTopic){
										$TOPIC = $rowForTopic['TOPIC'];
										$TOPIC_TOTAL = $rowForTopic['TOTAL'];
										$TOPIC_SCORED = $rowForTopic['SCORED'];
	
										$grid .= '<tr>';
										$grid .= '<td>
													<p class="my-0 font-weight-bold text-dark font-16">'.$TOPIC.'</p>
													<span class="font-weight-bold badge badge-pill font-13 mt-1 px-4 alert-secondary border border-secondary">'.$TOPIC_SCORED.' / '.$TOPIC_TOTAL.'</span>
													</td>';
										$grid .= '</tr>';
									}
									$grid .='</table></td>';
									$grid .= '</tr>';
								}
								$grid .='</table></td>';
								$grid .= '</tr>';
								$grid .= '</tr>';
							}
							$grid .= '</table>
									  </td>
									</tr>';
							$grid .= '</tbody>';
						}
					}else{
						$grid .= '<tr>
								<td class="mb-0 alert-light text-danger text-center font-weight-bold border">Section Not Found.</td>
							</tr>';
					}
					$grid .= '</table>';
			}

				// =========== TEST SECTION NAME ==============

				// $queryForTSEC= "SELECT SM.SECID,SM.SECTION ,COUNT(*) TOTAL,
				// SUM(CAST(R.RESULT AS INT)) SCORED
				// FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				// SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				// WHERE R.STID=T.STID
				// AND SM.SECTION NOT LIKE '%BREAK%'
				// AND T.STID IN 
				// (SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID)  AND REGID=$REGID AND
				// --CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				// ISDELETED=0)
				// AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				// AND C.SECID=SM.SECID
				// -- CHECK ALL ISDELETED
				// AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				// AND C.ISDELETED=0 AND SM.ISDELETED=0 
				// AND R.QUEID=Q.QUEID
				// GROUP BY  SM.SECID,SM.SECTION
				// ORDER BY SCORED ASC";
				// $resultForTSEC = sqlsrv_query($mysqli, $queryForTSEC);
				// $countForTSEC = unique($queryForTSEC);
				// if($countForTSEC > 0){
				// 	set_time_limit(0);
				// 	while ($rowForTSEC = sqlsrv_fetch_array($resultForTSEC,SQLSRV_FETCH_ASSOC)) {
						
				// 		$SECID = $rowForTSEC['SECID'];
				// 		$SECTION = $rowForTSEC['SECTION'];
				// 		$TSEC_TOTAL = $rowForTSEC['TOTAL'];
				// 		$TSEC_SCORED = $rowForTSEC['SCORED'];

				// 		// $grid .= '<tr class="bg-light"><th colspan="4"><u>SECTION :</u></th><tr>';
				// 		$grid .= '</thead>';

				// 		$grid .= '<tbody class="border-top">';
				// 		$grid .= '<tr class="thead-light">';
				// 		$grid .= '<td style="width:25%">
				// 					<p class="my-0 font-weight-bold text-dark font-16">'.$SECTION.'</p>
				// 					<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$TSEC_SCORED.' / '.$TSEC_TOTAL.'</span>
				// 				  </td>';
				// 		$grid .= '<td>
				// 					<table class="table mb-0">';
				// 						// <tr><th colspan="3" class="bg-light"><u>CATEGORY : </u></th></tr>';
				// 						// =========== CATEGORY ==============

				// 						$queryForCat= "SELECT C.CATID,C.CATEGORY ,COUNT(*) TOTAL,
				// 						SUM(CAST(R.RESULT AS INT)) SCORED
				// 						FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				// 						SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				// 						WHERE R.STID=T.STID
				// 						AND T.STID IN 
				// 						(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				// 						--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				// 						ISDELETED=0)
				// 						AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				// 						AND C.SECID=SM.SECID
				// 						-- CHECK ALL ISDELETED
				// 						AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				// 						AND C.ISDELETED=0 AND SM.ISDELETED=0 
				// 						AND R.QUEID=Q.QUEID
				// 						AND SM.SECID=$SECID
				// 						GROUP BY 
				// 						C.CATID,C.CATEGORY 
				// 						ORDER BY SCORED ASC";
				// 						$resultForCat = sqlsrv_query($mysqli, $queryForCat);
				// 						$countForCat = unique($queryForCat);
				// 						if($countForCat > 0){
				// 							set_time_limit(0);
				// 							while ($rowForCat = sqlsrv_fetch_array($resultForCat,SQLSRV_FETCH_ASSOC)) {
				// 								$CATEGORY = $rowForCat['CATEGORY'];
				// 								$CATID = $rowForCat['CATID'];
				// 								$CAT_TOTAL = $rowForCat['TOTAL'];
				// 								$CAT_SCORED = $rowForCat['SCORED'];
												
				// 								$grid .= '<tr>';
				// 								$grid .= '<td style="width:32%">
				// 											<p class="my-0 font-weight-bold text-dark font-16">'.$CATEGORY.'</p>
				// 											<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$CAT_SCORED.' / '.$CAT_TOTAL.'</span>
				// 										  </td>';
				// 								$grid .= '<td>
				// 											<table class="table mb-0">';
				// 												// <tr><th th colspan="2" class="bg-light"><u>SUB CATEGORY : </u></th></tr>';
				// 												// =========== SUB CATEGORY ==============
				// 												$queryForSubCat = "SELECT SC.SUBCATID,SC.SUBCATEGORY ,COUNT(*) TOTAL,
				// 												SUM(CAST(R.RESULT AS INT)) SCORED
				// 												FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				// 												SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				// 												WHERE R.STID=T.STID
				// 												AND T.STID IN 
				// 												(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				// 												--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				// 												ISDELETED=0)
				// 												AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				// 												AND C.SECID=SM.SECID
				// 												-- CHECK ALL ISDELETED
				// 												AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				// 												AND C.ISDELETED=0 AND SM.ISDELETED=0 
				// 												AND R.QUEID=Q.QUEID
				// 												AND C.CATID=$CATID
				// 												GROUP BY 
				// 												SC.SUBCATID,SC.SUBCATEGORY
				// 												ORDER BY SCORED ASC";
				// 												$resultForSubCat = sqlsrv_query($mysqli, $queryForSubCat);
				// 												$countForSubCat = unique($queryForSubCat);
				// 												if($countForSubCat > 0){
				// 													set_time_limit(0);
				// 													while ($rowForSubCat = sqlsrv_fetch_array($resultForSubCat,SQLSRV_FETCH_ASSOC)) {
				// 														$SUB_CATEGORY = $rowForSubCat['SUBCATEGORY'];
				// 														$SUBCATID = $rowForSubCat['SUBCATID'];
				// 														$SUBCAT_TOTAL = $rowForSubCat['TOTAL'];
				// 														$SUBCAT_SCORED = $rowForSubCat['SCORED'];

				// 														$grid .= '<tr>';
				// 														$grid .= '<td style="width:50%">
				// 																	<p class="my-0 font-weight-bold text-dark font-16">'.$SUB_CATEGORY.'</p>
				// 																	<span class="font-weight-bold badge badge-pill alert-secondary border border-secondary font-13 mt-1 px-4">'.$SUBCAT_SCORED.' / '.$SUBCAT_TOTAL.'</span>
				// 																  </td>';
				// 														$grid .= '<td><table class="table mb-0">';
				// 																//   <tr><th class="bg-light"><u>TOPIC : </u></th></tr>';
																					
				// 														// =========== TOPIC ==============
				// 														$queryForTopic = "SELECT ST.TOPICID,ST.TOPIC ,COUNT(*) TOTAL,
				// 														SUM(CAST(R.RESULT AS INT)) SCORED
				// 														FROM STUDENT_TEST_RESULTS R, STUDENT_TESTS T , QUESTION_BANK Q, 
				// 														SECTION_TOPICS ST, SECTION_SUB_CATEGORIES SC, SECTION_CATEGORIES C, SECTION_MASTER SM
				// 														WHERE R.STID=T.STID
				// 														AND T.STID IN 
				// 														(SELECT STID FROM STUDENT_TESTS WHERE ATTEMPT=1 AND TESTID IN (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID) AND REGID=$REGID AND
				// 														--CONVERT(DATE,TESTDATE,105) BETWEEN CONVERT(DATE,@FROMDATE,105) AND CONVERT(DATE,@TODATE,105)
				// 														ISDELETED=0)
				// 														AND Q.TOPICID=ST.TOPICID AND ST.SUBCATID=SC.SUBCATID AND SC.CATID=C.CATID
				// 														AND C.SECID=SM.SECID
				// 														-- CHECK ALL ISDELETED
				// 														AND R.ISDELETED=0 AND T.ISDELETED=0 AND Q.ISDELETED=0 AND ST.ISDELETED=0 AND SC.ISDELETED=0
				// 														AND C.ISDELETED=0 AND SM.ISDELETED=0 
				// 														AND R.QUEID=Q.QUEID
				// 														AND SC.SUBCATID=$SUBCATID
				// 														GROUP BY 
				// 														ST.TOPICID,ST.TOPIC
				// 														ORDER BY SCORED ASC";
				// 														$resultForTopic = sqlsrv_query($mysqli, $queryForTopic);
				// 														$countForTopic = unique($queryForTopic);
				// 														if($countForTopic > 0){
				// 															set_time_limit(0);
				// 															while ($rowForTopic = sqlsrv_fetch_array($resultForTopic,SQLSRV_FETCH_ASSOC)) {
				// 																$TOPIC = $rowForTopic['TOPIC'];
				// 																$TOPICID = $rowForTopic['TOPICID'];
				// 																$TOPIC_TOTAL = $rowForTopic['TOTAL'];
				// 																$TOPIC_SCORED = $rowForTopic['SCORED'];

				// 																$grid .= '<tr>';
				// 																$grid .= '<td>
				// 																			<p class="my-0 font-weight-bold text-dark font-16">'.$TOPIC.'</p>
				// 																			<span class="font-weight-bold badge badge-pill font-13 mt-1 px-4 alert-secondary border border-secondary">'.$TOPIC_SCORED.' / '.$TOPIC_TOTAL.'</span>
				// 																		  </td>';
				// 																$grid .= '</tr>';
				// 															}
				// 														}
			
				// 														$grid .='</table></td>';
				// 														$grid .= '</tr>';
				// 													}
				// 												}
				// 										$grid .='</table></td>';
				// 								$grid .= '</tr>';
																
											
				// 								$grid .= '</tr>';
				// 							}
			
				// 						}
										
										
				// 		$grid .= '</table>
				// 				  </td>
				// 				</tr>';
						
	
	


				// 		$grid .= '</tbody>';
				// 	}

				// }else{
				// 	$grid .= '<tr>
				// 				<td class="mb-0 alert-light text-danger text-center font-weight-bold border">Section Not Found.</td>
				// 			</tr>';
				// }
				// $grid .= '</table>';
		}
		else{
			$grid .= '<div class="row">
						<div class="col-12">
							<h3 class="text-danger text-center font-weight-bold">Test Not Found.</h3>
						</div>
					 </div>';
		}

		$grid .= '</div>';
		$data['StudentTest'] = $grid;
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
/*============ Get Student Test Report =============*/ 


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}
