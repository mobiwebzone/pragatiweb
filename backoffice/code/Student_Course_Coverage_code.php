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
	$sccid_ALL  = (empty($_POST['sccid_ALL']) || $_POST['sccid_ALL'] == 'undefined' || $_POST['sccid_ALL'] == '') ? array() : $_POST['sccid_ALL'];
	$SCCID_ARRAY = !$sccid_ALL ? array() : explode(',',$_POST['sccid_ALL']);
	

	$txtCoverageDT = $_POST['txtCoverageDT'] == 'undefined' ? '' : $_POST['txtCoverageDT'];
	$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
	$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
	// $ddlInventory = ($_POST['ddlInventory'] == 'undefined' || $_POST['ddlInventory'] == '') ? 0 : $_POST['ddlInventory'];
	$txtRemarkMain = $_POST['txtRemarkMain'] == 'undefined' ? '' : $_POST['txtRemarkMain'];
	$txtHomeWork = $_POST['txtHomeWork'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtHomeWork']);
	// $ddlInvChapter = ($_POST['ddlInvChapter'] == 'undefined' || $_POST['ddlInvChapter'] == '') ? 0 : $_POST['ddlInvChapter'];
	// $txtPageFrom = ($_POST['txtPageFrom'] == 'undefined' || $_POST['txtPageFrom'] == '') ? 0 : $_POST['txtPageFrom'];
	// $txtPageTo = ($_POST['txtPageTo'] == 'undefined' || $_POST['txtPageTo'] == '') ? 0 : $_POST['txtPageTo'];

	$ddlInventory = (!empty($_POST['ddlInventory'])) ? $_POST['ddlInventory'] : '';
	if($ddlInventory == ''){throw new Exception("Please Select 'Inventory Name.");}
	$ddlInventory = explode(',',$ddlInventory);

	$FinalChapters = (!isset($_POST['FinalChapters']) || $_POST['FinalChapters']=='undefined' || $_POST['FinalChapters']=='') ? array() : json_decode($_POST['FinalChapters'],true);
	if(count($FinalChapters) <= 0){throw new Exception("Please Select 'Inventory Chapter Name.");}
	// $data['$FinalChapters']= $FinalChapters;
	// echo json_encode($data);exit;
	
	$selectedStudentData = (!empty($_POST['selectedStudentData'])) ? $_POST['selectedStudentData'] : '';
	if($selectedStudentData != ''){
		$selectedStudentData = json_decode($selectedStudentData,true);
	}

	$data['selectedStudentData'] = $selectedStudentData;

	
	// === IMAGE
	$existingDocsUpload  = $_POST['existingDocsUpload'] == 'undefined' ? '' : $_POST['existingDocsUpload'];
	$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
	if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0 && $_FILES['DocsUpload']['size'] > 1048576) throw new Exception('File size too large.');
	// === IMAGE

	// echo json_encode($data);exit;

	
	$actionid = $sccid == 0 ? 1 : 2;

	if($txtCoverageDT == ''){throw new Exception("Please Enter 'Coverage Date'.");}
	if($ddlProduct == 0){throw new Exception("Please Select 'Product Name'.");}
	// if($ddlInventory == 0){throw new Exception("Please Select 'Inventory Name'.");}

	$GET_ALL_SCCID =array();

	


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
			foreach($FinalChapters as $item){
				$chapid = $item['CHAPID'];
				$invid = $item['INVID'];
				$query="EXEC [STUDENT_COURSE_COVERAGE_SP] $actionid,$sccid,'$txtCoverageDT',$ddlPlan,$ddlProduct,$invid,$chapid,
				'$txtRemarkMain','$txtHomeWork',$userid,'COVERAGE',0";
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
					$GET_ALL_SCCID[]=$GET_SCCID;
	
					// SAVE STUDENTS
					// if($selectedStudentData != ''){
					// 	foreach($selectedStudentData as $student){
					// 		$STID = (int)$student['studentid'];
					// 		$REAMRK = $student['remark'];
						
					// 		// INSERT
					// 		$queryST="INSERT INTO STUDENT_COURSE_COVERAGE_ATTENDEDBY (SCCID,REGID,REMARK) VALUES($GET_SCCID,$STID,'$REAMRK')";
					// 		sqlsrv_query($mysqli, $queryST);
	
					// 	}
					// }

					//========== IMAGE
					$DocsUpload = '';
					if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0){
						$ext = pathinfo($_FILES['DocsUpload']['name'],PATHINFO_EXTENSION);
						$DocsUpload .= strtolower($GET_SCCID.'_hw_'.time().'.'.$ext);
					}
					else
					{
						$DocsUpload="";
					}
					
					if($DocsUpload != ''){
						if($GET_SCCID > 0)
						{
							$insertimage = "UPDATE STUDENT_COURSE_COVERAGE SET HOMEWORK_DOC='$DocsUpload' WHERE SCCID=$GET_SCCID";
							sqlsrv_query($mysqli,$insertimage);
						}
		
		
						if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0)
						{
							move_uploaded_file($_FILES["DocsUpload"]["tmp_name"], '../images/course_coverage_hw/'.$DocsUpload);
						}
		
						
						if(isset($_FILES['DocsUpload']['name']) && $existingDocsUpload != '')
						{
							if (file_exists('../images/course_coverage_hw/'.$existingDocsUpload))
							{
								unlink('../images/course_coverage_hw/'.$existingDocsUpload);
							}
						}
					}

					//========== IMAGE
	
	
		
				}
			}
			$data['GET_ALL_SCCID'] = $GET_ALL_SCCID;
			$data['success'] = true;
			$data['message'] = 'Coverage successfully inserted.';
		}
		else if($actionid == 2){
			if(count($SCCID_ARRAY) <= 0) throw new Exception('Error : SCCID Not Found.');

			for ($i=0; $i < count($SCCID_ARRAY); $i++) { 
				$SCCID_NEW = $SCCID_ARRAY[$i];

				$data['GET_SCCID'] = $SCCID_NEW;
				$GET_ALL_SCCID[]=$SCCID_NEW;

				$updQuery = "UPDATE STUDENT_COURSE_COVERAGE SET CDATE='$txtCoverageDT',PLANID=$ddlPlan,PRODUCTID=$ddlProduct,
				REMARK='$txtRemarkMain',HOMEWORK='$txtHomeWork',UPDATEDATE=GETDATE(),UPDATEID=$userid
				WHERE SCCID=$SCCID_NEW";
				$stmtUpd=sqlsrv_query($mysqli, $updQuery);
				if(!$stmtUpd){
					$data['success'] = false;
				}else{
	
	
					//========== IMAGE
					$DocsUpload = '';
					if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0){
						$ext = pathinfo($_FILES['DocsUpload']['name'],PATHINFO_EXTENSION);
						$DocsUpload .= strtolower($SCCID_NEW.'_hw_'.time().'.'.$ext);
					}
					else
					{
						$DocsUpload="";
					}
					
					if($DocsUpload != ''){
						if($SCCID_NEW > 0)
						{
							$insertimage = "UPDATE STUDENT_COURSE_COVERAGE SET HOMEWORK_DOC='$DocsUpload' WHERE SCCID=$SCCID_NEW";
							sqlsrv_query($mysqli,$insertimage);
						}
		
		
						if(isset($_FILES['DocsUpload']['name']) && $_FILES['DocsUpload']['size'] > 0)
						{
							move_uploaded_file($_FILES["DocsUpload"]["tmp_name"], '../images/course_coverage_hw/'.$DocsUpload);
						}
		
						
						if(isset($_FILES['DocsUpload']['name']) && $existingDocsUpload != '')
						{
							if (file_exists('../images/course_coverage_hw/'.$existingDocsUpload))
							{
								unlink('../images/course_coverage_hw/'.$existingDocsUpload);
							}
						}
					}
		
					if($chkRemoveImgOnUpdate>0 && $existingDocsUpload!='' && $DocsUpload==''){
						if($SCCID_NEW>0){
							$insertimage = "UPDATE STUDENT_COURSE_COVERAGE SET HOMEWORK_DOC='' WHERE SCCID=$SCCID_NEW";
								sqlsrv_query($mysqli,$insertimage);
						}
						if (file_exists('../images/course_coverage_hw/'.$existingDocsUpload))
						{
							unlink('../images/course_coverage_hw/'.$existingDocsUpload);
						}
					}
					//========== IMAGE
	
					$data['success'] = true;
					$data['message'] = 'Coverage successfully updated.';
				}
			}
			$data['GET_ALL_SCCID']=$GET_ALL_SCCID;
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
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$txtFromDT_ST = ($_POST['txtFromDT_ST'] =='undefined' || $_POST['txtFromDT_ST'] =='') ? '' : $_POST['txtFromDT_ST'];
		$txtToDT_ST = ($_POST['txtToDT_ST'] =='undefined' || $_POST['txtToDT_ST'] =='') ? '' : $_POST['txtToDT_ST'];
		
		$query = "SELECT SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID)PLANNAME,
		PRODUCTID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT 'GRADE-'+GRADE FROM LA_GRADE_MASTER WHERE GRADEID=SCC.PRODUCTID)
			WHEN ATT_SOURCE='HOURLY' THEN (SELECT 'HOURLY TUTORING BY-'+FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=SCC.PRODUCTID)
			ELSE (SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SCC.PRODUCTID) END PRODUCT,
		INVID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT SUBJECTNAME FROM LA_SUBJECT_MASTER WHERE SUBID=SCC.INVID)
			WHEN ATT_SOURCE='HOURLY' THEN (SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SCC.INVID)
			ELSE (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) END INVENTORY,
		CHAPID,CASE WHEN ATT_SOURCE='LA' THEN (SELECT TOPIC FROM LA_TOPICS_MASTER WHERE TOPICID=SCC.CHAPID)
			ELSE ISNULL((SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID),'') END CHAPTER,
		REMARK,HOMEWORK,HOMEWORK_DOC,
		ISNULL((SELECT (SELECT FIRSTNAME + ' ' + LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID AND ISDELETED=0) + ' | ' FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND SCCA.SCCID=SCC.SCCID FOR XML PATH('')),'')STUDENTS,
		ATT_SOURCE
		FROM STUDENT_COURSE_COVERAGE SCC WHERE ISDELETED=0
		AND SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation))
		";
		if($txtFromDT_ST!=='' && $txtToDT_ST !=='') $query .=" AND CONVERT(DATE,CDATE,105) BETWEEN '$txtFromDT_ST' AND '$txtToDT_ST'";
		$query .= " ORDER BY CONVERT(DATE,CDATE,105) DESC";

		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['REMARK'] = rtrim($row['REMARK'],'#_#');
				$row['REMARK'] = str_replace("#_#","<br>",$row['REMARK']);
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
		$INVID = (!isset($_POST['INVID']) || $_POST['INVID'] == 'undefined' || $_POST['INVID'] == '') ? '' : implode(',',$_POST['INVID']);
		$data['$INVID']=$INVID;
		// echo json_encode($data);exit;
		if($INVID == '') throw new Exception('Select Inventory First.');
		$query = "SELECT CHAPID AS id,PRODUCTID,DESCR,CHAPNO,CHAPTER AS label,
				INVID,(SELECT TITLE FROM INVENTORY WHERE INVID=IC.INVID)INVENTORY
				FROM INV_CHAPTERS IC WHERE ISDELETED=0 AND INVID IN ($INVID) 
				ORDER BY INVID,LEN(CHAPTER),CHAPTER";
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
			$delQuery = "EXEC [STUDENT_COURSE_COVERAGE_SP] 3,$SCCID,'',0,0,0,0,'','',$userid,'',0";
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
		$data['success'] = false;
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
	$sccid_ALL  = (empty($_POST['sccid_ALL']) || $_POST['sccid_ALL'] == 'undefined' || $_POST['sccid_ALL'] == '') ? array() : $_POST['sccid_ALL'];
	$SCCID_ARRAY = !$sccid_ALL ? array() : explode(',',$_POST['sccid_ALL']);
	$data['$SCCID_ARRAY']=$SCCID_ARRAY;
	if(count($SCCID_ARRAY) <= 0) throw new Exception('Error : SCCID Not Found.');
	$sccid  = ($_POST['sccid'] == 'undefined' || $_POST['sccid'] == '') ? 0 : $_POST['sccid'];
	$ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
	$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
	
	$actionid = $sccdid == 0 ? 1 : 2;

	if($sccid == 0){throw new Exception("SCCID Not Found.");}
	if($actionid == 1){
		if($ddlStudent == ''){throw new Exception("Please Select 'Student Name'.");}
	}

	// === IMAGE
	$existingDocsUpload  = $_POST['existingDocsUpload'] == 'undefined' ? '' : $_POST['existingDocsUpload'];
	$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];
	if(isset($_FILES['DocsUploadST']['name']) && $_FILES['DocsUploadST']['size'] > 0 && $_FILES['DocsUploadST']['size'] > 1048576) throw new Exception('File size too large.');
	// === IMAGE

	if(count($SCCID_ARRAY) > 0){
		$data['success'] = false;
		for ($i=0; $i < count($SCCID_ARRAY); $i++) { 
			$SCCID_NEW = $SCCID_ARRAY[$i];

			
			$sql = "SELECT * FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=$SCCID_NEW AND REGID=$ddlStudent AND SCCDID!=$sccdid AND ISDELETED=0";
			$row_count = unique($sql);
			if($row_count == 0)
			{
				// if($actionid == 1){
				// 	$query="INSERT INTO STUDENT_COURSE_COVERAGE_ATTENDEDBY (SCCID,REGID,REMARK) VALUES($sccid,$ddlStudent,'$txtRemark')";
				// }
				// else if($actionid == 2){
				// 	$query="UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET REMARK='$txtRemark' WHERE SCCDID=$sccdid";
				// }
				$query="EXEC [STUDENT_COURSE_COVERAGE_ATTENDEDBY_SP]$actionid,$sccdid,$SCCID_NEW,$ddlStudent,'$txtRemark'";
				$data['query'][] = $query;
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					// $data['success'] = false;
					$data['query'][] = $query;
				}
				else
				{
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$data['GET_SCCDID'] = (int)$row['SCCDID'];
					$GET_SCCDID = (int)$row['SCCDID'];
					//========== IMAGE
					$DocsUpload = '';
					if(isset($_FILES['DocsUploadST']['name']) && $_FILES['DocsUploadST']['size'] > 0){
						$ext = pathinfo($_FILES['DocsUploadST']['name'],PATHINFO_EXTENSION);
						$DocsUpload .= strtolower($GET_SCCDID.'_st_'.time().'.'.$ext);
					}
					else
					{
						$DocsUpload="";
					}
					$data['$DocsUpload'] = $DocsUpload;
					
					if($DocsUpload != ''){
						if($GET_SCCDID > 0)
						{
							$insertimage = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET DOC='$DocsUpload' WHERE SCCDID=$GET_SCCDID";
							sqlsrv_query($mysqli,$insertimage);
						}
		
		
						if(isset($_FILES['DocsUploadST']['name']) && $_FILES['DocsUploadST']['size'] > 0)
						{
							move_uploaded_file($_FILES["DocsUploadST"]["tmp_name"], '../images/course_coverage_hw/'.$DocsUpload);
						}
		
						
						if(isset($_FILES['DocsUploadST']['name']) && $existingDocsUpload != '')
						{
							if (file_exists('../images/course_coverage_hw/'.$existingDocsUpload))
							{
								unlink('../images/course_coverage_hw/'.$existingDocsUpload);
							}
						}
					}
		
					if($chkRemoveImgOnUpdate>0 && $existingDocsUpload!='' && $DocsUpload==''){
						if($sccid>0){
							$insertimage = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET DOC='' WHERE SCCDID=$GET_SCCDID";
								sqlsrv_query($mysqli,$insertimage);
						}
						if (file_exists('../images/course_coverage_hw/'.$existingDocsUpload))
						{
							unlink('../images/course_coverage_hw/'.$existingDocsUpload);
						}
					}
					//========== IMAGE
		
		
					$data['success'] = true;
					if(!empty($sccdid))$data['message'] = 'Student successfully updated.';
					else $data['message'] = 'Student successfully inserted.';
				}
				
			}
		}
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

		// $query = "SELECT REGID AS studentid,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)student,REMARK as remark,
		// 		HOMEWORK_DONE homework_done,STUDENTWORK studentwork,HOMEWORK_IMG homework_img 
		// 		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND SCCID=$sccid ORDER BY STUDENT";
		$query = "SELECT SCCDID,REGID,(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)STUDENT,REMARK,DOC,
				HOMEWORK_DONE,STUDENTWORK,HOMEWORK_IMG 
				FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND SCCID=$sccid ORDER BY STUDENT";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Students Data not found.';
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
		$data['success'] = false;
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







