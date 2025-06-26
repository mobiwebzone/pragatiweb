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
        case "getClassSubject":getClassSubject($conn);break;
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

		$csubid = ($_POST['csubid'] == 'undefined' || $_POST['csubid'] == '') ? 0 : $_POST['csubid'];
		$ddlClassType = ($_POST['ddlClassType'] == 'undefined' || $_POST['ddlClassType'] == '') ? 0 : $_POST['ddlClassType'];
		$txtClassTypeOther = $_POST['txtClassTypeOther'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtClassTypeOther']);
		$txtCredit = ($_POST['txtCredit'] == 'undefined' || $_POST['txtCredit'] == '') ? 0 : str_replace("'","''",$_POST['txtCredit']);
		$ddlSemesterClass = $_POST['ddlSemesterClass'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlSemesterClass']);
		$ddlVirtualClass = $_POST['ddlVirtualClass'] == 'undefined' ? '' : str_replace("'","''",$_POST['ddlVirtualClass']);
		$txtShortDesc = $_POST['txtShortDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtShortDesc']);
		$txtLongDesc = $_POST['txtLongDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLongDesc']);
		$ddlPreReq1 = ($_POST['ddlPreReq1'] == 'undefined' || $_POST['ddlPreReq1'] == '') ? 0 : $_POST['ddlPreReq1'];
		$ddlPreReq2 = ($_POST['ddlPreReq2'] == 'undefined' || $_POST['ddlPreReq2'] == '') ? 0 : $_POST['ddlPreReq2'];
		$ddlPreReq3 = ($_POST['ddlPreReq3'] == 'undefined' || $_POST['ddlPreReq3'] == '') ? 0 : $_POST['ddlPreReq3'];
		$ddlNextClass1 = ($_POST['ddlNextClass1'] == 'undefined' || $_POST['ddlNextClass1'] == '') ? 0 : $_POST['ddlNextClass1'];
		$ddlNextClass2 = ($_POST['ddlNextClass2'] == 'undefined' || $_POST['ddlNextClass2'] == '') ? 0 : $_POST['ddlNextClass2'];
		$ddlNextClass3 = ($_POST['ddlNextClass3'] == 'undefined' || $_POST['ddlNextClass3'] == '') ? 0 : $_POST['ddlNextClass3'];


		$actionid = $csubid == 0 ? 1 : 2;

		if($ddlClassType == 0) throw new Exception("Select Class Type.");


		// $sql = "SELECT * FROM CLASS_SUBJECT_MASTER WHERE CLASSTYPEID=$ddlClassType AND CSUBID!=$csubid AND ISDELETED=0";
		// $row_count = unique($sql);

		// $data = array();
		// if($row_count == 0)
		// {
			$query="EXEC [CLASS_SUBJECT_MASTER_SP] $actionid,$csubid,$ddlClassType,'$txtClassTypeOther',$txtCredit,'$ddlSemesterClass',
				'$ddlVirtualClass','$txtShortDesc','$txtLongDesc',$ddlPreReq1,$ddlPreReq2,$ddlPreReq3,$ddlNextClass1,$ddlNextClass2,$ddlNextClass3,$userid";
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
				if(!empty($csubid))$data['message'] = 'Record successfully updated.';
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




/* ========== GET CLASS/SUBJECT =========== */
 function getClassSubject($mysqli){
	try
	{
		$data = array();
		// $query = "SELECT CSUBID,CLASSTYPEID,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=AM.CLASSTYPEID)CLASS_TYPE,
		// CLASSTYPEID_OTHER,CREDIT,SEMESTER_CLASS,VIRTUAL_CLASS,SHORT_DESC,LONG_DESC,PREREQ1,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ1))PREREQ1_NAME,PREREQ2,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ2))PREREQ2_NAME,PREREQ3,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ3))PREREQ3_NAME,NEXTCLASS1,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS1))NEXTCLASS1_NAME,NEXTCLASS2,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS2))NEXTCLASS2_NAME,NEXTCLASS3,
		// (SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=(SELECT CLASSTYPEID FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS3))NEXTCLASS3_NAME
		// FROM CLASS_SUBJECT_MASTER AM WHERE ISDELETED=0 ORDER BY CLASS_TYPE";
		$query = "SELECT CSUBID,CLASSTYPEID,
		(SELECT CLASS_TYPE FROM CLASS_TYPE_MASTER WHERE CLASSTYPEID=AM.CLASSTYPEID)CLASS_TYPE,
		CLASSTYPEID_OTHER,CREDIT,SEMESTER_CLASS,VIRTUAL_CLASS,SHORT_DESC,LONG_DESC,PREREQ1,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ1)PREREQ1_NAME,PREREQ2,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ2)PREREQ2_NAME,PREREQ3,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.PREREQ3)PREREQ3_NAME,NEXTCLASS1,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS1)NEXTCLASS1_NAME,NEXTCLASS2,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS2)NEXTCLASS2_NAME,NEXTCLASS3,
		(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=AM.NEXTCLASS3)NEXTCLASS3_NAME
		FROM CLASS_SUBJECT_MASTER AM WHERE ISDELETED=0 ORDER BY CLASS_TYPE";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CSUBID = ($_POST['CSUBID'] == 'undefined' || $_POST['CSUBID'] == '') ? 0 : $_POST['CSUBID'];  
			if($CSUBID == 0)throw new Exception('CSUBID Not Found.');
			$delQuery = "EXEC [CLASS_SUBJECT_MASTER_SP] 3,$CSUBID,0,'',0,'','','','',0,0,0,0,0,0,$userid";
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







