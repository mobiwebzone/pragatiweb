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
		//######## MASTER
        case "saveData":saveData($conn);break;
        case "getCollegeSpecMaster":getCollegeSpecMaster($conn);break;
        case "deleteData":deleteData($conn);break;
		
		
		//######## DETAILS
        case "saveDataDET":saveDataDET($conn);break;
        case "getCollgeSpecEssay_DET":getCollgeSpecEssay_DET($conn);break;
        case "deleteDET":deleteDET($conn);break;
		
		
		//######## EXTRA DATA TABLE

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;	

		$cseid = ($_POST['cseid'] == 'undefined' || $_POST['cseid'] == '') ? 0 : $_POST['cseid'];
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$ddlCollege = ($_POST['ddlCollege'] == 'undefined' || $_POST['ddlCollege'] == '') ? 0 : $_POST['ddlCollege'];
		$ddlCollegeMajor = ($_POST['ddlCollegeMajor'] == 'undefined' || $_POST['ddlCollegeMajor'] == '') ? 0 : $_POST['ddlCollegeMajor'];
		$ddlAdmType = $_POST['ddlAdmType'] == 'undefined' ? '' : $_POST['ddlAdmType'];
		$txtAdmTypeOther = $_POST['txtAdmTypeOther'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtAdmTypeOther']);
		$ddlAdmYear = ($_POST['ddlAdmYear'] == 'undefined' || $_POST['ddlAdmYear'] == '') ? 0 : $_POST['ddlAdmYear'];
		$chkUseMainEssay = ($_POST['chkUseMainEssay'] == 'undefined' || $_POST['chkUseMainEssay'] == '' || $_POST['chkUseMainEssay'] == false) ? 0 : 1;
		$ddlAppEssay = ($_POST['ddlAppEssay'] == 'undefined' || $_POST['ddlAppEssay'] == '') ? 0 : $_POST['ddlAppEssay'];
		$txtRemarks = $_POST['txtRemarks'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarks']);

		$actionid = $cseid == 0 ? 1 : 2;

		if($ddlUniversity == 0) throw new Exception("Select University Name.");


		// $sql = "SELECT * FROM SCHOLARSHIP_MASTER WHERE SCHOLARSHIP='$txtScholarship' AND SCHMID!=$schmid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$query="EXEC [COLLEGE_SPECIFIC_ESSAYS_SP] $actionid,$cseid,$ddlUniversity,$ddlCollege,$ddlCollegeMajor,'$ddlAdmType',
			'$txtAdmTypeOther',$ddlAdmYear,$chkUseMainEssay,$ddlAppEssay,'$txtRemarks',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// #### GET CSEID
				$row = sqlsrv_fetch_array($stmt);
				$data['GET_CSEID'] = $row['CSEID'];
				$GET_CSEID = $row['CSEID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($cseid))$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET COLLEGE SPECIFIC ESSAYS =========== */
 function getCollegeSpecMaster($mysqli){
	try
	{
		$data = array();
		$query = "SELECT CSEID,UNIVERSITYID,
		(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=CSE.UNIVERSITYID)UNIVERSITY,CLID,
		(SELECT COLLEGE FROM COLLEGES_MASTER WHERE CLID=CSE.CLID)COLLEGE,MAJORID,
		(SELECT MAJOR FROM COLLEGE_MAJOR_MASTER WHERE MAJORID=CSE.MAJORID)MAJOR,ADMTYPE,ADMTYPE_OTHER,ADMYEARID,
		(SELECT ADMYEAR FROM ADM_YEAR_MASTER WHERE ADMYEARID=CSE.ADMYEARID)ADMYEAR,
		CASE WHEN USE_MAIN_ESSAY=1 THEN 'Yes' ELSE 'No' END USE_MAIN_ESSAY,APPESSAYDETID,
		(SELECT APPESSAYID FROM APP_ESSAYS_DETAILS WHERE APPESSAYDETID=CSE.APPESSAYDETID)APPESSAYID,
		(SELECT ESSAY_TITLE FROM APP_ESSAYS_DETAILS WHERE APPESSAYDETID=CSE.APPESSAYDETID)ESSAY_TITLE,REMARKS
		FROM COLLEGE_SPECIFIC_ESSAYS CSE
		WHERE ISDELETED=0
		ORDER BY UNIVERSITY";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['CSEID'] = (int) $row['CSEID'];
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
/* ========== GET COLLEGE SPECIFIC ESSAYS =========== */







/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CSEID = ($_POST['CSEID'] == 'undefined' || $_POST['CSEID'] == '') ? 0 : $_POST['CSEID'];  
			if($CSEID == 0)throw new Exception('CSEID Not Found.');
			$delQuery = "EXEC [COLLEGE_SPECIFIC_ESSAYS_SP] 3,$CSEID,0,0,0,'','',0,0,0,'',$userid";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
function saveDataDET($mysqli){
	try
	{
	   $data = array();
	   global $userid;

	   $csedetid = ($_POST['csedetid'] == 'undefined' || $_POST['csedetid'] == '') ? 0 : $_POST['csedetid'];
	   $cseid = ($_POST['cseid'] == 'undefined' || $_POST['cseid'] == '') ? 0 : $_POST['cseid'];
	   $ddlEssayType = $_POST['ddlEssayType'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlEssayType']);
	   $txtEssayTypeOther = $_POST['txtEssayTypeOther'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEssayTypeOther']);
	   $txtEssay = $_POST['txtEssay'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtEssay']);
	   $ddlLimitOn = $_POST['ddlLimitOn'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlLimitOn']);
	   $txtMinLimit = ($_POST['txtMinLimit'] == 'undefined' || $_POST['txtMinLimit'] == '') ? 0 : $_POST['txtMinLimit'];
	   $txtMaxLimit = ($_POST['txtMaxLimit'] == 'undefined' || $_POST['txtMaxLimit'] == '') ? 0 : $_POST['txtMaxLimit'];
	   $txtRemarksDET = $_POST['txtRemarksDET'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtRemarksDET']);

	   $actionid = $csedetid == 0 ? 1 : 2;

	   if($cseid == 0) throw new Exception("CSEID Error.");
	   if($ddlEssayType == '') throw new Exception("Enter Essay Type.");
	   if($txtEssay == '') throw new Exception("Enter Essay.");


	//    $sql = "SELECT * FROM COLLEGE_SPECIFIC_ESSAYS_DETAILS WHERE CSEID=$cseid AND ESSAYTYPE='$ddlEssayType' AND ESSAYTYPE_OTHER='$txtEssayTypeOther'
	//     AND LIMITON='$ddlLimitOn' AND MINLIMIT=$txtMinLimit AND MAXLIMIT=$txtMaxLimit AND CSEDETID!=$csedetid AND ISDELETED=0";
	//    $row_count = unique($sql);
	   
	//    if($row_count == 0)
	//    {
		   $query="EXEC [COLLEGE_SPECIFIC_ESSAYS_DETAILS_SP] $actionid,$csedetid,$cseid,'$ddlEssayType','$txtEssayTypeOther','$txtEssay',
		   '$ddlLimitOn',$txtMinLimit,$txtMaxLimit,'$txtRemarksDET',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($csedetid))$data['message'] = 'Record successfully updated.';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	//    	$data['success'] = false;
	//    	$data['message'] = 'Record already exists';
	//    	echo json_encode($data);exit;
	//    }

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





/* ========== GET COLLEGE SPECIFIC ESSAY DETAILS =========== */
function getCollgeSpecEssay_DET($mysqli){
	try
	{
		$data = array();
		$cseid = ($_POST['cseid'] == 'undefined' || $_POST['cseid'] == '') ? 0 : $_POST['cseid'];
		if($cseid == 0)throw new Exception("CSEID Error.");

		$query = "SELECT CSEDETID,ESSAYTYPE,ESSAYTYPE_OTHER,ESSAY,LIMITON,MINLIMIT,MAXLIMIT,REMARKS 
		FROM COLLEGE_SPECIFIC_ESSAYS_DETAILS
		WHERE ISDELETED=0 AND CSEID=$cseid
		ORDER BY ESSAYTYPE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['CSEDETID'] = (int) $row['CSEDETID'];
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
/* ========== GET COLLEGE SPECIFIC ESSAY DETAILS =========== */







/* =========== Delete =========== */ 
function deleteDET($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CSEDETID = ($_POST['CSEDETID'] == 'undefined' || $_POST['CSEDETID'] == '') ? 0 : $_POST['CSEDETID'];  
			if($CSEDETID == 0)throw new Exception('CSEDETID Not Found.');
			$delQuery = "EXEC [COLLEGE_SPECIFIC_ESSAYS_DETAILS_SP] 3,$CSEDETID,0,'','','','',0,0,'',$userid";
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 














/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
/*============ GET STUDENT BY LOCATION =============*/ 

/*============ GET STUDENT BY LOCATION =============*/ 

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA DATA GET %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







