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
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$txtOrderby = $_POST['txtOrderby'] == 'undefined' ? '' : $_POST['txtOrderby'];
		$txtASC_DESC = $_POST['txtASC_DESC'] == 'undefined' ? 'ASC' : $_POST['txtASC_DESC'];
		$txtSerarch = $_POST['txtSerarch'] == 'undefined' ? '' : $_POST['txtSerarch'];

		if($txtFromDT == '' || $txtToDT == ''){
			throw new Exception('Select date first.');	
		}
		$data = array();
		$grid = '';

		
		// =========== TEST NAME ==============
		// $queryForTest = "SELECT DISTINCT TESTID,(SELECT TESTDESC FROM TEST_MASTER WHERE TESTID=T.TESTID) TESTDESC
		// FROM STUDENT_TESTS T WHERE
		// CONVERT(DATE,TESTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		// AND ISDELETED=0 
		// AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=T.TSECID) NOT LIKE '%BREAK%'";
		$queryForTest = "SELECT DISTINCT TGID,(SELECT GROUPNAME FROM TEST_GROUPS WHERE TGID=GD.TGID ) TESTGROUP
		FROM TEST_GROUPS_DETAILS GD WHERE TESTID IN (
			SELECT  TESTID
			FROM STUDENT_TESTS T WHERE REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND
				CONVERT(DATE,TESTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
				AND ISDELETED=0 
				AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TSECID=T.TSECID) NOT LIKE '%BREAK%')";

		$data['$queryForTest'] = $queryForTest;			


		$countForTest = unique($queryForTest);
		if($countForTest > 0){
			$resultForTest = sqlsrv_query($mysqli, $queryForTest);

			$grid .= '<div class="table-responsive mt-3 table2excel">';
			
			while ($rowForTest = sqlsrv_fetch_array($resultForTest)) {
				$TNAME = $rowForTest['TESTGROUP'];
				$TGID = $rowForTest['TGID'];
					$grid .= '<table class="table table-sm table-bordered">';
					$grid .= '<thead>';
					$grid .= '<tr class="bg-light">';
					$grid .= '<th colspan="10"><h3 class=" font-weight-bold mb-0 font-18 text-center">'.$TNAME.'</h3></th>';
					$grid .= '</tr>';

					$SECID = '';
					// =========== TEST SECTION NAME ==============
					$queryForTSEC= "SELECT DISTINCT SECID,(SELECT TOP 1 TESTSECTION FROM TEST_SECTIONS WHERE SECID=TS.SECID AND TESTID=TS.TESTID) SECTIONS 
					FROM TEST_SECTIONS TS WHERE TESTID IN
					(SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE TGID=$TGID AND ISDELETED=0)
					AND ISDELETED=0 AND TESTSECTION NOT LIKE '%BREAK%'";

					$countForTSEC = unique($queryForTSEC);
					if($countForTSEC > 0){
						// $data['$queryForTSEC'][] = $queryForTSEC;
						// echo json_encode($data);exit();
						$resultForTSEC = sqlsrv_query($mysqli, $queryForTSEC);

						$grid .= '<tr class="bg-light">';
						$grid .= '<th style="" class="pl-4">Student</th>';
						$grid .= '<th style="" class="pl-4">Test</th>';
						$grid .= '<th style="" class="pl-4">Date</th>';
						$ALLGROUPNO=[];
						$SECID = '';
						$SECID_NUM = [];
						while ($rowForTSEC = sqlsrv_fetch_array($resultForTSEC)) {
							// $TSECID = $rowForTSEC['TSECID'];
							$rowForTSEC['SECID'] = $rowForTSEC['SECID'];
							$number = $rowForTSEC['SECID'];
							if(strpos($SECID, "[$number]") === false){
								$SECID .= '['.$rowForTSEC['SECID'].'],';
								$SECID_NUM[] = $rowForTSEC['SECID'];
							}
							// $SECTIONS = rtrim($rowForTSEC['SECTIONS'],' | ');
							// $grid .= '<th style="">'.$SECTIONS.'</th>';
							// $ALLGROUPNO[]=$rowForTSEC['GROUPNO'];
							$grid .= '<th style="" class="pl-4">'.$rowForTSEC['SECTIONS'].'</th>';
						}
						$SECID = rtrim($SECID,',');
						$data['$SECID']=$SECID;
						// $data['$SECTIONS']=$SECTIONS;
						$grid .= '</tr>';
						
					}
					$grid .= '</thead>';
					$grid .= '<tbody>';

					if($SECID!=''){
						// GET MAIN DATA
						$queryForTestid = "SELECT (SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=MT.REGID) STUDENT,
						CONVERT(VARCHAR,TESTDATE,105) TESTDATE_O,
						(SELECT TESTDESC+' ('+CONVERT(VARCHAR,TESTYEAR)+')' FROM TEST_MASTER WHERE TESTID=MT.TESTID) TESTNAME,
						(SELECT LEN(TESTDESC) FROM TEST_MASTER WHERE TESTID=MT.TESTID)TEST_LENTH,*
						
						FROM
						(
							SELECT * 
							FROM
							(
								SELECT REGID,TESTID,CONVERT(DATE,TESTDATE,105) TESTDATE,RESULTTYPE,SECID,SUM(SCORE)TOTALSCORE
								FROM
								(
									SELECT REGID,CONVERT(DATE,TESTDATE,105) TESTDATE,TESTID,(SELECT SECID FROM TEST_SECTIONS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID) SECID,
									CASE WHEN (SELECT COUNT(*) FROM STUDENT_TEST_RESULTS WHERE STID=ST.STID AND ISDELETED=0)>0 THEN 'APPEARED' ELSE 'MANUAL' END RESULTTYPE,
									ISNULL(SCORE,0) SCORE
									FROM STUDENT_TESTS ST
									WHERE TGID=$TGID AND ISDELETED=0  AND REGID>0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation) AND CONVERT(DATE,TESTDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
									AND (SELECT TESTSECTION FROM TEST_SECTIONS WHERE TESTID=ST.TESTID AND TSECID=ST.TSECID) NOT LIKE '%BREAK%'
								)TT
								GROUP BY REGID,CONVERT(DATE,TESTDATE,105),TESTID,SECID,RESULTTYPE
							
							)
							TTT
							PIVOT (
							SUM([TOTALSCORE])
							FOR [SECID]
							IN ($SECID))
								
							
							AS PivotTable
						) MT";
	
						if($txtSerarch != ''){
							$queryForTestid .= " WHERE (SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=MT.REGID) LIKE '%$txtSerarch%'";
						}
	
						if($txtOrderby === 'STUDENT'){
							$queryForTestid .= " ORDER BY STUDENT $txtASC_DESC,TESTID";
						}
						else if($txtOrderby === 'TEST'){
							$queryForTestid .= " ORDER BY TEST_LENTH,TESTNAME $txtASC_DESC,STUDENT";
						}
						else if($txtOrderby === 'DATE'){
							$queryForTestid .= " ORDER BY CONVERT(DATE,TESTDATE,105) $txtASC_DESC,STUDENT";
						}else{
							$queryForTestid .= " ORDER BY TESTID,CONVERT(DATE,TESTDATE,105),STUDENT";
						}
	
						$data['$queryForTestid'][] = $queryForTestid;
	
						$countForTestid= unique($queryForTestid);
						if($countForTestid>0){
	
							$resultForTestid= sqlsrv_query($mysqli, $queryForTestid);
							if($countForTestid> 0){
								while ($rowForTestid = sqlsrv_fetch_array($resultForTestid,SQLSRV_FETCH_ASSOC)) {
									$data['ALLDATA'][] = $rowForTestid;
									
									
									$grid .= '<tr class="">';
		
									$STUDENT = $rowForTestid['STUDENT'];
									$TESTNAME = $rowForTestid['TESTNAME'];
									$TESTDATE = $rowForTestid['TESTDATE_O'];
									$RESULTTYPE = $rowForTestid['RESULTTYPE'];
									$TESTID = $rowForTestid['TESTID'];
									// $STUDENT = $rowForTestid['STUDENT'];
									// $SCALE = $rowForTestid['SCALE'];
		
									// $data['$SECID_NUM'] = $SECID_NUM;
									// echo json_encode($data);exit;
									$badgeColor = $RESULTTYPE == 'MANUAL' ? 'badge-secondary' : 'badge-dark';
									$badgeFont= $RESULTTYPE == 'MANUAL' ? '' : 'font-14';
									$grid .= '<td class="pl-4">'.$STUDENT.'</td>';
									$grid .= '<td class="pl-4">'.$TESTNAME.' <span class="badge ml-1 '.$badgeColor.' '.$badgeFont.'">'.$RESULTTYPE.'</span></td>';
									$grid .= '<td class="pl-4">'.$TESTDATE.'</td>';
		
		
									$INDEX = 0;
									for($i=0; $i<count($SECID_NUM); $i++){
										$INDEX = 4+$i;
										$SEC = $SECID_NUM[$i];
										$SCORE = (!$rowForTestid[$SEC] || $rowForTestid[$SEC]=='') ? 0 : $rowForTestid[$SEC];
		
										$data['SCORE'][] = $SCORE;
		
										// GET SCALE
										$queryGetScale = "SELECT DBO.GET_SCALE ($SCORE,$TESTID,$SEC) AS SCALE";
										$data['$queryGetScale'][]=$queryGetScale;
										$resultGetScale= sqlsrv_query($mysqli, $queryGetScale);
		
										$rowGetScale = sqlsrv_fetch_array($resultGetScale,SQLSRV_FETCH_ASSOC);
										$SCALE = ($rowGetScale['SCALE'] == null) ? 0 : $rowGetScale['SCALE'];
										$data['SCALE'][] = $SCALE;
										
										$grid .= '<td class="pl-4">Raw : '.$SCORE.' | Scale : '.$SCALE.'</td>';
									}
		
									
									
									$grid .= '</tr>';
								}
							}
						}
					}
					
						
			}


					$grid .= '</tbody>';
					$grid .= '</table>';
					
			
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







