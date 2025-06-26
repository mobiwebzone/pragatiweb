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
        case "getStudentCoursePending":getStudentCoursePending($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET Student Course Pending =========== */
 function getStudentCoursePending($mysqli){
	try
	{
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		
		if($txtFromDT == '' || $txtToDT == ''){
			throw new Exception('Select date first.');	
		}
		$data = array();
			
		$query = "SELECT PL.PLANID,PL.PLANNAME,PR.PRODUCTID,(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=PR.PRODUCTID) PRODUCT,
		I.INVID,I.TITLE,C.CHAPID,C.CHAPTER ,C.CHAPNO,
		CASE WHEN 
		ISNULL((SELECT COUNT(*) FROM STUDENT_COURSE_COVERAGE WHERE PLANID=PL.PLANID AND PRODUCTID=PR.PRODUCTID AND INVID=I.INVID AND CHAPID=C.CHAPID AND ISDELETED=0),0)>0 THEN 'COVERED' ELSE 'NOT COVERED' END CHAPTER_STATUS,
		CASE WHEN 
		ISNULL((SELECT COUNT(*) FROM STUDENT_COURSE_COVERAGE WHERE PLANID=PL.PLANID AND PRODUCTID=PR.PRODUCTID AND INVID=I.INVID AND CHAPID=C.CHAPID AND ISDELETED=0),0)>0 THEN 
		(SELECT FIRSTNAME + ' ' + LASTNAME  + ',' FROM REGISTRATIONS WHERE APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE ACTIVATE=1 AND PLANID=PL.PLANID AND CANCELLED=0) 
		AND REGID IN (SELECT DISTINCT REGID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND  SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE WHERE CONVERT(DATE,CDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT' ))AND REGID NOT IN (SELECT REGID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND SCCID IN
		(SELECT SCCID FROM STUDENT_COURSE_COVERAGE WHERE PLANID=PL.PLANID AND PRODUCTID=PR.PRODUCTID AND INVID=I.INVID AND CHAPID=C.CHAPID AND ISDELETED=0) ) ORDER BY FIRSTNAME FOR XML PATH(''))
		ELSE '-' END STUDENTS
		FROM PLANS PL,PLAN_PRODUCTS PR,INVENTORY I,INV_CHAPTERS C
		WHERE PL.PLANID=PR.PLANID AND I.PRODUCTID=PR.PRODUCTID AND C.INVID=I.INVID
		AND PL.ISDELETED=0 AND PR.ISDELETED=0 AND I.ISDELETED=0 AND C.ISDELETED=0
		AND PL.PLANID IN (SELECT DISTINCT PLANID FROM STUDENT_COURSE_COVERAGE WHERE ISDELETED=0)
		ORDER BY PLANNAME,PRODUCT,TITLE,CHAPNO";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['STUDENTS'] = rtrim($row['STUDENTS'],',');
				$row['STUDENTS'] = str_replace(',',",\n",$row['STUDENTS']);
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
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
/* ========== GET Student Course Pending =========== */



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







