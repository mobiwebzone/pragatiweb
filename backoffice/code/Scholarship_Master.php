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
        case "getScholarships":getScholarships($conn);break;
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

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$schmid = ($_POST['schmid'] == 'undefined' || $_POST['schmid'] == '') ? 0 : $_POST['schmid'];
		$txtScholarship = $_POST['txtScholarship'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtScholarship']);
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];
		$ddlCollegeMajor = ($_POST['ddlCollegeMajor'] == 'undefined' || $_POST['ddlCollegeMajor'] == '') ? 0 : $_POST['ddlCollegeMajor'];
		$txtLink = $_POST['txtLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLink']);
		$txtComments = $_POST['txtComments'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtComments']);		

		$actionid = $schmid == 0 ? 1 : 2;

		if($txtScholarship == '') throw new Exception("Enter Scholarship Name.");


		$sql = "SELECT * FROM SCHOLARSHIP_MASTER WHERE SCHOLARSHIP='$txtScholarship' AND SCHMID!=$schmid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{
			$query="EXEC [SCHOLARSHIP_MASTER_SP] $actionid,$schmid,'$txtScholarship',$ddlUniversity,$ddlCollege,$ddlCollegeMajor,
			'$txtLink','$txtComments',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($schmid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET SCHOLARSHIPS =========== */
 function getScholarships($mysqli){
	try
	{
		$data = array();
		$query = "SELECT SCHMID,SCHOLARSHIP,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=SM.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=SM.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=SM.MAJORID)MAJOR,SCHOLARSHIPLINK,COMMENTS 
		FROM SCHOLARSHIP_MASTER SM
		WHERE ISDELETED=0
		ORDER BY SCHOLARSHIP";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SCHMID'] = (int) $row['SCHMID'];
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
/* ========== GET SCHOLARSHIPS =========== */





/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $SCHMID = ($_POST['SCHMID'] == 'undefined' || $_POST['SCHMID'] == '') ? 0 : $_POST['SCHMID'];  
			if($SCHMID == 0)throw new Exception('SCHMID Not Found.');
			$delQuery = "EXEC [SCHOLARSHIP_MASTER_SP] 3,$SCHMID,'',0,0,0,'','',$userid";
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







