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
        case "saveData":saveData($conn);break;
        case "getStudentCourseCoverage":getStudentCourseCoverage($conn);break;
        case "getInvChapters":getInvChapters($conn);break;
        case "delete":delete($conn);break;
		
		// ============ GROUP ===========
        case "saveDataStudents":saveDataStudents($conn);break;
        case "getSelectedStudents":getSelectedStudents($conn);break;
        case "deleteStudents":deleteStudents($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveData($mysqli){
	try
	{
	global $userid;
	$data = array();

	$sccid = ($_POST['sccid'] == 'undefined' || $_POST['sccid'] == '') ? 0 : $_POST['sccid'];
	$txtCoverageDT = $_POST['txtCoverageDT'] == 'undefined' ? '' : $_POST['txtCoverageDT'];
	$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
	$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
	$ddlInventory = ($_POST['ddlInventory'] == 'undefined' || $_POST['ddlInventory'] == '') ? 0 : $_POST['ddlInventory'];
	$txtRemarkMain = $_POST['txtRemarkMain'] == 'undefined' ? '' : $_POST['txtRemarkMain'];
	// $ddlInvChapter = ($_POST['ddlInvChapter'] == 'undefined' || $_POST['ddlInvChapter'] == '') ? 0 : $_POST['ddlInvChapter'];
	// $txtPageFrom = ($_POST['txtPageFrom'] == 'undefined' || $_POST['txtPageFrom'] == '') ? 0 : $_POST['txtPageFrom'];
	// $txtPageTo = ($_POST['txtPageTo'] == 'undefined' || $_POST['txtPageTo'] == '') ? 0 : $_POST['txtPageTo'];

	$FinalChapters = (!empty($_POST['FinalChapters'])) ? $_POST['FinalChapters'] : '';
	if($FinalChapters == ''){throw new Exception("Please Select 'Inventory Chapter Name'.");}
	$FinalChapters = explode(',',$FinalChapters);
	
	$selectedStudentData = (!empty($_POST['selectedStudentData'])) ? $_POST['selectedStudentData'] : '';
	if($selectedStudentData != ''){
		$selectedStudentData = json_decode($selectedStudentData,true);
	}

	$data['selectedStudentData'] = $selectedStudentData;

	// echo json_encode($data);exit;

	
	$actionid = $sccid == 0 ? 1 : 2;

	if($txtCoverageDT == ''){throw new Exception("Please Enter 'Coverage Date'.");}
	if($ddlProduct == 0){throw new Exception("Please Select 'Product Name'.");}
	if($ddlInventory == 0){throw new Exception("Please Select 'Inventory Name'.");}

	


	// if($actionid == 1){
	// 	$sql = "SELECT * FROM STUDENT_COURSE_COVERAGE WHERE CONVERT(DATE,CDATE,105)='$txtCoverageDT' AND PLANID=$ddlPlan 
	// 		AND PRODUCTID=$ddlProduct AND INVID=$ddlInventory AND SCCID!=$sccid AND ISDELETED=0";
	// 	$row_count = unique($sql);
	// }
	// else{
	// 	$row_count = 0;
	// }

	// if($row_count == 0)
	// {
		if($actionid == 1){
			for($i=0; $i<count($FinalChapters); $i++){
				$chapid = $FinalChapters[$i];
				$query="EXEC [STUDENT_COURSE_COVERAGE_SP] $actionid,$sccid,'$txtCoverageDT',$ddlPlan,$ddlProduct,$ddlInventory,$chapid,
				'$txtRemarkMain',$userid";
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
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$data['GET_SCCID'] = (int)$row['SCCID'];
					$GET_SCCID = (int)$row['SCCID'];
	
					// SAVE STUDENTS
					if($selectedStudentData != ''){
						foreach($selectedStudentData as $student){
							$STID = (int)$student['studentid'];
							$REAMRK = $student['remark'];
						
							// INSERT
							$queryST="INSERT INTO STUDENT_COURSE_COVERAGE_ATTENDEDBY (SCCID,REGID,REMARK) VALUES($GET_SCCID,$STID,'$REAMRK')";
							sqlsrv_query($mysqli, $queryST);
	
						}
					}
	
	
		
				}
			}
			$data['success'] = true;
			$data['message'] = 'Coverage successfully inserted.';
		}
		else if($actionid == 2){
			$updQuery = "UPDATE STUDENT_COURSE_COVERAGE SET CDATE='$txtCoverageDT',PLANID=$ddlPlan,PRODUCTID=$ddlProduct,
			INVID=$ddlInventory,REMARK='$txtRemarkMain',UPDATEDATE=GETDATE(),UPDATEID=$userid
			WHERE SCCID=$sccid";
			$stmtUpd=sqlsrv_query($mysqli, $updQuery);
			if(!$stmtUpd){
				$data['success'] = false;
			}else{
				// DELETE STUDENTS
				if($actionid == 2){
					$del="DELETE FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=$sccid";
					sqlsrv_query($mysqli, $del);
				}
				// SAVE STUDENTS
				if($selectedStudentData != ''){
					foreach($selectedStudentData as $student){
						$STID = (int)$student['studentid'];
						$REAMRK = $student['remark'];
					
						// INSERT
						$queryST="INSERT INTO STUDENT_COURSE_COVERAGE_ATTENDEDBY (SCCID,REGID,REMARK) VALUES($sccid,$STID,'$REAMRK')";
						sqlsrv_query($mysqli, $queryST);

					}
				}


				$data['success'] = true;
				$data['message'] = 'Coverage successfully updated.';
			}
		}
		else{
			throw new Exception('Error.');
		}
		
		
		// if(!empty($sccid))$data['message'] = 'Coverage successfully updated.';
		// else $data['message'] = 'Coverage successfully inserted.';
		// $data['query'] = $query;
		echo json_encode($data);exit;
	// }
	// else
	// {
	// 	$data['success'] = false;
	// 	$data['message'] = 'Coverage already exists';
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
 // =============== SAVE DATA ==============






/* ========== GET STUDENT COURSE COVERAGE =========== */
function getStudentCourseCoverage($mysqli){
	try
	{
		$data = array();

		$query = "SELECT SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID)PLANNAME,PRODUCTID,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SCC.PRODUCTID)PRODUCT,INVID,
		(SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY,CHAPID,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,REMARK,
		ISNULL((SELECT (SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID AND ISDELETED=0) + ' | ' FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND SCCA.SCCID=SCC.SCCID FOR XML PATH('')),'')STUDENTS
		FROM STUDENT_COURSE_COVERAGE SCC WHERE ISDELETED=0 ORDER BY CONVERT(DATE,CDATE,105) DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['STUDENTS'] = rtrim($row['STUDENTS'],' | ');
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Data not found.';
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
/* ========== GET STUDENT COURSE COVERAGE =========== */





/*============ GET INVENTORY CHAPTERS =============*/ 
function getInvChapters($mysqli){
	try
	{
		$data = array();
		$INVID = ($_POST['INVID'] == 'undefined' || $_POST['INVID'] == '') ? 0 : $_POST['INVID'];
		if($INVID == 0) throw new Exception('INVID Not Found.');
		$query = "SELECT CHAPID AS id,PRODUCTID,DESCR,CHAPNO,CHAPTER AS label FROM INV_CHAPTERS WHERE ISDELETED=0 AND INVID=$INVID ORDER BY CHAPTER";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'data not found.';
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
/*============ GET INVENTORY CHAPTERS =============*/ 






/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCCID = ($_POST['SCCID'] == 'undefined' || $_POST['SCCID'] == '') ? 0 : $_POST['SCCID'];
			if($SCCID == 0){throw new Exception('SCCID Error.');}
			$delQuery = "EXEC [STUDENT_COURSE_COVERAGE_SP] 3,$SCCID,'',0,0,0,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Coverage successfully deleted.';
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





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENT SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE DATA ==================
function saveDataStudents($mysqli){
	try
	{
	global $userid;
	$data = array();

	$sccdid  = ($_POST['sccdid'] == 'undefined' || $_POST['sccdid'] == '') ? 0 : $_POST['sccdid'];
	$sccid  = ($_POST['sccid'] == 'undefined' || $_POST['sccid'] == '') ? 0 : $_POST['sccid'];
	$ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
	$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
	
	$actionid = $sccdid == 0 ? 1 : 2;

	if($sccid == 0){throw new Exception("SCCID Not Found.");}
	if($actionid == 1){
		if($ddlStudent == ''){throw new Exception("Please Select 'Student Name'.");}
	}


	$sql = "SELECT * FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=$sccid AND REGID=$ddlStudent AND SCCDID!=$sccdid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		if($actionid == 1){
			$query="INSERT INTO STUDENT_COURSE_COVERAGE_ATTENDEDBY (SCCID,REGID,REMARK) VALUES($sccid,$ddlStudent,'$txtRemark')";
		}
		else if($actionid == 2){
			$query="UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET REMARK='$txtRemark' WHERE SCCDID=$sccdid";
		}
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
			$data['success'] = true;
			if(!empty($sccdid))$data['message'] = 'Student successfully updated.';
			else $data['message'] = 'Student successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Student already exists';
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
 // =============== SAVE DATA ==============






/*============ GET ST_CC_STUDENTS =============*/ 
function getSelectedStudents($mysqli){
	try
	{
		$data = array();
		$sccid = ($_POST['sccid'] == 'undefined' || $_POST['sccid'] == '') ? 0 : $_POST['sccid'];
		if($sccid == 0) throw new Exception('SCCID Not Found.');

		$query = "SELECT REGID AS studentid,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)student,REMARK as remark 
				FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND SCCID=$sccid ORDER BY STUDENT";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
/*============ GET ST_CC_STUDENTS =============*/ 






/* =========== DELETE =========== */ 
function deleteStudents($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCCDID = ($_POST['SCCDID'] == 'undefined' || $_POST['SCCDID'] == '') ? 0 : $_POST['SCCDID'];
			if($SCCDID == 0){throw new Exception('SCCDID Error.');}
			$delQuery = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET ISDELETED=1 WHERE SCCDID=$SCCDID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Student successfully deleted.';
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






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







