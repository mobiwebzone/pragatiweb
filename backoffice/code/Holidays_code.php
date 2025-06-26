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
		case "login":login($conn);break;
        case "save":save($conn);break;
        case "getHolidays":getHolidays($conn);break;
        case "delete":delete($conn);break;
		case "getschoolname":getschoolname($conn);break;
		case "getFinancialYear": getFinancialYear($conn);break;
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
    
        $hmid  = ($_POST['hmid'] == 'undefined' || $_POST['hmid'] == '') ? 0 : $_POST['hmid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $TEXT_FY_YEAR_CD  = ($_POST['TEXT_FY_YEAR_CD'] == 'undefined' || $_POST['TEXT_FY_YEAR_CD'] == '') ? 0 : $_POST['TEXT_FY_YEAR_CD'];
        $txtDate  = $_POST['txtDate'] == 'undefined' ? '' : $_POST['txtDate'];
        $txtOccassion  = $_POST['txtOccassion'] == 'undefined' ? '' : $_POST['txtOccassion'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
        $TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? '' : $_POST['TEXT_SCHOOL_ID'];
		
		$actionid = $hmid == 0 ? 1 : 2;

		if($TEXT_FY_YEAR_CD == 0)
		{throw new Exception("Please Enter Year.");}
		if($txtDate == '')
		{throw new Exception("Please Select Holiday Date.");}
		if($txtOccassion == '')
		{throw new Exception("Please Enter Occassion.");}

		$sql = "SELECT * FROM HOLIDAYS_MASTER WHERE LOCID=$ddlLocation AND FY_YEAR_CD=$TEXT_FY_YEAR_CD AND HDATE='$txtDate' AND HOCCASSION LIKE '%$txtOccassion%' AND HMID!=$hmid AND ISDELETED=0";
		// throw new Exception($sql);
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [HOLIDAYS_MASTER_SP] $actionid,$hmid,$ddlLocation,$TEXT_FY_YEAR_CD,'$txtDate','$txtOccassion','$txtRemark',$userid,$TEXT_SCHOOL_ID";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($hmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Holiday already exists.';
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


function getschoolname($mysqli){
	try
	{
		global $userid;
		$query = "select SCHOOL_ID,SCHOOL_NAME FROM SCHOOL WHERE ISDELETED=0 
		AND SCHOOL_ID IN (SELECT SCHOOL_ID FROM SCHOOL_USER WHERE USER_ID= $userid AND ISDELETED=0)
		ORDER BY SCHOOL_ID ";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['SCHOOL_ID'] = (int) $row['SCHOOL_ID'];
				
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

function getFinancialYear($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=40 and isdeleted=0";

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



/*============ Get Holidays =============*/ 
 function getHolidays($mysqli){
	try
	{
		
		$data = array();
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];	
		$TEXT_FY_YEAR_CD_S  = $_POST['TEXT_FY_YEAR_CD_S'] == 'undefined' ? '' : $_POST['TEXT_FY_YEAR_CD_S'];	

		$query = "SELECT 
		HMID,
		LOCID,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=HM.LOCID)[LOCATION],
		FY_YEAR_CD,
        FY_YEAR,
		CONVERT(VARCHAR,HDATE,106)HDATE,
		HOCCASSION,
		REMARKS,
		SCHOOL_ID FROM HOLIDAYS_MASTER HM WHERE ISDELETED=0
		AND SCHOOL_ID = $TEXT_SCHOOL_ID ";
        
		if ($TEXT_FY_YEAR_CD_S != '') {
			$query .= " AND FY_YEAR_CD = $TEXT_FY_YEAR_CD_S"; 
		}
		
        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['HMID'] = (int) $row['HMID'];
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
            $hmid = ($_POST['hmid'] == 'undefined' || $_POST['hmid'] == '') ? 0 : $_POST['hmid'];  

			$query ="EXEC [HOLIDAYS_MASTER_SP] 3,$hmid,0,0,'','','',$userid,'' ";
			// $data['query']=$query;
			$stmt=sqlsrv_query($mysqli, $query);
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







