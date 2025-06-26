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
        case "getStudentTeacherDocs":getStudentTeacherDocs($conn);break;
        case "getTeacherStudent":getTeacherStudent($conn);break;
        case "getProduct":getProduct($conn);break;
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
    
        $stdid  = ($_POST['stdid'] == 'undefined' || $_POST['stdid'] == '') ? 0 : $_POST['stdid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlDocFor  = ($_POST['ddlDocFor'] == 'undefined' || $_POST['ddlDocFor'] == '') ? '' : $_POST['ddlDocFor'];
        $ddlDocForID  = ($_POST['ddlDocForID'] == 'undefined' || $_POST['ddlDocForID'] == '') ? 0 : $_POST['ddlDocForID'];
		$ddlDocType = $_POST['ddlDocType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlDocType']);
		$txtDocDesc = $_POST['txtDocDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtDocDesc']);

		$actionid = $stdid == 0 ? 1 : 2;

		// === IMAGE
		$existingPictureUpload  = $_POST['existingPictureUpload'] == 'undefined' ? '' : $_POST['existingPictureUpload'];
		$fileSize = $ddlDocType=='IMAGE' ? 1048576 : ($ddlDocType=='PDF' ? 2097152 : 20971520);
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0 && $_FILES['pictureUpload']['size'] > $fileSize) throw new Exception('File size too large.');

		$pictureUpload = '';
		if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0){
			$ext = pathinfo($_FILES['pictureUpload']['name'],PATHINFO_EXTENSION);
			$pictureUpload .= strtolower($ddlDocType.'_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$pictureUpload=$existingPictureUpload;
		}
		// === IMAGE

		if($ddlLocation == 0)throw new Exception("Select Location.");
		if($ddlDocFor == '')throw new Exception("Select Doc For.");
		if($ddlDocForID == 0)throw new Exception("Select Doc For Student/Teacher Name.");

		// $sql = "SELECT * FROM TOPICS WHERE PRODUCTID=$ddlProduct AND TOPIC='$txtTopic' AND TOPIC_ID!=$topicid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [STUDENT_TEACHER_DOCUMENTS_SP] $actionid,$stdid,$ddlLocation,'$ddlDocFor',$ddlDocForID,'$ddlDocType','$txtDocDesc','$pictureUpload',$userid";
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

				//========== IMAGE
				if($pictureUpload != ''){

					if(isset($_FILES['pictureUpload']['name']) && $_FILES['pictureUpload']['size'] > 0)
					{
						move_uploaded_file($_FILES["pictureUpload"]["tmp_name"], '../images/student_teacher_docs/'.$pictureUpload);
					}

					
					if(isset($_FILES['pictureUpload']['name']) && $existingPictureUpload != '')
					{
						if (file_exists('../images/student_teacher_docs/'.$existingPictureUpload))
						{
							unlink('../images/student_teacher_docs/'.$existingPictureUpload);
						}
					}
				}
				//========== IMAGE


				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($stdid))$data['message'] = 'Record successfully updated.';
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


/*============ Get Data =============*/ 
 function getStudentTeacherDocs($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$ddlDocFor = ($_POST['ddlDocFor'] == 'undefined' || $_POST['ddlDocFor'] == '') ? '' : $_POST['ddlDocFor'];
		$ddlDocForID = ($_POST['ddlDocForID'] == 'undefined' || $_POST['ddlDocForID'] == '') ? '' : $_POST['ddlDocForID'];

		if($ddlLocation == 0) throw new Exception('Invalid Location.');
		// if($ddlDocFor == '') throw new Exception('Invalid Doc For.');
		// if($ddlDocForID == 0) throw new Exception('Invalid Doc For Student/Teacher Name.');

		$query = "SELECT STDID,LOCID,DOCFOR,DOCFORID,
		CASE WHEN DOCFOR='STUDENT' 
			THEN (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.DOCFORID)
			ELSE (SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=ST.DOCFORID)
		END DOCFORNAME,
		DOCTYPE,DOCDESC,DOCUMENT FROM STUDENT_TEACHER_DOCUMENTS ST
		WHERE LOCID=$ddlLocation AND ISDELETED=0";

		if($ddlDocFor!=='') $query .=" AND DOCFOR='$ddlDocFor'";
		if($ddlDocForID>0) $query .=" AND DOCFORID=$ddlDocForID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$data['data'][] = $row;
		}
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



/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$STDID = ($_POST['STDID'] == 'undefined' || $_POST['STDID'] == '') ? 0 : $_POST['STDID'];  
		if($STDID == 0) throw new Exception('Invalid STDID.');
		$stmt=sqlsrv_query($mysqli, "EXEC [STUDENT_TEACHER_DOCUMENTS_SP] 3,$STDID,0,'',0,'','','',$userid");
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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




/*============ Get Teachers & Students =============*/ 
function getTeacherStudent($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$ddlDocFor = ($_POST['ddlDocFor'] == 'undefined' || $_POST['ddlDocFor'] == '') ? '' : $_POST['ddlDocFor'];
		
		if($ddlDocFor == 'STUDENT'){
			$query = "SELECT REGID AS ID,FIRSTNAME,LASTNAME FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND LOCATIONID=$LOCID ORDER BY FIRSTNAME";
		}
		else if($ddlDocFor == 'TEACHER'){
			$query = "SELECT UID AS ID,FIRSTNAME,LASTNAME FROM USERS WHERE ISDELETED=0 AND USERROLE='TEACHER' AND LOCID=$LOCID ORDER BY FIRSTNAME";
		}else{
			throw new Exception('Invalid Doc For.');
		}
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$data['data'][] = $row;
		}
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







