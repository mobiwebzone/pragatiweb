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
        case "getGradefrom":getGradefrom($conn);break;
        case "getGradeto":getGradeto($conn);break;
		case "getPublicStatus":getPublicStatus($conn);break;
		case "getDocutype":getDocutype($conn);break;
		case "getPublicationmanagementData":getPublicationmanagementData($conn);break;
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

/* ========== GET GRADE FROM =========== */
function getGradefrom($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

/* ========== GET GRADE TO =========== */
function getGradeto($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=28 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

/* ========== GET PUBLICATION STATUS =========== */
function getPublicStatus($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=25 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

/* ========== GET DOCUMENT TYPE=========== */
function getDocutype($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=26 and isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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

function save($mysqli){
	try
	{
		$data = array();
        global $userid;
		$PUBLICATION_ID  = ($_POST['PUBLICATION_ID'] == 'undefined' || $_POST['PUBLICATION_ID'] == '') ? 0 : $_POST['PUBLICATION_ID'];
        $txtPublicationName  = ($_POST['txtPublicationName'] == 'undefined' || $_POST['txtPublicationName'] == '') ? '' : $_POST['txtPublicationName'];
        $ddlGradefrom  = ($_POST['ddlGradefrom'] == 'undefined' || $_POST['ddlGradefrom'] == '') ? 0 : $_POST['ddlGradefrom'];
        $ddlGradeto  = ($_POST['ddlGradeto'] == 'undefined' || $_POST['ddlGradeto'] == '') ? 0 : $_POST['ddlGradeto'];
		$Sdate  = ($_POST['Sdate'] == 'undefined' || $_POST['Sdate'] == '') ? '' : $_POST['Sdate'];
		$Edate  = ($_POST['Edate'] == 'undefined' || $_POST['Edate'] == '') ? '' : $_POST['Edate'];
        $txtHourSpent  = ($_POST['txtHourSpent'] == 'undefined' || $_POST['txtHourSpent'] == '') ? '' : $_POST['txtHourSpent'];
        $txtBookcost  = ($_POST['txtBookcost'] == 'undefined' || $_POST['txtBookcost'] == '') ? '' : $_POST['txtBookcost'];
		$ddlPublicationStatus  = ($_POST['ddlPublicationStatus'] == 'undefined' || $_POST['ddlPublicationStatus'] == '') ? 0 : $_POST['ddlPublicationStatus'];
		$txtMainContri  = ($_POST['txtMainContri'] == 'undefined' || $_POST['txtMainContri'] == '') ? '': $_POST['txtMainContri'];
		$ddlDocutype  = ($_POST['ddlDocutype'] == 'undefined' || $_POST['ddlDocutype'] == '') ? 0: $_POST['ddlDocutype'];
		$txtLastdraft  = ($_POST['txtLastdraft'] == 'undefined' || $_POST['txtLastdraft'] == '') ? '' : $_POST['txtLastdraft'];
		$txtLDfileattached  = ($_POST['txtLDfileattached'] == 'undefined' || $_POST['txtLDfileattached'] == '') ? '' : $_POST['txtLDfileattached'];
		$txtLDraftdate  = ($_POST['txtLDraftdate'] == 'undefined' || $_POST['txtLDraftdate'] == '') ? '' : $_POST['txtLDraftdate'];
        $txtLDraftwho  = ($_POST['txtLDraftwho'] == 'undefined' || $_POST['txtLDraftwho'] == '') ? '' : $_POST['txtLDraftwho'];
		$txtFinalLink  = ($_POST['txtFinalLink'] == 'undefined' || $_POST['txtFinalLink'] == '') ? '' : $_POST['txtFinalLink'];
		$txtFileattached  = ($_POST['txtFileattached'] == 'undefined' || $_POST['txtFileattached'] == '') ? '': $_POST['txtFileattached'];
		$txtFinaldate  = ($_POST['txtFinaldate'] == 'undefined' || $_POST['txtFinaldate'] == '') ? '': $_POST['txtFinaldate'];
		$txtFinalwho  = ($_POST['txtFinalwho'] == 'undefined' || $_POST['txtFinalwho'] == '') ? '' : $_POST['txtFinalwho'];
		$txtFinalRemark  = ($_POST['txtFinalRemark'] == 'undefined' || $_POST['txtFinalRemark'] == '') ? '' : $_POST['txtFinalRemark'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $PUBLICATION_ID == 0 ? 1 : 2;
		
		if($txtPublicationName == '')
		{throw new Exception("Please Write Publication Name.");}
		
		$querycount = "SELECT * FROM MEP_PUBLICATION_MANAGEMENT WHERE PUBLICATION_NAME='$txtPublicationName' AND  PUBLICATION_ID!=$PUBLICATION_ID AND ISDELETED=0";
		$row_count = unique($querycount);	

		if($row_count == 0)
		{
			$query="EXEC [IT_PUBLICATION_MANAGEMENT_SP] $actionid,$PUBLICATION_ID,'$txtPublicationName',$ddlGradefrom,$ddlGradeto,'$Sdate',
			'$Edate','$txtHourSpent','$txtBookcost',$ddlPublicationStatus,'$txtMainContri',$ddlDocutype,'$txtLastdraft','$txtLDfileattached',
			'$txtLDraftdate','$txtLDraftwho','$txtFinalLink','$txtFileattached','$txtFinaldate','$txtFinalwho','$txtFinalRemark',$userid,'$txtRemark'";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				die( print_r( sqlsrv_errors(), true));
						throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($PUBLICATION_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
			}
			echo json_encode($data);exit;
			
			
		 }
		 else
		 {
			
		 	$data['success'] = false;
		 	$data['message'] = 'Record already exists.';
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

/*============ GET SALES DATA =============*/ 
function getPublicationmanagementData($mysqli){
	try
	{
		$data = array();
        $query = "SELECT PUBLICATION_ID,PUBLICATION_NAME,GRADE_FROM_CD,GRADE_FROM,GRADE_TO_CD,GRADE_TO,CONVERT(VARCHAR,START_DATE,106)START_DATE,
		CONVERT(VARCHAR,END_DATE,106)END_DATE,HOURS_SPENT,BOOK_COST,PUBLICATION_STATUS_CD,PUBLICATION_STATUS,MAIN_CONTRIBUTOR,DOCUMENT_TYPE_CD,
		DOCUMENT_TYPE,LAST_DRAFT_LINK,LAST_DRAFT_FILE_ATTACHED,CONVERT(VARCHAR,LAST_DRAFT_DATE,106)LAST_DRAFT_DATE,LAST_DRAFT_WHO,FINAL_LINK,
		FINAL_FILE_ATTACHED,CONVERT(VARCHAR,FINAL_DATE,106)FINAL_DATE,FINAL_WHO,FINAL_REMARKS,REMARKS 
		FROM MEP_PUBLICATION_MANAGEMENT 
		WHERE ISDELETED=0 
		ORDER BY PUBLICATION_ID";

		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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


/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $PUBLICATION_ID = ($_POST['PUBLICATION_ID'] == 'undefined' || $_POST['PUBLICATION_ID'] == '') ? 0 : $_POST['PUBLICATION_ID'];
			if($PUBLICATION_ID == 0) throw new Exception('Invalid PUBLICATION-ID.');
			$query = "EXEC [IT_PUBLICATION_MANAGEMENT_SP] 3,$PUBLICATION_ID,'',0,0,'','','','',0,'',0,'','','','','','','','','',$userid,''";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}






