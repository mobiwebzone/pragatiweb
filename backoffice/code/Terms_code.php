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
        case "saveTerm":saveTerm($conn);break;
        case "getTerm":getTerm($conn);break;
        case "deleteTerm":deleteTerm($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveTerm($mysqli){
     try
     {
		$data = array();
        global $userid;
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $termid  = ($_POST['termid'] == 'undefined' || $_POST['termid'] == '') ? 0 : $_POST['termid'];
        $txtTerm  = $_POST['txtTerm'] == 'undefined' ? '' : $_POST['txtTerm'];
		$txtTerm = str_replace("'","''",$txtTerm);
        // $txtFromDate  = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
        // $txtToDate  = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
		
		$actionid = $termid == 0 ? 1 : 2;

		if($txtTerm == '')
		{throw new Exception("Enter Term.");}

		$sql = "SELECT * FROM TERMS WHERE TERM='$txtTerm' AND TERMID!=$termid AND ISDELETED=0 AND LOC_ID=$ddlLocation";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TERMS_SP] $actionid,$termid,'$txtTerm',$userid,$ddlLocation";
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
				if(!empty($termid))$data['message'] = 'Record successfully updated';
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


/*============ Get Terms =============*/ 
 function getTerm($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
		$query = "SELECT TERMID,TERM,CONVERT(VARCHAR,FROM_DATE,107)FROMDATE,
				CONVERT(VARCHAR,TO_DATE,107)TODATE,ISNULL(LOC_ID,0),
				(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TERMS.LOC_ID)LOCATION
				FROM TERMS WHERE ISDELETED=0";
		if($LOCID > 0) $query.=" AND LOC_ID=$LOCID";

		$row_count = unique($query);
		if($row_count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TERMID'] = (int) $row['TERMID'];
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
function deleteTerm($mysqli){
	try{   
			global $userid;
			$data = array();     
            $termid = ($_POST['termid'] == 'undefined' || $_POST['termid'] == '') ? 0 : $_POST['termid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [TERMS_SP] 3,$termid,'',$userid,0");
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







