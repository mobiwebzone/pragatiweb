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
        case "getReviews":getReviews($conn);break;
        case "getStudentByLoc":getStudentByLoc($conn);break;
        case "getLocReviewByLoc":getLocReviewByLoc($conn);break;
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





// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $rvid  = ($_POST['rvid'] == 'undefined' || $_POST['rvid'] == '') ? 0 : $_POST['rvid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlSTCategory  = ($_POST['ddlSTCategory'] == 'undefined' || $_POST['ddlSTCategory'] == '') ? '' : $_POST['ddlSTCategory'];
        $ddlStudent  = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
        $txtFirstName  = $_POST['txtFirstName'] == 'undefined' ? '' : $_POST['txtFirstName'];
        $txtLastName  = $_POST['txtLastName'] == 'undefined' ? '' : $_POST['txtLastName'];
        $ddlReviewBy  = $_POST['ddlReviewBy'] == 'undefined' ? '' : $_POST['ddlReviewBy'];
        $txtReviewByName  = $_POST['txtReviewByName'] == 'undefined' ? '' : $_POST['txtReviewByName'];
        $txtPhone  = $_POST['txtPhone'] == 'undefined' ? '' : $_POST['txtPhone'];
        $txtEmail  = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
        $ddlLocReview  = ($_POST['ddlLocReview'] == 'undefined' || $_POST['ddlLocReview'] == '') ? 0 : $_POST['ddlLocReview'];
        $txtComment  = $_POST['txtComment'] == 'undefined' ? '' : $_POST['txtComment'];
		$txtComment = str_replace("'","''",$txtComment);
        $ddlStatus  = $_POST['ddlStatus'] == 'undefined' ? '' : $_POST['ddlStatus'];
		$ShowInHome = ($_POST['ShowInHome'] == 'undefined' || $_POST['ShowInHome'] == '') ? 0 : $_POST['ShowInHome'];
		$txtReviewDT = $_POST['txtReviewDT']=='undefined'?'':$_POST['txtReviewDT'];
		$txtReview = $_POST['txtReview']=='undefined'?'':$_POST['txtReview'];

		
		$actionid = $rvid == 0 ? 1 : 2;

		if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
		if($ddlSTCategory == ''){throw new Exception("Please Select 'Student Category'.");}
		if($ddlSTCategory != '' && $ddlSTCategory === 'REGISTERED' && $ddlStudent == 0){throw new Exception("Please Select 'Student Name'.");}
		if($txtFirstName == ''){throw new Exception("Please Enter 'First Name'.");}
		if($txtLastName == ''){throw new Exception("Please Enter 'Last Name'.");}
		if($ddlReviewBy == ''){throw new Exception("Please Select 'Review By'.");}
		if($txtReviewByName == ''){throw new Exception("Please Enter 'Review By Name'.");}
		if($txtComment == ''){throw new Exception("Please Enter 'Comment'.");}
		if($ddlStatus == ''){throw new Exception("Please Select 'Status'.");}

		// $sql = "SELECT * FROM REVIEWS WHERE TDSSUBCATID=$ddlSSubCategory AND LOCID=$ddlLocation AND TOUSER=$ddlUser AND [PRIORITY]='$ddlPriority' AND TODO='$txtToDo' AND ETA='$txtETADate' AND TDLID!=$tdlid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// $GET_TDLID = 0;
		// if($row_count == 0)
		// {
			$query="EXEC [REVIEWS_SP] $actionid,$rvid,$ddlLocation,'$ddlSTCategory',$ddlStudent,'$txtFirstName','$txtLastName','$ddlReviewBy',
			'$txtReviewByName','$txtPhone','$txtEmail',$ddlLocReview,'$txtComment','$ddlStatus',
			$ShowInHome,'$txtReviewDT','$txtReview',$userid";
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
				if(!empty($rvid))$data['message'] = 'Review successfully updated.';
				else $data['message'] = 'Review successfully inserted.';
			}
			
			$data['query'] = $query;
			echo json_encode($data);exit;
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['message'] = 'Todo already exists';
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






/*============ GET REVIEWS =============*/ 
 function getReviews($mysqli){
	try
	{
		$data = array();
		$ddlSearchStatus = $_POST['ddlSearchStatus'] == 'undefined' ? '' : $_POST['ddlSearchStatus'];
		$query = "SELECT RVID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCID)[LOCATION],
		STUDENT_CATEGORY,REGID,FIRSTNAME,LASTNAME,REVIEW_BY,REVIEWBY_NAME,PHONE,EMAILID,REVID,
		(SELECT REVIEWMEDIA FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWMEDIA,
		(SELECT REVIEWLINK FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWLINK,
		COMMENTS_GIVEN,[STATUS],SHOW_IN_HOMEPAGE,CONVERT(VARCHAR,REVIEW_DATE,106)REVIEW_DATE,REVIEW
		FROM REVIEWS R WHERE ISDELETED=0";
		if($ddlSearchStatus != '') $query .= " AND [STATUS]='$ddlSearchStatus'";
		$query .= " ORDER BY [LOCATION]";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$row['REVIEW_DATE']=($row['REVIEW_DATE'] == '' || $row['REVIEW_DATE']=='01 Jan 1900') ? '' : $row['REVIEW_DATE']; 
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Review not found.';
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
/*============ GET REVIEWS =============*/ 





/*============ GET STUDENT BY LOCATION =============*/ 
 function getStudentByLoc($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');

		$query = "SELECT REGID,MODE,FIRSTNAME,LASTNAME,PHONE,EMAIL,GRADE,ADDRESSLINE1,ADDRESSLINE2,P1_FIRSTNAME,P1_LASTNAME,P1_PHONE,P1_EMAIL,
		P2_FIRSTNAME,P2_LASTNAME,P2_PHONE,P2_EMAIL FROM REGISTRATIONS WHERE LOCATIONID=$ddlLocation AND ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Student not found.';
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
/*============ GET STUDENT BY LOCATION =============*/ 





/*============ GET LOC REVIEW BY LOCATION =============*/ 
 function getLocReviewByLoc($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		if($ddlLocation == 0) throw new Exception('Select Location First.');

		$query = "SELECT REVID,REVIEWMEDIA,REVIEWLINK FROM LOCATION_REVIEWS WHERE LOC_ID=$ddlLocation AND ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Location Review not found.';
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
/*============ GET LOC REVIEW BY LOCATION =============*/ 






/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $rvid = ($_POST['rvid'] == 'undefined' || $_POST['rvid'] == '') ? 0 : $_POST['rvid'];
			if($rvid == 0){throw new Exception('RVID Error.');}
			$delQuery = "EXEC [REVIEWS_SP] 3,$rvid,0,'',0,'','','','','','',0,'','',0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Review successfully deleted.';
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







