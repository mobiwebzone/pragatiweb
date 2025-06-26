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
        case "getPhyscialLoc":getPhyscialLoc($conn);break;
        case "getOperatingSystm":getOperatingSystm($conn);break;
		case "getCloudHosting":getCloudHosting($conn);break;
		case "getAccessWeb":getAccessWeb($conn);break;
		case "getInstancemanagementData":getInstancemanagementData($conn);break;
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

// GET PHYSICAL LOCATION 
function getPhyscialLoc($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=27 and isdeleted=0";

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

// GET OPERATING SYSTEM
function getOperatingSystm($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=24 and isdeleted=0";

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

// GET CLOUD HOSTING
function getCloudHosting($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";

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

// Get GET ACCESS TO WEB
function getAccessWeb($mysqli){
	try
	{
		
	$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=20 and isdeleted=0";

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
		$INSTANCE_ID  = ($_POST['INSTANCE_ID'] == 'undefined' || $_POST['INSTANCE_ID'] == '') ? 0 : $_POST['INSTANCE_ID'];
        $txtInstancename  = ($_POST['txtInstancename'] == 'undefined' || $_POST['txtInstancename'] == '') ? '' : $_POST['txtInstancename'];
        $ddlPhysicalLoc  = ($_POST['ddlPhysicalLoc'] == 'undefined' || $_POST['ddlPhysicalLoc'] == '') ? 0 : $_POST['ddlPhysicalLoc'];
        $ddlOperatingSytm  = ($_POST['ddlOperatingSytm'] == 'undefined' || $_POST['ddlOperatingSytm'] == '') ? 0 : $_POST['ddlOperatingSytm'];
		$ddlCloudHost  = ($_POST['ddlCloudHost'] == 'undefined' || $_POST['ddlCloudHost'] == '') ? 0 : $_POST['ddlCloudHost'];
		$ddlAccessWeb  = ($_POST['ddlAccessWeb'] == 'undefined' || $_POST['ddlAccessWeb'] == '') ? 0 : $_POST['ddlAccessWeb'];
        $txtHostMachine  = ($_POST['txtHostMachine'] == 'undefined' || $_POST['txtHostMachine'] == '') ? '' : $_POST['txtHostMachine'];
        $txtDBhost  = ($_POST['txtDBhost'] == 'undefined' || $_POST['txtDBhost'] == '') ? '' : $_POST['txtDBhost'];
		$txtDBname  = ($_POST['txtDBname'] == 'undefined' || $_POST['txtDBname'] == '') ? '' : $_POST['txtDBname'];
		$txtDBuser  = ($_POST['txtDBuser'] == 'undefined' || $_POST['txtDBuser'] == '') ? '': $_POST['txtDBuser'];
		$txtPasswd  = ($_POST['txtPasswd'] == 'undefined' || $_POST['txtPasswd'] == '') ? '': $_POST['txtPasswd'];
		$txtHostowner  = ($_POST['txtHostowner'] == 'undefined' || $_POST['txtHostowner'] == '') ? '' : $_POST['txtHostowner'];
		$txtMobile  = ($_POST['txtMobile'] == 'undefined' || $_POST['txtMobile'] == '') ? '' : $_POST['txtMobile'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $INSTANCE_ID == 0 ? 1 : 2;
		
		if($txtInstancename == '')
		{throw new Exception("Please Write Instance Name.");}
	
		$querycount = "SELECT * FROM MEP_INSTANCE_MANAGEMENT 
		WHERE INSTANCE_DESCRIPTION='$txtInstancename' 
		AND  INSTANCE_ID!=$INSTANCE_ID 
		AND  PHYSICAL_LOCATION_ID = $ddlPhysicalLoc
		AND  CLOUD_HOSTED_CD      = $ddlCloudHost
		AND  HOST_MACHINE_IP      = '$txtHostMachine'
		AND ISDELETED=0";
		$row_count = unique($querycount);	


		if($row_count == 0)
		{
			$query="EXEC [IT_INSTANCE_MANAGEMENT_SP] $actionid,$INSTANCE_ID,'$txtInstancename',$ddlPhysicalLoc,$ddlOperatingSytm,$ddlCloudHost,
			$ddlAccessWeb,'$txtHostMachine','$txtDBhost','$txtDBname','$txtDBuser','$txtPasswd','$txtHostowner','$txtMobile',$userid,'$txtRemark'";
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
				if(!empty($INSTANCE_ID))$data['message'] = 'Record successfully updated';
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
function getInstancemanagementData($mysqli){
	try
	{
		$data = array();
        $query = "SELECT INSTANCE_ID,INSTANCE_DESCRIPTION,PHYSICAL_LOCATION_ID,PHYSICAL_LOCATION,OPERATING_SYSTEM_CD,OPERATING_SYSTEM,CLOUD_HOSTED_CD
		,CLOUD_HOSTED,WEB_LINK_ACCESS_CD,WEB_LINK_ACCESS,HOST_MACHINE_IP,DB_HOST,DB_NAME,DB_USERNAME,DB_PASSOWRD,HOSTING_OWNER,MOBILE_NO,REMARKS 
		FROM MEP_INSTANCE_MANAGEMENT 
		WHERE ISDELETED=0 
		ORDER BY INSTANCE_ID";

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
            $INSTANCE_ID = ($_POST['INSTANCE_ID'] == 'undefined' || $_POST['INSTANCE_ID'] == '') ? 0 : $_POST['INSTANCE_ID'];
			if($INSTANCE_ID == 0) throw new Exception('Invalid INSTANCE-ID.');
			$query = "EXEC [IT_INSTANCE_MANAGEMENT_SP] 3,$INSTANCE_ID,'',0,0,0,0,'','','','','','','',$userid,''";
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






