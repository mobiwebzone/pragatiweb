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
		case "save":save($conn);break;
		case "InsertNextYear":InsertNextYear($conn);break;
		case "changeDraftFinal":changeDraftFinal($conn);break;
        case "getGradeSubject":getGradeSubject($conn);break;
		case "getClassSubject":getClassSubject($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
	try
	{
	$data = array();
	global $userid;
	
	$data = array();
	$gsid = ($_POST['gsid'] == 'undefined' || $_POST['gsid'] == '') ? 0 : $_POST['gsid'];
	$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
	$txtGrade = $_POST['txtGrade'] == 'undefined' ? '' : (int)preg_replace("/[^0-9]/", "", $_POST['txtGrade']);
	$txtClassOf = ($_POST['txtClassOf'] == 'undefined' || $_POST['txtClassOf'] == '') ? 0 : $_POST['txtClassOf'];
	$ddlClassSubject = ($_POST['ddlClassSubject'] == 'undefined' || $_POST['ddlClassSubject'] == '') ? 0 : $_POST['ddlClassSubject'];
	$chkDraf_Final = ($_POST['chkDraf_Final'] == 'undefined' || $_POST['chkDraf_Final'] == '') ? '' : $_POST['chkDraf_Final'];
	if($chkDraf_Final == '') throw new Exception('Select Draft / Final.');
	$DRAFT = $chkDraf_Final == 'DRAFT' ? 1 : 0; 
	$FINAL = $chkDraf_Final == 'DRAFT' ? 0 : 1; 
	$YEAR = $_POST['year'] == 'undefined' ? date("Y") : $_POST['year'];

	$actionid = $gsid == 0 ? 1 : 2;

	if($ddlStudent == 0){throw new Exception("Please Select Student Name.");}
	if($txtGrade == '' || strlen($txtGrade)>2){throw new Exception("Please Check & Update Grade of Student.");}
	if($txtClassOf == 0 || strlen($txtClassOf)>4 || strlen($txtClassOf)<4){throw new Exception("Please Check & Update Class of Student.");}
	if($ddlClassSubject == 0){throw new Exception("Please Select Class/Subject.");}

	// $sql = "SELECT * FROM STUDENT_GRADE_SUBJECTS WHERE REGID=$ddlStudent AND GRADE='$txtGrade' AND UNDER_GSID=$gsid AND GRYEAR=$YEAR 
	// AND CSUBID=$ddlClassSubject AND DRAFT=$DRAFT AND ISDELETED=0";
	// $row_count = unique($sql);

	// if($row_count == 0)
	// {
		// $query="EXEC [STUDENT_GRADE_SUBJECTS_SP] $actionid,$gsid,0,$ddlStudent,'$txtGrade',$YEAR,$ddlClassSubject,$DRAFT,$FINAL,$userid";
	$query="EXEC [ADD_FURTHER_CLASSES] $ddlStudent,$YEAR,$txtGrade,$ddlClassSubject,$DRAFT,$FINAL,$userid";
	$stmt=sqlsrv_query($mysqli, $query);
	
	if($stmt === false)
	{
		// die( print_r( sqlsrv_errors(), true));
		// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
		$data['success'] = true;
		$data['query'] = $query;
	}
	else
	{
		$data['query'] = $query;
		$data['success'] = true;
		$data['message'] = 'Record successfully inserted.';
		echo json_encode($data);exit;
	}
		
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Data already exists.';
	// 	echo json_encode($data);exit;
	// }

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




 /* ========== Insert Next Year =========== */
  function InsertNextYear($mysqli){
	try
	{
	$data = array();
	global $userid;

	$MAIN_DATA = (isset($_POST['MAIN_DATA']) && !empty($_POST['MAIN_DATA'])) ? json_decode($_POST['MAIN_DATA'],true) : '';
	$GRADE = ($_POST['GRADE'] == 'undefined' || $_POST['GRADE'] == '') ? 0 : $_POST['GRADE'];
	$YEAR = ($_POST['YEAR'] == 'undefined' || $_POST['YEAR'] == '') ? 0 : $_POST['YEAR'];
	
	$data['$MAIN_DATA'] = $MAIN_DATA;
	$CHECK_LEFT_YEARS = 12-$GRADE;
	$data['$CHECK_LEFT_YEARS'] = $CHECK_LEFT_YEARS;
	$NEXT = $CHECK_LEFT_YEARS>0?true:false;
	if(!$NEXT) throw new Exception('0 Year Left.');
	$NEXT_GRADE = $GRADE+1;
	$NEXT_YEAR = $YEAR+1;
	// echo json_encode($data);exit;

	if($MAIN_DATA == ''){throw new Exception("Invalid Data.");}
	if($GRADE == 0){throw new Exception("Invalid Grade.");}
	if($YEAR == 0){throw new Exception("Invalid Year.");}

	foreach($MAIN_DATA as $d){
		$CSUBID = $d['CSUBID'];
		$GSID = $d['GSID'];
		$REGID = $d['REGID'];
		// Get Next Class Data
		$qryNxtClss = "SELECT CSUBID,NEXTCLASS1,NEXTCLASS2,NEXTCLASS3 FROM CLASS_SUBJECT_MASTER WHERE CSUBID=$CSUBID";
		$countNxtClss = unique($qryNxtClss);
		if($countNxtClss > 0){
			$stmtNxtClss = sqlsrv_query($mysqli, $qryNxtClss);
			while($rowNxtClss = sqlsrv_fetch_array($stmtNxtClss,SQLSRV_FETCH_ASSOC)){
				$NEW_CSUBID = (int)$rowNxtClss['CSUBID'];
				$NEXTCLASS1 = (int)$rowNxtClss['NEXTCLASS1'];
				$NEXTCLASS2 = (int)$rowNxtClss['NEXTCLASS2'];
				$NEXTCLASS3 = (int)$rowNxtClss['NEXTCLASS3'];

				if($NEXTCLASS1 > 0){
					$query1="EXEC [STUDENT_GRADE_SUBJECTS_SP] 1,0,$GSID,$REGID,'$NEXT_GRADE',$NEXT_YEAR,$NEXTCLASS1,1,0,$userid";
					// throw new Exception($query);
					sqlsrv_query($mysqli, $query1);
					$data['query1'] = $query1;
				}
				if($NEXTCLASS2 > 0){
					$query1="EXEC [STUDENT_GRADE_SUBJECTS_SP] 1,0,$GSID,$REGID,'$NEXT_GRADE',$NEXT_YEAR,$NEXTCLASS2,1,0,$userid";
					// throw new Exception($query);
					sqlsrv_query($mysqli, $query1);
					$data['query1'] = $query1;
				}
				if($NEXTCLASS3 > 0){
					$query1="EXEC [STUDENT_GRADE_SUBJECTS_SP] 1,0,$GSID,$REGID,'$NEXT_GRADE',$NEXT_YEAR,$NEXTCLASS3,1,0,$userid";
					// throw new Exception($query);
					sqlsrv_query($mysqli, $query1);
					$data['query1'] = $query1;
				}
			}
			$data['success'] = true;
			$data['message'] = 'Record successfully inserted.';
		}
		else{
			$data['success'] = false;
			$data['message'] = 'Next class not found.';
		}
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
 /* ========== Insert Next Year =========== *




 /* ========== UPDATE DRAFT/FINAL =========== */
  function changeDraftFinal($mysqli){
	try
	{
	$data = array();
	global $userid;

	$GSID = ($_POST['GSID'] == 'undefined' || $_POST['GSID'] == '') ? 0 : $_POST['GSID'];
	$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
	$DRAFT = ($_POST['DRAFT'] != 'undefined' || $_POST['DRAFT'] != '') ? ($_POST['DRAFT'] == 1 ? 0 : 1) : -1 ;
	$FINAL = ($_POST['FINAL'] != 'undefined' || $_POST['FINAL'] != '') ? ($_POST['FINAL'] == 1 ? 0 : 1) : -1 ;

	if($GSID == 0)throw new Exception('GSID Error.');
	if($REGID == 0)throw new Exception('REGID Error.');
	if($DRAFT == -1)throw new Exception('DRAFT Error.');
	if($FINAL == -1)throw new Exception('FINAL Error.');
	


	$updDraftFinal = "UPDATE STUDENT_GRADE_SUBJECTS SET DRAFT=$DRAFT,FINAL=$FINAL WHERE GSID=$GSID AND REGID=$REGID";
	sqlsrv_query($mysqli, $updDraftFinal);
	$data['success'] = true;
	$data['message'] = 'Record successfully updated.';
		
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
 /* ========== UPDATE DRAFT/FINAL =========== *




/*============ GET GRADE SUBJECT =============*/ 
function getGradeSubject($mysqli){
	try
	{
		global $userid;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefind' || $_POST['REGID'] == '') ? 0 :$_POST['REGID'];

		// RE-CALCULATE
		$reCalculate = "EXEC [DRAW_GRADE_SUBJECT_CLASSOF] $REGID,$userid";
		$resultRC = sqlsrv_query($mysqli, $reCalculate);
		if($resultRC){

			$query = "SELECT GSID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SGS.REGID)STUDENTNAME,
					(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=SGS.REGID)LOCID,
					GRADE,LEN(GRADE)ss,GRYEAR,CSUBID,
					(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SGS.CSUBID)SHORT_DESC,DRAFT,FINAL 
					FROM STUDENT_GRADE_SUBJECTS SGS WHERE ISDELETED=0 AND REGID=$REGID ORDER BY LEN(GRADE),GRADE,SHORT_DESC";
			$data['$query']=$query;
			$count = unique($query);		
			if($count > 0){
				$result = sqlsrv_query($mysqli, $query);
				$GRADE_1 = 0;
				$GRADE_2 = 0;
				$FINAL = array();
				$index = -1;
				while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
					$row['GSID'] = (int) $row['GSID'];
					$GRADE_1 = (int) $row['GRADE'];
					
					if($GRADE_1 != $GRADE_2){
						$index++;
						$FINAL[$index]['MAIN_DATA'][] = $row;
						$FINAL[$index]['GRADE'] = $GRADE_1;
						$FINAL[$index]['YEAR'] = (int)$row['GRYEAR'];
						$GRADE_2 = (int) $row['GRADE'];
					}else{
						$FINAL[$index]['MAIN_DATA'][] = $row;
						$FINAL[$index]['GRADE'] = $GRADE_1;
						$FINAL[$index]['YEAR'] = (int)$row['GRYEAR'];
						// $GRADE_2 = (int) $row['GRADE'];
					}
	
					$data['data'][] = $row;
					
					$data['FINAL']=$FINAL;
				}
				$data['success'] = true;
			}else{
				$data['success'] = false;
			}
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
/*============ GET GRADE SUBJECT =============*/ 




/* ========== GET CLASS/SUBJECT =========== */
 function getClassSubject($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CSUBID,SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE ISDELETED=0 ORDER BY SHORT_DESC";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CSUBID'] = (int) $row['CSUBID'];
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
/* ========== GET CLASS/SUBJECT =========== */






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $GSID = ($_POST['GSID'] == 'undefined' || $_POST['GSID'] == '') ? 0 : $_POST['GSID'];  

			$query ="EXEC [STUDENT_GRADE_SUBJECTS_SP] 3,$GSID,0,0,'',0,0,0,0,$userid";
			// $data['query']=$query;
			$stmt=sqlsrv_query($mysqli, $query);
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







