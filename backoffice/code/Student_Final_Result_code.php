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
        case "getStudentFinalResult":getStudentFinalResult($conn);break;
        case "getCollegeByUniversity":getCollegeByUniversity($conn);break;
        case "getStudentByPlan":getStudentByPlan($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$sfrid = ($_POST['sfrid'] == 'undefined' || $_POST['sfrid'] == '') ? 0 : $_POST['sfrid'];
		$ddlStudentType = $_POST['ddlStudentType'] == 'undefined' ? '' : $_POST['ddlStudentType'];
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$txtFirstName = $_POST['txtFirstName'] == 'undefined' ? '' : $_POST['txtFirstName'];
		$txtLastName = $_POST['txtLastName'] == 'undefined' ? '' : $_POST['txtLastName'];
		$ddlProduct1 = ($_POST['ddlProduct1'] == 'undefined' || $_POST['ddlProduct1'] == '') ? 0 : $_POST['ddlProduct1'];
		$ddlProduct2 = ($_POST['ddlProduct2'] == 'undefined' || $_POST['ddlProduct2'] == '') ? 0 : $_POST['ddlProduct2'];
		$txtStudiedFromDT = $_POST['txtStudiedFromDT'] == 'undefined' ? '' : $_POST['txtStudiedFromDT'];
		$txtStudiedToDT = $_POST['txtStudiedToDT'] == 'undefined' ? '' : $_POST['txtStudiedToDT'];
		$ddlSelected = $_POST['ddlSelected'] == 'undefined' ? '' : $_POST['ddlSelected'];
		$txtSuperScore = ($_POST['txtSuperScore'] == 'undefined' || $_POST['txtSuperScore'] == '') ? 0 : $_POST['txtSuperScore'];
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];
		$ddlCollegeMajor = ($_POST['ddlCollegeMajor'] == 'undefined' || $_POST['ddlCollegeMajor'] == '') ? 0 : $_POST['ddlCollegeMajor'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
		$txtRemark = str_replace("'","''",$txtRemark);
		$ShowInHome = ($_POST['ShowInHome'] == 'undefined' || $_POST['ShowInHome'] == '') ? 0 : $_POST['ShowInHome'];

		$actionid = $sfrid == 0 ? 1 : 2;
		
		if($ddlStudentType == ''){throw new Exception("Please Select Student Type.");}
		if($txtFirstName == ''){throw new Exception("Please Enter First Name.");}
		if($txtLastName == ''){throw new Exception("Please Enter Last Name.");}
		if($ddlProduct1 == 0){throw new Exception("Please Select Product 1 Name.");}
		// if($ddlUniversity == 0){throw new Exception("Please Select University Name.");}
		// if($ddlCollege == 0){throw new Exception("Please Select College Name.");}

		// $sql = "SELECT * FROM STUDENT_FINAL_RESULTS WHERE UNIVERSITYID=$ddlUniversity AND COLLEGE='$txtCollegeName' AND CLID!=$clid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {

			$query="EXEC [STUDENT_FINAL_RESULTS_SP]$actionid,$sfrid,'$ddlStudentType',$ddlPlan,'$ddlStudent','$txtFirstName','$txtLastName',
				$ddlProduct1,$ddlProduct2,'$txtStudiedFromDT','$txtStudiedToDT','$ddlSelected',$txtSuperScore,$ddlUniversity,$ddlCollege,$ddlCollegeMajor,
				'$txtRemark',$ShowInHome,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($sfrid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists';
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
/* ========== SAVE DATA =========== */




/* ========== GET STUDENT FINAL RESULT =========== */
 function getStudentFinalResult($mysqli){
	try
	{
		$query = "SELECT SFRID,STUDENT_TYPE,PLANID,REGID,
		FIRSTNAME,LASTNAME,PRODUCTID1,PRODUCTID2,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SFR.PRODUCTID1)PRODUCT1,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SFR.PRODUCTID2)PRODUCT2,
		CONVERT(VARCHAR,STUDIED_FROM,106)STUDIED_FROM,CONVERT(VARCHAR,STUDIED_UPTO,106)STUDIED_UPTO,
		SELECTED,SUPERSCORE,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SFR.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SFR.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=SFR.MAJORID)MAJOR,REMARK,SHOW_IN_HOMEPAGE
		FROM STUDENT_FINAL_RESULTS SFR
		WHERE ISDELETED=0";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['SFRID'] = (int) $row['SFRID'];
				$row['STUDIED_FROM'] = ($row['STUDIED_FROM'] == '' || $row['STUDIED_FROM']=='01 Jan 1900') ? '' : $row['STUDIED_FROM'];
				$row['STUDIED_UPTO'] = ($row['STUDIED_UPTO'] == '' || $row['STUDIED_UPTO']=='01 Jan 1900') ? '' : $row['STUDIED_UPTO'];
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
/* ========== GET STUDENT FINAL RESULT =========== */




/* ========== GET COLLEGES =========== */
 function getCollegeByUniversity($mysqli){
	try
	{
		$UNIVERSITYID = ($_POST['UNIVERSITYID'] == 'undefined' || $_POST['UNIVERSITYID'] == '') ? 0 : $_POST['UNIVERSITYID'];
		if($UNIVERSITYID == 0)throw new Exception('UNIVERSITYID Not Found.');

		$query = "SELECT CLID,COLLEGE FROM COLLEGES_MASTER
		WHERE ISDELETED=0 AND UNIVERSITYID=$UNIVERSITYID ORDER BY COLLEGE";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLID'] = (int) $row['CLID'];
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
/* ========== GET COLLEGES =========== */




/* ========== GET STUDENT BY PLAN =========== */
 function getStudentByPlan($mysqli){
	try
	{	
		$PLANID = ($_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];
		if($PLANID == 0)throw new Exception('PLANID Not Found.');

		$query = "SELECT REGID,(SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)FIRSTNAME,
			(SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=RD.REGID)LASTNAME 
			FROM REGISTRATION_DETAILS RD WHERE PLANID=$PLANID AND CANCELLED=0";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
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
/* ========== GET STUDENT BY PLAN  =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SFRID = ($_POST['SFRID'] == 'undefined' || $_POST['SFRID'] == '') ? 0 : $_POST['SFRID'];  
			if($SFRID == 0){throw new Exception('SFRID NOT FOUND.');}
			$delQuery = "UPDATE STUDENT_FINAL_RESULTS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE SFRID=$SFRID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







