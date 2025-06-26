<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// if(!empty($_SESSION['MEP_USERID']))
// {$userid=$_SESSION['MEP_USERID'];}
// else
// {$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "saveTestForm":saveTestForm($conn);break;
        case "getCarouselMaster":getCarouselMaster($conn);break;
        case "getBottomScrollData":getBottomScrollData($conn);break;
		case "getHomeMenu":getHomeMenu($conn);break;
		case "getHomeFooterMenu":getHomeFooterMenu($conn);break;
		case "getOnlineTeachers":getOnlineTeachers($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* =========== SAVE TEST FORM =========== */
function saveTestForm($mysqli){
	try
	{
		$data = array();
		
		$txtFName = $_POST['txtFName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtFName']);
		$txtLName = $_POST['txtLName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLName']);
		$txtGrade = $_POST['txtGrade'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtGrade']);
		$txtPhone = $_POST['txtPhone'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtPhone']);
		$txtEmail = $_POST['txtEmail'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEmail']);

		if($txtFName == ''){throw new Exception("Enter First Name.");}
		if($txtLName == ''){throw new Exception("Enter Last Name.");}
		if($txtGrade == ''){throw new Exception("Enter Grade.");}
		if($txtPhone == ''){throw new Exception("Enter Phone Number.");}
		if($txtEmail == ''){throw new Exception("Enter Email ID.");}
			
	
		// CHECK USER EMAIL EXIST
		$queryEmailExist="SELECT REGID FROM FREE_TEST_USERS WHERE ISDELETED=0 AND EMAIL='$txtEmail'";
		$cntEE = unique($queryEmailExist);
	
		if($cntEE>0){
			$resultEmailExist=sqlsrv_query($mysqli, $queryEmailExist);
			$rowEmailExist=sqlsrv_fetch_array($resultEmailExist,SQLSRV_FETCH_ASSOC);
			$REGID = (int)$rowEmailExist['REGID'];
			$data['REGID'] = $REGID;
			$data['success'] = true;
			$data['message'] = 'User Already Exist.';
		}else{
			$query="EXEC [FREE_TEST_USERS_SP] 1, '$txtFName','$txtLName','$txtGrade','$txtPhone','$txtEmail'";
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
				// GET REGID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$REGID = (int)$row['REGID'];
				$data['REGID'] = $REGID;
				
				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'User information has been successfully submitted to myexamprep.';
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


/*============ GET CAROUSEL MASTER =============*/ 
function getCarouselMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CID,DISPLAY_TYPE,PIC,PIC_CAPTION,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
				PIC_INTERVAL,SEQNO FROM CAROUSEL_MASTER WHERE ISDELETED=0 AND 
				CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,PIC_FROMDT,105) AND CONVERT(DATE,PIC_TODT,105)
				ORDER BY SEQNO ASC";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['CID'] = (int) $row['CID'];
				$row['PIC'] = 'backoffice/images/carousel/'.$row['PIC'];
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
/*============ GET CAROUSEL MASTER =============*/ 


/*============ GET BOTTOM SCROLLBAR DATA =============*/ 
function getBottomScrollData($mysqli){
	try
	{
		$data = array();
		$query = "SELECT PSID,OBJECTTYPE,OBJECTNAME,TITLE,TITLE_DESC,LINK 
		FROM HOME_PAGE_SCROLL_MASTER WHERE ISDELETED=0 AND INACTIVE=0 ORDER BY SEQNO ASC";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PSID'] = (int) $row['PSID'];
				$row['OBJECTNAME'] = 'backoffice/images/home_page_scroll/'.$row['OBJECTNAME'];
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
/*============ GET BOTTOM SCROLLBAR DATA =============*/ 


/*============ GET HOME MENU =============*/ 
function getHomeMenu($mysqli){
	try
	{
		$data = array();
		$query = "SELECT MENUID,MENU,SEQNO,COLOR,
		ISNULL((SELECT (SELECT CAST(PDMID AS VARCHAR)+'#BREAK#'+DISPLAY_PRODUCT+'#BREAK#'+COLORCODE FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=MI.PDMID)+'#_#' 
			FROM MENU_ITEMS MI WHERE ISDELETED=0 AND MENUID=M.MENUID ORDER BY SEQNO FOR XML PATH('')),'')PRODUCTS
		FROM MENU M
		WHERE ISDELETED=0 ORDER BY SEQNO ASC";
		$data['$query']=$query;
		// echo json_encode($data);exit;

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['PRODUCTS']!=''){
					$PRODUCTS = rtrim($row['PRODUCTS'],'#_#');
					$PRODUCT_ARR = explode('#_#',$PRODUCTS);
					foreach($PRODUCT_ARR as $key=>$item){
						$ARR = explode('#BREAK#',$item);
						$row['PRODUCTS_LIST'][$key]['PDMID'] = (int) $ARR[0];
						$row['PRODUCTS_LIST'][$key]['DISPLAY_PRODUCT'] = $ARR[1];
						$row['PRODUCTS_LIST'][$key]['COLORCODE'] = $ARR[2];
						$row['PRODUCTS_LIST'][$key]['PTYPE'] = 'P';
					}
				}
				$data['data'][] = $row;
			}
			// $data['$PRODUCT_ARR']=$PRODUCT_ARR;
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
/*============ GET HOME MENU =============*/ 


/*============ GET HOME FOOTER MENU =============*/ 
function getHomeFooterMenu($mysqli){
	try
	{
		$data = array();
		$query = "SELECT FOOTID,HEADING,COLOR,
		(SELECT CASE WHEN PRODUCT_RESOURCE='PRODUCT'
					THEN ISNULL((SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0 AND PDMID=MFD.PRODUCT_RESOURCE_ID),'')
					WHEN PRODUCT_RESOURCE='RESOURCE' 
					THEN ISNULL((SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=MFD.PRODUCT_RESOURCE_ID),'')
					ELSE '-'
				END+'#BREAK#'+ISNULL(CAST(PRODUCT_RESOURCE_ID AS VARCHAR),'-')+'#BREAK#'+ISNULL(PRODUCT_RESOURCE,'-')+'#BREAK#'+ISNULL(CAST(ISHEADER AS VARCHAR),'-')+'#BREAK#'+CASE WHEN HEADER IS NULL OR HEADER='' OR HEADER='null' THEN '-' ELSE HEADER END +'#_#' 
			FROM MENU_FOOTER_DETAILS MFD WHERE ISDELETED=0 AND FOOTID=MF.FOOTID ORDER BY SEQNO FOR XML PATH(''))MENU
		FROM MENU_FOOTER MF WHERE ISDELETED=0
		ORDER BY SEQNO";
		$data['$query']=$query;
		// echo json_encode($data);exit;

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				if($row['MENU']!=''){
					$MENU = rtrim($row['MENU'],'#_#');
					$MENU_ARR = explode('#_#',$MENU);
					foreach($MENU_ARR as $key=>$item){
						$ARR = explode('#BREAK#',$item);
						$row['MENU_LIST'][$key]['MENU'] = $ARR[0];
						$row['MENU_LIST'][$key]['DISPLAY_PRODUCT'] = $ARR[0];

						$row['MENU_LIST'][$key]['ID'] = (int) $ARR[1];
						$row['MENU_LIST'][$key]['PDMID'] = (int) $ARR[1];
						
						$row['MENU_LIST'][$key]['PRODUCT_RESOURCE'] = $ARR[2];
						$row['MENU_LIST'][$key]['ISHEADER'] = $ARR[3];
						$row['MENU_LIST'][$key]['HEADER'] = $ARR[4];
						$row['MENU_LIST'][$key]['PTYPE'] = 'P';
					}
				}
				$data['data'][] = $row;
			}
			// $data['$PRODUCT_ARR']=$PRODUCT_ARR;
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
/*============ GET HOME FOOTER MENU =============*/ 


/*============ GET ONLINE TEACHERS =============*/ 
function getOnlineTeachers($mysqli){
	try
	{
		$data = array();
		// TOTAL ONLINE TEACHERS
		// $query = "SELECT COUNT(*) ONLINE_TEACHERS
		// FROM 
		// 	(SELECT COUNT(TEACHERID) ONLINE_TEACHERS
		// 	FROM TEACHER_ONLINE_STATUS TOS WHERE ISDELETED=0 AND
		// 	(CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108)) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105)
		// 	GROUP BY TEACHERID)
		
		// TT";
		$query = "EXEC [GET_ONLINE_TUTORS_DETAILS]";
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);

		if ($result === false) {
			die(print_r(sqlsrv_errors(), true));
		}
		
		// Check if the first result set has rows
		if (sqlsrv_has_rows($result)) {
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
			$data['TOTAL_ONLINE_TEACHERS'] = $row['ONLINE_TEACHERS'];
			$data['successTOTAL'] = true;
		}else{
			$data['successTOTAL'] = false;
		}
		
		// Move to the next result set, if any
		while (sqlsrv_next_result($result)) {
			if (sqlsrv_has_rows($result)) {
				while ($row2 = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
					$data['data'][] = $row2;
				}
				$data['success'] = true;
			}else{
				$data['success'] = false;
			}
		}
		
		// Check for errors after processing all result sets
		if (($errors = sqlsrv_errors()) != null) {
			foreach ($errors as $error) {
				echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
				echo "Code: " . $error['code'] . "<br />";
				echo "Message: " . $error['message'] . "<br />";
			}
		}

		// $count = unique($query);
		// if($count>0){
		// 	$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		// 	$data['TOTAL_ONLINE_TEACHERS'] = $row['ONLINE_TEACHERS'];
		// 	$data['successTOTAL'] = true;
		// }else{
		// 	$data['successTOTAL'] = false;
		// }


		// GET AVAILABLE SUBJECTS
		// $query2 = "SELECT SHORT_DESC SUBJECTS,(SELECT COUNT(*) FROM TEACHER_SUBJECTS WHERE CSUBID=CS.CSUBID AND TEACHERID IN (SELECT TEACHERID FROM TEACHER_ONLINE_STATUS WHERE (CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108)) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105) ))ONLINE_TUTORS
		// FROM CLASS_SUBJECT_MASTER CS WHERE ISDELETED=0 AND 
		// CSUBID IN (SELECT CSUBID FROM TEACHER_SUBJECTS WHERE TEACHERID IN (SELECT TEACHERID FROM TEACHER_ONLINE_STATUS WHERE (CONVERT(VARCHAR,GETDATE(),108) BETWEEN CONVERT(VARCHAR, ONTIME,108) AND CONVERT(VARCHAR, OUTTIME,108)) AND CONVERT(DATE,GETDATE(),105)=CONVERT(DATE,INSERTDATE,105) ))
		// ORDER BY SUBJECTS";
		// $query2 = "";
		// $data['$query2']=$query2;

		// $count2 = unique($query2);
		// if($count2>0){
		// 	$result2 = sqlsrv_query($mysqli, $query2);
		// 	while ($row2 = sqlsrv_fetch_array($result2,SQLSRV_FETCH_ASSOC)) {
		// 		$data['data'][] = $row2;
		// 	}
		// 	$data['success'] = true;
		// }else{
		// 	$data['success'] = false;
		// }

		sqlsrv_free_stmt($result);
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ GET ONLINE TEACHERS =============*/ 

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







