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
		case "getPublicationName":getPublicationName($conn);break;
        case "getPublicationSITE":getPublicationSITE($conn);break;
		case "getPublicationData":getPublicationData($conn);break;
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

 // GET PUBLICATION NAME 
 function getPublicationName($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT PUBLICATION_ID,PUBLICATION_NAME FROM MEP_PUBLICATION_MANAGEMENT WHERE ISDELETED=0";
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




// GET PUBLICATION SITES 
function getPublicationSITE($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=29 and isdeleted=0";

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
		$PUBLICATION_SITES_ID  = ($_POST['PUBLICATION_SITES_ID'] == 'undefined' || $_POST['PUBLICATION_SITES_ID'] == '') ? 0 : $_POST['PUBLICATION_SITES_ID'];
        $ddlPublicationname  = ($_POST['ddlPublicationname'] == 'undefined' || $_POST['ddlPublicationname'] == '') ? 0 : $_POST['ddlPublicationname'];
        $ddlPublicationSITE  = ($_POST['ddlPublicationSITE'] == 'undefined' || $_POST['ddlPublicationSITE'] == '') ? 0 : $_POST['ddlPublicationSITE'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $PUBLICATION_SITES_ID == 0 ? 1 : 2;
		
		
		if($ddlPublicationname == 0)
		{throw new Exception("Please Select Publication Name.");}
	
		$querycount = "SELECT * FROM MEP_PUBLICATION_SITES WHERE PUBLICATION_ID=$ddlPublicationname AND PUBLICATION_SITE_CD=$ddlPublicationSITE AND PUBLICATION_SITES_ID!=$PUBLICATION_SITES_ID AND ISDELETED=0";
		$row_count = unique($querycount);	


		if($row_count == 0)
		{
			$query="EXEC [MEP_PUBLICATION_SITES_SP] $actionid,$PUBLICATION_SITES_ID,$ddlPublicationname,$ddlPublicationSITE,$userid,'$txtRemark'";
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
				if(!empty($PUBLICATION_SITES_ID))$data['message'] = 'Record successfully updated';
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
function getPublicationData($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT PUBLICATION_SITES_ID,PUBLICATION_ID,PUBLICATION_NAME,PUBLICATION_SITE_CD,PUBLICATION_SITE,REMARKS 
		FROM MEP_PUBLICATION_SITES 
		WHERE ISDELETED=0 
		ORDER BY PUBLICATION_SITES_ID";

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
            $PUBLICATION_SITES_ID = ($_POST['PUBLICATION_SITES_ID'] == 'undefined' || $_POST['PUBLICATION_SITES_ID'] == '') ? 0 : $_POST['PUBLICATION_SITES_ID'];
			if($PUBLICATION_SITES_ID == 0) throw new Exception('Invalid PUBLICATION-ID.');
			$query = "EXEC [MEP_PUBLICATION_SITES_SP] 3,$PUBLICATION_SITES_ID,0,0,$userid,''";
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






