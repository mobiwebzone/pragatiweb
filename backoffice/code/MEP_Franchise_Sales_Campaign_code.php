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
        case "getCampaigncatgy":getCampaigncatgy($conn);break;
        case "getSalesCampData":getSalesCampData($conn);break;
		case "getMastertask":getMastertask($conn);break;
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

// Get Campaign Category 
function getCampaigncatgy($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=13 and isdeleted=0";

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

// Get getMastertask
function getMastertask($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";
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

 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		$CAMPAIGN_ID  = ($_POST['CAMPAIGN_ID'] == 'undefined' || $_POST['CAMPAIGN_ID'] == '') ? 0 : $_POST['CAMPAIGN_ID'];
        $ddlSalesCampCategory  = ($_POST['ddlSalesCampCategory'] == 'undefined' || $_POST['ddlSalesCampCategory'] == '') ? 0 : $_POST['ddlSalesCampCategory'];
        // $txtSaleName  = ($_POST['txtSaleName'] == 'undefined' || $_POST['txtSaleName'] == '') ? 0 : $_POST['txtSaleName'];
        $SCpurpose  = ($_POST['SCpurpose'] == 'undefined' || $_POST['SCpurpose'] == '') ? '' : $_POST['SCpurpose'];
		$txtSoftwareuse  = ($_POST['txtSoftwareuse'] == 'undefined' || $_POST['txtSoftwareuse'] == '') ? '' : $_POST['txtSoftwareuse'];
		$Sdate  = ($_POST['Sdate'] == 'undefined' || $_POST['Sdate'] == '') ? '' : $_POST['Sdate'];
		$Edate  = ($_POST['Edate'] == 'undefined' || $_POST['Edate'] == '') ? '' : $_POST['Edate'];
		$ddlMastertask  = ($_POST['ddlMastertask'] == 'undefined' || $_POST['ddlMastertask'] == '') ? 0 : $_POST['ddlMastertask'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $CAMPAIGN_ID == 0 ? 1 : 2;
		
		
		if($ddlSalesCampCategory == 0)
		{throw new Exception("Please Select Sales Campaign Category.");}
	
		
		//$sql = "SELECT * FROM MEP_FRANCHISE_SALES_CAMPAIGN WHERE SALES_CAMPAIGN_CATG_ID=$ddlSalesCampCategory AND PURPOSE='$SCpurpose' 
				//AND CAMPAIGN_ID!=$CAMPAIGN_ID AND ISDELETED=0";
		//$row_count = unique($sql);
		
		
		
		//if($row_count == 0)
		//{
			$query="EXEC [Franchise_Sales_Campaign_SP] $actionid,$CAMPAIGN_ID,$ddlSalesCampCategory,'$SCpurpose','$txtSoftwareuse','$Sdate','$Edate',$userid,'$txtRemark', $ddlMastertask";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			// echo json_encode($data);exit;
			// throw new Exception($query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($CAMPAIGN_ID))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		// }
		// else
		// {
			
		// 	$data['success'] = false;
		// 	$data['message'] = 'Record already exists.';
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


/*============ GET SALES DATA =============*/ 
function getSalesCampData($mysqli){
	try
	{
		$data = array();
		

		$query = "SELECT CAMPAIGN_ID,SALES_CAMPAIGN_CATG_ID,SALES_CAMPIGN_CATG,PURPOSE,
		SOFTWARE_NAME,CONVERT(VARCHAR,START_DATE,106)START_DATE,CONVERT(VARCHAR,END_DATE,106)END_DATE,
		MASTER_TASK_CD,MASTER_TASK_DESC,REMARKS FROM MEP_FRANCHISE_SALES_CAMPAIGN 
		WHERE ISDELETED=0 
		ORDER BY CAMPAIGN_ID";

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
            $CAMPAIGN_ID = ($_POST['CAMPAIGN_ID'] == 'undefined' || $_POST['CAMPAIGN_ID'] == '') ? 0 : $_POST['CAMPAIGN_ID'];
			if($CAMPAIGN_ID == 0) throw new Exception('Invalid CAMPAIGNID.');
			$query = "EXEC [Franchise_Sales_Campaign_SP] 3,$CAMPAIGN_ID,0,'','','','',$userid,'',0";
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






