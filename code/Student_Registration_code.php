<?php
session_start();
require_once 'connection.php';




if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "getPlanCountryName":getPlanCountryName($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get Plan Country Name =============*/ 
function getPlanCountryName($mysqli){
	try
	{
		$ENROLL_LOCID = ($_POST['ENROLL_LOCID'] == 'undefined' || $_POST['ENROLL_LOCID'] == '') ? 0 : $_POST['ENROLL_LOCID'];
		$ENROLL_PLANID = ($_POST['ENROLL_PLANID'] == 'undefined' || $_POST['ENROLL_PLANID'] == '') ? 0 : $_POST['ENROLL_PLANID'];

		if($ENROLL_LOCID > 0){
			$query = "SELECT PLANNAME,COUNTRYID FROM
			(SELECT (SELECT  PLANNAME FROM PLANS WHERE PLANID=$ENROLL_PLANID) AS PLANNAME,
			(SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=$ENROLL_LOCID) AS COUNTRYID) TT";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$data['PLANNAME'] = $row['PLANNAME'];
				$data['COUNTRYID'] = $row['COUNTRYID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
			$data ['$query '] = $query ;
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




function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['MEP_USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
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







