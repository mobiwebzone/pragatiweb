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
        case "saveUsers":saveUsers($conn);break;
        case "getUsers":getUsers($conn);break;
        case "getLocations":getLocations($conn);break;
        case "deleteUsers":deleteUsers($conn);break;
		case "getOrganization":getOrganization($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveUsers($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $uid  = ($_POST['uid'] == 'undefined' || $_POST['uid'] == '') ? 0 : $_POST['uid'];
        $txtFName  = $_POST['txtFName'] == 'undefined' ? '' : $_POST['txtFName'];
        $txtLName  = $_POST['txtLName'] == 'undefined' ? '' : $_POST['txtLName'];
        $txtMobile  = $_POST['txtMobile'] == 'undefined' ? '' : $_POST['txtMobile'];
        $txtEmail  = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
        $txtLoginId  = $_POST['txtLoginId'] == 'undefined' ? '' : $_POST['txtLoginId'];
        $txtPWD  = $_POST['txtPWD'] == 'undefined' ? '' : $_POST['txtPWD'];
        $ddlUserRole  = $_POST['ddlUserRole'] == 'undefined' ? '' : $_POST['ddlUserRole'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlSupervisor  = ($_POST['ddlSupervisor'] == 'undefined' || $_POST['ddlSupervisor'] == '') ? 0 : $_POST['ddlSupervisor'];
        $ddlBrand  = ($_POST['ddlBrand'] == 'undefined' || $_POST['ddlBrand'] == '') ? 0 : $_POST['ddlBrand'];
		$TEXT_ORG_ID  = ($_POST['TEXT_ORG_ID'] == 'undefined' || $_POST['TEXT_ORG_ID'] == '') ? 0 : $_POST['TEXT_ORG_ID'];
		$actionid = $uid == 0 ? 1 : 2;

		if($txtFName == ''){throw new Exception("Enter First Name.");}
		if($txtLName == ''){throw new Exception("Enter Last Name.");}
		if($txtMobile == ''){throw new Exception("Enter Mobile No.");}
		if($txtEmail == ''){throw new Exception("Enter Email ID.");}
		if($txtLoginId == ''){throw new Exception("Enter Login ID.");}
		if($txtPWD == ''){throw new Exception("Enter Password.");}
		if($ddlUserRole == ''){throw new Exception("Select User Role.");}
		if($ddlLocation == 0){throw new Exception("Select Location.");}

		$sql = "SELECT * FROM USERS WHERE LOGINID='$txtLoginId' AND UID!=$uid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [USERS_SP] $actionid,$uid,'$txtFName','$txtLName','$txtMobile','$txtEmail','$txtLoginId','$txtPWD',$ddlLocation,'$ddlUserRole',$ddlSupervisor,$ddlBrand,$userid,$TEXT_ORG_ID";
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
				if(!empty($uid))$data['message'] = 'Record successfully updated';
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


/*============ Get Users =============*/ 
 function getUsers($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT [UID],FIRSTNAME,LASTNAME,MOBILE,EMAIL,LOGINID,DBO.GET_CLEAR_USER_PASSWORD([UID]) PWD,USERROLE,
		(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=U.LOCID)[LOCATION],LOCID,SUPERVISOR,
		ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=U.SUPERVISOR),'')SUPERVISOR_NAME,
		ORG_ID
		FROM USERS U WHERE ISDELETED=0 AND LOCID=$ddlLocation";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['UID'] = (int) $row['UID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

function getOrganization($mysqli){
	try
	{
		global $userid;

        $query = "SELECT ORG_ID, ORG_NAME  FROM ORGANIZATION
                  WHERE ISDELETED =0  ";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ORG_ID'] = (int) $row['ORG_ID'];
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


/*============ Get Locations =============*/ 
 function getLocations($mysqli){
	try
	{
		if($_SESSION['USER_LOCID'] == '1')
		{
			$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 ORDER BY LOCATION";
		}
		else{
			$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=".$_SESSION['USER_LOCID']."";
		}

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
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
function deleteUsers($mysqli){
	try{   
			global $userid;
			$data = array();     
            $uid = ($_POST['uid'] == 'undefined' || $_POST['uid'] == '') ? 0 : $_POST['uid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [USERS_SP] 3,$uid,'','','','','','',0,'',0,0,$userid,'' ");
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







