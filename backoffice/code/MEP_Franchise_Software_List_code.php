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
		case "getSoftwarepurposeData":getSoftwarepurposeData($conn);break;
		case "getsoftwarepurpose":getsoftwarepurpose($conn);break;
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



// Get getsoftwarepurpose
function getsoftwarepurpose($mysqli){
	try
	{
		$data = array();
		
		$query = "SELECT CODE_DETAIL_ID,CODE_DETAIL_DESC FROM MEP_CODE_DETAILS where code_id=17 and isdeleted=0";
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


// SAVE
 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$data = array();
		$SOFTWARE_LIST_ID  = ($_POST['SOFTWARE_LIST_ID'] == 'undefined' || $_POST['SOFTWARE_LIST_ID'] == '') ? 0 : $_POST['SOFTWARE_LIST_ID'];
		$txtSoftwarename  = ($_POST['txtSoftwarename'] == 'undefined' || $_POST['txtSoftwarename'] == '') ? '' : $_POST['txtSoftwarename'];
        $ddlSoftwarePurpose  = ($_POST['ddlSoftwarePurpose'] == 'undefined' || $_POST['ddlSoftwarePurpose'] == '') ? 0 : $_POST['ddlSoftwarePurpose'];
        $txtSoftwareversion  = ($_POST['txtSoftwareversion'] == 'undefined' || $_POST['txtSoftwareversion'] == '') ? '' : $_POST['txtSoftwareversion'];
        $txtLicenceinfo  = ($_POST['txtLicenceinfo'] == 'undefined' || $_POST['txtLicenceinfo'] == '') ? '' : $_POST['txtLicenceinfo'];
		$txtLogininfo  = ($_POST['txtLogininfo'] == 'undefined' || $_POST['txtLogininfo'] == '') ? '' : $_POST['txtLogininfo'];
		$txtPassword  = ($_POST['txtPassword'] == 'undefined' || $_POST['txtPassword'] == '') ? '' : $_POST['txtPassword'];
		$ddlMastertask  = ($_POST['ddlMastertask'] == 'undefined' || $_POST['ddlMastertask'] == '') ? 0 : $_POST['ddlMastertask'];
		$txtRemark  = ($_POST['txtRemark'] == 'undefined' || $_POST['txtRemark'] == '') ? '' : $_POST['txtRemark'];
		
		
		$actionid = $SOFTWARE_LIST_ID == 0 ? 1 : 2;
		
		
		if($ddlSoftwarePurpose == 0)
		{throw new Exception("Please Select Software Purpose.");}
	
		
		// $sql = "SELECT * FROM MEP_FRANCHISE_SOFTWARE_LIST WHERE FRANCHISE_NAME=$ddlfranchiseID AND TASK_DESC='$txtTask' AND TASK_ID!=$TASK_ID AND ISDELETED=0";
		// $data['$query'] = $sql;
		// echo json_encode($data);exit;
		// $row_count = unique($sql);
		
		
		// if($row_count == 0)
		// {
			$query="EXEC [FRANCHISE_SOFTWARE_LIST_SP] $actionid,$SOFTWARE_LIST_ID,'$txtSoftwarename',$ddlSoftwarePurpose,'$txtSoftwareversion','$txtLicenceinfo',
			'$txtLogininfo','$txtPassword',$userid,'$txtRemark', $ddlMastertask";
			$data['query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
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
				if(!empty($SOFTWARE_LIST_ID))$data['message'] = 'Record successfully updated';
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


/*============ GET SOFTWARE PURPOSE DATA =============*/ 
function getSoftwarepurposeData($mysqli){
	try
	{
		$data = array();

		$query = "SELECT SOFTWARE_LIST_ID,SOFTWARE_NAME,SOFTWARE_PURPOSE_ID,SOFTWARE_PURPOSE,
		SOFTWARE_VERSION,LICENSE_INFO,LOGIN_USER_NAME,LOGIN_PASSWORD,MASTER_TASK_CD,MASTER_TASK_DESC,REMARKS 
		from MEP_FRANCHISE_SOFTWARE_LIST
		WHERE ISDELETED=0
		ORDER BY SOFTWARE_LIST_ID";
		

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
            $SOFTWARE_LIST_ID = ($_POST['SOFTWARE_LIST_ID'] == 'undefined' || $_POST['SOFTWARE_LIST_ID'] == '') ? 0 : $_POST['SOFTWARE_LIST_ID'];
			if($SOFTWARE_LIST_ID == 0) throw new Exception('Invalid Item ID.');
			$query="EXEC [FRANCHISE_SOFTWARE_LIST_SP] 3,$SOFTWARE_LIST_ID,'',0,'','','','',$userid,'',0";

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





